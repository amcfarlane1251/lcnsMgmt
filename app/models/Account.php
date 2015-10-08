<?php
class Account extends Elegant
{

	protected $table = 'users';

	public function owner() {
		return $this->belongsTo('Request', 'request_id');
	}

	public function role() {
		return $this->belongsTo('Role', 'role_id');
	}

	public function createAccount($values) {
		foreach($values as $key => $value) {
			$this->$key = $value;
		}
		$this->save();
	}

}