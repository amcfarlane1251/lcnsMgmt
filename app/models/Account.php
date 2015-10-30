<?php
class Account extends Elegant
{

	protected $table = 'accounts';
	public $timestamps = false;

	public static function withParams(array $params)
	{
		$instance = new self();
		foreach($params as $key => $value) {
			$instance->$key = $value;
		}
		return $instance;
	}

	public function validation()
	{
		//check if username exists
		$validator = Validator::make(array("username"=>$this->username, "firstName"=>$this->first_name, "lname"=>$this->last_name), 
					  array('username' => array('unique:accounts,username', 'required'), 'firstName'=>'required', 'lname'=>'required'));
		if($validator->fails()) {
			return $validator->messages();
		}
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