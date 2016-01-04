@extends('backend/layouts/default')

@section('title')
	@lang('request.title')
@parent
@stop

@section('content')
<div class="row">
	<div class="page-header col-md-12">
		<h1>Check-In Request</h1>
	</div>
</div>

<div class="row">
	<div class="col-md-6 item-details">
		<h2>@lang('request.details')</h2>
		<div>
			<label>@lang('request.status'):</label>
			@if($request->request_code==1)
				@lang('request.status.bmo')
			@elseif($request->request_code==2)
				@lang('request.approved')
			@else
				@lang('request.status.authorizer')
			@endif
		</div>
		<div>
			<label>@lang('general.ec'):</label> {{$request->roles->role}}
		</div>
		<div>
			<label>@lang('general.unit'):</label> {{$request->unit->name}}
		</div>
		<div>
			<label>@lang('request.requester'):</label> {{$request->owner->first_name." ".$request->owner->last_name}} 
		</div>
		@if($request->pc_name)
			<div class="row transition-block">
				<div class="col-sm-5">
					<label>@lang('request.pcName.original'):</label> {{$request->pc_name}}
				</div>
				<div class="col-sm-2">
					<i class="fa fa-long-arrow-right" style="font-size:2.25rem;"></i>
				</div>
				<div class="col-sm-5">
					<label>@lang('request.pcName.empty')</label>
				</div>
			</div>
		@endif

		<h2>@lang('request.accountInfo')</h2>
		<div class="row transition-block">
			<div class="col-sm-5">
				<label>@lang('account.username.original'):</label> <p>{{($request->account ? $request->account->username : '')}}</p>
				<label>@lang('account.name.original'):</label> <p>{{$request->account ? $request->account->first_name." ".$request->account->last_name : ''}}</p>
			</div>
			<div class="col-sm-2">
				<i class="fa fa-long-arrow-right" style="font-size:2.25rem;"></i>
			</div>
			<div class="col-sm-5">
				<label>@lang('request.userInfo.empty')</label>
			</div>
		</div>
	</div>

	<div class="col-md-6 item-details">
		<h2>@lang('request.requestedLcns')</h2>
		<div><a href="">{{$license->name}}</a></div>
	</div>
</div>

@stop