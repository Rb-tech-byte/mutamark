{{ Form::model($vehicle, ['route' => ['vehicle.update', $vehicle->id], 'method' => 'PUT','class'=>'needs-validation','novalidate']) }}
<div class="modal-body">
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('name', __('Vehicle Name'), ['class' => 'form-label']) }}<x-required></x-required>
                {{ Form::text('name', null, ['class' => 'form-control', 'placeholder' => __('Enter Vehicle Name'), 'required' => 'required']) }}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('vehicle_type', __('Vehicle Type'), ['class' => 'form-label']) }}<x-required></x-required>
                {{ Form::select('vehicle_type', $vehicleTypes, null, ['class' => 'form-control', 'required' => 'required']) }}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('registration_date', __('Registration Date'), ['class' => 'form-label']) }}<x-required></x-required>
                {{ Form::date('registration_date', null, ['class' => 'form-control', 'required' => 'required']) }}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('register_ex_date', __('Register expiry date (Optional)'), ['class' => 'form-label']) }}
                {{ Form::date('register_ex_date', null, ['class' => 'form-control']) }}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('fuel_type', __('Fuel Type'), ['class' => 'form-label']) }}<x-required></x-required>
                {{ Form::select('fuel_type', $FuelType, null, ['class' => 'form-control', 'required' => 'required']) }}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('driver_name', __('Driver Name'), ['class' => 'form-label']) }}
                <select class="form-control {{ !empty($errors->first('client_name')) ? 'is-invalid' : '' }}" name="driver_name"  id="driver_name">
                    @foreach ($drivers as $driver)
                        <option value="{{ $driver->id }}" {{ $driver->id == $vehicle->driver_name ? 'selected' : '' }}>
                            {{ !empty($driver->client_id) ? $driver->client->name : $driver->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('lincense_plate', __('License Plate Number'), ['class' => 'form-label']) }}<x-required></x-required>
                {{ Form::number('lincense_plate', null, ['class' => 'form-control', 'placeholder' => __('Enter License Plate Number'), 'required' => 'required']) }}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('vehical_id_num', __('Vehicle Identification Number'), ['class' => 'form-label']) }}<x-required></x-required>
                {{ Form::number('vehical_id_num', null, ['class' => 'form-control', 'placeholder' => __('Enter Vehicle Identification Number'), 'required' => 'required']) }}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('model_year', __('Model Year'), ['class' => 'form-label']) }}
                {{ Form::selectYear('model_year', (int) $vehicle->model_year, date('Y'), 0, ['class' => 'month-btn form-control']) }}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('status', __('Status (Optional)'), ['class' => 'form-label']) }}
                {{ Form::select('status', ['Active' => 'Active', 'Inactive' => 'Inactive', 'Maintenance' => 'Maintenance'], null, ['class' => 'form-control', 'placeholder' => 'Select Trip Status']) }}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('seat_capacity', __('Seat Capacity'), ['class' => 'form-label']) }}<x-required></x-required>
                {{ Form::number('seat_capacity', null, ['class' => 'form-control', 'required' => 'required', 'placeholder' => 'Enter Seat Capacity ( With Driver )']) }}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('rate', __('Rate'), ['class' => 'form-label']) }}<x-required></x-required>
                {{ Form::number('rate', null, ['class' => 'form-control', 'required' => 'required', 'placeholder' => 'Enter Rate']) }}
            </div>
        </div>
        @if (module_is_active('CustomField') && !$customFields->isEmpty())
            <div class="col-md-6">
                <div class="tab-pane fade show" id="tab-2" role="tabpanel">
                    @include('custom-field::formBuilder', ['fildedata' => $vehicle->customField])
                </div>
            </div>
        @endif
    </div>
</div>

<div class="modal-footer">
    <input type="button" value="{{ __('Cancel') }}" class="btn  btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Update') }}" class="btn  btn-primary">
</div>

{{ Form::close() }}
