<?php namespace Controllers\Admin;

use AdminController;
use Cartalyst\Sentry\Users\LoginRequiredException;
use Cartalyst\Sentry\Users\PasswordRequiredException;
use Cartalyst\Sentry\Users\UserExistsException;
use Cartalyst\Sentry\Users\UserNotFoundException;
use HTML;
use URL;
use Config;
use DB;
use Input;
use User;
use Asset;
use Lang;
use Actionlog;
use Location;
use Setting;
use Redirect;
use Role;
use Sentry;
use Validator;
use View;
use Datatable;
use League\Csv\Reader;
use Mail;
use License;

class UsersController extends AdminController
{
    /**
     * Declare the rules for the form validation
     *
     * @var array
     */
    protected $validationRules = array(
        'first_name'       => 'required|alpha_space|min:2',
        'last_name'        => 'required|alpha_space|min:2',
		'location_id'      => 'required',
        'email'            => 'required|email|unique:users,email',
        'password'         => 'required|min:6',
        'password_confirm' => 'required|min:6|same:password',
    );

    /**
     * Show a list of all the users.
     *
     * @return View
     */
    public function getIndex()
    {

        // Show the page
        return View::make('backend/users/index');
    }

    /**
     * User create.
     *
     * @return View
     */
    public function getCreate()
    {
        $adminRole = Sentry::getUser()->sysAdmin();
        if($adminRole){
            // Get all the available groups
            $groups = Sentry::getGroupProvider()->findAll();
            // Selected groups
            $userGroups = Input::old('groups', array());
        }
        else{
            $groups = array();
            $userGroups = array();
        }

        // Get all the available permissions
        $permissions = Config::get('permissions');
        $this->encodeAllPermissions($permissions);

        //get all the environmental commands
        $ec = Sentry::getUser()->filterRoles();

        //define array of license types
        $lcnsTypes = DB::table('license_types')->lists('name', 'id');

        // Selected permissions
        $userPermissions = Input::old('permissions', array('superuser' => -1));
        $this->encodePermissions($userPermissions);

        $location_list = array('' => '') + Location::lists('name', 'id');
        $manager_list = array('' => '') + DB::table('users')
            ->select(DB::raw('concat(first_name," ",last_name) as full_name, id'))
            ->whereNull('deleted_at','and')
            ->orderBy('last_name', 'asc')
            ->orderBy('first_name', 'asc')
            ->lists('full_name', 'id');

		/*echo '<pre>';
		print_r($userPermissions);
		echo '</pre>';
		exit;
		*/

        // Show the page
        return View::make('backend/users/edit', compact('groups', 'userGroups', 'permissions', 'userPermissions'))
        ->with('location_list',$location_list)
        ->with('manager_list',$manager_list)
        ->with('user',new User)
        ->with('ec', $ec)
        ->with('lcnsTypes', $lcnsTypes)
        ->with('adminRole', $adminRole);

    }

