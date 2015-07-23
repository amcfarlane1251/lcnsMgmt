@extends('backend/layouts/default')

{{-- Page title --}}
@section('title')
    @if ($license->id)
        @lang('admin/licenses/form.update') ::
    @else
        @lang('admin/licenses/form.create') ::
    @endif
@parent
@stop

{{-- Page content --}}
@section('content')

<div class="row header">
    <div class="col-md-12">
            <a href="{{ URL::previous() }}" class="btn-flat gray pull-right right">
            <i class="fa fa-arrow-left icon-white"></i> @lang('general.back')</a>
        <h3>
        @if ($license->id)
            @lang('admin/licenses/form.update')
        @else
            @lang('admin/licenses/form.create')
        @endif
        </h3>
    </div>
</div>

<div class="row form-wrapper">

<form class="form-horizontal" method="post" action="" autocomplete="off">
    <!-- CSRF Token -->
    <input type="hidden" name="_token" value="{{ csrf_token() }}" />

            <!-- License -->
            <div class="form-group {{ $errors->has('name') ? ' has-error' : '' }}">
                <label for="name" class="col-md-3 control-label">@lang('admin/licenses/form.name')
                 <i class='fa fa-asterisk'></i></label>
                 </label>
                    <div class="col-md-7">
                        <input class="form-control" type="text" name="name" id="name" value="{{ Input::old('name', $license->name) }}" />
                        {{ $errors->first('name', '<br><span class="alert-msg"><i class="fa fa-times"></i> :message</span>') }}
                    </div>
            </div>

            <!-- Serial -->
            <div class="form-group {{ $errors->has('serial') ? ' has-error' : '' }}">
                <label for="serial" class="col-md-3 control-label">@lang('admin/licenses/form.serial')
                 <i class='fa fa-asterisk'></i></label>
                 </label>
                    <div class="col-md-7">
                        <textarea class="form-control" type="text" name="serial" id="serial">{{ Input::old('serial', $license->serial) }}</textarea>
                        {{ $errors->first('serial', '<br><span class="alert-msg"><i class="fa fa-times"></i> :message</span>') }}
                    </div>
            </div>

            <!-- EC -->
            <div class="form-group {{ $errors->has('ec') ? 'has-error' : '' }}">
                <label class="col-md-3 control-label" for="role_id">@lang('admin/users/table.ec')</label>
                <div class="col-md-7">
                    {{ Form::select('role_id', $ec, Input::old('role_id', $license->role_id), array('class'=>'select2', 'style'=>'width:250px')) }}
                    {{ $errors->first('role_id', '<br><span class="alert-msg">:message</span>') }}
                </div>
            </div>

            <!-- License Type -->
            <div class="form-group {{ $errors->has('lcnsTypes') ? 'has-error' : '' }}">
                <label class="col-md-3 control-label" for="lcnsTypes">@lang('admin/users/table.lcnsTypes')</label>
                <div class="col-md-5">
                   <div class="controls">
                        {{ Form::select('type_id', $lcnsTypes, $license->licenseType()->lists('id'), array('class'=>'select2', 'style'=>'width:240px')) }}
                    </div>
                </div>
            </div>

            <!-- Seats -->
            <div class="form-group {{ $errors->has('seats') ? ' has-error' : '' }}">
                <label for="seats" class="col-md-3 control-label">@lang('admin/licenses/form.seats')
                 <i class='fa fa-asterisk'></i></label>
                 </label>
                    <div class="col-md-3">
                        <input class="form-control" type="text" name="seats" id="seats" value="{{ Input::old('seats', $license->seats) }}" />
                        {{ $errors->first('seats', '<br><span class="alert-msg"><i class="fa fa-times"></i> :message</span>') }}
                    </div>
            </div>

            <!-- Reassignable -->
            <div class="form-group {{ $errors->has('reassignable') ? ' has-error' : '' }}">
                <label for="reassignable" class="col-md-3 control-label">@lang('admin/licenses/form.reassignable')</label>
                <div class="col-md-7 input-group">
                    {{ Form::Checkbox('reassignable', '1', Input::old('reassignable', $license->id ? $license->reassignable : '1')) }}
                    @lang('general.yes')
                </div>
            </div>

            <!-- Purchase Date -->
            <div class="form-group {{ $errors->has('purchase_date') ? ' has-error' : '' }}">
                <label for="purchase_date" class="col-md-3 control-label">@lang('admin/licenses/form.date')</label>
                <div class="input-group col-md-2">
                    <input type="date" class="datepicker form-control" data-date-format="yyyy-mm-dd" placeholder="Select Date" name="purchase_date" id="purchase_date" value="{{ Input::old('purchase_date', $license->purchase_date) }}">
                    <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                {{ $errors->first('purchase_date', '<br><span class="alert-msg"><i class="fa fa-times"></i> :message</span>') }}
                </div>
            </div>

            <!-- Purchase Cost -->
            <div class="form-group {{ $errors->has('purchase_cost') ? ' has-error' : '' }}">
                <label for="purchase_cost" class="col-md-3 control-label">@lang('admin/licenses/form.cost')</label>
                <div class="col-md-2">
                    <div class="input-group">
                        <span class="input-group-addon">@lang('general.currency')</span>
                        <input class="col-md-2 form-control" type="text" name="purchase_cost" id="purchase_cost" value="{{ Input::old('purchase_cost', number_format($license->purchase_cost,2)) }}" />
                        {{ $errors->first('purchase_cost', '<br><span class="alert-msg"><i class="fa fa-times"></i> :message</span>') }}
                     </div>
                 </div>
            </div>

            <!-- Purchase Order -->
            <div class="form-group {{ $errors->has('purchase_order') ? ' has-error' : '' }}">
                <label for="purchase_order" class="col-md-3 control-label">@lang('admin/licenses/form.purchase_order')</label>
                    <div class="col-md-7">
                        <input class="form-control" type="text" name="purchase_order" id="purchase_order" value="{{ Input::old('purchase_order', $license->purchase_order) }}" />
                        {{ $errors->first('purchase_order', '<br><span class="alert-msg"><i class="fa fa-times"></i> :message</span>') }}
                    </div>
            </div>


            <!-- Expiration Date -->
            <div class="form-group {{ $errors->has('expiration_date') ? ' has-error' : '' }}">
                <label for="expiration_date" class="col-md-3 control-label">@lang('admin/licenses/form.expiration')</label>
                <div class="input-group col-md-2">
                    <input type="date" class="datepicker form-control" data-date-format="yyyy-mm-dd" placeholder="Select Date" name="expiration_date" id="expiration_date" value="{{ Input::old('expiration_date', $license->expiration_date) }}">
                    <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                {{ $errors->first('expiration_date', '<br><span class="alert-msg"><i class="fa fa-times"></i> :message</span>') }}
                </div>
            </div>

            <!-- Notes -->
            <div class="form-group {{ $errors->has('notes') ? ' has-error' : '' }}">
                <label for="notes" class="col-md-3 control-label">@lang('admin/licenses/form.notes')</label>
                <div class="col-md-7">
                    <textarea class="col-md-6 form-control" id="notes" name="notes">{{{ Input::old('notes', $license->notes) }}}</textarea>
                    {{ $errors->first('notes', '<br><span class="alert-msg"><i class="fa fa-times"></i> :message</span>') }}
                </div>
            </div>

            <!-- Form actions -->
                <div class="form-group">
                <label class="col-md-3 control-label"></label>
                    <div class="col-md-7">

                        <a class="btn btn-link" href="{{ URL::previous() }}">@lang('button.cancel')</a>
                        <button type="submit" class="btn btn-success"><i class="fa fa-check icon-white"></i> @lang('general.save')</button>
                    </div>
                </div>

</form>
</div>

@stop
