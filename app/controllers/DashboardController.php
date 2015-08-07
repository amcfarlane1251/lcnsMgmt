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
        error_log(print_R($ec,true));
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
        $lcnsTypes = LicenseType::lists('name', 'id');
        $licenses = array();
        //get the total amount of licenses
        $totalAlloc = $licenseObj->countTotalByRole($roleId);
        $totalUsed = $licenseObj->countUsedByRole($roleId);
        $totalRemaining = $licenseObj->countRemainingByRole($roleId);
        //get asset info
        $allAssets = DB::table('models')
                    ->join('assets', 'assets.model_id', '=','models.id')
                    ->orwhere(function ($query) use ($roleId) {
                        $query->where('assets.role_id', $roleId);
                    })->get();
        $assets = array();

        //populate licenses array
        foreach($lcnsTypes as $key => $type){
            $allocated = $licenseObj->countTotalByType($key, $roleId);
            $used = $licenseObj->countUsedByType($key, $roleId);
            $remaining = $licenseObj->countRemainingByType($key, $roleId);

            $licenses[$key] = new \stdClass();
            $licenses[$key]->name = $type;
            $licenses[$key]->allocated = $allocated;
            $licenses[$key]->used = $used; 
            $licenses[$key]->remaining = $remaining;

            //get percentages for chart
            $data = array(
                'allocated' => ($licenses[$key]->allocated / $totalAlloc) * 100
            );
            $licenses[$key]->percentages = new \ArrayObject($data);

        }

        //populate assets array
        foreach($allAssets as $key => $asset){
            $modelObj = Asset::find($asset->id);
            if($modelObj->assigneduser){
                $location = $modelObj->assetloc->name;
            }
            elseif($modelObj->defaultLoc){
                $location = $modelObj->defaultLoc->name;
            }
            else{
                $location = null;
            }

            $assets[$key] = new \stdClass();
            $assets[$key]->model = $asset->name;
            $assets[$key]->assetTag = $asset->asset_tag;
            $assets[$key]->numOfLcns = $modelObj->licenseSeatsCount();
            $assets[$key]->location = $location;
        }

        return View::make('backend/dashboard')->with('licenses',$licenses)->with('assets',$assets);
    }

}
