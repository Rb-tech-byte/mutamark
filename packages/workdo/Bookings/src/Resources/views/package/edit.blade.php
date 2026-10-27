{{ Form::model($bookingspackage, array('route' => array('booking-package.update', $bookingspackage->id), 'method' => 'PUT','class'=>'needs-validation','novalidate')) }}
<div class="modal-body">
    <div class="row">
        @php
            $selectedServices = explode(',', $bookingspackage->services);
        @endphp
        <div class="form-group col-md-6">
            {{ Form::label('name', __('Name'), ['class' => 'form-label']) }}<x-required></x-required>
            {{ Form::text('name', null, ['class' => 'form-control', 'placeholder' => __('Enter Name'), 'required' => 'required']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('item', __('Item'), ['class' => 'form-label']) }}<x-required></x-required>
            {{ Form::select('item', $services, null, ['class' => 'form-control', 'required' => 'required', 'placeholder' => 'Enter Item','id'=>'service']) }}
        </div>
    </div>
    <div class="row">
        <div class="form-group col-md-6">
            {{ Form::label('services', __('Extra Services'), ['class' => 'form-label']) }}
            <select class="form-control choices" name="services[]" id="values_name" multiple required>
                @foreach ($extraservices as $key => $service)
                    <option value="{{ $key }}" {{ in_array($key, $selectedServices) ? 'selected' : '' }}>{{ $service }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('delivery_time', __('Delivery time'), ['class' => 'form-label']) }}<x-required></x-required>
            {{ Form::time('delivery_time', null, ['class' => 'form-control', 'required' => 'required', 'placeholder' => 'Enter Delivery time']) }}
        </div>
    </div>
    <div class="row">
        <div class="form-group col-md-6">
            {{ Form::label('delivery_period', __('Delivery Period'), ['class' => 'form-label']) }}<x-required></x-required>
            {{ Form::select('delivery_period', $periods, null, ['class' => 'form-control', 'required' => 'required', 'placeholder' => 'Enter Delivery Period', 'id' => 'Delivery Period']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('price', __('Price'), ['class' => 'form-label']) }}<x-required></x-required>
            {{ Form::text('price', null, ['class' => 'form-control', 'required' => 'required', 'placeholder' => 'Enter Price']) }}
        </div>
    </div>
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{__('Cancel')}}</button>
    <button type="submit" class="btn btn-primary">{{__('Update')}}</button>
</div>
{{ Form::close() }}

<script>

    $(document).on('change', '#service', function() {
        var service = $('#service').val();
        items(service);
    });

    function items(service) {
    $.ajax({
        url: "{{ route('package.item.price') }}",
        type: "POST",
        data: {
            _token: "{{ csrf_token() }}",
            serviceId: service
        },
        success: function(data) {
                var item = JSON.parse(data);
                var totalItemTaxRate = 0;
                var totalAmountWithTax = 0;

                if (item.product != null) {
                    totalAmountWithTax = parseFloat(item.product.sale_price);
                }

                if (item.taxes.length > 0) {
                    for (var i = 0; i < item.taxes.length; i++) {
                        totalItemTaxRate += parseFloat(item.taxes[i].rate);
                    }

                    var itemTaxPrice = (totalItemTaxRate / 100) * totalAmountWithTax;
                    totalAmountWithTax += itemTaxPrice;
                }

                $('.price').val(totalAmountWithTax.toFixed(2));
            }
        });
    }
</script>

