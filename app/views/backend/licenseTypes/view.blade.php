@extends('backend/layouts/default')

{{-- Page title --}}
@section('title')
	View License Type
@parent
@stop

{{-- Page content --}}
@section('content')

<div class="row header">
    <div class="col-md-12">
		<h3>View License Type</h3>
	</div>
</div>

<div class="row">
	<div class="col-md-6">
		<div><label>Name: </label> {{$licenseType->name}}</div>
		<div>
			<label>Requires Computer Name: </label> {{$licenseType->asset_flag == 1 ?  "Yes" : "No"}}
		</div>
	</div>
</div>

@stop