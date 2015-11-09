@extends('backend/layouts/default')

{{-- Page title --}}
@section('title')
@lang('general.dashboard') ::
@parent
@stop

{{-- Page content --}}
@section('content')

<link rel="stylesheet" type="text/css" href="{{ asset('assets/css/lib/morris.css') }}">

<!-- morrisjs -->
<script src="{{ asset('assets/js/raphael-min.js') }}"></script>
<script src="{{ asset('assets/js/morris.min.js') }}"></script>

<h1>@lang('general.dashboard')</h1>
<div id="main-stats">
    <div class="row stats-row">
        <div class="col-md-3 col-sm-3 stat">
            <div class="data">
                <a href="{{ URL::to('request') }}">
                    <span class="number">{{ number_format(Requests::count('license',$user->role->id)) }}</span>
                    <span style="color:black">@lang('request.licenses')</span>
                </a>
            </div>
        </div>
        <div class="col-md-3 col-sm-3 stat">
            <div class="data">
                <a href="{{ URL::to('request?type=account') }}">
                    <span class="number">{{ number_format(Requests::count('account',$user->role->id)) }}</span>
                    <span style="color:black">@lang('request.accounts')</span>
                </a>
            </div>
        </div>
        <div class="col-md-3 col-sm-3 stat">
            <div class="data">
                <a href="{{ URL::to('request?type=checkin') }}">
                    <span class="number">{{ number_format(Requests::count('checkin',$user->role->id)) }}</span>
                       <span style="color:black">@lang('request.checkin')</span>
                </a>
            </div>
        </div>
        <div class="col-md-3 col-sm-3 stat">
            <div class="data">
                <a href="{{ URL::to('request?type=checkin') }}">
                    <span class="number">{{ number_format(License::assetcount()) }}</span>
                    <span style="color:black">@lang('request.move')</span>
                </a>
            </div>
        </div>
    </div>
    @if($user->hasAccess('admin'))
    	<div class="row stats-row">
    		@foreach($ec as $key => $role)
    			<div class="col-md-4 col-sm-4 stat">
    				<div class="data">
    					<a href="{{ URL::to('dashboard/'.$key) }}"> {{ $role }} </a>
    				</div>
    			</div>
    		@endforeach
    	</div>
    @endif
</div>

@stop
