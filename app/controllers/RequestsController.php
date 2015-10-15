<?php
namespace Controllers;

use Account;
use Asset;
use DB;
use Illuminate\Http\Request as HttpRequest;
use Input;
use Lang;
use License;
use Redirect;
use Requests as Request;
use Response;
use Sentry;
use Validator;
use View;
use URL;
use User;

class RequestsController extends \BaseController {

	public function __construct(HttpRequest $request){
		$this->httpRequest = $request;
	}

	/**
     * Declare the rules for the form validation
     *
     * @var array
     */
    protected $validationRules = array(
    	'pcName'	=> 'required',
        'ec'       => 'required',
        'lcnsTypes'        => 'required',
    );

 	/**
	 * Display a listing of requests.
	 *
	 * @return Response
	 */
	public function index()
	{
		//get user role
		$user = Sentry::getUser();
		$role = $user->role;

		//get request code
		(Input::get('reqCode') ? $reqCode = Input::get('reqCode') : $reqCode = '');
		//get role id
		(Input::get('roleId') ? $roleId = Input::get('roleId') : $roleId = '');
		//get request type
		(Input::get('type') ? $type = Input::get('type') : $type = 'license');

		if($role->role != 'All' && $role->id != $roleId) {
			return Redirect::to('/')->with('error', 'Cannot access that EC');
		}

		if($roleId) {
			$requests = Request::where('request_code',$reqCode)->where('role_id', $roleId)->where('type', $type)->orderBy('created_at','desc')->get();
		}
		else{
			$requests = Request::where('request_code',$reqCode)->where('type', $type)->orderBy('created_at','desc')->get();
		}

		//ajax request so return json
		if($this->httpRequest->ajax()){
			foreach($requests as $key => $request){
				$requests[$key]->requester = $request->owner->first_name." ".$request->owner->last_name;
				$requests[$key]->role = $request->roles->role;
				$requests[$key]->lcnsTypes =  $request->licenseTypes;
				if($type=='account') {
					$requests[$key]->name = $request->account->first_name." ".$request->account->last_name;
				}

				if($requests[$key]->request_code != 'closed'){
					if($user->hasAccess('admin') || $user->role->id == $requests[$key]->role_id){
						$requests[$key]->actions = "<a href=".URL::to('request/'.$requests[$key]->id.'/edit')."><i class='fa fa-pencil icon-white'></i></a>";
					}
					if($user->hasAccess('admin')){
						$requests[$key]->actions .= "<a href=" .URL::to('request/'.$request->id.'/approve'). "><i class='fa fa-check icon-white'></i></a>";
					}
				}
			}

			header('Content-type: application/json');
			//return Response::json(array('requests'=>$requests, 'isAdmin' => $user->hasAccess('admin'), 'roleId' => $user->role->id), 200);
			echo json_encode(array('requests'=>$requests, 'isAdmin' => $user->hasAccess('admin'), 'roleId' => $user->role->id));
		}
		//return View
		else{
			//html request
			return View::make('backend.requests.index')->with('requests', $requests)->with('user', $user)->with('roleId', $roleId)->with('type', $type);
		}		
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{	
		//get the request type
		$type = Input::get('type');
		//get all the environmental commands
		$ec = Sentry::getUser()->filterRoles();

		if($type=='license') {
			//define array of license types
	        $lcnsTypes = DB::table('license_types')->where('name','!=', 'DLN LMS')->lists('name', 'id');

			return View::make('backend/requests/license/edit')
				->with('request',new Request)
				->with('ec', $ec)
				->with('lcnsTypes', $lcnsTypes)
				->with('isApprover', false);
		}
		elseif($type=='account') {
			//define array of employee types
			$empTypes = DB::table('emp_types')->lists('type', 'id');

			return View::make('backend/requests/accounts/edit')
				->with('request',new Request)
				->with('account', new Account)
				->with('ec', $ec)
				->with('empTypes', $empTypes)
				->with('isApprover', false);
		}
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
		$type = Input::get('type');
		$ec = Input::get('ec');
		$fname = Input::get('firstName');
		$lname = Input::get('lastName');
		$email = Input::get('email');
		$empType = Input::get('empType');
		$empNum = Input::get('empNum');
		$unitName = Input::get('unitName');
		$location = Input::get('location');
		$requesterId = Sentry::getUser()->id;

		if($type=='account') {
			$request = new Request();
			$account = new User();

			$requestValues = array(
				'role_id' => $ec,
				'user_id' => $requesterId,
				'type' => $type,
			);

			$accountValues = array(
				'role_id' => $ec,
				'first_name' => $fname,
				'last_name' => $lname,
				'password' => 'default',
				'activated' => 0,
				'email' => $email,
				'emp_types_id' => $empType,
				'emp_num' => $empNum,
				'unit_name' => $unitName,
				'location_id' => $location
			);

			try{
				$request->createForAccount($requestValues);

				$accountValues['request_id'] = $request->id;
				$account->createAccount($accountValues);
			}
			catch(Exception $e) {
				echo 'Caught exception: ', $e->getMessage(), "\n";
			}
			$success = Lang::get('admin/request/message.success.create');
			return Redirect::to('request/'.$request->id)->with('success', $success);
		}

		elseif($type=='license') {
			$pcName = Input::get('pcName');
			$lcnsTypes = Input::get('lcnsTypes');

	        // Create a new validator instance from our validation rules
	        $validator = Validator::make(Input::all(), $this->validationRules);

	        //TODO: check if request is JSON or HTML content-type
	        if($validator->fails()){
	        	// Ooops.. something went wrong
	            return Redirect::back()->withInput()->withErrors($validator);
	        }

	        $request = new Request();
	        $request->user_id = Sentry::getUser()->id;
	        $request->pc_name = $pcName;
	        $request->role_id = $ec;

	        try{
	        	$request->save();

	        	//save the license types to the request
	        	foreach($lcnsTypes as $lcnsType){
	        		$request->licenseTypes()->attach($lcnsType);
	        	}
	        }
	        catch(Exception $e){
	        	echo 'Caught exception: ',  $e->getMessage(), "\n";
	        }

	        $success = Lang::get('admin/request/message.success.create');
	        return Redirect::to('request/'.$request->id)->with('success', $success);
	    }

	}


	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
		$request = Request::find($id);

		//return resource not found if there is no request
		if(!$request){
			header(' ', true, 404);
			return Redirect::route('request')->with('error', 'Request not found.');
		}

		if($request->type=='account') {
			return View::make('backend/requests/accounts/view')->with('request', $request);
		}
		elseif($request->type=='license'){
			return View::make('backend/requests/view')->with('request', $request);
		}
	}


	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
		if(is_null($request = Request::find($id))){
			// Redirect to the requests page with error
            return Redirect::to('request')->with('error', Lang::get('admin/hardware/message.not_found'));
		}

