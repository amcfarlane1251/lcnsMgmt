<?php
class Account extends Elegant
{

	protected $table = 'accounts';
	public $errors;
	public $timestamps = false;
	
	public static function withParams(array $params)
	{
		$instance = new self();
		foreach($params as $key => $value) {
			$instance->$key = $value;
		}
		return $instance;
	}
	
	public static function getByUsername($username)
	{
		
	}
	
	public function empType()
	{
		return $this->belongsTo('EmpType', 'emp_type_id');
	}
	
	public function unit()
	{
		return $this->belongsTo('Unit', 'unit_id');
	}
	
	public function role()
	{
		return $this->belongsTo('Role', 'role_id');
	}
	
	public function location()
	{
		return $this->belongsTo('Location', 'location_id');
	}

	public function validation()
	{
		//check if username exists
		$validator = Validator::make(array("username"=>$this->username, "firstName"=>$this->first_name, "lastName"=>$this->last_name), 
					  array('username' => array('unique:accounts,username'), 'firstName'=>'required', 'lastName'=>'required'));
		if($validator->fails()) {
			$this->errors = $validator->messages();
			return false;
		}
		return true;
	}

	public function store()
	{
		try{
			$this->save();
		}
		catch(Exception $e){
    		echo 'Caught exception: ',  $e->getMessage(), "\n";
    	}
		return $this->id;
	}
}