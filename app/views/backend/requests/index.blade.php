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
	            <a id="request-status" class="pull-right btn btn-default" href="{{ URL::to('request?roleId='.$roleId) }}">@lang('request.open')</a>
        	@else
        		<a id="request-status" class="pull-right btn btn-default" href="{{ URL::to('request?reqCode=closed&roleId='.$roleId) }}">@lang('request.closed')</a>
        	@endif
        </div>
        <h3> @if(Request::is('request?reqCode=closed')) @lang('request.open') @else @lang('request.open') @endif </h3>
	</div>
	<div class="btn-group" role="group" aria-label="..." data-type="{{$type}}" data-url="{{URL::to('request')}}" data-role="{{$roleId}}">
	 	<button type="button" class="btn btn-default" data-type="license">Licenses</button>
		<button type="button" class="btn btn-default" data-type="account">Accounts</button>
	</div>
	<div id="requests">
		<table class="table table-striped table-hover" id="requests">
			<thead>

			</thead>
			<tbody>
				
			</tbody>
		</table>
	</div>

	<script>
		$(function(){
			var reqs = new Requests();
			
			/*
			function createTable(table, data, roleId) {
				for(var i=0;i<data.length;i++){
					var obj = data[i];
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
					if(roleId == obj['role_id'] || roleId == 1)
					{
						tableRow += "<td>"+(obj['actions'] ? obj['actions'] : "")+"</td>";
					}
					tableRow +="</tr>";
					table.append(tableRow);
				}
			}

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

			// event handler for switching between licenses and accounts
			$('.btn-group button').click(function(e){
				e.preventDefault() ? e.preventDefault() : e.returnValue = false;
				var container = $('#requests table');
				var url = $(this).data('url');
				var role = $(this).data('role')
				var type = $(this).data('type')

				$.ajax({
					url:url,
					data:{
						'roleId':role,
						'type':type
					},
					type:'GET',
					dataType:'json',
					success:function(data){
						console.log(data.requests);
						container.fadeOut();
						container.find('thead').empty().append('<tr><td>Requester</td><td>Computer Name</td><td>EC</td><td>Type(s)</td><td>Date Requested</td><td>Actions</td></tr>');
						container.find('tbody').empty();
						
						createTable(container.find('tbody'), data.requests, data.roleId);
						container.fadeIn();
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
					roleId = query[2]
				}
				else{
					reqCode = '';
					roleId = query[2]
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

					$(this).attr('href', baseUri+"/request?roleId="+roleId);
					$('.page-header h3').text('Closed Requests');
					$(this).text('Open Requests');
				}
				//switching to open requests
				else{
					url = $(this).attr('href');

					$(this).attr('href', baseUri+"/request?reqCode=closed&roleId="+roleId);
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
			});*/
		});
	</script>
@stop