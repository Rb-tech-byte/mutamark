{{ Form::model($bookingextraservice, array('route' => array('bookings-extra-services.update', $bookingextraservice->id), 'method' => 'PUT','enctype'=>'multipart/form-data','class'=>'needs-validation','novalidate')) }}
<div class="modal-body">
    <div class="row">
        <div class="col-12">
            <div class="form-group">
                {{ Form::label('name', __('Name'), ['class' => 'form-label']) }}<x-required></x-required>
                {{ Form::text('name', null, ['class' => 'form-control', 'placeholder' => __('Enter Name'), 'required' => 'required']) }}
            </div>
        </div>
        <div class="col-12">
            <div class="form-group">
                {{ Form::label('status', __('Status'), ['class' => 'form-label']) }}
                <div class="d-flex radio-check">
                    <div class="form-check form-check-inline form-group col-md-3">
                        <input type="radio" id="active" value="active" {{isset($bookingextraservice->status) && $bookingextraservice->status == 'active' ? 'checked' : ''}} name="status" class="form-check-input code" checked="checked">
                        <label class="custom-control-label" for="active">{{ __('Active') }}</label>
                    </div>
                    <div class="form-check form-check-inline form-group col-md-3">
                        <input type="radio" id="in_active" value="Inactive" {{isset($bookingextraservice->status) && $bookingextraservice->status == 'Inactive' ? 'checked' : ''}} name="status" class="form-check-input code">
                        <label class="custom-control-label" for="in_active">{{ __('InActive') }}</label>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn  btn-light" data-bs-dismiss="modal">{{__('Cancel')}}</button>
    <button type="submit" class="btn  btn-primary">{{__('Update')}}</button>
</div>

{{ Form::close() }}
