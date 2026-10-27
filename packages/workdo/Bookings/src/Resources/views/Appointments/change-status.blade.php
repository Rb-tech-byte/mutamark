
{{Form::model($appointment,array('route' => array('bookings.appointment.status.update', $appointment->id), 'id' => 'business-edit-form','enctype' => 'multipart/form-data')) }}
<div class="modal-body">
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                {{ Form::label('status', __('Status'), ['class' => 'form-label']) }}
                {{ Form::select('status', $status, $appointment->stage_id, ['class' => 'form-control', 'required' => 'required']) }}

            </div>
            <input type="hidden" name="appointment_id" value="{{ $appointment->id }}">
        </div>

    </div>
    <div class="modal-footer">
        <button type="button" class="btn  btn-light" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
        {{ Form::submit(__('Update'), ['class' => 'btn  btn-primary']) }}
    </div>
    {{ Form::close() }}

