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
	<div class="col-md-12 top-bar">
		<h3 id="unit-name">{{$unit->name}}</h3>
		<div class="form-group">
			<label for="unit-selector">Select Unit:</label>
			<select id="unit-selector" class="">
				@foreach($units as $unit)
					<option value='{{$unit->id}}'>{{$unit->name}}</option>
				@endforeach
			</select>
		</div>
	</div>
	<div class="col-md-6">
		<h4>@lang('admin/licenses/general.info')</h4>
		<table id="license-info" class="table table-striped">
			<thead>
				<tr>
					<td>Type</td>
					<td>Allocated</td>
					<td>Used</td>
					<td>Remaining</td>
				</tr>
			</thead>
			<tbody>
				@foreach($licenses as $license)
					<tr>
						<td>{{ $license->name }}</td>
						<td>{{ $license->allocated }}</td>
						<td>{{ $license->used }}</td>
						<td>{{ $license->remaining }}</td>
					</tr>
				@endforeach
			</tbody>
		</table>
	</div>

	<div class="col-md-6 chart">
		<h4>@lang('Percentage Allocated by Type')</h4>
		<div id="hero-lcns" style="height: 250px;"></div>
	</div>

</div>

<div class="row">
	<div class="col-md-6">
		<h4>@lang('admin/hardware/general.info')</h4>
		<table id="asset-info" class="table table-striped">
			<thead>
				<tr>
					<td>Model</td>
					<td>Asset Tag</td>
					<td># of Lcns</td>
					<td>Location</td>
				</tr>
			</thead>
			<tbody>
				@foreach($assets as $asset)
					<tr>
						<td>{{ $asset->model }}</td>
						<td>{{ $asset->assetTag }}</td>
						<td>{{ $asset->numOfLcns }}</td>
						<td>{{ $asset->location }}</td>
					</tr>
				@endforeach
			</tbody>
		</table>
	</div>
</div>

@section('scripts')
	<script src="{{ asset('assets/js/dashboard.js') }}"></script>
@parent
@stop

<!-- build the charts -->
<script type="text/javascript">
    // Morris Donut Chart
    Morris.Donut({
        element: 'hero-lcns',
        data: [
        	@foreach($licenses as $license)
        		{label: "{{$license->name}}", value: {{ $license->percentages['allocated'] }} },
        	@endforeach
        ],
        colors: ["#30a1ec", "#76bdee", "#c4dafe"],
        formatter: function (y) { return y + "%" }
    });
</script>

@stop