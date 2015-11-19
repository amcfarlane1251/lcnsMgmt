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
        <h3> @lang('request.requestAccount') </h3>
	</div>

	<div class="row form-wrapper">
		<div class="col-md-12 column">
			{{ Form::open(array('url'=>'request/'.$request->id,'class'=>'form-horizontal', 'action'=>$action)) }}

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
			
			<div class="form-group {{ $errors->has('username') ? 'has-error' : '' }}">
				<div class="col-md-2">
					{{ Form::label('username', Lang::get('account.username'), array('class'=>'control-label')) }}
				</div>
				<div class="col-md-4">
					{{ Form::text('username', $account->username, array('class'=>'form-control')) }}
					{{ $errors->first('username', '<br><span class="alert-msg">:message</span>') }}
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

			<div class="form-group {{ $errors->has('dob') ? 'has-error' : ''}}">
				<div class="col-md-2">
					{{ Form::label('dob', Lang::get('account.dob'), array('class'=>'control-label')) }}
				</div>
				<div class="col-md-4">
					{{ Form::text('dob', $account->dob, array('class'=>'form-control datepicker', 'style'=>'width:223px;')) }}
					{{ $errors->first('dob', '<br><span class="alert-msg">:message</span>') }}
				</div>
			</div>
			
			<div class="form-group {{ $errors->has('sfn') ? 'has-error' : ''}}">
				<div class="col-md-2">
					{{ Form::label('sfn', Lang::get('account.sfn'), array('class'=>'control-label')) }}
				</div>
				<div class="col-md-4">
					{{ Form::text('sfn', $account->sfn, array('class'=>'form-control', 'readonly'=>true)) }}
					{{ $errors->first('altEmpNum', '<br><span class="alert-msg">:message</span>') }}
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
					{{ Form::label('unit', Lang::get('account.unit'), array('class'=>'control-label')) }}
				</div>
				<div class="col-md-4">
					{{ Form::select('unit', $units, $account->unit_id, array('class'=>'form-control')) }}
					{{ $errors->first('unit', '<br><span class="alert-msg">:message</span>') }}
				</div>
			</div>

			<div class="form-group {{ $errors->has('location') ? 'has-error' : '' }}">
				<div class="col-md-2">
					{{ Form::label('location', Lang::get('account.location'), array('class'=>'control-label')) }}
				</div>
				<div class="col-md-4">
					{{ Form::select('location', $locations, $account->location_id, array('class'=>'form-control')) }}
					{{ $errors->first('location', '<br><span class="alert-msg">:message</span>') }}
				</div>
			</div>

			{{ Form::submit('Submit Request') }}

		</div>
	</div>
	@section('scripts')
		<script src="{{ asset('assets/js/edit-account.js') }}"></script>
	@parent
	@stop
@stop