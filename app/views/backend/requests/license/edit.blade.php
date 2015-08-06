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
			<h3> @lang('request.approveLicense') </h3>
    	@else
        	<h3> @lang('request.requestLicense') </h3>
        @endif
	</div>

	<div class="row form-wrapper">
		<div class="col-md-12 column">
			@if($isApprover)
				{{ Form::open(array('class'=>'form-horizontal')) }}
			@else
				{{ Form::open(array('url'=>'request','class'=>'form-horizontal')) }}
			@endif
			<div class="form-group {{ $errors->has('pcName') ? 'has-error' : '' }}">
				<div class="col-md-2">
					{{ Form::label('pcName', Lang::get('request.pcName'), array('class'=>'control-label')) }}
				</div>
				<div class="col-md-4">
					{{ Form::text('pcName', $request->pc_name, array('class'=>'form-control')) }}
					{{ $errors->first('pcName', '<br><span class="alert-msg">:message</span>') }}
				</div>
			</div>

			<div class="form-group {{ $errors->has('ec') ? 'has-error' : '' }}">
				<div class="col-md-2">
					{{ Form::label('ec', Lang::get('general.ec'), array('class'=>'control-label')) }}
				</div>
				<div class="col-md-4">
					{{ Form::select('ec', $ec, $request->role_id, array('class'=>'form-control')) }}
					{{ $errors->first('ec', '<br><span class="alert-msg">:message</span>') }}
				</div>
			</div>

			<div class="form-group {{ $errors->has('lcnsTypes') ? 'has-error' : '' }}">
				<div class="col-md-2">
					{{ Form::label('lcnsTypes', Lang::get('admin/licenses/general.types'), array('class'=>'control-label')) }}
				</div>
				<div class="col-md-4">
					{{ Form::select('lcnsTypes[]', $lcnsTypes, $request->licenseTypes()->lists('id'), array('class'=>'select2', 'style'=>'width:240px', 'multiple'=>'multiple')) }}
					{{ $errors->first('lcnsTypes', '<br><span class="alert-msg">:message</span>') }}
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