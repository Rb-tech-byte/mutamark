{{ Form::model($bookingstaff, array('route' => array('booking-staff.update', $bookingstaff->id), 'method' => 'PUT')) }}
<div class="modal-body">
    <div class="col-md-12">
        <div id="repeater-container">
            <div id="form-container">
                <div class="form-group-container">
                    <div class="row">
                        <div class="form-group col-md-12 ml-4">
                            {{ Form::label('staff', __('Staff'), ['class' => 'form-label']) }}
                            <select name="staff" class='form-control font-style' id="staff">
                                <option value="0" selected disabled>{{ __('Staff') }}</option>
                                @foreach ($staffs as $key => $staff)
                                    <option value="{{ $key }}" {{ $key == $bookingstaff->staff ? 'selected' : '' }}>{{ $staff }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            {!! Form::select('services[]', $services ?? [], explode(',', $bookingstaff->service), [
                                'class' => 'form-control choices',
                                'multiple' => true,
                                'id' => 'values_name',
                            ]) !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{__('Cancel')}}</button>
    {{ Form::submit(__('Update'), ['class' => 'btn btn-primary']) }}
</div>
{{ Form::close() }}
