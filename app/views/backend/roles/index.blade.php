@extends('backend/layouts/default')

{{-- Page title --}}
@section('title')
	@lang('general.roles') ::
@parent
@stop

{{-- page content --}}
@section('content')

<div class="row header">
	<div class="col-md-12">
		<a href="roles/create" class="btn btn-success pull-right">
			<i class="fa fa-plus icon-white"></i> @lang('general.create')
		</a>
	</div>
</div>


<div class="row form-wrapper">
	<table id="example">
	    <thead>
	        <tr role="row">
	            <th class="col-md-3">@lang('admin/groups/table.name')</th>
	            <th class="col-md-2">@lang('admin/groups/table.users')</th>
	            <th class="col-md-2">@lang('general.created_at')</th>
	            <th class="col-md-1 actions">@lang('table.actions')</th>
	        </tr>
	    </thead>
	    <tbody>
	    	@if($roles->count() >= 1)
	    		@foreach($roles as $role)
	    			<tr>
	    				<td>{{ $role->role }}</td>
	    			</tr>
	    		@endforeach
	    	@else
	    	<tr>
	    		<td colspan="5">@lang('general.no_results')</td>
	    	</tr>
	    	@endif
	    </tbody>
	</table>
</div>
@stop


