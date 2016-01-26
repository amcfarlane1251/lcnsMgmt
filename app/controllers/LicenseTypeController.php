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

class LicenseTypeController extends \BaseController {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		//
	}


	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		return View::make('backend/licenseTypes/edit')->with('licenseType', new LicenseType())->with('action', 'POST');
	}


	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
		$params = array(
			'name' => Input::get('name'),
			'asset_flag' => Input::get('asset_flag')
		);
		
		$licenseType = LicenseType::withParams($params);
		if($licenseType->validate($params)) {
			$licenseType->store();
			$return = array('success' => true, 'message' => "License type successfully created");
		}
		else{
			$return = array('success' => false, 'message' => $licenseType->errors());
		}
		
		if($return['success']) {
			return Redirect::to('license_types/'.$licenseType->id)->with('success', $return['message']);
		}
		else{
			return Redirect::back()->with('action', 'POST')->withErrors($return['message'])->withInput();
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
		$licenseType = LicenseType::find($id);
		return View::make('backend/licenseTypes/view')->with('licenseType', $licenseType);
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
