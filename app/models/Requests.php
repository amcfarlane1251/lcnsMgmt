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

	public function account() {
		return $this->belongsTo('User', 'id', 'request_id');
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
}