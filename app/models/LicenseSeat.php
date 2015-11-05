<?php

class LicenseSeat extends Elegant
{
	use SoftDeletingTrait;
    protected $dates = ['updated_at','deleted_at'];
    protected $guarded = 'id';
    protected $table = 'license_seats';

    public function license()
    {
        return $this->belongsTo('License','license_id');
    }

    public function user()
    {
        return $this->belongsTo('User','assigned_to')->withTrashed();
    }

    public function account()
    {
        return $this->belongsTo('Account', 'assigned_to')->select(array('id','first_name','last_name'));
    }

    public function asset()
    {
        return $this->belongsTo('Asset','asset_id')->withTrashed();
    }

    //get a request associated with the license seat -> this is for checkin or move requests
    public function request()
    {
        return $this->belongsTo('Requests','id', 'license_id');
    }

    public static function checkIn($seat)
    {
        $user = Sentry::getUser();
        $request = new Requests();
        $params = array(
            'license_id' => $seat->id,
            'user_id' => $user->id,
            'role_id' => $seat->license->role_id,
            'unit_id' => $user->unit_id,
            'type' => 'license',
        );

        if( $request->store($params) ) {
            return array('success'=>1, 'message'=>Lang::get('license:checkin:success'));
        }
    }
}