    /**
     * User create form processing.
     *
     * @return Redirect
     */
    public function postCreate()
    {
        //check if sys admin
        $adminRole = Sentry::getUser()->sysAdmin();

        if(!$adminRole){

            //default to Users group
            try{
                $userGroup = Sentry::getGroupProvider()->findByName('Users');
            }
            catch(Cartalyst\Sentry\Groups\GroupNotFoundException $e){
                echo 'Group was not found';
            }
            $groups = array('0' => $userGroup->id);
        }
        else{
            $groups = Input::get('groups');
        }

        //If no password is given, default to random string
        if(!Input::has('password')){
            $randStr = substr(md5(rand()), 0, 7);
            Input::merge(array('password' => $randStr));
            Input::merge(array('password_confirm' => $randStr));
        }

        // Create a new validator instance from our validation rules
        $validator = Validator::make(Input::all(), $this->validationRules);
		$permissions = Input::get('permissions', array());
		$this->decodePermissions($permissions);
        app('request')->request->set('permissions', $permissions);

        // If validation fails, we'll exit the operation now.
        if ($validator->fails()) {
            // Ooops.. something went wrong
            return Redirect::back()->withInput()->withErrors($validator)->with('permissions',$permissions);
        }

        try {
            // We need to reverse the UI specific logic for our
            // permissions here before we create the user.

            if(!Input::get('activated')){
                Input::merge(array('activated' => 0));
            }
            // Get the inputs, with some exceptions
            $inputs = Input::except('csrf_token', 'password_confirm', 'groups','email_user','lcnsTypes');

            if ($inputs['location_id']=='') {
            	unset($inputs['location_id']);
            }

            // Was the user created?
            if ($user = Sentry::getUserProvider()->create($inputs)) {
                // Assign the selected groups to this user
                foreach ($groups as $groupId) {
                    $group = Sentry::getGroupProvider()->findById($groupId);
                    $user->addGroup($group);
                }

                //add license types to user
                foreach(Input::get('lcnsTypes', array()) as $type){
                    $user->licenseTypes()->attach($type);
                }

                //if user is activated, assign them to their license seat(s)
                if(Input::get('activated') == 1)
                {
                    $toAdd = array(); //seats to add
                    //get array of license types
                    foreach($user->licenseTypes()->lists('name') as $lcnsName)
                    {
                        //get available seat for license
                        $seat = DB::table('licenses')
                            ->join('license_types', 'licenses.type_id', '=', 'license_types.id')
                            ->join('license_seats', 'licenses.id', '=','license_seats.license_id')
                            ->orwhere(function($query) use ($lcnsName, $user){
                                $query
                                    ->where('licenses.role_id', $user->role_id)
                                    ->where('license_types.name', '=', $lcnsName)
                                    ->whereNull('license_seats.assigned_to');
                            })
                            ->first();
                        if($seat){
                            $toAdd[] = $seat;
                        }
                        else{
                            $messageKey = str_replace(' ', '', strtolower($lcnsName));
                            $error = Lang::get('admin/users/message.no_lcn_'.$messageKey);
                            // Redirect to the user creation page
                            return Redirect::route('update/user', $user->id)->withInput()->with('error', $error);
                        }
                    }

                    if(count($toAdd)){
                        foreach($toAdd as $seat){
                            DB::table('license_seats')
                                ->where('id', $seat->id)
                                ->update(array('assigned_to' => $user->id));
                        }
                    }
                }

                // Prepare the success message
                $success = Lang::get('admin/users/message.success.create');

                // Redirect to the new user page
                //return Redirect::route('update/user', $user->id)->with('success', $success);

                if (Input::get('email_user')==1) {
					// Send the credentials through email

					$data = array();
					$data['email'] = e(Input::get('email'));
					$data['first_name'] = e(Input::get('first_name'));
					$data['password'] = e(Input::get('password'));

		            Mail::send('emails.send-login', $data, function ($m) use ($user) {
		                $m->to($user->email, $user->first_name . ' ' . $user->last_name);
		                $m->subject('Welcome ' . $user->first_name);
		            });
				}

                //get the selected license types
                $selectedTypes = Input::get('lcnsTypes', array());
                //add types to user
                foreach($selectedTypes as $type){
                    $user->licenseTypes()->attach($type);
                }

                return Redirect::route('users')->with('success', $success);
            }



            // Prepare the error message
            $error = Lang::get('admin/users/message.error.create');

            // Redirect to the user creation page
            return Redirect::route('create/user')->with('error', $error);
        } catch (LoginRequiredException $e) {
            $error = Lang::get('admin/users/message.user_login_required');
        } catch (PasswordRequiredException $e) {
            $error = Lang::get('admin/users/message.user_password_required');
        } catch (UserExistsException $e) {
            $error = Lang::get('admin/users/message.user_exists');
        }

        // Redirect to the user creation page
        return Redirect::route('create/user')->withInput()->with('error', $error);
    }

