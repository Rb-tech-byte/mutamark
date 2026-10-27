{{Form::open(array('url'=>'logbook','method'=>'post', 'enctype'=>'multipart/form-data','class'=>'needs-validation','novalidate'))}}
    <div class="modal-body">
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    {{ Form::label('driver_name', __('Driver Name'), ['class' => 'form-label']) }}
                    <select class="form-control select_person_email {{ !empty($errors->first('client_name')) ? 'is-invalid' : '' }}" name="driver_name"  id="driver_name">
                        <option value="">{{ __('Select Driver Name') }}</option>
                        @foreach ($drivers as $id => $customer)
                            <option value="{{ $customer->id }}">
                                {{ !empty($customer->client_id) ? $customer->client->name : $customer->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    {{ Form::label('vehicle_name', __('Vehicle Name'),['class'=>'form-label']) }}<x-required></x-required>
                    {{ Form::select('vehicle_name', $vehicle, null, array('class' => 'form-control','required'=>'required')) }}
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    {{ Form::label('start_date', __('Start Date'),['class'=>'form-label']) }}<x-required></x-required>
                    {{ Form::date('start_date', null, array('class' => 'form-control','required'=>'required')) }}
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    {{ Form::label('end_date', __('End Date'),['class'=>'form-label']) }}<x-required></x-required>
                    {{ Form::date('end_date', null, array('class' => 'form-control','required'=>'required')) }}
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="start_odometer" class="form-label">{{ __('Start Odometer') }}</label><x-required></x-required>
                    <input type="number" id="start_odometer" name="start_odometer" class="form-control" placeholder = "Enter Start Odometer" required>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="end_odometer" class="form-label">{{ __('End Odometer') }}</label><x-required></x-required>
                    <input type="number" id="end_odometer" name="end_odometer" class="form-control" placeholder = "Enter Start Odometer" required>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    {{ Form::label('rate', __('Rate'),['class'=>'form-label']) }}
                    {{ Form::select('rate', $rate ,null, array('class' => 'form-control','placeholder'=>__('Enter Rate'))) }}
                    @if (count($rate) <= 0)
                        <div class="text-muted text-xs">{{ __('Please create new item') }} <a href="{{ route('product-service.create',['item_type'=>'fleet']) }}">{{ __('here') }}</a></div>
                    @endif
                </div>
            </div>
            <input type="hidden" value="" name="rate_price" id="rate_price">
            <div class="col-md-4">
                <div class="form-group">
                    <label for="total_distance" class="form-label">{{ __('Total Distance') }}</label><x-required></x-required>
                    <input type="number" id="total_distance" name="total_distance" class="form-control" required placeholder="Enter Total Distance">
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label for="total_price" class="form-label">{{ __('Total Price') }}</label>
                    <input type="number" id="total_price" name="total_price" class="form-control" required placeholder="Enter Total Price" readonly>
                </div>
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    {{ Form::label('notes', __('Note'),['class'=>'form-label']) }}
                    {{ Form::textarea('notes', null, array('class' => 'form-control','placeholder'=>__('Enter Note'),'rows'=>3)) }}
                </div>
            </div>
        </div>
    </div>

    <div class="modal-footer">
        <input type="button" value="{{__('Cancel')}}" class="btn btn-light" data-bs-dismiss="modal">
        <input type="submit" value="{{__('Create')}}" class="btn btn-primary">
    </div>

{{Form::close()}}

<script>
    $(document).ready(function() {
        $('#end_odometer').on('input', function() {
            calculateTotalDistance();
            calculateTotalPrice();
        });

        $('#start_odometer').on('input', function() {
            calculateTotalDistance();
            calculateTotalPrice();
        });

        $('#rate').on('change', function() {
            var rate = parseFloat($(this).val());
            if (!isNaN(rate)) {
                $.ajax({
                    url: "{{ route('item.rate') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        rate: rate
                    },
                    success: function(data) {

                        if (data) {
                            $('#total_price').val(data[0].sale_price);
                            $('#rate_price').val(data[0].sale_price);
                            calculateTotalPrice();
                        } else {
                            $('#total_price').val('');
                        }
                    },
                });
            } else {
                $('#total_price').val('');
            }
        });

        function calculateTotalDistance() {
            var startOdometer = parseFloat($('#start_odometer').val());
            var endOdometer = parseFloat($('#end_odometer').val());

            if (!isNaN(startOdometer) && !isNaN(endOdometer)) {
                var totalDistance = Math.max(0, endOdometer - startOdometer);
                $('#total_distance').val(totalDistance);
            }
        }

        function calculateTotalPrice() {
            var totalDistance = parseFloat($('#total_distance').val());
            var rate = parseFloat($('#rate_price').val());

            if (!isNaN(totalDistance) && !isNaN(rate)) {
                var totalPrice = totalDistance * rate;
                $('#total_price').val(totalPrice.toFixed(2));
            } else {
                $('#total_price').val('');
            }
        }

    });
</script>

