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

	<div class="row form-wrapper">
		<div class="col-md-12 column">
			{{Form::open(array('url'=>'request','class'=>'form-horizontal','method'=>'POST'))}}
			
				<div class="form-group">
					<div class="col-md-2">
						{{ Form::label('', Lang::get('request.unit'), array('class'=>'control-label')) }}
					</div>
					<div class="col-md-4">
						<p class='form-control-static'>{{$asset->unit->name}}</p>
					</div>
				</div>
			
				<div class="form-group">
					<div class="col-md-2">
						{{ Form::label('', Lang::get('request.ec'), array('class'=>'control-label')) }}
					</div>
					<div class="col-md-4">
						<p class='form-control-static'>{{$asset->roles->role}}</p>
					</div>
				</div>
			
				<div class="form-group">
					<div class="col-md-2">
						{{ Form::label('', Lang::get('request.pcName'), array('class'=>'control-label')) }}
					</div>
					<div class="col-md-4">
						<p class='form-control-static'>{{$asset->asset_tag}}</p>
					</div>
				</div>
			
				<div class="form-group">
					<div class="col-md-2">
						{{ Form::label('', Lang::get('admin/hardware/general.owner'), array('class'=>'control-label')) }}
					</div>
					<div class="col-md-4">
						<p class='form-control-static'>{{$asset->assignedTo->first_name." ".$asset->assignedTo->last_name}}</p>
					</div>
				</div>
			
				<input type='hidden' id='asset_id' name='asset_id' value='{{$asset->id}}'/>
				<input type='hidden' id='unit' name='unit' value='{{$asset->unit_id}}'/>
				<input type='hidden' id='ec' name='ec' value='{{$asset->role_id}}'/>
				<input type='hidden' id='type' name='type' value='checkin'/>
			{{Form::submit('Submit Check-In Request', array('class'=>'btn btn-success'))}}
		</div>
	</div>
	
	<section class="assigned-licenses">
		<h3>{{Lang::get('admin/hardware/general.licenses')}}</h3>
		<table class="table table-hover">
			<thead>
				<tr>
					<td>Name</td>
					<td>Last Updated</td>
					<td>Actions</td>
				</tr>
			</thead>
			<tbody>
				@foreach($asset->licenseseats as $licenseSeat)
					<tr>
						<td>{{$licenseSeat->license->name}}</td>
						<td>{{$licenseSeat->updated_at}}</td>
						<td><a href="{{URL::to('licenses/'.$licenseSeat->id)}}" class='checkin-license btn btn-primary btn-xs'>Check In</a></td>
					</tr>
				@endforeach
			</tbody>
		</table>
	</section>
@stop