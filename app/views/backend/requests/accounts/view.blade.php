@extends('backend/layouts/default')

@section('title')
	@lang('request.title')
@parent
@stop

@section('content')
<div class="row">
	<div class="page-header col-md-12">
		<h1>View Account Request</h1>
	</div>
</div>

<div class="row">
	<div class="col-md-6 item-details">
		<h2>@lang('request.details')</h2>
		<div>
			<label>@lang('request.requester'):</label> {{$request->owner->first_name." ".$request->owner->last_name}} 
		</div>
		<div>
			<label>@lang('request.name'):</label> {{$request->account->first_name." ".$request->account->last_name}}
		</div>
		<div>
			<label>@lang('request.role'):</label> {{$request->roles->role}}
		</div>
		<div>
			<label>@lang('request.email'):</label> {{$request->account->email}}
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