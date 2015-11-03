<?php
class Requests extends Elegant
{

	protected $table = 'requests';

	public static function withParams(array $params)
	{
		$instance = new self();
		foreach($params as $key => $value) {
			$instance->$key = $value;
		}
		return $instance;
	}

	public function owner()
	{
		return $this->belongsTo('User', 'user_id');
	}

	public function roles()
	{
		return $this->belongsTo('Role', 'role_id');
	}

	public function unit()
	{
		return $this->belongsTo('Unit', 'unit_id');
	}

	public function account() {
		return $this->belongsTo('Account', 'account_id', 'id');
	}

	protected function accountCreator()
	{
		return $this->belongsTo('Account', 'id', 'created_from');
	}

	public function licenseTypes()
	{
		return $this->belongsToMany('LicenseType', 'licensetype_request', 'request_id', 'type_id');
	}

	public static function count($type, $roleId)
	{
		( $roleId!=1 ? $reqCode='' : $reqCode=1 );
		return DB::table('requests')->where('request_code', '=', $reqCode)->where('type', $type)->count();
	}

	public static function openReqCount()
	{
		return DB::table('requests')->where('request_code', '!=', 'closed')->count();
	}

	public static function closedReqCount()
	{
		return DB::table('requests')->where('request_code', 'closed')->count();
	}

	public function createForAccount($values)
	{
		foreach($values as $key=>$value) {
			$this->$key = $value;
		}
		return $this->save();
	}

	public function validation()
	{
		//check if username exists
		$validator = Validator::make(array("pcName"=>$this->pc_name), 
					  array('pcName' => 'required'));
		if($validator->fails()) {
			return $validator->messages();
		}
	}

	public static function retrieve($roleId = null, $reqCode, $type)
	{	
		if($roleId){
			return Requests::where('request_code',$reqCode)->where('role_id', $roleId)->where('type', $type)->orderBy('created_at','desc')->get();
		}
		else{
			return Requests::where('request_code',$reqCode)->where('type', $type)->orderBy('created_at','desc')->get();
		}
	}

	public function store($values)
	{
		foreach($values as $key=>$value){
			$this->$key = $value;
		}

		try{
			$this->save();
		}
		catch(Exception $e){
    		echo 'Caught exception: ',  $e->getMessage(), "\n";
    	}

    	return $this->id;
	}

	public function approve()
	{	
		//check the users group
		$authorizers = Sentry::findGroupByName('Authorizers');
		$admins = Sentry::findGroupByName('Admin');

		if(Sentry::getUser()->inGroup($authorizers)){
			$this->request_code = 1;
			$this->save();
			return array('success'=>1,'message'=>'Request Approved');
		}
		elseif(Sentry::getUser()->inGroup($admins)){
			//get all license types for request
			$lcnsNames = $this->licenseTypes()->lists('name');

			//get available license seats for each license type
			$that = $this;
			foreach($lcnsNames as $lcnsName){
				$lcnsSeat = DB::table('licenses')
								->join('license_types', 'licenses.type_id', '=','license_types.id')
								->join('license_seats', 'licenses.id', '=','license_seats.license_id')
								->orwhere(function ($query) use ($lcnsName, $that){
									$query
										->where('license_types.name', '=', $lcnsName)
										->where('licenses.role_id', $that->role_id)
										->whereNull('license_seats.assigned_to');
								})->first();
				//if seats available add it to array for later processing, else return with error message
				if($lcnsSeat){
					$toAdd[$lcnsName] = $lcnsSeat;
				}
				else{
					$messageKey = str_replace(' ', '', strtolower($lcnsName));
					$error = Lang::get('request.message_no_lcns.'.$lcnsName);
					return array('success'=>0,'message'=>$error);
				}
			}
			foreach($toAdd as $key=>$lcnsSeat){
				error_log($key);
				if($key == 'SABA Publisher') {
					//create computer name as an asset if it doesnt exist
					if($obj = DB::table('assets')->where('serial', $this->pc_name)->first(array('id'))){
						$asset = Asset::find($obj->id);
					}
					else{
						$asset = new Asset();
						$asset->name = "DWAN PC";
						$asset->serial = $this->pc_name;
						$asset->asset_tag = $this->pc_name;
						$asset->model_id = 7; //TODO: Remove this hard coding for model id
						$asset->status_id = 1;
						$asset->assigned_to = $this->account->id;
					}
					$asset->role_id = $this->role_id;
					$asset->save();
					License::checkOutToAsset($lcnsSeat->id, $asset->id);
				}
				//checkout to account the request has been made for
				License::checkOutToAccount($lcnsSeat->id, $this->account_id);
			}

			//marked as closed
			$this->request_code = 2;
			$this->save();

			//detach requested licenses
			$this->licenseTypes()->detach();

			return array('success'=>1,'message'=>'Request Approved');
		}
	}

	public function deletePrep($id = null)
	{
		if($id){
			$this->find($id);
		}

		$this->licenseTypes()->detach();
		if($account = $this->accountCreator){
			$account->forceDelete();
		}
	}
}