    /**
     * User update.
     *
     * @param  int  $id
     * @return View
     */
    public function getEdit($id = null)
    {
        try {
            // Get the user information
            $user = Sentry::getUserProvider()->findById($id);

            // Get this user groups
            $userGroups = $user->groups()->lists('group_id', 'name');

            // Get this user permissions
            $userPermissions = array_merge(Input::old('permissions', array('superuser' => -1)), $user->getPermissions());
            $this->encodePermissions($userPermissions);

            // Get a list of all the available groups
            $groups = Sentry::getGroupProvider()->findAll();

            // Get all the available permissions
            $permissions = Config::get('permissions');
            $this->encodeAllPermissions($permissions);

            $location_list = array('' => '') + Location::lists('name', 'id');
            $manager_list = array('' => 'Select a User') + DB::table('users')
            ->select(DB::raw('concat(first_name," ",last_name) as full_name, id'))
            ->whereNull('deleted_at')
            ->where('id','!=',$id)
            ->orderBy('last_name', 'asc')
            ->orderBy('first_name', 'asc')
            ->lists('full_name', 'id');

            //get all the environmental commands
            $ec = Sentry::getUser()->filterRoles();

            //define array of license types
            $lcnsTypes = DB::table('license_types')->lists('name', 'id');

        } catch (UserNotFoundException $e) {
            // Prepare the error message
            $error = Lang::get('admin/users/message.user_not_found', compact('id'));

            // Redirect to the user management page
            return Redirect::route('users')->with('error', $error);
        }
        // Show the page
        return View::make('backend/users/edit', compact('user', 'groups', 'userGroups', 'permissions', 'userPermissions'))
        ->with('location_list',$location_list)
        ->with('manager_list',$manager_list)
        ->with('ec', $ec)
        ->with('lcnsTypes', $lcnsTypes)
        ->with('adminRole', Sentry::getUser()->sysAdmin());
    }

