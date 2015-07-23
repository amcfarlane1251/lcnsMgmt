<?php

use Cartalyst\Sentry\Users\Eloquent\User as SentryUserModel;

class User extends SentryUserModel
{
    /**
     * Indicates if the model should soft delete.
     *
     * @var bool
     */
    use SoftDeletingTrait;
	protected $dates = ['deleted_at'];


    /**
     * Returns the user full name, it simply concatenates
     * the user first and last name.
     *
     * @return string
     */
    public function fullName()
    {
        return "{$this->first_name} {$this->last_name}";
    }
    

    /**
     * Returns the user Gravatar image url.
     *
     * @return string
     */
    public function gravatar()
    {
        // Generate the Gravatar hash
        $gravatar = md5(strtolower(trim($this->email)));

        // Return the Gravatar url
        return "//gravatar.com/avatar/{$gravatar}";
    }

    public function assets()
    {
        return $this->hasMany('Asset', 'assigned_to')->withTrashed();
    }
    
     public function accessories()
    {
        return $this->belongsToMany('Accessory', 'accessories_users', 'assigned_to','accessory_id')->withPivot('id')->withTrashed();
    }

    public function licenses()
    {
        return $this->belongsToMany('License', 'license_seats', 'assigned_to', 'license_id')->withPivot('id');
    }

    /**
     * get the license types associated with a user
     * @return belongsToMany
    */
    public function licenseTypes()
    {
        return $this->belongsToMany('LicenseType', 'licensetype_user', 'user_id', 'type_id')->withTimestamps();
    }

    /**
    * Get roles for this user
    **/
    public function role()
    {
        return $this->belongsTo('Role', 'role_id');
    }

    /**
    * Get action logs for this user
    */
    public function userlog()
    {
        return $this->hasMany('Actionlog','checkedout_to')->orderBy('created_at', 'DESC')->withTrashed();
    }

    /**
    * Get the asset's location based on the assigned user
    **/
    public function userloc()
    {
        return $this->belongsTo('Location','location_id')->withTrashed();
    }

    /**
    * Get the user's manager based on the assigned user
    **/
    public function manager()
    {
        return $this->belongsTo('User','manager_id')->withTrashed();
    }
   
    public function accountStatus()
    {
        if ($this->sentryThrottle) {
    	    if ($this->sentryThrottle->suspended==1) {
    		 	return 'suspended';	
    		} elseif ($this->sentryThrottle->banned==1) {
    		 	return 'banned';	
    	 	} else {		 	
    		 	return false;
    	 	}
        } else {
            return false;
        }
    }
    
    public function sentryThrottle() {	    
	    return $this->hasOne('Throttle');
    }
    
    public function scopeGetDeleted($query)
	{
		return $query->withTrashed()->whereNotNull('deleted_at');
	}
	
	public function scopeGetNotDeleted($query)
	{
		return $query->where('activated', '=', 1);
	}

    public function scopeGetRequests($query)
    {
        return $query->where('activated', '=', 0);
    }

    public function filterByRole($content){
        $role = $this->role->role;
        if($role == "All"){
            return $content;
        }

        foreach($content as $key => $value){
            if($value->role){
                if($role != $value->role->role){
                    unset($content[$key]);
                }
            }
        }
        return $content;
    }

    public function filterRoles(){
        if($this->role->role == 'All'){
            $ec = Role::lists('role', 'id');
        }
        else{
            $ec = Role::where('role', '<>', 'All')->lists('role', 'id');
        }

        return $ec;
    }

    public function sysAdmin(){
        if ($this->role->role == 'All'){
            return true;
        }
        else{
            return false;
        }
    }

}
