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
        return $this->belongsTo('Account', 'assigned_to')->select(array('id','first_name','last_name', 'username'));
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
	
	public function unit()
	{
		return $this->belongsTo('Unit');
	}

    public static function checkInRequest($seat)
    {
        //create a request for the check-in
        $user = Sentry::getUser();
        $params = array(
            'license_id' => $seat->id,
            'user_id' => $user->id,
            'account_id' => $seat->assigned_to,
            'role_id' => $seat->license->role_id,
            'unit_id' => $user->unit_id,
			'pc_name' => ($seat->asset ? $seat->asset->asset_tag : ''),
            'type' => 'checkin',
        );
		$request = Requests::withParams($params);

        if( $request->dbStore() ) {
            return array('success'=>1, 'message'=>Lang::get('license:checkin:success'));
        }
        return array('error'=>1, 'message'=>Lang::get('license:checkin:error'));
    }

    public function checkIn()
    {
        $this->asset_id = NULL;
        $this->assigned_to = NULL;
		$this->unit_id = NULL;
        $this->save();
    }
	
	public function checkOut($request, $assetId)
	{
		$this->asset_id = $assetId;
		$this->assigned_to = $request->account_id;
		$this->unit_id = $request->unit_id;
		try{
			$this->save();
		}
		catch(Exception $e) {
			echo 'Caught exception: ',  $e->getMessage(), "\n";
		}
	}


	public function getLicenseType()
	{
		$typeId = $this->license->type_id;
		return LicenseType::find($typeId);
	}
}
