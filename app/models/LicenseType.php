<?php

class LicenseType extends Elegant
{
	protected $table = 'license_types';
	protected $rules = array(
		'name' => 'required|unique:license_types',
	);
	protected $errors;
	
	public static function withParams($params)
	{
		$instance = new self();
		foreach($params as $key => $value) {
			$instance->$key = $value;
		}
		return $instance;
	}
	
	public function license()
    {
        return $this->hasMany('License', 'type_id');
    }

    public function users()
    {
        return $this->belongsToMany('User', 'licensetype_user');
    }
	
	public function validate($data)
	{
		$v = Validator::make($data, $this->rules);
		
		//check for failure
		if($v->fails()) {
			$this->errors = $v->messages();
			return false;
		}
		return true;
	}
	
	public function getErrors()
	{
		return $this->errors;
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