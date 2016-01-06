@extends('backend/layouts/default')

@section('title')
	@lang('requests.title')
@parent
@stop


{{-- Page content --}}
@section('content')
	<div class="page-header">
        <div class="pull-right">
        	<a href="{{URL::to('request/create?type=license')}}" class="btn btn-primary">New Request</a>
        </div>
        <h3> @if(Request::is('request?reqCode=closed')) @lang('request.open') @else @lang('request.open') @endif </h3>
	</div>
	<div class="btn-group" role="group" aria-label="..." data-type="{{$type}}" data-url="{{URL::to('request')}}" data-role="{{$roleId}}">
	 	<button type="button" class="btn btn-default {{($type=='license' ? 'active' : '')}}" data-type="license">Licenses</button>
		<button type="button" class="btn btn-default {{($type=='account' ? 'active' : '')}}" data-type="account">Accounts</button>
		<button type="button" class="btn btn-default {{($type=='checkin' ? 'active' : '')}}" data-type="checkin">@Lang('requests.checkin')</button>
		<button type="button" class="btn btn-default {{($type=='move' ? 'active' : '')}}" data-type="move">@Lang('requests.move')</button>
	</div>
	<div id="requests">
		<table class="table table-striped table-hover" id="requests">
			<thead>
				
			</thead>
			<tbody>
				
			</tbody>
		</table>
	</div>

	<script>
		$(function(){
			var reqs = new Requests();
		});
	</script>
@stop