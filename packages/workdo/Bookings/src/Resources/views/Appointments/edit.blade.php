{{Form::model($bookingsappointment,array('route' => array('booking-appointments.update', $bookingsappointment->id), 'method' => 'PUT','class'=>'needs-validation','novalidate')) }}
    <div class="modal-body">
        <div class="row">
            <div class="form-group col-md-6">
                {{ Form::label('date', __('Date'), ['class' => 'form-label']) }}<x-required></x-required>
                {{ Form::date('date'  , null, ['class' => 'form-control', 'required' => 'required', 'placeholder' => 'Enter Date']) }}
            </div>
            <div class="form-group col-md-6">
                {{ Form::label('service', __('Service'), ['class' => 'form-label']) }}<x-required></x-required>
                {{ Form::select('service' , $service ,null, ['class' => 'form-control', 'required' => 'required', 'placeholder' => 'Enter Service','id'=>'service']) }}
            </div>
            <div class="form-group col-md-6">
                {{ Form::label('package', __('Package'), ['class' => 'form-label']) }}<x-required></x-required>
                {{ Form::select('package',[],null, ['class' => 'form-control', 'required' => 'required', 'placeholder' => 'Enter Package','id'=>'package']) }}
            </div>
            <div class="form-group col-md-6">
                {{ Form::label('customer', __('Customer'), ['class' => 'form-label']) }}
                <select class="form-control {{ !empty($errors->first('client_name')) ? 'is-invalid' : '' }}" name="client_id">
                    <option value="">{{ __('Select Client') }}</option>
                    @foreach ($customers as $id => $customer)
                        <option value="{{ $customer->id }}" {{ $customer->id == old('client_id', $bookingsappointment->client_id) ? 'selected' : '' }}>
                            {{ !empty($customer->client_id) ? $customer->client->name : $customer->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-6">
                {{ Form::label('staff', __('Staff'), ['class' => 'form-label']) }}<x-required></x-required>
                {{ Form::select('staff' , $staff ,null, ['class' => 'form-control', 'required' => 'required', 'placeholder' => 'Enter Staff', 'id' => 'staff']) }}
            </div>
            <input type="hidden" id="bookingsappointment_id" name="bookingsappointment_id" value="{{ $bookingsappointment->id }}">
            <div class="text-end mt-3">
                <button class="btn btn-warning" id="searchBtn" type="button">{{ __('Choose Slot') }}</button>
            </div>
            <section class="VendorDetails mt-2" id="append_div">
            </section>
        </div>
    <div>

{{ Form::close() }}

<script type="text/javascript">
    $('#service').on('change', function() {
        var serviceId = $(this).val();
        getappointment(serviceId);
    });

    $(document).ready(function() {
        var serviceId = $('#service').val();
        getappointment(serviceId);
    });

    function getappointment(serviceId) {

        if(serviceId) {
            $.ajax({
                url: "{{ route('package.service') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    serviceId: serviceId
                },
                success: function(data) {

                    $('#package').empty();
                    $('#package').append('<option value="">Enter Package</option>');
                    $.each(data, function(key, value) {
                        var package = '{{ $bookingsappointment->package }}';
                            $('#package').append('<option value="' + key + '" ' + (key ==
                            package ? 'selected' : '') + '>' + value + '</option>');
                    });
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.log('Error: ' + textStatus + ' - ' + errorThrown);
                }
            });
        } else {
            $('#package').empty();
            $('#package').append('<option value="">Enter Package</option>');
        }
    }
</script>


