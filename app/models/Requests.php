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

	public function owner(){
		return $this->belongsTo('User', 'user_id');
	}

	public function roles(){
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

	public function licenseTypes(){
		return $this->belongsToMany('LicenseType', 'licensetype_request', 'request_id', 'type_id');
	}

	public static function count($type)
	{
		return DB::table('requests')->where('request_code', '!=', 'closed')->where('type', $type)->count();
	}

	public static function openReqCount()
	{
		return DB::table('requests')->where('request_code', '!=', 'closed')->count();
	}

	public static function closedReqCount()
	{
		return DB::table('requests')->where('request_code', 'closed')->count();
	}

	public function createForAccount($values) {
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