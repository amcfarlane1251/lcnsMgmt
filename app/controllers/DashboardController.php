<?php namespace Controllers;

use AdminController;
use Asset;
use Actionlog;
use DB;
use License;
use LicenseType;
use Sentry;
use View;

class DashboardController extends \BaseController
{

    public function dashboardRouter(){
        $user = Sentry::getUser();
        $ec = $user->filterRoles();

        if ($user->hasAccess('admin')){
            return View::make('backend/adminDashboard')->with('ec',$ec);
        }
        else{
            return $this->getIndex();
        }
    }
    /**
     * Show the administration dashboard page.
     *
     * @return View
     */
    public function getIndex($roleId = null)
    {
        $user = Sentry::getUser();

        (is_null($roleId) ? $roleId = $user->role_id : '');

        // get license info
        $licenseObj = new License();
        $licenses = $licenseObj->populateDashboard($roleId);

        //get asset info
        $assetObj = new Asset();
        $assets = $assetObj->populateDashboard($roleId);

        return View::make('backend/dashboard')->with('licenses',$licenses)->with('assets',$assets);
    }

}
