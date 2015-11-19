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
		<h2>@lang('request.account.details')</h2>
		<div>
			<label>@lang('request.name'):</label> {{$account->first_name." ".$account->last_name}}
		</div>
		<div>
			<label>@lang('account.username'):</label> {{$account->username}}
		</div>
		<div>
			<label>@lang('account.email'):</label> {{$account->email}}
		</div>
		<div>
			<label>@lang('account.empType'):</label> {{$account->empType->type}}
		</div>
		<div>
			<label>@lang('account.empNum'):</label> {{$account->emp_num}}
		</div>
		<div>
			<label>@lang('account.dob'):</label> {{$account->dob}}
		</div>
		<div>
			<label>@lang('account.sfn'):</label> {{$account->sfn}}
		</div>
		<div>
			<label>@lang('account.unit'):</label> {{$account->unit->name}}
		</div>
		<div>
			<label>@lang('account.ec'):</label> {{$account->role->role}}
		</div>
	</div>
	
	<div class="col-md-6 item-details">
		<h2>@lang('request.requester.details')</h2>
		<div>
			<label>@lang('request.requester'):</label> {{$request->owner->first_name." ".$request->owner->last_name}} 
		</div>
		<div>
			<label>@lang('request.role'):</label> {{$request->roles->role}}
		</div>
		<div>
			<label>@lang('request.unit'):</label> {{$request->unit->name}}
		</div>
	</div>
	
	<div class="col-md-6 item-details clearfix">
		<h2>@lang('request.authorizer.details')</h2>
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
			<label>@lang('request.authorizedBy'):</label> {{$request->authorizer_id ? $request->authorizer->first_name." ".$request->authorizer->last_name : Lang::get('request.status.authorizer')}}
		</div>
	</div>
</div>
<div class="row">
	<div class="col-lg-12">
		{{ Form::open(array('url'=>'request/'.$request->id, 'method'=>'PUT')) }}
		<input type="hidden" name="action" value="approve"/>
		{{ Form::submit('Approve Request', array('class'=>'btn btn-approve')) }}
	</div>
</div>
</div>

@stop