    /**
     * User update form processing page.
     *
     * @param  int  $id
     * @return Redirect
     */
    public function postEdit($id = null)
    {
        // We need to reverse the UI specific logic for our
        // permissions here before we update the user.
        $permissions = Input::get('permissions', array());
        $this->decodePermissions($permissions);
        app('request')->request->set('permissions', $permissions);

        try {
            // Get the user information
            $user = Sentry::getUserProvider()->findById($id);
        } catch (UserNotFoundException $e) {
            // Prepare the error message
            $error = Lang::get('admin/users/message.user_not_found', compact('id'));

            // Redirect to the user management page
            return Redirect::route('users')->with('error', $error);
        }

        //
        $this->validationRules['email'] = "required|email|unique:users,email,{$user->email},email";

        // Do we want to update the user password?
        if ( ! $password = Input::get('password')) {
            unset($this->validationRules['password']);
            unset($this->validationRules['password_confirm']);
            #$this->validationRules['password']         = 'required|between:3,32';
            #$this->validationRules['password_confirm'] = 'required|between:3,32|same:password';
        }

        // Create a new validator instance from our validation rules
        $validator = Validator::make(Input::all(), $this->validationRules);


        // If validation fails, we'll exit the operation now.
        if ($validator->fails()) {
            // Ooops.. something went wrong
            return Redirect::back()->withInput()->withErrors($validator);
        }

        // Only update the email address if locking is set to false
        if (!Config::get('app.lock_passwords')) {
			$user->email       		= Input::get('email');
		}

        if(!Input::get('activated')){
                Input::merge(array('activated' => 0));
        }

        try {
            $prevActivated = $user->activated;
            // Update the user
            $user->first_name  		= Input::get('first_name');
            $user->last_name   		= Input::get('last_name');
            $user->employee_num		= Input::get('employee_num');
            $user->activated   		= Input::get('activated', $user->activated);
            $user->permissions 		= Input::get('permissions');
            $user->jobtitle 		= Input::get('jobtitle');
            $user->phone 			= Input::get('phone');
            $user->location_id 		= Input::get('location_id');
            $user->manager_id 		= Input::get('manager_id');
            $user->role_id          = Input::get('role_id');
            $user->notes		= Input::get('notes');

            if ($user->manager_id == "") {
                $user->manager_id = NULL;
            }

            if ($user->location_id == "") {
                    $user->location_id = NULL;
            }


            // Do we want to update the user password?
            if (($password)  && (!Config::get('app.lock_passwords'))) {
                $user->password = $password;
            }

            // Get the current user groups
            $userGroups = $user->groups()->lists('group_id', 'group_id');

            // Get the selected groups
            $selectedGroups = Input::get('groups', array());

            // Groups comparison between the groups the user currently
            // have and the groups the user wish to have.
            $groupsToAdd    = array_diff($selectedGroups, $userGroups);
            $groupsToRemove = array_diff($userGroups, $selectedGroups);

			if (!Config::get('app.lock_passwords')) {

	            // Assign the user to groups
	            foreach ($groupsToAdd as $groupId) {
	                $group = Sentry::getGroupProvider()->findById($groupId);

	                $user->addGroup($group);
	            }

	            // Remove the user from groups
	            foreach ($groupsToRemove as $groupId) {
	                $group = Sentry::getGroupProvider()->findById($groupId);

	                $user->removeGroup($group);
	            }
	         }

            //get the current user license types
            $userLcnsTypes = $user->licenseTypes()->lists('type_id');
            //get the selected license types
            $selectedTypes = Input::get('lcnsTypes', array());

            $typesToAdd = array_diff($selectedTypes, $userLcnsTypes);
            $typesToRemove = array_diff($userLcnsTypes, $selectedTypes);

            //add types to user
            foreach($typesToAdd as $type){
                $user->licenseTypes()->attach($type);
            }

            //remove types from user
            foreach($typesToRemove as $type){
                $user->licenseTypes()->detach($type);
            }

            //if activating a user, assign them their licenses
            if(Input::get('activated')){

                //get list of current licenses assigned to user
                $currentLcns = DB::table('licenses')
                    ->join('license_seats', function($join) use ($id)
                        {
                            $join->on('licenses.id', '=', 'license_seats.license_id')
                                ->where('license_seats.assigned_to','=', $id);
                        })->lists('type_id');

                //get the licenses to add
                $lcnsTypeAdd = array_diff($selectedTypes, $currentLcns);
                $toAdd = array();

                foreach($lcnsTypeAdd as $licenseTypeId){
                    //get available licenses
                     $lcns = DB::table('licenses')
                        ->join('license_types', 'licenses.type_id', '=', 'license_types.id')
                        ->join('license_seats', 'licenses.id', '=','license_seats.license_id')
                        ->orwhere(function($query) use ($licenseTypeId, $user){
                            $query
                                ->where('licenses.role_id', $user->role_id)
                                ->where('licenses.type_id', '=', $licenseTypeId)
                                ->whereNull('license_seats.assigned_to');
                        })
                        ->first();
                    if($lcns){
                        $toAdd[] = $lcns;
                    }
                    else{
                        $messageKey = str_replace(' ', '', strtolower($licenseType));
                        $error = Lang::get('admin/users/message.no_lcn_'.$messageKey);
                        // Redirect to the user creation page
                        return Redirect::route('update/user', $id)->withInput()->with('error', $error);
                    }

                }
                $license = new License();
                //add the licenses
                foreach($toAdd as $key => $lcns){
                    DB::table('license_seats')
                            ->where('id', '=', $lcns->id)
                            ->update(array('assigned_to' => $user->id));
                }

                //remove the licenses
                $lcnsTypeRemove = array_diff($currentLcns, $selectedTypes);

                foreach($lcnsTypeRemove as $licenseTypeId){
                    $lcns = DB::table('licenses')
                        ->join('license_types', 'licenses.type_id', '=', 'license_types.id')
                        ->join('license_seats', 'licenses.id', '=','license_seats.license_id')
                        ->orwhere(function($query) use ($licenseTypeId, $user){
                            $query
                                ->where('licenses.type_id', '=', $licenseTypeId)
                                ->where('license_seats.assigned_to', $user->id);
                        })
                        ->first();
                    try{
                        $license->checkIn($lcns->id);
                    }
                    catch (Exception $e) {
                        echo 'Caught exception: ',  $e->getMessage(), "\n";
                    }
                }
                
            }

            // Was the user updated?
            if ($user->save()) {
                // Prepare the success message
                $success = Lang::get('admin/users/message.success.update');

                // Redirect to the user page
                return Redirect::route('view/user', $id)->with('success', $success);
            }

            // Prepare the error message
            $error = Lang::get('admin/users/message.error.update');
        } catch (LoginRequiredException $e) {
            $error = Lang::get('admin/users/message.user_login_required');
        }

        // Redirect to the user page
        return Redirect::route('update/user', $id)->withInput()->with('error', $error);
    }

