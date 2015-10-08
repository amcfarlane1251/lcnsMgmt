@extends('backend/layouts/default')

@section('title')
	@lang('requests.title')
@parent
@stop


{{-- Page content --}}
@section('content')
	<div class="page-header">
        <div class="pull-right">
            <a href="{{ URL::previous() }}" class="btn-flat gray"><i class="fa fa-arrow-left icon-white"></i>  @lang('general.back')</a>
        </div>
        @if($isApprover)
			<h3> @lang('request.approveAccount') </h3>
    	@else
        	<h3> @lang('request.requestAccount') </h3>
        @endif
	</div>

	<div class="row form-wrapper">
		<div class="col-md-12 column">
			@if($isApprover)
				{{ Form::open(array('class'=>'form-horizontal')) }}
			@else
				{{ Form::open(array('url'=>'request','class'=>'form-horizontal')) }}
			@endif

			<input type="hidden" name="type" value="account"/>
			<h2>{{ Lang::get('request.userInfo') }}</h2>
			<div class="form-group {{ $errors->has('lastName') ? 'has-error' : '' }}">
				<div class="col-md-2">
					{{ Form::label('lastName', Lang::get('account.lastName'), array('class'=>'control-label')) }}
				</div>
				<div class="col-md-4">
					{{ Form::text('lastName', $account->last_name, array('class'=>'form-control')) }}
					{{ $errors->first('lastName', '<br><span class="alert-msg">:message</span>') }}
				</div>
			</div>

			<div class="form-group {{ $errors->has('firstName') ? 'has-error' : '' }}">
				<div class="col-md-2">
					{{ Form::label('firstName', Lang::get('account.firstName'), array('class'=>'control-label')) }}
				</div>
				<div class="col-md-4">
					{{ Form::text('firstName', $account->first_name, array('class'=>'form-control')) }}
					{{ $errors->first('firstName', '<br><span class="alert-msg">:message</span>') }}
				</div>
			</div>

			<div class="form-group {{ $errors->has('email') ? 'has-error' : '' }}">
				<div class="col-md-2">
					{{ Form::label('email', Lang::get('account.email'), array('class'=>'control-label')) }}
				</div>
				<div class="col-md-4">
					{{ Form::email('email', $account->email, array('class'=>'form-control', 'type' => 'email')) }}
					{{ $errors->first('email', '<br><span class="alert-msg">:message</span>') }}
				</div>
			</div>

			<div class="form-group {{ $errors->has('empType') ? 'has-error' : '' }}">
				<div class="col-md-2">
					{{ Form::label('empType', Lang::get('account.empType'), array('class'=>'control-label')) }}
				</div>
				<div class="col-md-4">
					{{ Form::select('empType', $empTypes, $account->emp_type_id, array('class'=>'form-control')) }}
					{{ $errors->first('empType', '<br><span class="alert-msg">:message</span>') }}
				</div>
			</div>

			<div class="form-group {{ $errors->has('empNum') ? 'has-error' : '' }}">
				<div class="col-md-2">
					{{ Form::label('empNum', Lang::get('account.empNum'), array('class'=>'control-label')) }}
				</div>
				<div class="col-md-4">
					{{ Form::text('empNum', $account->emp_num, array('class'=>'form-control')) }}
					{{ $errors->first('empNum', '<br><span class="alert-msg">:message</span>') }}
				</div>
			</div>

			<h2>{{ Lang::get('request.unitInfo') }}</h2>
			<div class="form-group {{ $errors->has('ec') ? 'has-error' : '' }}">
				<div class="col-md-2">
					{{ Form::label('ec', Lang::get('general.ec'), array('class'=>'control-label')) }}
				</div>
				<div class="col-md-4">
					{{ Form::select('ec', $ec, $account->role_id, array('class'=>'form-control')) }}
					{{ $errors->first('ec', '<br><span class="alert-msg">:message</span>') }}
				</div>
			</div>
			<div class="form-group {{ $errors->has('unitName') ? 'has-error' : '' }}">
				<div class="col-md-2">
					{{ Form::label('unitName', Lang::get('account.unitName'), array('class'=>'control-label')) }}
				</div>
				<div class="col-md-4">
					{{ Form::text('unitName', $account->unit_name, array('class'=>'form-control')) }}
					{{ $errors->first('unitName', '<br><span class="alert-msg">:message</span>') }}
				</div>
			</div>

			<div class="form-group {{ $errors->has('location') ? 'has-error' : '' }}">
				<div class="col-md-2">
					{{ Form::label('location', Lang::get('account.location'), array('class'=>'control-label')) }}
				</div>
				<div class="col-md-4">
					{{ Form::text('location', $account->location, array('class'=>'form-control')) }}
					{{ $errors->first('location', '<br><span class="alert-msg">:message</span>') }}
				</div>
			</div>

			@if(isset($approver))
				<div class="form-group">
					<div class="col-md-2">
						{{ Form::label('approve', Lang::get('request.approve'), array('class'=>'control-label')) }}
					</div>
					<div class="col-md-4">
						{{ Form::select('approve', array('0' => 'No', '1' => 'Yes'), 1) }}
					</div>
				</div>
			@endif

			{{ Form::submit('Submit Request') }}

		</div>
	</div>
@stop