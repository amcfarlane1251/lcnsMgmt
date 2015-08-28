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
use Sentry;
use Validator;
use View;
use URL;

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
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{	
		//get all the environmental commands
		$ec = Sentry::getUser()->filterRoles();

		//define array of license types
        $lcnsTypes = DB::table('license_types')->where('name','!=', 'DLN LMS')->lists('name', 'id');

		return View::make('backend/requests/license/edit')
			->with('request',new Request)
			->with('ec', $ec)
			->with('lcnsTypes', $lcnsTypes)
			->with('isApprover', false);
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
		$pcName = Input::get('pcName');
		$ec = Input::get('ec');
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

		return View::make('backend/requests/view')->with('request', $request);
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
		$request->licenseTypes()->detach();
		$request->delete();

		// Prepare the success message
        $success = Lang::get('request/message.success.delete');

        // Redirect to the request management page
        return Redirect::to('request');
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

		return Redirect::to('request')->with('success', 'Request Approved');

	}
}
