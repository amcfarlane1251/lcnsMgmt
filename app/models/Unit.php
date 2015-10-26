<?php

class Unit extends Elegant
{
	protected $table = 'units';

	public function users()
	{
		return $this->hasMany('Requests');
	}
}