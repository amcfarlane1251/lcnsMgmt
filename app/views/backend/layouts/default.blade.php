<!DOCTYPE html>

<html lang="en">
    <head>

        <!-- Basic Page Needs
        ================================================== -->
        <meta charset="utf-8" />
        <title>
            @section('title')
             {{{ Setting::getSettings()->site_name }}}
            @show
        </title>

        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">


		 <!-- bootstrap -->
	    <link href="{{ asset('assets/css/bootstrap/bootstrap.css') }}" rel="stylesheet" />
        <link href="{{ asset('assets/css/bootstrap/bootstrap-overrides.css') }}" type="text/css" rel="stylesheet" />

	    <!-- global styles -->
        <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/compiled/layout.css') }}">
        <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/compiled/elements.css') }}">
        <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/compiled/icons.css') }}">

	    <!-- libraries -->
	    <link rel="stylesheet" href="{{ asset('assets/css/lib/jquery-ui-1.10.2.custom.css') }}" type="text/css">
	    <link rel="stylesheet" href="{{ asset('assets/css/lib/font-awesome.min.css') }}" type="text/css">
	    <link rel="stylesheet" href="{{ asset('assets/css/lib/morris.css') }}" type="text/css">
        <link rel="stylesheet" href="{{ asset('assets/css/lib/select2.css') }}" type="text/css">
        <link rel="stylesheet" href="{{ asset('assets/css/lib/bootstrap.datepicker.css') }}" type="text/css">
        <link rel="stylesheet" href="{{ asset('assets/css/compiled/index.css') }}" type="text/css" media="screen" />
        <link rel="stylesheet" href="{{ asset('assets/css/compiled/user-list.css') }}" type="text/css" media="screen" />
        <link rel="stylesheet" href="{{ asset('assets/css/compiled/user-profile.css') }}" type="text/css" media="screen" />
        <link rel="stylesheet" href="{{ asset('assets/css/compiled/form-showcase.css') }}" type="text/css" media="screen" />
        <link rel="stylesheet" href="{{ asset('assets/css/lib/jquery.dataTables.css') }}" type="text/css" media="screen" />
        <link rel="stylesheet" href="{{ asset('assets/css/compiled/dataTables.colVis.css') }}" type="text/css" media="screen" />
        <link rel="stylesheet" href="{{ asset('assets/css/compiled/dataTables.tableTools.css') }}" type="text/css" media="screen" />
        <link rel="stylesheet" href="{{ asset('assets/css/compiled/styles.css') }}" type="text/css" media="screen" />
        <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/compiled/print.css') }}" media="print" />



	    <!-- open sans font -->
	    <link href='http://fonts.googleapis.com/css?family=Open+Sans:300italic,400italic,600italic,700italic,800italic,400,300,600,700,800' rel='stylesheet' type='text/css'>

        <!-- global header javascripts -->
        <script src="{{ asset('assets/js/jquery-latest.js') }}"></script>
        <script src="{{ asset('assets/js/queryVar.js') }}"></script>
        <script src="{{ asset('assets/js/jquery.dataTables.min.js') }}"></script>
        <script src="{{ asset('assets/js/dataTables.colVis.js') }}"></script>
        <script src="{{ asset('assets/js/dataTables.tableTools.js') }}"></script>
        <script src="{{ asset('assets/js/Requests.js') }}"></script>
        <script src="{{ asset('assets/js/ToggleHelper.js') }}"></script>

        <script>
            window.snipeit = {
                settings: {
                    "per_page": {{{ Setting::getSettings()->per_page }}}
                }
            };
        </script>



		@if (Setting::getSettings()->load_remote=='1')
        <!-- open sans font -->
        <link href='//fonts.googleapis.com/css?family=Open+Sans:300italic,400italic,600italic,700italic,800italic,400,300,600,700,800' rel='stylesheet' type='text/css'>
		@endif

        <!--[if lt IE 9]>
          <script src="{{ asset('assets/js/html5.js') }}"></script>
        <![endif]-->

        <style>

        @section('styles')
        h3 {
            padding: 10px;
        }

        @show

		@if (Setting::getSettings()->header_color)
			.navbar-inverse {
				background-color: {{{ Setting::getSettings()->header_color }}};
				background: -webkit-linear-gradient(top,  {{{ Setting::getSettings()->header_color }}} 0%,{{{ Setting::getSettings()->header_color }}} 100%);
				border-color: {{{ Setting::getSettings()->header_color }}};
			}
		@endif

        </style>


    </head>

    <body>

    <!-- navbar -->


    <!-- navbar -->
    <header class="navbar navbar-inverse" role="banner">
	    <div class="navbar-header">
            <button class="navbar-toggle" type="button" data-toggle="collapse" id="menu-toggler">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>


	            @if (Setting::getSettings()->logo)
	            	<a class="navbar-brand" href="{{ Config::get('app.url') }}" style="padding: 5px;">
	            	<img src="/uploads/{{{ Setting::getSettings()->logo }}}">
	            	</a>
	            @else
	            	<a class="navbar-brand" href="{{ URL::to('/') }}">
	            	{{{ Setting::getSettings()->site_name }}}
	            	</a>
	            @endif
        </div>


        <ul class="nav navbar-nav navbar-right">
            @if (Sentry::check())

                 @if(Sentry::getUser()->hasAccess('admin'))
                 <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        <i class="fa fa-plus"></i> @lang('general.create')
                        <b class="caret"></b>
                    </a>
                    <ul class="dropdown-menu">
                       <li {{{ (Request::is('hardware/create') ? 'class="active"' : '') }}}>
                               <a href="{{ route('create/hardware') }}">
                                   <i class="fa fa-barcode"></i>
                                   @lang('general.asset')</a>
                           </li>
                        <li {{{ (Request::is('admin/licenses/create') ? 'class="active"' : '') }}}>
                            <a href="{{ route('create/licenses') }}">
                                <i class="fa fa-certificate"></i>
                                @lang('general.license')</a>
                        </li>
                        <li {{{ (Request::is('admin/users/create') ? 'class="active"' : '') }}}>
                            <a href="{{ route('create/user') }}">
                            <i class="fa fa-user"></i>
                            @lang('general.user')</a>
                        </li>
                    </ul>
                </li>
                @endif
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        {{{ Lang::get('general.welcome', array('name' => Sentry::getUser()->first_name)) }}}
                        <b class="caret"></b>
                    </a>
                    <ul class="dropdown-menu">
                        <li{{{ (Request::is('account/profile') ? ' class="active"' : '') }}}>
                         	<a href="{{ route('view-assets') }}">
                                <i class="fa fa-check"></i> @lang('general.viewassets')
                        	</a>
                             <a href="{{ route('profile') }}">
                                <i class="fa fa-user"></i> @lang('general.editprofile')
                            </a>
                             <a href="{{ route('change-password') }}">
                                <i class="fa fa-lock"></i> @lang('general.changepassword')
                            </a>
                            <a href="{{ route('change-email') }}">
                                <i class="fa fa-envelope"></i> @lang('general.changeemail')
                            </a>
                        </li>
                        <li class="divider"></li>
                        <li>
                            <a href="{{ route('logout') }}">
                                <i class="fa fa-sign-out"></i>
                                @lang('general.logout')
                            </a>
                        </li>
                    </ul>
                </li>
                @if(Sentry::getUser()->hasAccess('admin'))
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        <i class="fa fa-wrench icon-white"></i> @lang('general.admin')
                        <b class="caret"></b>
                    </a>
                    <ul class="dropdown-menu">
                        <li{{ (Request::is('hardware/models*') ? ' class="active"' : '') }}>
                            <a href="{{ URL::to('hardware/models') }}">
                                <i class="fa fa-th"></i> @lang('general.asset_models')
                            </a>
                        </li>
                        <li{{ (Request::is('admin/settings/categories*') ? ' class="active"' : '') }}>
                            <a href="{{ URL::to('admin/settings/categories') }}">
                                <i class="fa fa-check"></i> @lang('general.categories')
                            </a>
                        </li>
                        <li{{ (Request::is('admin/settings/manufacturers*') ? ' class="active"' : '') }}>
                            <a href="{{ URL::to('admin/settings/manufacturers') }}">
                                <i class="fa fa-briefcase"></i> @lang('general.manufacturers')
                            </a>
                        </li>
                        <li{{ (Request::is('admin/settings/suppliers*') ? ' class="active"' : '') }}>
                            <a href="{{ URL::to('admin/settings/suppliers') }}">
                                <i class="fa fa-credit-card"></i> @lang('general.suppliers')
                            </a>
                        </li>
                        <li{{ (Request::is('admin/settings/statuslabels*') ? ' class="active"' : '') }}>
                            <a href="{{ URL::to('admin/settings/statuslabels') }}">
                                <i class="fa fa-list"></i> @lang('general.status_labels')
                            </a>
                        </li>
                        <li{{ (Request::is('admin/settings/depreciations*') ? ' class="active"' : '') }}>
                            <a href="{{ URL::to('admin/settings/depreciations') }}">
                                <i class="fa fa-arrow-down"></i> @lang('general.depreciation')
                            </a>
                        </li>
                        <li{{ (Request::is('admin/settings/locations*') ? ' class="active"' : '') }}>
                            <a href="{{ URL::to('admin/settings/locations') }}">
                                <i class="fa fa-globe"></i> @lang('general.locations')
                            </a>
                        </li>
                        <li{{ (Request::is('admin/groups*') ? ' class="active"' : '') }}>
                            <a href="{{ URL::to('admin/groups') }}">
                                <i class="fa fa-group"></i> @lang('general.groups')
                            </a>
                        </li>
                        <li class="divider"></li>
                        <li>
                            <a href="{{ route('app') }}">
                                <i class="fa fa-cog"></i> @lang('general.settings')
                            </a>
                        </li>

                    </ul>
                </li>
                @endif

            @else
                    <li {{{ (Request::is('auth/signin') ? 'class="active"' : '') }}}><a href="{{ route('signin') }}">@lang('general.sign_in')</a></li>
            @endif
            </ul>
    </header>
    <!-- end navbar -->
    
    @if (Sentry::check())
    <!-- sidebar -->
    <div id="sidebar-nav">
        <ul id="dashboard-menu">

            @if(Sentry::getUser()->hasAccess('request') || Sentry::getUser()->hasAccess('authorize'))
                <li{{ (Request::is('*/') ? ' class="active"><div class="pointer"><div class="arrow"></div><div class="arrow_border"></div></div>' : '>') }}
                    <a href="{{ URL::to('/'); }}"><i class="fa fa-dashboard"></i><span>Dashboard</span></a>
                </li>
                <li {{ (Request::segment(1) == 'request' ? 'class="active"><div class="pointer"><div class="arrow"></div><div class="arrow_border"></div></div>': '>')}}
                    <a href="{{ URL::to('request*') }}" class="dropdown-toggle">
                    <i class="fa fa-laptop"></i>
                        <span>@lang('general.request')</span>
                        <b class="fa fa-chevron-down"></b>
                    </a>

                    <ul class="submenu{{ (Request::is('request*') ? ' active' : '') }}">
                        <li{{ (Request::is('request*') ? ' class="active"><div class="pointer"><div class="arrow"></div><div class="arrow_border"></div></div>' : '>') }}
                            <a href="{{ URL::to('request/create?type=license') }}">@lang('request.requestLicense')</a>
                        </li>
                        <li{{ (Request::is('request') ? ' class="active"><div class="pointer"><div class="arrow"></div><div class="arrow_border"></div></div>' : '>') }}
                            <a href="{{ URL::to('request/create?type=account') }}">@lang('request.requestAccount')</a>
                        </li>
                        <li class="divider">&nbsp;</li>
                        @if(Sentry::getUser()->hasAccess('admin'))
                            <li{{ (Request::is('request') ? ' class="active"><div class="pointer"><div class="arrow"></div><div class="arrow_border"></div></div>' : '>') }}
                                <a href="{{ URL::to('request') }}">
                                    @lang('request.viewAll')
                                </a>
                            </li>
                        @endif
                        @foreach($roles as $key => $value)
                            @if(Sentry::getUser()->role->role == $value || Sentry::getUser()->role->role == "All")
                                <li>
                                    <a href="{{ URL::to('request?roleId='.$key) }}">
                                        @lang('request.'.$value)
                                    </a>
                                </li>
                            @endif
                        @endforeach
                    </ul>

                </li>
            @endif

            <li{{ (Request::is('hardware*') ? ' class="active"><div class="pointer"><div class="arrow"></div><div class="arrow_border"></div></div>' : '>') }}
                <a href="{{ URL::to('hardware') }}" class="dropdown-toggle">
                    <i class="fa fa-barcode"></i>
                    <span>@lang('general.assets')</span>
                    <b class="fa fa-chevron-down"></b>
                </a>

                <ul class="submenu{{ (Request::is('hardware*') ? ' active' : '') }}">
                    @if(Sentry::getUser()->hasAccess('admin'))
                        <li>
                            <a href="{{ URL::to('hardware') }}">All Assets</a>
                        </li>
                    @endif
                    @foreach($roles as $key => $value)
                        @if(Sentry::getUser()->role->role == $value || Sentry::getUser()->role->role == "All")
                            <li>
                                <a href="{{ URL::to('asset?roleId='.$key) }}">
                                    @lang('admin/hardware/general.'.$value)
                                </a>
                            </li>
                        @endif
                    @endforeach
                </ul>
            </li>

            <li>
                <a href="{{URL::to('licenses')}}" class="dropdown-toggle">
                    <i class="fa fa-certificate"></i>
                    <span>@lang('general.licenses')</span>
                    <b class="fa fa-chevron-down"></b>
                </a>

                <ul class="submenu">
                    @if(Sentry::hasAccess('admin'))
                        <li>
                            <a href="{{URL::to('admin/licenses')}}">@lang('licenses.all')</a>
                        </li>
                    @endif

                    @foreach($roles as $key => $value)
                        @if(Sentry::getUser()->role->role == $value || Sentry::getUser()->role->role == "All")
                            <li>
                                <a href="{{URL::to('licenses?roleId='.$key)}}">@lang('admin/licenses/general.'.$value)</a>
                            </li>
                        @endif
                    @endforeach
                </ul>
            </li>
        </ul>
    </div>
    <!-- end sidebar -->

    @endif