    /**
     * Delete the given user.
     *
     * @param  int  $id
     * @return Redirect
     */
    public function getDelete($id = null)
    {
        try {
            // Get user information
            $user = Sentry::getUserProvider()->findById($id);

            // Check if we are not trying to delete ourselves
            if ($user->id === Sentry::getId()) {
                // Prepare the error message
                $error = Lang::get('admin/users/message.error.delete');

                // Redirect to the user management page
                return Redirect::route('users')->with('error', $error);
            }


            // Do we have permission to delete this user?
            if ((!Sentry::getUser()->isSuperUser()) || (Config::get('app.lock_passwords'))) {
                // Redirect to the user management page
                return Redirect::route('users')->with('error', 'Insufficient permissions!');
            }

            if (count($user->assets) > 0) {

                // Redirect to the user management page
                return Redirect::route('users')->with('error', 'This user still has '.count($user->assets).' assets associated with them.');
            }

            if (count($user->licenses) > 0) {

                // Redirect to the user management page
                return Redirect::route('users')->with('error', 'This user still has '.count($user->licenses).' licenses associated with them.');
            }

            // Delete the user
            $user->delete();

            // Prepare the success message
            $success = Lang::get('admin/users/message.success.delete');

            // Redirect to the user management page
            return Redirect::route('users')->with('success', $success);
        } catch (UserNotFoundException $e) {
            // Prepare the error message
            $error = Lang::get('admin/users/message.user_not_found', compact('id' ));

            // Redirect to the user management page
            return Redirect::route('users')->with('error', $error);
        }
    }

    /**
     * Restore a deleted user.
     *
     * @param  int  $id
     * @return Redirect
     */
    public function getRestore($id = null)
    {
        try {
            // Get user information
            $user = Sentry::getUserProvider()->createModel()->withTrashed()->find($id);

            // Restore the user
            $user->restore();

            // Prepare the success message
            $success = Lang::get('admin/users/message.success.restored');

            // Redirect to the user management page
            return Redirect::route('users')->with('success', $success);
        } catch (UserNotFoundException $e) {
            // Prepare the error message
            $error = Lang::get('admin/users/message.user_not_found', compact('id'));

            // Redirect to the user management page
            return Redirect::route('users')->with('error', $error);
        }
    }


    /**
     * Get user info for user view
     *
     * @param  int  $userId
     * @return View
     */
    public function getView($userId = null)
    {

        $user = Sentry::getUserProvider()->createModel()->find($userId);

            if (isset($user->id)) {
                return View::make('backend/users/view', compact('user'));
            } else {
                // Prepare the error message
                $error = Lang::get('admin/users/message.user_not_found', compact('id' ));

                // Redirect to the user management page
                return Redirect::route('users')->with('error', $error);
            }

    }


    /**
     * Unsuspend the given user.
     *
     * @param  int      $id
     * @return Redirect
     */
    public function getUnsuspend($id = null)
    {
        try {
            // Get user information
            $user = Sentry::getUserProvider()->findById($id);

            // Check if we are not trying to unsuspend ourselves
            if ($user->id === Sentry::getId()) {
                // Prepare the error message
                $error = Lang::get('admin/users/message.error.unsuspend');

                // Redirect to the user management page
                return Redirect::route('users')->with('error', $error);
            }

            // Do we have permission to unsuspend this user?
            if ($user->isSuperUser() and ! Sentry::getUser()->isSuperUser()) {
                // Redirect to the user management page
                return Redirect::route('users')->with('error', 'Insufficient permissions!');
            }

            // Unsuspend the user
            $throttle = Sentry::findThrottlerByUserId($id);
            $throttle->unsuspend();

            // Prepare the success message
            $success = Lang::get('admin/users/message.success.unsuspend');

            // Redirect to the user management page
            return Redirect::route('users')->with('success', $success);
        } catch (UserNotFoundException $e) {
            // Prepare the error message
            $error = Lang::get('admin/users/message.user_not_found', compact('id' ));

            // Redirect to the user management page
            return Redirect::route('users')->with('error', $error);
        }
    }

