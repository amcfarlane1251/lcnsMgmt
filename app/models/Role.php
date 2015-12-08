<?php

class Role extends Elegant
{

	protected $table = 'roles';

	public function users(){
		return $this->hasMany('User', 'role_id', 'id');
	}

	public function licenses(){
		return $this->hasMany('License', 'role_id', 'id');
	}

	public static function getRoleById($id){
		return Role::where('id', $id)->lists('id');
	}
	
	public function getUnits($user = null){
		if($user && $user->inGroup(Sentry::findGroupByName('Requestors'))){
			return Unit::where('id',$user->unit_id)->get();
		}
		return Unit::where('role_id', $this->id)->get();
	}
}