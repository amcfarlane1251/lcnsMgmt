<?php

class License extends Depreciable
{
	use SoftDeletingTrait;
    protected $dates = ['deleted_at'];

    public $timestamps = true;

    protected $guarded = 'id';
    protected $table = 'licenses';
    protected $rules = array(
            'name'   => 'required|alpha_space|min:3|max:255',
            'serial'   => 'required|min:5',
            'seats'   => 'required|min:1|max:10000|integer',
            'note'   => 'alpha_space',
            'notes'   => 'alpha_space|min:0',
            'role_id' => 'required',
            'type_id' => 'required',
        );

    /**
     * Get the assigned user
     */
    public function assignedusers()
    {
        return $this->belongsToMany('User','license_seats','assigned_to','license_id');
    }

    /**
    * Get asset logs for this asset
    */
    public function assetlog()
    {
        return $this->hasMany('Actionlog','asset_id')
            ->where('asset_type', '=', 'software')
            ->orderBy('created_at', 'desc');
    }

    /**
    * Get uploads for this asset
    */
    public function uploads()
    {
        return $this->hasMany('Actionlog','asset_id')
            ->where('asset_type', '=', 'software')
            ->where('action_type', '=', 'uploaded')
            ->whereNotNull('filename')
            ->orderBy('created_at', 'desc');
    }


    /**
    * Get admin user for this asset
    */
    public function adminuser()
    {
        return $this->belongsTo('User','user_id');
    }

    /**
    * Get total licenses
    */
     public static function assetcount()
    {
        return DB::table('license_seats')
                    ->whereNull('deleted_at')
                    ->count();
    }


    /**
    * Get total licenses
    */
     public function totalSeatsByLicenseID()
    {
        return DB::table('license_seats')
        			->where('license_id', '=', $this->id)
                    ->whereNull('deleted_at')
                    ->count();
    }


    /**
    * Get total licenses not checked out
    */
     public static function availassetcount()
    {
        return DB::table('license_seats')
                    ->whereNull('assigned_to')
                    ->whereNull('asset_id')
                    ->whereNull('deleted_at')
                    ->count();
    }

    /**
     * Get the number of available seats
     */
    public function availcount()
    {
        return DB::table('license_seats')
                    ->whereNull('assigned_to')
                    ->whereNull('asset_id')
                    ->where('license_id', '=', $this->id)
                    ->whereNull('deleted_at')
                    ->count();
    }

    /**
     * Get the number of assigned seats
     *
     */
    public function assignedcount()
    {

		return LicenseSeat::where('license_id', '=', $this->id)
			->where( function ( $query )
			{
			$query->whereNotNull('assigned_to')
			->orWhereNotNull('asset_id');
			})
		->count();


    }

    public function remaincount()
    {
    	$total = $this->totalSeatsByLicenseID();
        $taken =  $this->assignedcount();
        $diff =   ($total - $taken);
        return $diff;
    }

    /**
     * Get the total number of seats
     */
    public function totalcount()
    {
        $avail =  $this->availcount();
        $taken =  $this->assignedcount();
        $diff =   ($avail + $taken);
        return $diff;
    }

    /**
     * Get license seat data
     */
    public function licenseseats()
    {
        return $this->hasMany('LicenseSeat');
    }

    /**
     * Get license type data
     */
    public function licenseType()
    {
        return $this->belongsTo('LicenseType', 'type_id', 'id');
    }

    public function role()
    {
        return $this->belongsTo('Role', 'role_id');
    }

    public function supplier()
    {
        return $this->belongsTo('Supplier','supplier_id');
    }

    public function checkIn($seatId){
        DB::table('license_seats')
            ->where('id', '=', $seatId)
            ->update(array('assigned_to' => NULL));
    }

    public static function checkOutToAsset($seatId, $assetId){
        DB::table('license_seats')
            ->where('id', '=', $seatId)
            ->update(array('asset_id' => $assetId, 'updated_at'=>DB::raw('NOW()')));
    }

    public static function checkOutToAccount($seatId, $accountId, $unitId){
        DB::table('license_seats')
            ->where('id', '=', $seatId)
            ->update(array('assigned_to' => $accountId, 'unit_id' => $unitId));
    }

