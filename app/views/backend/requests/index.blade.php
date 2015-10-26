@extends('backend/layouts/default')

@section('title')
	@lang('requests.title')
@parent
@stop


{{-- Page content --}}
@section('content')
	<div class="page-header">
        <div class="pull-right">
        	@if(Request::is('request?reqCode=closed'))
	            <a id="request-status" class="pull-right btn btn-default" href="{{ URL::to('request?roleId='.$roleId) }}">@lang('request.open')</a>
        	@else
        		<a id="request-status" class="pull-right btn btn-default" href="{{ URL::to('request?reqCode=closed&roleId='.$roleId) }}">@lang('request.closed')</a>
        	@endif
        </div>
        <h3> @if(Request::is('request?reqCode=closed')) @lang('request.open') @else @lang('request.open') @endif </h3>
	</div>
	<div class="btn-group" role="group" aria-label="..." data-type="{{$type}}" data-url="{{URL::to('request')}}" data-role="{{$roleId}}">
	 	<button type="button" class="btn btn-default" data-type="license">Licenses</button>
		<button type="button" class="btn btn-default" data-type="account">Accounts</button>
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