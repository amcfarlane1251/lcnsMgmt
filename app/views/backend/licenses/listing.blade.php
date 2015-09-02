@extends('backend/layouts/default')

@section('title')

@parent
@stop

@section('content')
<h2>{{$heading}}</h2>

<div class="row">
	<table class="table table-striped table-hover" id="ec-assets">
		<thead>
			<tr>
				<td>@lang('admin/licenses/table.title')</td>
				<td>@lang('admin/licenses/table.license_name')</td>
				<td>@lang('admin/licenses/table.assigned_to')</td>
				<td>@lang('admin/licenses/table.updated')</td>
				<td>@lang('table.actions')</td>
			</tr>
		</thead>

		<tbody>
			@foreach($licenses as $obj)
			<tr>
				<td>{{$obj->name}}</td>
				<td>{{$obj->assignedAsset}}</td>
				<td>{{$obj->assignedUser}}</td>
				<td>{{$obj->updatedAt}}</td>
			</tr>
			@endforeach
		</tbody>
	</table>
</div>
@stop