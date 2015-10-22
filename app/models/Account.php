<?php
class Account extends Elegant
{

	protected $table = 'accounts';

	public $timestamps = false;

	public function store($values) {
		foreach($values as $key => $value) {
			$this->$key = $value;
		}
		$this->save();
	}

}