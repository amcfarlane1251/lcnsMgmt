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
	            <a id="request-status" class="pull-right btn btn-default" href="{{ URL::to('role/'.$roleId.'/request') }}">@lang('request.open')</a>
        	@else
        		<a id="request-status" class="pull-right btn btn-default" href="{{ URL::to('role/'.$roleId.'/request?reqCode=closed') }}">@lang('request.closed')</a>
        	@endif
        </div>
        <h3> @if(Request::is('request')) @lang('request.closed') @else @lang('request.open') @endif </h3>
	</div>

		<table class="table table-striped table-hover" id="requests">
			<thead>
			    <tr>
			        <td>Requester</td>
			        <td>Computer Name</td>
			        <td>EC</td>
			        <td>Type(s)</td>
			        <td>Date Requested</td>
			        <td>Actions</td>
			    </tr>
			</thead>
			<tbody>
				@foreach($requests as $request)
					<tr id="request-{{$request->id}}">
						<td>{{ $request->owner->first_name ." ".$request->owner->last_name}}</td>
						<td>{{ $request->pc_name }}</td>
						<td><a href="{{ URL::to('request?roleId='.$request->role_id) }}"/> {{ $request->roles->role }} </a></td>
						<td>
							@foreach($request->licenseTypes as $key => $lcns) 
								@if($key == (count($request->licenseTypes) - 1)) 
									{{$lcns->name}}
								@else {{$lcns->name.", "}} 
								
								@endif 
							@endforeach
						</td>
						<td>{{ $request->created_at }}</td>
						<td>
						@if($user->hasAccess('admin') || $user->role->id == $request->role_id)
							<a class="action-link" href="{{URL::to('request/'.$request->id.'/edit')}}"><i class="fa fa-pencil icon-white"></i></a>
							<a id="{{$request->id}}" class="action-link delete-request" href="{{ URL::to('request/'.$request->id)}}">
								<i class="fa fa-trash icon-red"></i>
							</a>
						@endif
						@if($user->hasAccess('admin'))
							<a class = "action-link" href="{{ URL::to('request/'.$request->id.'/approve') }}"><i class="fa fa-check icon-white"></i></a>
						@endif
						</td>
					</tr>
				@endforeach
			</tbody>
		</table>
	</div>

	<script>

		// event handler for deleting a request
		$('.delete-request').click(function(e){
			e.preventDefault() ? e.preventDefault() : e.returnValue = false;
			var reqId = $(this).attr('id');
			var url = $(this).attr('href');

			$.ajax({
				url:url,
				type:'DELETE',
				dataType:'json',
				success:function(data){
					if(!data.error)
					{
						var row = $('tr#request-'+reqId);
						row.find('td').css('background-color', 'transparent');
						
						row.addClass('delete-highlight');
						row.fadeOut('slow');
					}
				},
				error:function(response){
					//
				}
			});
		});

		// event handler for switching between open/closed req's
		$('#request-status').click(function(e){
			e.preventDefault() ? e.preventDefault() : e.returnValue = false;
			var urlArr = $(this).attr('href').split('?');
			if(urlArr[1]){
				var query = urlArr[1].split('=');
				reqCode = query[1];
			}
			else{
				reqCode = '';
			}

			//seperate uri segments into array
			var pathArr = $(this).attr('href').split('/');
			//get the base uri of the application
			var baseUri = $(this).attr('href').split('/request');
			baseUri = baseUri[0];

			//switching to closed requests
			if(reqCode){
				//get the url for use in the ajax request 
				url = $(this).attr('href');

				$(this).attr('href', baseUri+"/request");
				$('.page-header h3').text('Closed Requests');
				$(this).text('Open Requests');
			}
			//switching to open requests
			else{
				url = $(this).attr('href');

				$(this).attr('href', baseUri+"/request?reqCode=closed");
				$('.page-header h3').text('Open Requests');
				$(this).text('Closed Requests');
			}

			$.ajax({
				type:'GET',
				url:url,
				dataType:'json',
				success:function(response, status, xhr){
					var table = $('table#requests tbody');
					table.empty();
					requests = response.requests;
					for(var i=0;i<requests.length;i++){
						var obj = requests[i];
						console.log(obj['actions']);
						//format the license names
						obj['lcnsNames'] = '';
						for(x=0;x<obj['lcnsTypes'].length;x++){
							obj['lcnsNames'] += obj['lcnsTypes'][x]['name'];
							(x == (obj['lcnsTypes'].length -1) ? '' : obj['lcnsNames'] += ', ' );
						}

						var tableRow = "<tr>"+
								"<td>"+obj['requester']+"</td>"+
								"<td>"+obj['pc_name']+"</td>"+
								"<td>"+obj['role']+"</td>"+
								"<td>"+obj['lcnsNames']+"</td>"+
								"<td>"+obj['created_at']+"</td>";
						if(response.roleId == obj['role_id'] || response.roleId == 1)
						{
							tableRow += "<td>"+(obj['actions'] ? obj['actions'] : "")+"</td>";
						}
						tableRow +="</tr>";
						table.append(tableRow);
					}
				},
				error:function(response){
					//
				}
			});
		});
	</script>
@stop