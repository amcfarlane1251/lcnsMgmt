<?php
class Requests extends Elegant
{

	protected $table = 'requests';

	public function owner(){
		return $this->belongsTo('User', 'user_id');
	}

	public function roles(){
		return $this->belongsTo('Role', 'role_id');
	}

	public function licenseTypes(){
		return $this->belongsToMany('LicenseType', 'licensetype_request', 'request_id', 'type_id');
	}
}