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
				<td>{{( !empty($obj->request) ? "<a href='".URL::to('request/'.$obj->request->id)."'>".Lang::get('admin/licenses/general.in_request')."</span></a>" : $obj->assignedAsset )}}</td>
				<td>{{( !empty($obj->request) ? "<a href='".URL::to('request/'.$obj->request->id)."'>".Lang::get('admin/licenses/general.in_request')."</span>" : $obj->assignedUser )}}</td>
				<td>{{$obj->updatedAt}}</td>
				@if($obj->request)
					<td><a href="{{URL::to('licenses/'.$obj->id)}}" class="cancel-request btn btn-primary btn-xs">Cancel Request?</a></td>
				@elseif($obj->assignedUser || $obj->assignedAsset)
					<td>
						<a href="{{URL::to('licenses/'.$obj->id)}}" class='checkin-license btn btn-primary btn-xs'>Check In</a>
						<a href="{{URL::to('licenses/'.$obj->id)}}" class='move-license btn btn-primary btn-xs'>Move</a>
					</td>
				@else
					<td></td>
				@endif
			</tr>
			@endforeach
		</tbody>
	</table>
</div>
@stop