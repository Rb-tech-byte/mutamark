{{ Form::open(array('url' => 'fuelType' ,'class'=>'needs-validation','novalidate')) }}
<div class="modal-body">
    <div class="row">
        <div class="form-group col-12">
            {{ Form::label('name', __('Fuel Type Name'),['class'=>'form-label']) }}<x-required></x-required>
            {{ Form::text('name', '', array('class' => 'form-control','placeholder'=> __('Enter Fuel Type Name') ,'required'=>'required')) }}
        </div>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn  btn-light" data-bs-dismiss="modal">{{__('Cancel')}}</button>
    <button type="submit" class="btn  btn-primary">{{__('Create')}}</button>
</div>

{{ Form::close() }}