<!-- main container -->
    <div class="content">

        <div id="pad-wrapper">

                <!-- Notifications -->
                @include('frontend/notifications')

                <!-- Content -->
                @yield('content')

        </div>
        
    </div>

    <footer>

        <div id="footer" class="col-md-offset-2 col-md-9 col-sm-12 col-xs-12 text-center">
		                <div class="muted credit">
	                  			<a target="_blank" href="http://snipeitapp.com">Snipe IT</a> is a free open source
					  		project by
					  			<a target="_blank" href="http://twitter.com/snipeyhead">@snipeyhead</a>.
						  		<a target="_blank" href="https://github.com/snipe/snipe-it">Fork it</a> |
						  		<a target="_blank" href="http://snipeitapp.com/documentation/">Documentation</a> |
						  		<a href="https://crowdin.com/project/snipe-it">Help Translate It! </a> |
						  		<a target="_blank" href="https://github.com/snipe/snipe-it/issues?state=open">Report a Bug</a>
						  		 &nbsp; &nbsp; ({{{  Config::get('version.app_version') }}})
                  		</div>
        </div>
    </footer>

    <!-- end main container -->

    <div class="modal fade" id="dataConfirmModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title" id="myModalLabel"></h4>
                </div>
                <div class="modal-body"></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button><a class="btn btn-danger btn-sm" id="dataConfirmOK">@lang('general.yes')</a>
                </div>
            </div>
        </div>
    </div>

    <!-- scripts -->
    <script src="{{ asset('assets/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('assets/js/jquery.knob.js') }}"></script>
    <script src="{{ asset('assets/js/select2.min.js') }}"></script>
    <script src="{{ asset('assets/js/jquery.uniform.min.js') }}"></script>
    <script src="{{ asset('assets/js/bootstrap.datepicker.js') }}"></script>
    <script src="{{ asset('assets/js/theme.js') }}"></script>
    <script src="{{ asset('assets/js/snipeit.js') }}"></script>
	<script>
		$(function(){
			$( ".ColVis" ).prepend('<button class="ColVis_Button">Reset Defaults</button>').click(function(e){
				e.preventDefault();
				oTable.api().state.clear();
				window.location.reload();
			});
		});
	</script>
    </body>
</html>
