<?php
namespace Controllers;

use Account;
use Asset;
use DB;
use Form;
use Illuminate\Http\Request as HttpRequest;
use Input;
use Lang;
use License;
use LicenseSeat;
use LicenseType;
use Location;
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
	
		//get URL params
		(Input::get('reqCode') ? $reqCode = Input::get('reqCode') : $reqCode = '');
		(Input::get('roleId') ? $roleId = Input::get('roleId') : $roleId = $role->id);
		(Input::get('type') ? $type = Input::get('type') : $type = 'license');

		if($role->role != 'All' && $role->id != $roleId) {
			return Redirect::to('/')->with('error', 'Cannot access that EC');
		}
		if($role->role == 'All') {
			if(!$reqCode){$reqCode = 1;} //ASAP
			if($roleId==1){$roleId='';}
		}
		
		//set unitId if a requester
		$user->inGroup(Sentry::findGroupByName('Requestors')) ? $unitId = $user->unit_id : $unitId = null;
		
		//get the requests
		$requests = Request::retrieve($roleId, $unitId, $type);
		
		//ajax request so return json
		if($this->httpRequest->ajax()){
			//make table headers
			$header = array(Lang::get('request.requester'), Lang::get('general.unit'), Lang::get('general.ec'), Lang::get('request.for'),
							Lang::get('licenses.general.type'), Lang::get('request.dateReq'), Lang::get('general.actions'));

			$return = array();
			foreach($requests as $key => $request){
				$return[$key] = new \stdClass();
				$return[$key]->id = $request->id;
				$return[$key]->requester = $request->owner->first_name." ".$request->owner->last_name;
				$return[$key]->unit = $request->unit->name;
				$return[$key]->role = $request->roles->role;
				$request->account ? $return[$key]->name = $request->account->first_name." ".$request->account->last_name : $return[$key]->name = '';
				
				if($type=='license') {
					$return[$key]->lcnsTypes =  $request->licenseTypes;
				}
				elseif($type=='checkin') {
					$return[$key]->lcnsTypes = [];
					if($request->license_id) {
						$return[$key]->lcnsTypes[]['name'] = $request->licenseSeat->license->name;
					}
					else if($request->asset_id) {
						$licenseSeats = Asset::find($request->asset_id)->licenseSeats;
						
						foreach( $licenseSeats as $licenseSeat) {
							$return[$key]->lcnsTypes[]['name'] = $licenseSeat->license->name;
						}
					}
				}
				
				$return[$key]->created_at = (string)$request->created_at;
				$return[$key]->actions = '';
				
				//delete
				if( $user->role->id == $requests[$key]->role_id || in_array('Authorizers',$user->group()) ) {
					$return[$key]->actions .= "<a href=".URL::to('request/'.$requests[$key]->id)." class='del-request'><i class='fa fa-trash icon-white'></i></a>";
				}
				elseif( in_array('Admin',$user->group()) ){
					$return[$key]->actions .= "<a href=".URL::to('request/'.$requests[$key]->id)." class='del-request'><i class='fa fa-trash icon-white'></i></a>";
				}
				//edit
				if( ($user->hasAccess('authorize') || $user->role->id == $requests[$key]->role_id) && empty($request->request_code) ){
					$return[$key]->actions .= "<a href=".URL::to('request/'.$requests[$key]->id.'/edit')."><i class='fa fa-pencil icon-white'></i></a>";
				}
				//approve
				if( (in_array('Authorizers',$user->group()) && empty($request->request_code)) || (in_array('Admin',$user->group()) && $request->request_code==1) ) {
					$return[$key]->actions .= "<a href=" .URL::to('request/'.$request->id.'/approve'). "><i class='fa fa-check icon-blue'></i></a>";
				}
			}
			if(!$return){$return="No requests found";}
			header('Content-type: application/json');
			//return Response::json(array('requests'=>$requests, 'isAdmin' => $user->hasAccess('admin'), 'roleId' => $user->role->id), 200);
			echo json_encode(array('requests'=>$return, 'header'=> $header,'isAdmin' => $user->hasAccess('admin'), 'roleId' => $user->role->id));
		}
		//return View
		else{
			//html request
			return View::make('backend.requests.index')->with('requests', $requests)->with('user', $user)->with('roleId', $roleId)->with('type', $type);
		}		
	}

	/**
	 * Show the form for creating a new request resource.
	 *
	 * @return Response
	 */
	public function create()
	{	
		//get the request type
		$type = Input::get('type');
		if(!$type){$type='license';}
		
		//get all the environmental commands, units, and locations
		$ec = Sentry::getUser()->filterRoles();
		$units = Sentry::getUser()->filterUnits();
		$locations = Location::lists('name', 'id');
		
		//define array of license types
	    $lcnsTypes = DB::table('license_types')->where('name','!=', 'DLN LMS')->select(array('name', 'id', 'asset_flag'))->get();

		if($type=='license') {
			return View::make('backend/requests/license/edit')
				->with('request',new Request)
				->with('ec', $ec)
				->with('units', $units)
				->with('lcnsTypes', $lcnsTypes)
				->with('type',$type)
				->with('action', 'POST');
		}
		else if($type=='checkin') {
			return View::make('backend/requests/asset/checkin/edit')
				->with('asset', Asset::find(Input::get('asset_id')));
		}
		else if($type=='move') {
			$license = LicenseSeat::find(Input::get('lcnsId'));
			$request = new Request();
			$account = Account::find(Input::get('accntId'));
			if($account) {
				$userStatus = 'existing';
				$request->account()->associate($account);
			}
			return View::make('backend/requests/license/move')
					->with('request', $request)
					->with('license', $license)
					->with('userStatus', $userStatus)
					->with('type',$type)
					->with('action', 'POST');
		}
		elseif($type=='account') {
			//define array of employee types
			$empTypes = DB::table('emp_types')->lists('type', 'id');

			return View::make('backend/requests/accounts/edit')
				->with('request',new Request)
				->with('account', new Account)
				->with('ec', $ec)
				->with('units', $units)
				->with('locations', $locations)
				->with('empTypes', $empTypes)
				->with('action', 'POST');
		}
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
		//get and format request variables
		$reqParams = $this->formatReqParams(Input::all());
		$accountParams = $this->formatAccntParams(Input::all());
		$userStatus = Input::get('userStatus');
		$type = Input::get('type');
		
		if($type=='account') {
			$account = Account::withParams($accountParams);
			if(!$account->validation()) {
				return Redirect::back()->withErrors($account->errors)->withInput();
			}
			$account->store();
			
			$reqParams['account_id'] = $account->id;
			$request = Request::withParams($reqParams);
			$request->dbStore();
			
			$account->created_from = $request->id;
			$account->save();
			return Redirect::to('request/'.$request->id)->with('success', Lang::get('admin/request/message.success.create'));
		}
		elseif($type=='license' || $type=='move') {
			$pcName = Input::get('pcName');
			$lcnsTypes = Input::get('lcnsTypes');
	        $request = Request::withParams($reqParams);
			$account = Account::withParams($accountParams);
			$assetFlag = false;
			
			//check if request requires a pc name
			foreach($lcnsTypes as $typeId) {
				if(LicenseType::find($typeId)->asset_flag){
					if(!$request->validation()) {
						//return with errors if validation failed
						return Redirect::back()->withErrors($request->errors)->with('status',$userStatus)->withInput();
					}
				}
			}
			
			//validate the account
			if($userStatus!='existing') {
				if(!$account->validation()) {
					return Redirect::back()->withErrors($account->errors)->with('status',$userStatus)->withInput();
				}
			}
			//validate the asset (if any)
			if(!$request->validateAsset($account)) {
				return Redirect::back()->withErrors($request->errors)->with('status',$userStatus)->withInput();
			}
			
	        $return = $request->store($lcnsTypes, $userStatus, $account);
	        if($return['success']){
	        	return Redirect::to('request/'.$request->id)->with('success', $return['message']);
	        }
	    }
		elseif($type=='checkin') {
			$asset = Asset::find(Input::get('asset_id'));
			$request = Request::withParams($reqParams);
			
			$return = $request->store();
	        if($return['success']){
	        	return Redirect::to('request/'.$request->id)->with('success', $return['message']);
	        }
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
			return Redirect::to('request')->with('error', 'Request not found.');
		}

		//return the view based on request type
		if($request->type=='account') {
			$account = $request->account()->first();
			return View::make('backend/requests/accounts/view')->with('request', $request)->with('account',$account);
		}
		else if($request->type=='license') {
			return View::make('backend/requests/view')->with('request', $request);
		}
		else if($request->type=='checkin') {
			//license checkin
			if($request->license_id){
				return View::make('backend/requests/license/checkin/view')->with('request', $request)->with('license', LicenseSeat::find($request->license_id)->license);
			}
			//asset checkin
			else if($request->asset_id){
				return View::make('backend/requests/asset/checkin/view')->with('request', $request)->with('asset', Asset::find($request->asset_id));
			}
		}
		else if($request->type=='move') {
			return View::make('backend/requests/license/move/view')->with('request', $request)->with('license', LicenseSeat::find($request->license_id));
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
		
		//get all the environmental commands
		$ec = Sentry::getUser()->filterRoles();
		$unit = Sentry::getUser()->filterUnits();
		$type = $request->type;

		//define array of license types
		$lcnsTypes = DB::table('license_types')->where('name','!=', 'DLN LMS')->select(array('name', 'id', 'asset_flag'))->get();

		(Sentry::getUser()->hasAccess('admin') ? $approver = true : $approver = false);

		return View::make('backend/requests/license/edit')
			->with('request', $request)
			->with('ec', $ec)
			->with('lcnsTypes', $lcnsTypes)
			->with('assignedLcns',$request->licenseTypes())
			->with('approver', $approver)
			->with('units', $unit)
			->with('type', $type)
			->with('userStatus','existing')
			->with('action', 'put');
	}


	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
		$request = Request::find($id);
		//return resource not found if there is no request
		if(!$request){
			header(' ', true, 404);
			return Redirect::to('request')->with('error', 'Request not found.');
		}

		if(Input::get('action')=='approve') {
			$userStatus = Input::get('userStatus');
			$return = $request->approve();
		}
		else{
			$lcnsTypes = Input::get('lcnsTypes');
			$reqParams = $this->formatReqParams(Input::all());
			$userStatus = Input::get('userStatus');
			$pcName = Input::get('pcName');
			$accountParams = $this->formatAccntParams(Input::all(), 'license');
			$return = $request->store($lcnsTypes, $userStatus, $accountParams);
		}
		if($return['success']){
			return Redirect::to('request?type='.$return['type'])->with('success', $return['message']);
		}
		else{
			return Redirect::back()->with('error', $return['message'])->with('status',$userStatus)->withInput();
		}
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
		try{
			$request->deletePrep();
			$request->delete();
		}
		catch(Exception $e){
			echo "Caught Exception: ", $e->getMessage(), "\n";
		}

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

	public function approvalForm($id)
	{
		$request = Request::find($id);
		//return resource not found if there is no request
		if(!$request){
			header(' ', true, 404);
			return Redirect::to('request')->with('error', 'Request not found.');
		}
		if($request->type=='account') {
			$account = $request->account()->first();
			return View::make('backend/requests/accounts/approve')->with('request',$request)->with('account',$account);
		}
		elseif($request->type=='license') {
			return View::make('backend/requests/license/approve')->with('request',$request);
		}
		else if($request->type=='checkin') {
			//license checkin
			if($request->license_id){
				return View::make('backend/requests/license/checkin/approve')->with('request', $request)->with('license', LicenseSeat::find($request->license_id)->license);
			}
			//asset checkin
			else if($request->asset_id){
				return View::make('backend/requests/asset/checkin/approve')->with('request', $request)->with('asset', Asset::find($request->asset_id));
			}
		}
		elseif($request->type=='move') {
			return View::make('backend/requests/license/move/approve')->with('request', $request)->with('license', LicenseSeat::find($request->license_id));
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
		return $request->approve();
	}

	private function formatReqParams($input)
	{	
		if($input['type']=='license' || $input['type']=='checkin') {
			return array(
				'user_id'=>Sentry::getUser()->id,
				'unit_id'=>$input['unit'],
				'role_id'=>$input['ec'],
				'type'=>$input['type'],
				'asset_id'=> (isset($input['asset_id']) ? $input['asset_id'] : 0),
				'pc_name'=> (isset($input['pcName']) ? $input['pcName'] : ''),
			);
		}
		elseif($input['type']=='move') {
			return array(
				'user_id'=>Sentry::getUser()->id,
				'unit_id'=>$input['unit'],
				'role_id'=>$input['ec'],
				'type'=>$input['type'],
				'pc_name'=> (isset($input['pcName']) ? $input['pcName'] : ''),
				'license_id' => $input['lcnsId']
			);
		}
	}

	private function formatAccntParams($input)
	{
		if($input['type']=='account'){
			return array(
				'first_name'=>$input['firstName'],
				'last_name'=>$input['lastName'],
				'username'=>$input['username'],
				'email'=>$input['email'],
				'emp_type_id'=>$input['empType'],
				'emp_num'=>$input['empNum'],
				'dob'=>$input['dob'],
				'sfn'=>$input['sfn'],
				'role_id'=>$input['ec'],
				'unit_id'=>$input['unit'],
				'location_id'=>$input['location'],
			);
		}
		else if($input['type']=='license' || $input['type']=='move') {
			return array(
				'first_name'=>$input['firstName'],
				'last_name'=>$input['lastName'],
				'username'=>$input['username'],
				'role_id'=>$input['ec'],
				'unit_id'=>$input['unit'],
			);
		}
	}
}
