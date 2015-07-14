<?php

class LicenseType extends Elegant
{
	protected $table = 'license_types';

	public function license()
    {
        return $this->hasMany('License', 'type_id');
    }

    public function users()
    {
        return $this->belongsToMany('User', 'licensetype_user');
    }
}