<?php
namespace Controllers;
use Asset;
use DB;
use Illuminate\Http\Request as HttpRequest;
use Input;
use Lang;
use License;
use Redirect;
use Requests as Request;
use Route;
use Sentry;
use Validator;
use View;
use URL;

class RolesController extends \BaseController {

	public function __construct(HttpRequest $request)
	{
		$this->httpRequest = $request;
	}
	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		//
	}

	public function indexRequests($roleId)
	{	
		//get user role
		$user = Sentry::getUser();
		$role = $user->role->role;

		//get request code
		(Input::get('reqCode') ? $reqCode = Input::get('reqCode') : $reqCode = '');

		if($role != 'All' && $user->role->id != $roleId)
		{
			return Redirect::to('/')->with('error', 'Cannot access that EC');
		}

		$requests = Request::where('request_code',$reqCode)->where('role_id', $roleId)->orderBy('created_at','desc')->get();

		if($this->httpRequest->ajax()){
			//ajax request so return json
			foreach($requests as $key => $request){
				$requests[$key]->requester = $request->owner->first_name." ".$request->owner->last_name;
				$requests[$key]->role = $request->roles->role;
				$requests[$key]->lcnsTypes =  $request->licenseTypes;

				if($requests[$key]->request_code != 'closed'){
					if($user->hasAccess('admin') || $user->id == $requests[$key]->user_id){
						$requests[$key]->actions = "<a href=".URL::to('request/'.$requests[$key]->id.'/edit')."><i class='fa fa-pencil icon-white'></i></a>";
					}
					if($user->hasAccess('admin')){
						$requests[$key]->actions .= "<a href=" .URL::to('request/'.$request->id.'/approve'). "><i class='fa fa-check icon-white'></i></a>";
					}
				}
			}

			header('Content-type: application/json');
			echo json_encode($requests);
		}
		else{
			//html request
			return View::make('backend.requests.index')->with('requests', $requests)->with('user', $user);
		}		
	}

	public function indexAssets($roleId)
	{
		//get the user
		$user = Sentry::getUser();
		$role = $user->role->role;

		if($role != 'All' && $user->role->id != $roleId)
		{
			return Redirect::to('/')->with('error', 'Cannot access that EC');
		}

		//get the assets
		$assets = Asset::where('role_id', $roleId)->get();

        foreach($assets as $key => $asset){
            $assets[$key]->role = $asset->roles->role;
            $assets[$key]->status = $asset->assetstatus->name;
            ($assets[$key]->rtd_location_id ? $assets[$key]->location = $asset->defaultLoc->name : $assets[$key]->location = '');
            
            if (($assets[$key]->assigned_to !='') && ($assets[$key]->assigned_to > 0))
            {
                $assets[$key]->inOut = '<a href="'.route('checkin/hardware', $assets[$key]->id).'" class="btn btn-primary btn-sm">'.Lang::get('general.checkin').'</a>';
            } 
            else 
            {
                $assets[$key]->inOut = '<a href="'.route('checkout/hardware', $assets[$key]->id).'" class="btn btn-info btn-sm">'.Lang::get('general.checkout').'</a>';
            }
            if ($assets[$key]->deleted_at=='')
            {
                $assets[$key]->actions = '<a href="'.route('update/hardware', $assets[$key]->id).'" class="btn btn-warning btn-sm"><i class="fa fa-pencil icon-white"></i></a>';
                if($user->hasAccess('admin')){ $assets[$key]->actions .= ' <a data-html="false" class="btn delete-asset btn-danger btn-sm" data-toggle="modal" href="'.route('delete/hardware', $assets[$key]->id).'" data-content="'.Lang::get('admin/hardware/message.delete.confirm').'" data-title="'.Lang::get('general.delete').' '.htmlspecialchars($assets[$key]->asset_tag).'?" onClick="return false;"><i class="fa fa-trash icon-white"></i></a>';}
            }
        }

        if ($this->httpRequest->ajax()){
            header('Content-Type: application/json');
            echo json_encode($assets);
        }
        else{
            $heading = Lang::get('admin/hardware/general.all') . " - ".$user->role->role;
            return View::make('backend/hardware/listing')->with('heading', $heading)->with('assets', $assets);
        }
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		//
	}


	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
		//
	}


	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($roleId, $resourceId)
	{
		
	}


	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
		//
	}


	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
		//
	}


	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
		//
	}


}
