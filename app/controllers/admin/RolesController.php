<?php namespace Controllers\Admin;

use AdminController;
use Input;
use Lang;
use Accessory;
use Redirect;
use Setting;
use DB;
use Sentry;
use Role;
use Str;
use Validator;
use View;
use User;
use Actionlog;
use Mail;
use Datatable;
use Slack;
use Config;

class RolesController extends AdminController
{
	/**
	 * Show a list of all roles
	 *
	 *@return View
	 */

	public function index()
	{
		$roles = Role::orderBy('role', 'ASC')->get();
		return View::make('backend/roles/index', compact('roles'));
	}

	public function getCreate()
	{
		return View::make('backend/roles/edit')->with('role', new Role);
	}

	public function create()
	{
		
	}
}