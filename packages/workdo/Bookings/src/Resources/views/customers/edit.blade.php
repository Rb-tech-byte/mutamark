{{Form::model($booking,array('route' => array('booking-customers.update', $booking->id), 'method' => 'PUT','class'=>'needs-validation','novalidate')) }}
    <div class="modal-body">
        <div class="row">
            <div class="form-group col-md-6 col-12 mb-3">
                <label class= "form-label">{{ __('Customer type') }}</label><x-required></x-required>
                <select class="form-select" id="customer" name="Customer" required>
                    <option selected>{{ __('Select Type') }}</option>
                    <option id="account_type_custom" value="custom" name="account_type" value="walk_in"  {{ !empty($booking) ? ($booking->customer == 'custom' ? 'selected' : '') : '' }}>{{ __('Walk-in') }}</option>
                    <option id="account_type_client" value="client" name="account_type" value="client"  {{ !empty($booking) ? ($booking->customer == 'client' ? 'selected' : '') : '' }}>{{ __('Client') }}</option>
                </select>
            </div>
            <div class="form-group col-md-6 col-12 mb-3" id="customname">
                <label class="form-label">{{ __('Name') }}</label>
                <input class="form-control {{ !empty($errors->first('name')) ? 'is-invalid' : '' }}"
                    type="text" name="name" value="{{ isset($booking->name) ? $booking->name : '' }}" placeholder="{{ __('Name') }}">
                <div class="invalid-feedback">
                    {{ $errors->first('name') }}
                </div>
            </div>
            <div class="form-group col-md-6 col-12 mb-3 d-none" id="emailClient">
                <label class="form-label">{{ __('Select') }}</label>
                <select class="form-control select_person_email {{ !empty($errors->first('client_name')) ? 'is-invalid' : '' }}" name="client_name">
                <option value="">{{ __('Select Client') }}</option>
                @foreach ($client as $id => $name)
                    <option value="{{ $id }}" @if (!empty($booking->client_id) && $booking->client_id == $id) selected @endif>
                        {{ $name }}
                    </option>
                @endforeach
                </select>
            </div>
        </div>
        <div class="row">
            <div class="form-group col-md-6 col-12 mb-3">
                <label class="form-label">{{ __('Email') }}</label><x-required></x-required>
                <input class="form-control email"
                    type="email" name="email" required="" value="{{ isset($booking->email) ? $booking->email : '' }}" placeholder="{{ __('Email') }}"
                    id="emailAddressField">
                <div class="invalid-feedback">
                    {{ $errors->first('email') }}
                </div>
            </div>
            <div class="form-group col-md-6 col-12 mb-3">
                <x-mobile divClass="form-label" type="number" name="number" class="form-control" label="{{ __('Mobile Number') }}" placeholder="{{ __('Mobile Number') }}" value="{{ isset($booking->number) ? $booking->number : '' }}" id="mobileField" required></x-mobile>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn  btn-light" data-bs-dismiss="modal">{{__('Cancel')}}</button>
        <button type="submit" class="btn  btn-primary">{{__('Update')}}</button>
    </div>

{{ Form::close() }}

<script>
    var $emailAddressField = $('#emailAddressField');
    var $mobileField = $('#mobileField');
    $(document).on('change', '#customer', function() {
        var value = $(this).val();
        $('#emailAddressField').val('');
        $('#mobileField').val('');
        getproject(value);
    });

    $(document).ready(function () {
        var value = $('#customer').val();
        getproject(value);
    });

    function getproject(value) {
        if (value === 'client') {
            $('#customname').addClass('d-none');
            $('#emailClient').removeClass('d-none');
            $('#select').val('');
            $emailAddressField.css('background-color', '#e9ecef');
            $mobileField.css('background-color', '#e9ecef');
            if ($(this).val() !== '') {
                $emailAddressField.prop('readonly', true);
                $mobileField.prop('readonly', true);
            } else {
                $emailAddressField.prop('readonly', false);
                $mobileField.prop('readonly', false);
            }
        } else if (value === 'custom') {
            $('#emailClient').addClass('d-none');
            $('#customname').removeClass('d-none');

            $emailAddressField.css('background-color', '');
            $mobileField.css('background-color', '');
            if ($(this).val() == '') {
                $emailAddressField.prop('readonly', true);
                $mobileField.prop('readonly', true);
            } else {
                $emailAddressField.prop('readonly', false);
                $mobileField.prop('readonly', false);
            }
        }
    }
</script>

<script>
    $(document).on('change', '.select_person_email', function() {
        var userId = $(this).val();
        $.ajax({
            url: '{{ route('bookings.getuser') }}',
            type: 'POST',
            data: {
                "user_id": userId,
                "_token": "{{ csrf_token() }}",
            },
            success: function(data) {
                $('#emailAddressField').val(data.email);
                $('#mobileField').val(data.mobile_no);
            }
        });
    });
</script>


