@extends('backend/layouts/default')

@section('title')

@parent
@stop

@section('content')
<h2>All Assets</h2>
<div class="row">
	<table class="table table-striped table-hover" id="ec-assets">
		<thead>
			<tr>
				<td>Asset Name</td>
				<td>Asset Tag</td>
				<td>Environmental Command</td>
				<td>Status</td>
				<td>Location</td>
				<td>In/Out</td>
				<td>Actions</td>
			</tr>
		</thead>

		<tbody>

		</tbody>
	</table>
</div>

<script>
	path = (location.href).split('/');
	$.ajax({
		type: "GET",
		url: location.href,
		data: { roleId : path[path.length-1] },
		dataType: 'json',
		success: function(response, status, xhr){
			for(var i=0;i<response.length;i++){
				var obj = response[i];
				$('table#ec-assets tbody').append(
					"<tr>"+
						"<td>"+obj['name']+"</td>"+
						"<td>"+obj['asset_tag']+"</td>"+
						"<td>"+obj['role']+"</td>"+
						"<td>"+obj['status']+"</td>"+
						"<td>"+obj['location']+"</td>"+
						"<td>"+obj['inOut']+"</td>"+
						"<td>"+obj['actions']+"</td>"+
					"</tr>"
				);
			}
		}
	})
</script>
@stop
