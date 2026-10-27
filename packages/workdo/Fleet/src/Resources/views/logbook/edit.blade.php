{{ Form::model($logbook, ['route' => ['logbook.update', $logbook->id], 'method' => 'PUT', 'enctype' => 'multipart/form-data','class'=>'needs-validation','novalidate']) }}
<div class="modal-body">
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('driver_name', __('Driver Name'), ['class' => 'form-label']) }}
                <select class="form-control" name="driver_name"  id="driver_name">
                    @foreach ($drivers as $driver)
                        <option value="{{ $driver->id }}" {{ $driver->id == $logbook->driver_name ? 'selected' : '' }}>
                            {{ !empty($driver->client_id) ? $driver->client->name : $driver->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('vehicle_name', __('Vehicle Name'), ['class' => 'form-label']) }}<x-required></x-required>
                {{ Form::select('vehicle_name', $vehicle, $logbook->vehicle_name, ['class' => 'form-control', 'required' => 'required']) }}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('start_date', __('Start Date'), ['class' => 'form-label']) }}<x-required></x-required>
                {{ Form::date('start_date', null, ['class' => 'form-control', 'required' => 'required']) }}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('end_date', __('End Date'), ['class' => 'form-label']) }}<x-required></x-required>
                {{ Form::date('end_date', null, ['class' => 'form-control', 'required' => 'required']) }}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('start_odometer', __('Start Odometer'), ['class' => 'form-label']) }}<x-required></x-required>
                {{ Form::number('start_odometer',null, ['class' => 'form-control', 'required' => 'required']) }}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('end_odometer', __('End Odometer'), ['class' => 'form-label']) }}<x-required></x-required>
                {{ Form::number('end_odometer',null, ['class' => 'form-control', 'required' => 'required']) }}
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                {{ Form::label('rate', __('Rate'),['class'=>'form-label']) }}<x-required></x-required>
                {{ Form::select('rate', $rate , $logbook->rate, array('class' => 'form-control','required'=>'required')) }}
            </div>
        </div>
        <input type="hidden" name="rate_price" id="rate_price">

        <div class="col-md-4">
            <div class="form-group">
                {{ Form::label('total_distance', __('Total Distance'), ['class' => 'form-label']) }}<x-required></x-required>
                {{ Form::number('total_distance', $logbook->total_distance, ['class' => 'form-control', 'required' => 'required', 'placeholder' => 'Total Distance']) }}
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                {{ Form::label('total_price', __('Total Price'), ['class' => 'form-label']) }}<x-required></x-required>
                {{ Form::number('total_price', $logbook->total_price, ['class' => 'form-control', 'required' => 'required', 'placeholder' => 'Total Price']) }}
            </div>
        </div>
        <div class="col-md-12">
            <div class="form-group">
                {{ Form::label('notes', __('Note'), ['class' => 'form-label']) }}
                {{ Form::textarea('notes', $logbook->notes, ['class' => 'form-control','placeholder' => __('Enter Note'), 'rows' => 3]) }}
            </div>
        </div>
    </div>
</div>

<div class="modal-footer">
    <input type="button" value="{{ __('Cancel') }}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Update') }}" class="btn btn-primary">
</div>

{{ Form::close() }}
<script>
    $(document).ready(function() {
        // Function to calculate total distance
        function calculateTotalDistance() {
            var startOdometer = parseFloat($('#start_odometer').val()) || 0;
            var endOdometer = parseFloat($('#end_odometer').val()) || 0;

            if (!isNaN(startOdometer) && !isNaN(endOdometer)) {
                var totalDistance = Math.max(0, endOdometer - startOdometer);
                $('#total_distance').val(totalDistance);
            } else {
                $('#total_distance').val('');
            }
        }

        // Function to calculate total price
        function calculateTotalPrice() {
            var totalDistance = parseFloat($('#total_distance').val()) || 0;
            var rate = parseFloat($('#rate_price').val());

            if (!isNaN(totalDistance) && !isNaN(rate)) {
                var totalPrice = totalDistance * rate;
                console.log(totalDistance ,rate);
                $('#total_price').val(totalPrice.toFixed(2));
            } else {
                $('#total_price').val('');
            }
        }

        // Event handlers for input changes
        $('#end_odometer').on('input', function() {
            calculateTotalDistance();
            calculateTotalPrice();
        });

        $('#start_odometer').on('input', function() {
            calculateTotalDistance();
            calculateTotalPrice();
        });


        $(document).ready(function() {
            ratecalculate();
        });
        $('#rate').on('change', function() {
            ratecalculate();
        });
        function ratecalculate() {
            var rate = parseFloat($('#rate').val());
            if (!isNaN(rate)) {
                $.ajax({
                    url: "{{ route('item.rate') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        rate: rate
                    },
                    success: function(data) {
                        if (data && data[0]) {
                            $('#total_price').val(data[0].sale_price);
                            $('#rate_price').val(data[0].sale_price);
                            calculateTotalPrice();
                        } else {
                            $('#total_price').val('');
                        }
                    },
                    error: function() {
                        $('#total_price').val('');
                    }
                });
            } else {
                $('#total_price').val('');
            }
        }
    });
</script>
