@extends('backend/layouts/default')

{{-- Page title --}}
@section('title')
@lang('general.dashboard') ::
@parent
@stop

{{-- Page content --}}
@section('content')

<link rel="stylesheet" type="text/css" href="{{ asset('assets/css/lib/morris.css') }}">

<!-- morrisjs -->
<script src="{{ asset('assets/js/raphael-min.js') }}"></script>
<script src="{{ asset('assets/js/morris.min.js') }}"></script>

<h1>@lang('general.dashboard')</h1>
<div class="row">
	@foreach($ec as $key => $role)
		<div class="col-xs-4">
			<a href="{{ URL::to('dashboard/'.$key) }}"> {{ $role }} </a>
		</div>
	@endforeach
</div>

@stop
