@extends('backend/layouts/default')

@section('title')
	@lang('request.title')
@parent
@stop

@section('content')
<div class="row">
	<div class="page-header col-md-12">
		<h1>View Request</h1>
	</div>
</div>

<div class="row">
	<div class="col-md-6 item-details">
		<h2>@lang('request.details')</h2>
		<div>
			<label>@lang('request.requester'):</label> {{$request->owner->first_name." ".$request->owner->last_name}} 
		</div>
		<div>
			<label>@lang('request.pcName'):</label> {{$request->pc_name}}
		</div>
		<div>
			<label>@lang('request.ec'):</label> {{$request->roles->role}}
		</div>
	</div>

	<div class="col-md-6 item-details">
		<h2>@lang('request.requestedLcns')</h2>
		@foreach($request->licenseTypes as $lcns)
			<div><a href="">{{$lcns->name}}</a></div>
		@endforeach
	</div>
</div>

@stop