@extends('backend/layouts/default')

{{-- Page title --}}
@section('title')
	Create License Type
@parent
@stop

{{-- Page content --}}
@section('content')
	<div class="row header">
		<div class="col-md-12">
				<a href="{{ URL::previous() }}" class="btn-flat gray pull-right right">
				<i class="fa fa-arrow-left icon-white"></i> @lang('general.back')</a>
			<h3>
			@if ($licenseType->id)
				Edit License Type
			@else
				Create License Type
			@endif
			</h3>
		</div>
	</div>

	<div class="row form-wrapper">
		{{ Form::open(array('url'=>'license_types/'.$licenseType->id,'class'=>'form-horizontal col-lg-12', 'method'=>$action)) }}
			<div class="form-group">
				<label for="name" class="col-md-2 control-label">Name:</label>
				<div class="col-md-4">
					<input type="text" name="name" id="name" value="{{Input::old('name')}}" class="form-control"/>
					{{ $errors->first('name', '<br><span class="alert-msg"><i class="fa fa-times"></i> :message</span>') }}
				</div>
			</div>
			
			<div class="form-group">
				<label for="asset_flag" class="col-md-2 control-label">Requires Computer Name:</label>
				<div class="col-md-4">
					<label class="radio-inline"><input type="radio" name="asset_flag" value=1>Yes</label>
					<label class="radio-inline"><input type="radio" name="asset_flag" value=0>No</label>
				</div>
			</div>
		{{ Form::submit('Submit') }}
	</div>
@stop