		//check if request is up for approval or not
		$url = explode('/',$this->httpRequest->url());
		if($url[count($url)-1] == "approve"){
			$isApprover = true;
		}
		else{
			$isApprover = false;
		}

		//get all the environmental commands
		$ec = Sentry::getUser()->filterRoles();

		//define array of license types
		$lcnsTypes = DB::table('license_types')->where('name','!=', 'DLN LMS')->lists('name', 'id');

		(Sentry::getUser()->hasAccess('admin') ? $approver = true : $approver = false);

		return View::make('backend/requests/license/edit')
			->with('request', $request)
			->with('ec', $ec)
			->with('lcnsTypes', $lcnsTypes)
			->with('approver', $approver)
			->with('isApprover', $isApprover);
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

		$request = Request::find($id);
		
		//return resouse not found if there is no request
		if(!$request){
			header(' ', true, 404);
			return Redirect::route('request')->with('error', 'Request not found.');
		}

		//delete request and any records in pivot table
		//$request->licenseTypes()->detach();
		//$request->forceDelete();

		if($this->httpRequest->ajax())
		{
			return Response::json(array('error' => false, 'message' => Lang::get('request.message.success.delete')), 200);
		}
		else
		{
			// Prepare the success message
	        $success = Lang::get('request/message.success.delete');

	        // Redirect to the request management page
	        $user = Sentry::getUser();

	        return Redirect::to("role/{$user->role->id}/request")->with('success', Lang::get('request.message.success.delete'));
		}
	}

	/**
	 * Approve the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function approve($id)
	{
		$request = Request::find($id);
		//return resource not found if there is no request
		if(!$request){
			header(' ', true, 404);
			return Redirect::to('request')->with('error', 'Request not found.');
		}

		//get all license types for request
		$lcnsNames = $request->licenseTypes()->lists('name');

		//get available license seats for each license type
		foreach($lcnsNames as $lcnsName){
			$lcnsSeat = DB::table('licenses')
							->join('license_types', 'licenses.type_id', '=','license_types.id')
							->join('license_seats', 'licenses.id', '=','license_seats.license_id')
							->orwhere(function ($query) use ($lcnsName, $request){
								$query
									->where('license_types.name', '=', $lcnsName)
									->where('licenses.role_id', $request->role_id)
									->whereNull('license_seats.assigned_to');
							})->first();
			//if seats available add it to array for later processing, else return with error message
			if($lcnsSeat){
				$toAdd[] = $lcnsSeat;
			}
			else{
				$messageKey = str_replace(' ', '', strtolower($lcnsName));
				$error = Lang::get('request.message_no_lcns.'.$lcnsName);
				return Redirect::to('request/'.$request->id.'/edit')->with('error', $error);
			}
		}

		//create computer name as an asset if it doesnt exist
		if($obj = DB::table('assets')->where('serial', $request->pc_name)->first(array('id'))){
			$asset = Asset::find($obj->id);
		}
		else{
			$asset = new Asset();

			$asset->name = "DWAN PC";
			$asset->serial = $request->pc_name;
			$asset->asset_tag = $request->pc_name;
			$asset->model_id = 7; //TODO: Remove this hard coding for model id
			$asset->status_id = 1;
		}
		
		$asset->role_id = $request->role_id;

		if($asset->save()){
			//asset saved, lets attach license seats to the asset
			foreach($toAdd as $lcnsSeat){
				License::checkOutToAsset($lcnsSeat->id, $asset->id);
			}
		}

		//marked as closed
		$request->request_code = 'closed';
		$request->save();

		return Redirect::to("dashboard/$request->role_id")->with('success', 'Request Approved');

	}
}