    public function getClone($id = null)
    {
        // We need to reverse the UI specific logic for our
        // permissions here before we update the user.
        $permissions = Input::get('permissions', array());
        $this->decodePermissions($permissions);
        app('request')->request->set('permissions', $permissions);


        try {
            // Get the user information
            $user_to_clone = Sentry::getUserProvider()->findById($id);
            $user = clone $user_to_clone;
            $user->first_name = '';
            $user->last_name = '';
            $user->email = substr($user->email, ($pos = strpos($user->email, '@')) !== false ? $pos  : 0);;
            $user->id = null;

            // Get this user groups
            $userGroups = $user_to_clone->groups()->lists('group_id', 'name');

            // Get this user permissions
            $userPermissions = array_merge(Input::old('permissions', array('superuser' => -1)), $user_to_clone->getPermissions());
            $this->encodePermissions($userPermissions);

            // Get a list of all the available groups
            $groups = Sentry::getGroupProvider()->findAll();

            // Get all the available permissions
            $permissions = Config::get('permissions');
            $this->encodeAllPermissions($permissions);

            $location_list = array('' => '') + Location::lists('name', 'id');
            $manager_list = array('' => 'Select a User') + DB::table('users')
            ->select(DB::raw('concat(first_name," ",last_name) as full_name, id'))
            ->whereNull('deleted_at')
            ->where('id','!=',$id)
            ->orderBy('last_name', 'asc')
            ->orderBy('first_name', 'asc')
            ->lists('full_name', 'id');

                // Show the page
            return View::make('backend/users/edit', compact('groups', 'userGroups', 'permissions', 'userPermissions'))
                ->with('location_list',$location_list)
                ->with('manager_list',$manager_list)
                ->with('user',$user)
                ->with('clone_user',$user_to_clone);

        } catch (UserNotFoundException $e) {
            // Prepare the error message
            $error = Lang::get('admin/users/message.user_not_found', compact('id'));

            // Redirect to the user management page
            return Redirect::route('users')->with('error', $error);
        }
    }

    /**
	 * User import.
	 *
	 * @return View
	 */
	public function getImport()
	{
		// Get all the available groups
		$groups = Sentry::getGroupProvider()->findAll();
		// Selected groups
		$selectedGroups = Input::old('groups', array());
		// Get all the available permissions
		$permissions = Config::get('permissions');
		$this->encodeAllPermissions($permissions);
		// Selected permissions
		$selectedPermissions = Input::old('permissions', array('superuser' => -1));
		$this->encodePermissions($selectedPermissions);
		// Show the page
		return View::make('backend/users/import', compact('groups', 'selectedGroups', 'permissions', 'selectedPermissions'));
	}


	/**
	 * User import form processing.
	 *
	 * @return Redirect
	 */
	public function postImport()
	{

		if (! ini_get("auto_detect_line_endings")) {
			ini_set("auto_detect_line_endings", '1');
		}

		$csv = Reader::createFromPath(Input::file('user_import_csv'));
		$csv->setNewline("\r\n");

		if (Input::get('has_headers')==1) {
			$csv->setOffset(1);
		}

		$duplicates = '';

		$nbInsert = $csv->each(function ($row) use ($duplicates) {

			if (array_key_exists(2, $row)) {

				if (Input::get('activate')==1) {
					$activated = '1';
				} else {
					$activated = '0';
				}

				$pass = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 10);



				try {
						// Check if this email already exists in the system
						$user = DB::table('users')->where('email', $row[2])->first();
						if ($user) {
							$duplicates .= $row[2].', ';
						} else {

							$newuser = array(
								'first_name' => $row[0],
								'last_name' => $row[1],
								'email' => $row[2],
								'password' => $pass,
								'activated' => $activated,
								'permissions'	=> '{"user":1}',
                                'notes'         => 'Imported user'
							);

							DB::table('users')->insert($newuser);

							$updateuser = Sentry::findUserByLogin($row[2]);

						    // Update the user details
						    $updateuser->password = $pass;

						    // Update the user
						    $updateuser->save();


							if (Input::get('email_user')==1) {
								// Send the credentials through email

								$data = array();
								$data['email'] = $row[2];
								$data['first_name'] = $row[0];
								$data['password'] = $pass;

					            Mail::send('emails.send-login', $data, function ($m) use ($newuser) {
					                $m->to($newuser['email'], $newuser['first_name'] . ' ' . $newuser['last_name']);
					                $m->subject('Welcome ' . $newuser['first_name']);
					            });
							}
						}


				} catch (Exception $e) {
					echo 'Caught exception: ',  $e->getMessage(), "\n";
				}
				return true;
			}

		});


