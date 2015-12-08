<?php namespace Controllers;

use AdminController;
use Asset;
use Actionlog;
use DB;
use Illuminate\Http\Request as HttpRequest;
use Input;
use License;
use LicenseType;
use Role;
use Sentry;
use Unit;
use View;

class DashboardController extends \BaseController
{
	public function __construct(HttpRequest $request){
		$this->httpRequest = $request;
	}
	
    public function dashboardRouter(){
        $user = Sentry::getUser();
        $ec = $user->filterRoles();

        if ($user->hasAccess('authorize')){
            return View::make('backend/adminDashboard')->with('ec',$ec)->with('user', $user);
        }
        else{
            return $this->getIndex($user->role_id, $user);
        }
    }
    /**
     * Show the administration dashboard page.
     *
     * @return View
     */
    public function getIndex($roleId, $user = null)
    {
		if($user && $user->inGroup(Sentry::findGroupByName('Requestors'))) { 
			$unitId = $user->unit_id;
		}
		else{
			$unitId = input::get('unitId');
		}
		
		if(is_null($unitId)){
			$unit = Unit::select(array('id', 'name'))->where('role_id', $roleId)->first();
			$unitId = $unit->id;
		}
		else{
			$unit = Unit::select(array('id', 'name'))->where('id', $unitId)->first();
		}
        // get license info
        $licenseObj = new License();
        $licenses = $licenseObj->populateDashboard($roleId, $unitId);

        //get asset info
        $assetObj = new Asset();
        $assets = $assetObj->populateDashboard($roleId);
		
		//get all the units for the EC
		$role = Role::find($roleId);
		$units = $role->getUnits($user);
		
		if($this->httpRequest->ajax()) {
			header('Content-type: application/json');
			echo json_encode(array('success'=>true,'assets'=>$assets, 'licenses'=>$licenses, 'unit'=>$unit));
		}
		else{
			return View::make('backend/dashboard')->with('licenses',$licenses)->with('assets',$assets)->with('units', $units)->with('unit',$unit);
		}
    }

}
