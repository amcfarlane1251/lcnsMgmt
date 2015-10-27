<?php
namespace Controllers;

use Account;
use DB;
use Illuminate\Http\Request as HttpRequest;
use Input;
use Lang;
use License;
use LicenseType;
use Redirect;
use Requests as Request;
use Response;
use Sentry;
use Validator;
use View;
use URL;
use User;

class AccountsController extends \BaseController{
	public function index()
	{
		$accounts = Account::get(array('id', 'username', 'first_name', 'last_name'));

		header('Content-type: application/json');
		echo json_encode(array('accounts'=>$accounts));
	}
}
