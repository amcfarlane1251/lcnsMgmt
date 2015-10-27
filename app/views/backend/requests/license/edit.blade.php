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
				<div class="form-group {{ $errors->has('username') ? 'has-error' : '' }}">
					<div class="col-md-2">
						{{ Form::label('username', Lang::get('account.username'), array('class'=>'control-label')) }}
					</div>
					<div class="col-md-4">
						{{ Form::text('username', ($request->account ? $request->account->username : ''), array('class'=>'form-control')) }}
						{{ $errors->first('username', '<br><span class="alert-msg">:message</span>') }}
					</div>
				</div>

				<div class="form-group {{ $errors->has('fname') ? 'has-error' : '' }}">
					<div class="col-md-2 clearfix">
						{{ Form::label('fname', Lang::get('account.firstName'), array('class'=>'control-label')) }}
					</div>
					<div class="col-md-4">
						{{ Form::text('fname', ($request->account ? $request->account->first_name : ''), array('class'=>'form-control')) }}
						{{ $errors->first('fname', '<br><span class="alert-msg">:message</span>') }}
					</div>
				</div>

				<div class="form-group {{ $errors->has('lname') ? 'has-error' : '' }}">
					<div class="col-md-2 clearfix">
						{{ Form::label('lname', Lang::get('account.lastName'), array('class'=>'control-label')) }}
					</div>
					<div class="col-md-4">
						{{ Form::text('lname', ($request->account ? $request->account->last_name : ''), array('class'=>'form-control')) }}
						{{ $errors->first('lname', '<br><span class="alert-msg">:message</span>') }}
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
    		var userEntry;

			var req = $.ajax({
				url:'../accounts',
				success:function(data){
					data = JSON.parse(data);
					users = data.accounts;
				},
			});

			req.then(function(){
				$(document).on('keydown.autocomplete', '#username', function(){
					$(this).autocomplete({
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
										lastName: item['last_name']
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
							userEntry = ui.item.label;
						}
					});//end autocomplete
				});
			});

			var elem = $('#username');
			// Save current value of element
			elem.data('oldVal', elem.val());

			// Look for changes in the value
			elem.bind("propertychange change keyup input paste", function(event){
			    // If value has changed...
			    if (elem.data('oldVal') != elem.val()) {
				    // Updated stored value
				    elem.data('oldVal', elem.val());

				    // Do action
					$('#fname').val('').prop('disabled', false);
					$('#lname').val('').prop('disabled', false);
				}
			});
		});
	</script>
@stop