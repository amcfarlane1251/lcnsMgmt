@extends('backend/layouts/default')

@section('title')
	@lang('requests.title')
@parent
@stop


{{-- Page content --}}
@section('content')
	<div class="page-header">
        <div class="pull-right">
        	@if(Input::get('reqCode') == '1'){
	            <a id="request-status" class="pull-right btn btn-default" href="{{ URL::to('request') }}">@lang('request.open')</a>
        	}
        	@else
        		<a id="request-status" class="pull-right btn btn-default" href="{{ URL::to('request?reqCode=closed') }}">@lang('request.closed')</a>
        	@endif
        </div>
        <h3> @lang('request.all') </h3>
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
						<td>{{ $request->roles->role }}</td>
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
			var pathArr = $(this).attr('href').split('?');
			if(pathArr.length > 1){
				pathArr = pathArr[1].split('=');
				reqCode = pathArr[1];
				$(this).attr('href', 'request');
				$(this).text('Open Requests');
			}
			else{
				$(this).attr('href', 'request?reqCode='+reqCode);
				$(this).text('Closed Requests');
				reqCode = '';
			}
			console.log(reqCode);

			$.ajax({
				type:'GET',
				url:location.href,
				data:{reqCode: reqCode},
				dataType:'json',
				success:function(response, status, xhr){
					var table = $('table#requests tbody');
					table.empty();
					for(var i=0;i<response.length;i++){
						var obj = response[i];
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
								"<td>"+obj['actions']+"</td>"+
							"</tr>"
						);
					}
				}
			});

		});
	</script>
@stop