    public function populateDashboard($roleId, $unitId)
    {        
        $licenses = array();
        $lcnsTypes = LicenseType::lists('name', 'id');
        //get the total amount of licenses
        $totalAlloc = $this->countTotalByRole($roleId, $unitId);
        $totalUsed = $this->countUsedByRole($roleId, $unitId);
        $totalRemaining = $this->countRemainingByRole($roleId, $unitId);

        foreach($lcnsTypes as $key => $type){
            $allocated = $this->countTotalByType($key, $roleId);
            $used = $this->countUsedByType($key, $roleId, $unitId);
            $remaining = $this->countRemainingByType($key, $roleId);

            $licenses[$key] = new \stdClass();
            $licenses[$key]->name = $type;
            $licenses[$key]->allocated = $allocated;
            $licenses[$key]->used = $used; 
            $licenses[$key]->remaining = $remaining;

            //get percentages for chart
            if($totalAlloc == 0){$percentAlloc = 25;}else{$percentAlloc = ($licenses[$key]->allocated / $totalAlloc) * 100;}

            $data = array(
                'allocated' => $percentAlloc
            );
            $licenses[$key]->percentages = new \ArrayObject($data);
        }
        return $licenses;
    }

    public function countTotalByType($typeId, $roleId = null, $unitId = null){
        return DB::table('licenses')
            ->join('license_seats', 'licenses.id', '=', 'license_seats.license_id')
            ->orwhere(function($query) use ($typeId, $roleId){
                $query->where('licenses.type_id', $typeId)
                      ->where('licenses.role_id', $roleId);
            })->count();
    }

    public function countUsedByType($typeId, $roleId = null, $unitId = null){
        return DB::table('licenses')
            ->join('license_seats', 'licenses.id', '=', 'license_seats.license_id')
            ->orwhere(function($query) use ($typeId, $roleId, $unitId){
                $query->where('licenses.type_id', $typeId)
                      ->where('licenses.role_id', $roleId)
					  ->where('license_seats.unit_id', $unitId)
                      ->whereNotNull('license_seats.assigned_to');
            })->count();
    }

    public function countRemainingByType($typeId, $roleId = null){
        return DB::table('licenses')
            ->join('license_seats', 'licenses.id', '=', 'license_seats.license_id')
            ->orwhere(function($query) use ($typeId, $roleId){
                $query->where('licenses.type_id', $typeId)
                      ->where('licenses.role_id', $roleId)
                      ->whereNull('license_seats.assigned_to')
                      ->whereNull('license_seats.asset_id');
            })->count();
    }

    public function countTotalByRole($roleId){
        return DB::table('licenses')
            ->join('license_seats', 'licenses.id', '=', 'license_seats.license_id')
            ->orwhere(function($query) use ($roleId){
                $query->where('licenses.role_id', $roleId);
            })->count();
    }

    public function countUsedByRole($roleId, $unitId = null){
        return DB::table('licenses')
            ->join('license_seats', 'licenses.id', '=', 'license_seats.license_id')
            ->orwhere(function($query) use ($roleId, $unitId){
                $query->where('licenses.role_id', $roleId)
					  ->where('license_seats.unit_id', $unitId)
					  ->whereNotNull('license_seats.assigned_to');
            })->count();
    }

    public function countRemainingByRole($roleId){
        return DB::table('licenses')
            ->join('license_seats', 'licenses.id', '=', 'license_seats.license_id')
            ->orwhere(function($query) use ($roleId){
                $query->where('licenses.role_id', $roleId)
					  ->whereNull('license_seats.assigned_to');
            })->count();
    }

    public static function getByRole($roleId, $unitId = null)
    {
        $query = DB::table('licenses')
                        ->join('license_seats', 'licenses.id', '=', 'license_seats.license_id')
                        ->where('licenses.role_id', $roleId)
                        ->orderBy('license_seats.updated_at', 'DESC')
                        ->orderBy('name', 'ASC')
                        ->select('licenses.name', 'license_seats.*');
		if(isset($unitId)) {$query->where('license_seats.unit_id', $unitId);}
		$licenses = $query->get();
        $lcnsObj = array();

        foreach($licenses as $key => $lcns)
        {
            $lcnsObj[$key] = new \stdClass();
            $lcnsObj[$key]->id = $lcns->id;
            $lcnsObj[$key]->name = $lcns->name;
            $lcnsObj[$key]->assignedUser = '';
            $lcnsObj[$key]->assignedAsset = '';
            $lcnsObj[$key]->request = '';
            $lcnsObj[$key]->updatedAt = $lcns->updated_at;
            //get assigned user and assigned asset, if applicable
            $seat = LicenseSeat::find($lcns->id);

            if($user = $seat->account) {
				$lcnsObj[$key]->assignedUserId = $user->id;
                $lcnsObj[$key]->assignedUser = $user->first_name." ".$user->last_name;
            }
            if($asset = $seat->asset) {
                $lcnsObj[$key]->assignedAsset = $asset->name." - ".$asset->asset_tag;
            }
            if($request = $seat->request) {
                if($request->request_code != 2){$lcnsObj[$key]->request = $request;}
            }
        }

        return $lcnsObj;
    }

    /**
     * Filter licenses by specified role
     **/
    public function filterByRole($roleId)
    {
        return DB::table('licenses')
            ->where('roleid', $roleId)
            ->get();
    }
}
