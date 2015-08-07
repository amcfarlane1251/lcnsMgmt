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
        if ($user->hasAccess('admin')){
            return $this->getAdminIndex();
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
    public function getIndex()
    {
        $user = Sentry::getUser();
        // get license info
        $licenseObj = new License();
        $lcnsTypes = LicenseType::lists('name', 'id');
        $licenses = array();
        //get the total amount of licenses
        $totalAlloc = $licenseObj->countTotalByRole($user->role_id);
        $totalUsed = $licenseObj->countUsedByRole($user->role_id);
        $totalRemaining = $licenseObj->countRemainingByRole($user->role_id);

        //get asset info
        $allAssets = DB::table('models')
                    ->join('assets', 'assets.model_id', '=','models.id')
                    ->orwhere(function ($query) use ($user) {
                        $query->where('assets.role_id', $user->role_id);
                    })->get();
        $assets = array();

        foreach($lcnsTypes as $key => $type){
            $allocated = $licenseObj->countTotalByType($key, $user->role_id);
            $used = $licenseObj->countUsedByType($key, $user->role_id);
            $remaining = $licenseObj->countRemainingByType($key, $user->role_id);

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
