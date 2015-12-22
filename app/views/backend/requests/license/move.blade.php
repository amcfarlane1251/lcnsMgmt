@extends('backend/layouts/default')

@section('title')
	@lang('request.moveLicense')
@parent
@stop

{{-- Page content --}}
@section('content')
	<div class="page-header">
        <div class="pull-right">
            <a href="{{ URL::previous() }}" class="btn-flat gray"><i class="fa fa-arrow-left icon-white"></i>  @lang('general.back')</a>
        </div>
		@if($action=='PUT')
			<h3>@lang('request.edit')</h3>
		@else
			<h3> @lang('request.moveLicense') </h3>
		@endif
	</div>

	<div class="row form-wrapper">
		<div class="col-md-12 column">
			{{ Form::open(array('url'=>'request/'.$request->id,'class'=>'form-horizontal', 'method'=>$action)) }}
			
			<h3>@Lang('request.formSection')</h3>
			
			{{-- License Name --}}
			<div class="form-group">
				<div class="col-md-2">
					<label> {{ Lang::get('admin/licenses/general.type') }} </label>
				</div>
				<div class="col-md-4">
					{{$license->license->name}}
					<input type="hidden" value="{{$license->license->licenseType->id}}" name="lcnsTypes[]" id="lcnsTypes[]"/>
				</div>
			</div>
			
			{{-- EC --}}
			<div class="form-group">
				<div class="col-md-2">
					{{ Form::label('ec', Lang::get('general.ec')) }}
				</div>
				<div class="col-md-4">
					{{$license->license->role->role}}
					<input type="hidden" value="{{$license->license->role->id}}" name="ec" id="ec"/>
				</div>
			</div>
			
			{{-- Unit --}}
			<div class="form-group">
				<div class="col-md-2">
					{{ Form::label('unit', Lang::get('general.unit')) }}
				</div>
				<div class="col-md-4">
					{{$license->unit->name}}
					<input type="hidden" value="{{$license->unit->id}}" name="unit" id="unit"/>
				</div>
			</div>
			
			{{-- PC Name --}}
			<div class="form-group {{ $license->license->licenseType->asset_flag ? '' : 'hidden' }} " data-toggle='pcName'>
				<div class="col-md-2">
					{{ Form::label('pcName', Lang::get('request.pcName'), array('class'=>'control-label')) }}
				</div>
				<div class="col-md-4">
					{{ Form::text('pcName', (isset($license->asset) ? $license->asset->asset_tag : ''), array('class'=>'form-control')) }}
					{{ $errors->first('pcName', '<br><span class="alert-msg">:message</span>') }}
				</div>
			</div>
				
			{{-- Account --}}
			<div class="" data-toggle='accountInfo'>
				<h3>@Lang('account.move')</h3>
				<div class="btn-group" role="group" id="user-select">
					<h5>@Lang('account.selectOption')</h5>
					<button type='button' class="btn btn-default" data-select="existing">Existing User?</button>
					<button type='button' class="btn btn-default" data-select="new">New User?</button>
				</div>
				<div class="form-container">
					<div id="account-id" class="col-md-2 alert alert-success {{ !isset($request->account->id) ? 'hidden' : '' }}" style="float:none;display:inline-block;padding:2px;">
						<p><i class="fa fa-check"></i><strong>Success: </strong>Account Selected</p>
					</div>
					<div class="form-group {{ $errors->has('username') ? 'has-error' : '' }}">
						<div class="col-md-2">
							{{ Form::label('username', Lang::get('account.username'), array('class'=>'control-label')) }}
						</div>
						<div class="col-md-4">
							{{ Form::text('username', ($request->account ? $request->account->username : ''), array('class'=>'form-control','readonly'=>'true')) }}
							{{ $errors->first('username', '<br><span class="alert-msg">:message</span>') }}
						</div>
					</div>

					<div class="form-group {{ $errors->has('firstName') ? 'has-error' : '' }}">
						<div class="col-md-2 clearfix">
							{{ Form::label('firstName', Lang::get('account.firstName'), array('class'=>'control-label','readonly'=>true)) }}
						</div>
						<div class="col-md-4">
							{{ Form::text('firstName', ($request->account ? $request->account->first_name : ''), array('class'=>'form-control','readonly'=>'true')) }}
							{{ $errors->first('firstName', '<br><span class="alert-msg">:message</span>') }}
						</div>
					</div>

					<div class="form-group {{ $errors->has('lastName') ? 'has-error' : '' }}">
						<div class="col-md-2 clearfix">
							{{ Form::label('lastName', Lang::get('account.lastName'), array('class'=>'control-label')) }}
						</div>
						<div class="col-md-4">
							{{ Form::text('lastName', ($request->account ? $request->account->last_name : ''), array('class'=>'form-control','readonly'=>'true')) }}
							{{ $errors->first('lastNname', '<br><span class="alert-msg">:message</span>') }}
						</div>
					</div>
				</div>
			</div>

			<div class="hidden">
				<input type="hidden" value="{{$type}}" name="type" id="type"/>
			</div>
			@if(isset($license))
				<div class="hidden">
					<input type="hidden" value="{{$license->id}}" name="lcnsId" id="lcnsId"/>
				</div>
			@endif
			<div class="hidden">
				<input type="hidden" value="{{(isset($userStatus) ? $userStatus : Input::old('userStatus'))}}" name="userStatus" id="userStatus"/>
			</div>
			{{ Form::submit('Submit Request') }}
		</div>
	</div>

	<script>
		$(function(){
			toggleHelper1 = new ToggleHelper('lcnsContainer','pcName');
			toggleHelper1.init();

			var pcNameDOM = $('#pcName');
			var usernameDOM = $('#username');
			var firstNameDOM = $('#firstName');
			var lnameDOM = $('#lastName');
			var users;

			pcNameDOM.mask('?***-***-*******');
			var req = $.ajax({
				url:'../accounts',
				success:function(data){
					data = JSON.parse(data);
					users = data.accounts;
				},
			});

			//select exisiting or new account
			$('#user-select .btn').click(function(e){
				//toggle button classes
				$(this).siblings('.btn').removeClass('active');
				$(this).addClass('active');

				//enable username field
				$('#userStatus').val($(this).data('select'));
				usernameDOM.val('').prop('readonly', false);

				if( $(this).data('select') == 'existing' ){
					//clear fields and set to read only
					firstNameDOM.val('').prop('readonly', true);
					lnameDOM.val('').prop('readonly', true);
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

									$('#firstName').val(ui.item.firstName).prop('readonly', true);
									$('#lastName').val(ui.item.lastName).prop('readonly', true);
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
						    //usernameDOM.val('').prop('readonly', false);
							$('#firstName').val('');
							$('#lastName').val('');
							$('#account-id').addClass('hidden');
							$('#account-id').attr('data-account', '');
						}
					});
				}
				//new user
				else if( $(this).data('select') == 'new' ){
					firstNameDOM.val('').prop('readonly', false);
					lnameDOM.val('').prop('readonly', false);
					$('#account-id').addClass('hidden');
					$('#account-id').attr('data-account', '');

					usernameDOM.unbind();
					if( usernameDOM.autocomplete("instance")!=undefined ){
						usernameDOM.autocomplete('destroy');
					}
				}
			})

			if($('#userStatus').val() == 'new'){
				$("#user-select .btn[data-select='"+$('#userStatus')+"']").addClass('active');

				usernameDOM.prop('readonly', false);
				firstNameDOM.prop('readonly', false);
				lnameDOM.prop('readonly', false);
			}
		});
	</script>
@stop