{{Form::model($fuel,array('route' => array('fuel.update', $fuel->id), 'method' => 'PUT','class'=>'needs-validation','novalidate')) }}

<div class="modal-body">
    <div class="row">
        @if(\Auth::user()->type != "driver")
            <div class="col-md-6">
                <div class="form-group">
                    {{ Form::label('driver_name', __('Driver Name'), ['class' => 'form-label']) }}
                    <select class="form-control" name="driver_name"  id="driver_name">
                        @foreach ($driver as $drivers)
                            <option value="{{ $drivers->id }}" {{ $drivers->id == $fuel->driver_name ? 'selected' : '' }}>
                                {{ !empty($drivers->client_id) ? $drivers->client->name : $drivers->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        @endif
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('vehicle_name', __('Vehicle Name'),['class'=>'form-label']) }}<x-required></x-required>
                {{ Form::select('vehicle_name', $vehicle, null, array('class' => 'form-control','required'=>'required')) }}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('fuel_type', __('Fuel Type'),['class'=>'form-label']) }}<x-required></x-required>
                {{ Form::select('fuel_type', $fuelType, null, array('class' => 'form-control','required'=>'required')) }}
            </div>
        </div>
        <div class="form-group col-md-6  mb-1">
            <label for="datetime" class="form-label">{{ __('Fueling Date and Time') }}</label><x-required></x-required>
            <input class="form-control" placeholder="{{ __('Fueling Date and Time') }}"
                required="required" name="fill_date" type="datetime-local"
                value="{{ isset($fuel) ? date('Y-m-d\TH:i', strtotime($fuel->fill_date)) : '' }}">
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('quantity', __('Gallons/Liters of Fuel'),['class'=>'form-label']) }}<x-required></x-required>
                {{ Form::text('quantity', null, array('class' => 'form-control','required'=>'required','placeholder'=>__('Enter Gallons/Liters of Fuel'))) }}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('cost', __('Cost per Gallon/Liter'),['class'=>'form-label']) }}<x-required></x-required>
                {{ Form::number('cost', null, array('class' => 'form-control','required'=>'required','placeholder'=>__('Enter Cost per Gallon/Liter'))) }}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('total_cost', __('Total Cost'),['class'=>'form-label']) }}<x-required></x-required>
                {{ Form::number('total_cost', null, array('class' => 'form-control','required'=>'required','placeholder'=>__('Enter Total Cost'))) }}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('odometer_reading', __('Odometer Reading'),['class'=>'form-label']) }}<x-required></x-required>
                {{ Form::text('odometer_reading', null, array('class' => 'form-control','required'=>'required','placeholder'=>__('Enter Odometer Reading'))) }}
            </div>
        </div>
        <div class="col-md-12">
            <div class="form-group">
                {{ Form::label('notes', __('Notes'),['class'=>'form-label']) }}<x-required></x-required>
                {{ Form::textarea('notes', null, array('class' => 'form-control','required'=>'required','placeholder'=>__('Enter Notes'),'rows'=>3)) }}
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn  btn-light" data-bs-dismiss="modal">{{__('Cancel')}}</button>
    <button type="submit" class="btn  btn-primary">{{__('Update')}}</button>
</div>

{{ Form::close() }}
