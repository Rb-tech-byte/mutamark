{{ Form::open(['url' => 'custom-field']) }}
<div class="modal-body">
    <div class="row">
        <div class="form-group col-md-12">
            {{ Form::label('name', __('Custom Field Name'), ['class' => 'form-label']) }}
            {{ Form::text('name', null, ['class' => 'form-control', 'required' => 'required','placeholder'=>'Create New Custom Field']) }}
        </div>
        <div class="form-group col-md-12">
            {{ Form::label('type', __('Type'), ['class' => 'form-label']) }}
            {{ Form::select('type', $types, null, ['class' => 'form-control select2 ', 'required' => 'required']) }}
        </div>

        <div class="form-group col-md-12">
            {{ Form::label('module', __('Module'), ['class' => 'form-label']) }}
            {{ Form::select('module', $module_custumefield, null, ['class' => 'form-control ', 'required' => 'required']) }}
        </div>
        <div class="col-md-12">
            <div class="form-group">
                {{ Form::label('is_required', __('Rule'), ['class' => 'form-label']) }}
                {{ Form::select('is_required', ['0' => 'Not Required', '1' => 'Is Required'], null, ['class' => 'form-control ', 'placeholder' => __('Select Rule'),'required' => 'required']) }}
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{ __('Cancel') }}" class="btn  btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Create') }}" class="btn  btn-primary">
</div>
{{ Form::close() }}
