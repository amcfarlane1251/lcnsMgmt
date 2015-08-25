@extends('backend/layouts/default')

@section('title')
	@lang('requests.title')
@parent
@stop


{{-- Page content --}}
@section('content')
	<div class="page-header">
        <div class="pull-right">
        	@if(Request::is('request/closed'))
	            <a id="request-status" class="pull-right btn btn-default" href="{{ URL::to('request') }}">@lang('request.open')</a>
        	@else
        		<a id="request-status" class="pull-right btn btn-default" href="{{ URL::to('request/status/closed') }}">@lang('request.closed')</a>
        	@endif
        </div>
        <h3> @if(Request::is('request/closed')) @lang('request.closed') @else @lang('request.open') @endif </h3>
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
					<tr>
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
						@if($user->hasAccess('admin') || $user->id == $request->user_id)
							<a href="{{URL::to('request/'.$request->id.'/edit')}}"><i class="fa fa-pencil icon-white"></i></a>@endif
						@if($user->hasAccess('admin'))
							<a href="{{ URL::to('request/'.$request->id.'/approve') }}"><i class="fa fa-check icon-white"></i></a>
						@endif
						</td>
					</tr>
				@endforeach
			</tbody>
		</table>
	</div>

	<script>

		$('#request-status').click(function(e){
			e.preventDefault() ? e.preventDefault() : e.returnValue = false;

			//seperate uri segments into array
			var pathArr = $(this).attr('href').split('/');
			//get the base uri of the application
			var baseUri = $(this).attr('href').split('/request');
			baseUri = baseUri[0];
			
			var reqCode = '';

			//switching to closed requests
			if(jQuery.inArray('closed', pathArr) >= 0){
				//get the url for use in the ajax request 
				url = $(this).attr('href');

				$(this).attr('href', baseUri+"/request");
				$('.page-header h3').text('Closed Requests');
				$(this).text('Open Requests');

				reqCode = 'closed';
			}
			//switching to open requests
			else{
				url = $(this).attr('href');

				$(this).attr('href', baseUri+"/request/status/closed");
				$('.page-header h3').text('Open Requests');
				$(this).text('Closed Requests');

				reqCode = '';
			}

			$.ajax({
				type:'GET',
				url:url,
				dataType:'json',
				success:function(response, status, xhr){
					var table = $('table#requests tbody');
					table.empty();

					for(var i=0;i<response.length;i++){
						var obj = response[i];
						//format the license names
						obj['lcnsNames'] = '';
						for(x=0;x<obj['lcnsTypes'].length;x++){
							obj['lcnsNames'] += obj['lcnsTypes'][x]['name'];
							(x == (obj['lcnsTypes'].length -1) ? '' : obj['lcnsNames'] += ', ' );
						}

						table.append(
							"<tr>"+
								"<td>"+obj['requester']+"</td>"+
								"<td>"+obj['pc_name']+"</td>"+
								"<td>"+obj['role']+"</td>"+
								"<td>"+obj['lcnsNames']+"</td>"+
								"<td>"+obj['created_at']+"</td>"+
								"<td>"+(obj['actions'] ? obj['actions'] : "")+"</td>"+
							"</tr>"
						);
					}
				},
				error:function(response){
					console.log(response);
				}
			});

		});
	</script>
@stop