@extends('backend/layouts/default')

@section('title')
	@lang('requests.title')
@parent
@stop

@section('content')
	<div class="page-header">
        <div class="pull-right">
            <a href="{{ URL::previous() }}" class="btn-flat gray"><i class="fa fa-arrow-left icon-white"></i>  @lang('general.back')</a>
        </div>
		<h3>Check In Asset Request</h3>
	</div>

		<div class="col-md-12 column">
				<div class="row">
					<div class="col-md-2">
						{{ Form::label('', Lang::get('request.unit'), array('class'=>'control-label')) }}
					</div>
					<div class="col-md-4">
						<p>{{$asset->unit->name}}</p>
					</div>
				</div>
			
				<div class="row">
					<div class="col-md-2">
						{{ Form::label('', Lang::get('request.ec'), array('class'=>'control-label')) }}
					</div>
					<div class="col-md-4">
						<p>{{$asset->roles->role}}</p>
					</div>
				</div>
			
				<div class="row">
					<div class="col-md-2">
						{{ Form::label('', Lang::get('request.pcName'), array('class'=>'control-label')) }}
					</div>
					<div class="col-md-4">
						<p>{{$asset->asset_tag}}</p>
					</div>
				</div>
			
				<div class="row">
					<div class="col-md-2">
						{{ Form::label('', Lang::get('admin/hardware/general.owner'), array('class'=>'control-label')) }}
					</div>
					<div class="col-md-4">
						<p>{{$asset->assignedTo->first_name." ".$asset->assignedTo->last_name}}</p>
					</div>
				</div>
		</div>
	
	<section class="assigned-licenses">
		<div class="col-lg-12">
			<h3>{{Lang::get('request.licenses.checkin')}}</h3>
					@foreach($asset->licenseseats as $licenseSeat)
							<h5><a href="{{URL::to('licenses/'.$licenseSeat->id)}}">{{$licenseSeat->license->name}}</a></h5>
					@endforeach
		</div>
	</section>
@stop