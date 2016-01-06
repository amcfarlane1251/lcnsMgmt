<?php
class Requests extends Elegant
{

	protected $table = 'requests';
	public $errors;

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
	
	public function account() {
		return $this->belongsTo('Account', 'account_id', 'id');
	}

	public function authorizer() {
		return $this->belongsTo('User', 'authorizer_id', 'id');
	}
	
	public function roles()
	{
		return $this->belongsTo('Role', 'role_id');
	}

	public function unit()
	{
		return $this->belongsTo('Unit', 'unit_id');
	}

	protected function accountCreator()
	{
		return $this->belongsTo('Account', 'id', 'created_from');
	}

	public function licenseTypes()
	{
		return $this->belongsToMany('LicenseType', 'licensetype_request', 'request_id', 'type_id');
	}

	//get the license seat associated with a request -> this is for checkin or move requests
	public function licenseSeat()
	{
		return $this->belongsTo('LicenseSeat', 'license_id');
	}

	public static function count($type, $roleId)
	{
		( $roleId!=1 ? $reqCode='' : $reqCode=1 );
		$query = DB::table('requests')->where('request_code', '=', $reqCode)->where('type', $type);
		if($roleId!=1) {
			$query->where('role_id', $roleId);
		}
		return $query->count();
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
			$this->errors = $validator->messages();
			return false;
		}
		return true;
	}
	
	/*
	 * Validate an existing asset
	 */
	public function validateAsset($account)
	{
		//check if asset belongs to the account it has been requested for
		if($this->pc_name) {
			//if a move request, allow moving to the same resource for a different account
			if($this->type=='move' && ( LicenseSeat::find($this->license_id)->asset->asset_tag == $this->pc_name)){
				return true;
			}
			
			//if existing asset perform validation. If it's new, return true
			$asset = Asset::findByName($this->pc_name);
			
			if($asset && $asset->assignedTo){
				if($asset->assignedTo->first_name.$asset->assignedTo->last_name != $account->first_name.$account->last_name) {
					// New MessageBag
					$errorMessages = new Illuminate\Support\MessageBag;
					$errorMessages->add('pcName', 'Computer Name already belongs to a different user');
					$this->errors = $errorMessages;
					return false;
				}
				return true;
			}
			else{
				return true;
			}
		}
		else{
			return true;
		}
	}

	public static function retrieve($roleId = null, $unitId = null, $type)
	{	
		$query = Requests::where('type', $type);
		
		if($roleId) {
			$query->where('role_id', $roleId);
		}
		if($unitId) {
			$query->where('unit_id', $unitId);
		}
		
		return $query->orderBy('created_at','desc')->get();
	}
	

	public function store($lcnsTypes, $userStatus, $account)
	{	
		if($userStatus=='existing') {
			//overide account
			$account = Account::where('username', $account->username)->first();
			$this->account_id = $account->id;
			$this->dbStore();
		}
		else{
			$id = $account->store();
			
			$this->account_id = $id;
			$account->created_from = $this->dbStore();
			$account->save();
		}

		//license types to be added
		foreach($lcnsTypes as $lcnsType){
			if(!in_array($lcnsType, $this->licenseTypes()->lists('id'))) {
				$this->licenseTypes()->attach($lcnsType);
			}
		}
		//license types to be removed
		foreach($this->licenseTypes()->lists('id') as $id){
			if(!in_array($id, $lcnsTypes)) {
				$this->licenseTypes()->detach($id);
			}
		}

		$success = Lang::get('admin/request/message.success.create');
		return array('success'=>1,'message'=>$success,'type'=>$this->type);
	}
	
	public function dbStore()
	{
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
		//DLN authorizers
		if(Sentry::getUser()->inGroup($authorizers)){
			$this->request_code = 1;
			$this->save();
			return array('success'=>1,'message'=>'Request Approved','type'=>$this->type);
		}
		//BMO authorizers
		elseif(Sentry::getUser()->inGroup($admins)){
			//get all license types for request
			$lcnsNames = $this->licenseTypes()->lists('name');
			//get available license seats for each license type
			$that = $this;
			$toAdd = [];

			if($this->type=='license'){
				//TODO:: remove and place in seperate method - this will need to be used to update a request
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
					$seat = LicenseSeat::find($lcnsSeat->id);
					//create/retreive asset and check it out to the account the request is for
					$assetId = $this->asset($seat);
					//checkout license seat to the asset account the request has been made for
					$seat->checkOut($this, $assetId);
				}
			}
			elseif($this->type=='checkin') {
				$seat = LicenseSeat::find($this->license_id);
				$seat->checkIn();
				if($this->pc_name) {
					$asset = Asset::findByName($this->pc_name);
					$asset->checkIn();
				}
			}
			elseif($this->type=='move') {
				$seat = LicenseSeat::find($this->license_id);
				$assetId = $this->asset($seat);

				$seat->checkOut($this, $assetId);
			}

			//detach requested licenses
			$this->licenseTypes()->detach();
			$type = $this->type;
			//marked as closed
			$this->delete();

			return array('success'=>1,'message'=>'Request Approved','type'=>$type);
		}
	}

	private function asset($seat)
	{
		$type = $seat->getLicenseType();

		if($type->asset_flag) {
			//create computer name as an asset if it doesnt exist
			if($obj = DB::table('assets')->where('serial', $this->pc_name)->first(array('id'))){
				$asset = Asset::find($obj->id);
				//TODO: most likely put a check in here to see if asset is already assigned to a user and return error
			}
			else{
				$asset = new Asset();
				$asset->name = "DWAN PC";
				$asset->serial = $this->pc_name;
				$asset->asset_tag = $this->pc_name;
				$asset->model_id = 7; //TODO: Remove this hard coding for model id
				$asset->status_id = 1;
			}
			$asset->assigned_to = $this->account->id;
			$asset->role_id = $this->role_id;
			$asset->unit_id = $this->unit_id;
			$asset->save();
			
			return $asset->id;
		}
		else{
			return null;
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