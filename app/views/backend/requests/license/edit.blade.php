@extends('backend/layouts/default')

@section('title')
	@lang('requests.title')
@parent
@stop


{{-- Page content --}}
@section('content')
	<div class="page-header">
        <div class="pull-right">
            <a href="{{ URL::previous() }}" class="btn-flat gray"><i class="fa fa-arrow-left icon-white"></i>  @lang('general.back')</a>
        </div>
        @if($isApprover)
			<h3> @lang('request.approveLicense') </h3>
    	@else
        	<h3> @lang('request.requestLicense') </h3>
        @endif
	</div>

	<div class="row form-wrapper">
		<div class="col-md-12 column">
			@if($isApprover)
				{{ Form::open(array('class'=>'form-horizontal')) }}
			@else
				{{ Form::open(array('url'=>'request','class'=>'form-horizontal')) }}
			@endif

			{{-- Unit --}}
			<div class="form-group {{ $errors->has('unit') ? 'has-error' : '' }}">
				<div class="col-md-2">
					{{ Form::label('unit', Lang::get('general.unit'), array('class'=>'control-label')) }}
				</div>
				<div class="col-md-4">
					{{ Form::select('unit', $units ? $units : array(''), $request->unit_id, array('class'=>'form-control')) }}
					{{ $errors->first('unit', '<br><span class="alert-msg">:message</span>') }}
				</div>
			</div>

			{{-- EC --}}
			<div class="form-group {{ $errors->has('ec') ? 'has-error' : '' }}">
				<div class="col-md-2">
					{{ Form::label('ec', Lang::get('general.ec'), array('class'=>'control-label')) }}
				</div>
				<div class="col-md-4">
					{{ Form::select('ec', $ec, $request->role_id, array('class'=>'form-control')) }}
					{{ $errors->first('ec', '<br><span class="alert-msg">:message</span>') }}
				</div>
			</div>

			{{-- License Types--}}
			<div class="form-group {{ $errors->has('lcnsTypes') ? 'has-error' : '' }}">
				<div class="col-md-2">
					{{ Form::label('lcnsTypes', Lang::get('admin/licenses/general.types'), array('class'=>'control-label')) }}
				</div>
				<div class="col-md-4" id="lcnsContainer">
					<select name="lcnsTypes[]" id="lcnsTypes[]" multiple="multiple" class="form-control">
						@foreach($lcnsTypes as $id => $lcnsType)
							<option value="{{$id}}"
							{{ (in_array($id, $request->licenseTypes()->lists('id')) ? 'selected="selected"' : '') }}>
							{{{ $lcnsType }}}
							</option>
						@endforeach
					</select>
				</div>
			</div>

			{{-- PC Name --}}
			<div class="form-group {{ $errors->has('pcName') ? 'has-error' : '' }} hidden" data-toggle='pcName'>
				<div class="col-md-2">
					{{ Form::label('pcName', Lang::get('request.pcName'), array('class'=>'control-label')) }}
				</div>
				<div class="col-md-4">
					{{ Form::text('pcName', $request->pc_name, array('class'=>'form-control')) }}
					{{ $errors->first('pcName', '<br><span class="alert-msg">:message</span>') }}
				</div>
			</div>

			{{-- Account --}}
			<div class="hidden" data-toggle='accountInfo'>
				<h3>Account</h3>
				<div class="btn-group" role="group" id="user-select">
					<h5>Please select an option:</h5>
					<button type='button' class="btn btn-default" data-select="existing">Existing User?</button>
					<button type='button' class="btn btn-default" data-select="new">New User?</button>
				</div>
				<div class="form-container">
					<div class="form-group {{ $errors->has('username') ? 'has-error' : '' }}">
						<div class="col-md-2">
							{{ Form::label('username', Lang::get('account.username'), array('class'=>'control-label')) }}
						</div>
						<div class="col-md-4">
							{{ Form::text('username', ($request->account ? $request->account->username : ''), array('class'=>'form-control','disabled'=>'true')) }}
							{{ $errors->first('username', '<br><span class="alert-msg">:message</span>') }}
						</div>
					</div>

					<div class="form-group {{ $errors->has('fname') ? 'has-error' : '' }}">
						<div class="col-md-2 clearfix">
							{{ Form::label('fname', Lang::get('account.firstName'), array('class'=>'control-label')) }}
						</div>
						<div class="col-md-4">
							{{ Form::text('fname', ($request->account ? $request->account->first_name : ''), array('class'=>'form-control','disabled'=>'true')) }}
							{{ $errors->first('fname', '<br><span class="alert-msg">:message</span>') }}
						</div>
					</div>

					<div class="form-group {{ $errors->has('lname') ? 'has-error' : '' }}">
						<div class="col-md-2 clearfix">
							{{ Form::label('lname', Lang::get('account.lastName'), array('class'=>'control-label')) }}
						</div>
						<div class="col-md-4">
							{{ Form::text('lname', ($request->account ? $request->account->last_name : ''), array('class'=>'form-control','disabled'=>'true')) }}
							{{ $errors->first('lname', '<br><span class="alert-msg">:message</span>') }}
						</div>
					</div>
				</div>
			</div>

			@if(isset($approver))
				<div class="form-group">
					<div class="col-md-2">
						{{ Form::label('approve', Lang::get('request.approve'), array('class'=>'control-label')) }}
					</div>
					<div class="col-md-4">
						{{ Form::select('approve', array('0' => 'No', '1' => 'Yes'), 1) }}
					</div>
				</div>
			@endif

			<div class="hidden">
				<input type="hidden" value="{{$type}}" name="type" id="type"/>
			</div>
			{{ Form::submit('Submit Request') }}
			<div id="account-id" class="col-md-3 alert alert-success hidden" style="float:none;display:inline-block;padding:2px;">
				<p><i class="fa fa-check"></i><strong>Success: </strong>Account Selected</p>
			</div>
		</div>
	</div>

	<script>
		$(function(){
			var shared = {"SABA Publisher":"accountInfo", "Pedagogue":"accountInfo"};
			toggleHelper1 = new ToggleHelper('lcnsContainer','SABA Publisher','pcName',shared);
			toggleHelper1.init();
			toggleHelper2 = new ToggleHelper('lcnsContainer','Pedagogue','accountInfo',shared);
			toggleHelper2.init();
			toggleHelper3 = new ToggleHelper('lcnsContainer','SABA Publisher','accountInfo',shared);
			toggleHelper3.init();

			var users;
			var req = $.ajax({
				url:'../accounts',
				success:function(data){
					data = JSON.parse(data);
					users = data.accounts;
				},
			});

			//select exisiting or new account
			$('#user-select .btn').click(function(e){
				$(this).siblings('.btn').removeClass('active');
				$(this).addClass('active');
				//clear and enable fields
				var usernameDOM = $('#username');
				var fnameDOM = $('#fname');
				var lnameDOM = $('#lname');

				usernameDOM.val('').prop('disabled', false);

				if( $(this).data('select') == 'existing' ){
					fnameDOM.val('').prop('disabled', true);
					lnameDOM.val('').prop('disabled', true);
		    		var userEntry;

					req.then(function(){
							usernameDOM.autocomplete({
								source: function(request, response){
									var results = [];
									var term = request.term;

									if(term.length > 0){
										for(var index in users){
											if(users[index].username.toLowerCase().indexOf(term.toLowerCase()) == 0){
												results.push(users[index]);
											}
										}
									}
									else{
										results = ['Start typing...'];
									}

									response($.map(results, function(item){
										if(item['id']){
											return{
												label: item['username'],
												firstName: item['first_name'],
												lastName: item['last_name'],
												accountId: item['id']
											}
										}
										else{
											return results;
										}
									}));
								},//end source
								focus: function(event,ui){
									//do nothing
									return false;
								},
								select: function(event, ui){
									$(this).blur();

									$('#fname').val(ui.item.firstName).prop('disabled', true);
									$('#lname').val(ui.item.lastName).prop('disabled', true);
									$('#account-id').removeClass('hidden');
									$('#account-id').attr('data-account', ui.item.accountId);

									userEntry = ui.item.label;
								}
							});//end autocomplete

					});//end promise

					// Save current value of element
					usernameDOM.data('oldVal', usernameDOM.val());

					// Look for changes in the value
					usernameDOM.bind("propertychange change keyup input paste", function(event){
					    // If value has changed...
					    if (usernameDOM.data('oldVal') != usernameDOM.val()) {
						    // Updated stored value
						    usernameDOM.data('oldVal', usernameDOM.val());
						    // Do action
						    //usernameDOM.val('').prop('disabled', false);
							$('#fname').val('');
							$('#lname').val('');
							$('#account-id').addClass('hidden');
							$('#account-id').attr('data-account', '');
						}
					});
				}
				//new user
				else if( $(this).data('select') == 'new' ){
					fnameDOM.val('').prop('disabled', false);
					lnameDOM.val('').prop('disabled', false);
					$('#account-id').addClass('hidden');
					$('#account-id').attr('data-account', '');

					usernameDOM.unbind();
					if( usernameDOM.autocomplete("instance")!=undefined ){
						usernameDOM.autocomplete('destroy');
					}
				}
			})
		});
	</script>
@stop