		return Redirect::route('users')->with('duplicates',$duplicates)->with('success', 'Success');

	}


	public function getDatatable($status = null)
    {

	$users = User::with('assets','licenses','role','sentryThrottle');
	switch ($status) {
		case 'requests':
			$users->GetRequests();
			break;
		case '':
			$users->GetNotDeleted();
			break;
	}
	$users = $users->orderBy('created_at', 'DESC')->get();
    
    //filter users by EC
    $user = Sentry::getUser();
    $users = $user->filterByRole($users);

    $actions = new \Chumper\Datatable\Columns\FunctionColumn('actions', function ($users)
        	{
	        	$action_buttons = '';


                if ( ! is_null($users->deleted_at)) {
                	$action_buttons .= '<a href="'.route('restore/user', $users->id).'" class="btn btn-warning btn-sm"><i class="fa fa-share icon-white"></i></a> ';
                } else {
	                if ($users->accountStatus()=='suspended') {
			               $action_buttons .= '<a href="'.route('unsuspend/user', $users->id).'" class="btn btn-warning btn-sm"><span class="fa fa-time icon-white"></span></a> ';
					}

                	$action_buttons .= '<a href="'.route('update/user', $users->id).'" class="btn btn-warning btn-sm"><i class="fa fa-pencil icon-white"></i></a> ';

					if ((Sentry::getId() !== $users->id) && (!Config::get('app.lock_passwords'))) {
	                	$action_buttons .= '<a data-html="false" class="btn delete-asset btn-danger btn-sm" data-toggle="modal" href="'.route('delete/user', $users->id).'" data-content="Are you sure you wish to delete this user?" data-title="Delete '.htmlspecialchars($users->first_name).'?" onClick="return false;"><i class="fa fa-trash icon-white"></i></a> ';
	                } else {
	                	$action_buttons .= ' <span class="btn delete-asset btn-danger btn-sm disabled"><i class="fa fa-trash icon-white"></i></span>';
	                }
                }
                return $action_buttons;

	        });


        return Datatable::collection($users)
        ->addColumn('name',function($users)
	        {
		        return '<a title="'.$users->fullName().'" href="users/'.$users->id.'/view">'.$users->fullName().'</a>';
	        })

	     ->addColumn('email',function($users)
	        {
		        return '<a title="'.$users->email.'" href="mailto:'.$users->email.'">'.$users->email.'</a>';
	        })

	     ->addColumn('role',function($users)
	        {
		        if ($users->role) {
		       	 return '<a title="'.$users->role->role.'" href="users/'.$users->role->id.'/view">'.$users->role->role.'</a>';
		       	}
	        })

		->addColumn('assets',function($users)
	        {
		        return $users->assets->count();
	        })

		->addColumn('licenses',function($users)
	        {
		        return $users->licenses->count();
	        })
	    ->addColumn('activated',function($users)
	        {
		        return $users->isActivated() ? '<i class="fa fa-check"></i>' : '';
	        })

	    ->addColumn($actions)
        ->searchColumns('name','email','manager','activated', 'licenses','assets')
        ->orderColumns('name','email','manager','activated', 'licenses','assets')
        ->make();

		}
}
