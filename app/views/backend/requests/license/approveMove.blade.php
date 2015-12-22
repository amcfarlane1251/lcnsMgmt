@extends('backend/layouts/default')

@section('title')
	@lang('request.title')
@parent
@stop

@section('content')
<div class="row">
	<div class="page-header col-md-12">
		<h1>Approve Move Request</h1>
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
					<label>@lang('request.pcName.original'):</label> {{$license->asset->asset_tag}}
				</div>
				<div class="col-sm-2">
					<i class="fa fa-long-arrow-right" style="font-size:2.25rem;"></i>
				</div>
				<div class="col-sm-5">
					<label>@lang('request.pcName.requested'):</label> {{$request->pc_name}}
				</div>
			</div>
		@endif

		<h2>@lang('request.accountInfo')</h2>
		<div class="row transition-block">
			<div class="col-sm-5">
				<label>@lang('account.username.original'):</label> <p>{{($license->account ? $license->account->username : '')}}</p>
				<label>@lang('account.name.original'):</label> <p>{{$license->account ? $license->account->first_name." ".$license->account->last_name : ''}}</p>
			</div>
			<div class="col-sm-2">
				<i class="fa fa-long-arrow-right" style="font-size:2.25rem;"></i>
			</div>
			<div class="col-sm-5">
				<label>@lang('account.username.requested'):</label> <p>{{($request->account ? $request->account->username : '')}}</p>
				<label>@lang('account.name.requested'):</label> <p>{{$request->account ? $request->account->first_name." ".$request->account->last_name : ''}}</p>
			</div>
		</div>
	</div>

	<div class="col-md-6 item-details">
		<h2>@lang('request.requestedLcns')</h2>
		@foreach($request->licenseTypes as $lcns)
			<div><a href="">{{$lcns->name}}</a></div>
		@endforeach
	</div>
</div>

<div class="row">
	<div class="col-lg-12">
		{{ Form::open(array('url'=>'request/'.$request->id, 'method'=>'PUT')) }}
		<input type="hidden" name="action" value="approve"/>
		{{ Form::submit('Approve Request', array('class'=>'btn btn-success')) }}
	</div>
</div>
@stop