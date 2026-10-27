{{ Form::open(['url' => 'driver', 'method' => 'post', 'enctype' => 'multipart/form-data','class'=>'needs-validation','novalidate']) }}
<div class="modal-body">
    <div class="row">
        <div class="form-group col-md-6 col-12">
            <label class="form-label">{{ __('Type') }}</label><x-required></x-required>
            <select class="form-select" id="select_driver_type" name="select_driver_type" required>
                <option selected disabled>{{ __('Select Type') }}</option>
                <option value="contractor">{{ __('Contractor') }}</option>
                <option value="staff">{{ __('Staff') }}</option>
            </select>
        </div>

        <div class="form-group col-md-6 col-12" id="name">
            <label class="form-label">{{ __('Name') }}</label>
            <input class="form-control"
                type="text" name="name" placeholder="{{ __('Enter Name') }}">
        </div>

        <div class="form-group col-md-6 col-12 d-none" id="emailStaff">
            <label class="form-label">{{ __('Select') }}</label>
            <select class="form-control select_person_email" name="staff_id">
            <option value="">{{ __('Select Staff') }}</option>
            @foreach ($staffs as $id => $name)
                <option value="{{ $id }}">
                    {{ $name }}
                </option>
            @endforeach
            </select>
        </div>
        <div class="form-group col-md-6 col-12">
            <label class="form-label">{{ __('Email') }}</label>
            <input class="form-control email"
                type="email" name="email" required="" placeholder="{{ __('Enter Email') }}"
                id="emailField">
        </div>

            <x-mobile divClass="col-md-6" name="phone" label="{{ __('Mobile Number') }}" placeholder="{{ __('Enter Mobile Number') }}" id="mobileField" required="required"></x-mobile>
        <div class="form-group col-md-6 col-12" id="passwordField" style="display: none;">
            <label class="form-label">{{ __('Password') }}</label>
            <input class="form-control" type="password" name="password" placeholder="{{ __('Enter Password') }}" id="password">
        </div>

        <div class="form-group col-md-6 col-12">
            {{ Form::label('dob', __('Date of Birth'), ['class' => 'form-label']) }}<x-required></x-required>
            {{ Form::date('dob', null, ['class' => 'form-control current_date', 'required' => 'required', 'autocomplete' => 'off', 'placeholder' => 'Select Date of Birth','max' => date('Y-m-d')]) }}
        </div>

        <div class="col-md-6 form-group">
                {!! Form::label('join_date', __('Join Date'), ['class' => 'form-label']) !!}<x-required></x-required>
                {{ Form::date('join_date', null, ['class' => 'form-control current_date', 'required' => 'required', 'autocomplete' => 'off', 'placeholder' => 'Select join date']) }}
        </div>

        <div class="col-md-6 form-group">
                {{ Form::label('lincese_number', __('Licence Number'), ['class' => 'form-label']) }}<x-required></x-required>
                {{ Form::text('lincese_number', null, ['class' => 'form-control', 'placeholder' => __('Enter Licence Number'), 'required' => 'required']) }}
        </div>

        <div class="col-md-6 form-group">
                {{ Form::label('lincese_type', __('Licence Type'), ['class' => 'form-label']) }}<x-required></x-required>
                {{ Form::select('lincese_type', $lincese_type, null, ['class' => 'form-control', 'required' => 'required']) }}
                @if (count($lincese_type) <= 0)
                    <div class="text-muted text-xs">{{ __('Please create new licence type') }} <a href="{{ route('license.index') }}">{{ __('here') }}</a></div>
                @endif
        </div>

        <div class="col-md-6 form-group">
                {!! Form::label('expiry_date', __('Licence Expire Date'), ['class' => 'form-label']) !!}<x-required></x-required>
                {{ Form::date('expiry_date', null, ['class' => 'form-control current_date', 'required' => 'required', 'autocomplete' => 'off', 'placeholder' => 'Select Issue Date']) }}
        </div>
        <div class="col-md-6 form-group">
                {{ Form::label('driver_status', __('Driver Status'), ['class' => 'form-label']) }}<x-required></x-required>
                {{ Form::select('driver_status', ['Active' => 'Active', 'Inactive' => 'Inactive'], null, ['class' => 'form-control', 'required' => 'required', 'placeholder' => 'Select Driver Status']) }}
        </div>

        <div class="col-md-6 form-group">
                {!! Form::label('Working_time', __('Working Hours'), ['class' => 'form-label']) !!}<x-required></x-required>
                {{ Form::text('Working_time', null, ['class' => 'form-control current_date', 'required' => 'required', 'pattern' => '^(0?[1-9]|1[0-2]):[0-5][0-9](AM|PM|am|pm)\s-\s(0?[1-9]|1[0-2]):[0-5][0-9](AM|PM|am|pm)$', 'placeholder' => '10:00AM - 6:00PM']) }}
        </div>
        <div class="form-group col-md-12">
            {!! Form::label('address', __('Address'), ['class' => 'form-label']) !!}<x-required></x-required>
            {!! Form::textarea('address', null, [
                'class' => 'form-control',
                'placeholder' => __('Enter Address'),
                'required' => 'required',
                'rows' => 3,
            ]) !!}
        </div>

        @if (module_is_active('CustomField') && !$customFields->isEmpty())
            <div class="col-md-6">
                <div class="tab-pane fade show" id="tab-2" role="tabpanel">
                    @include('custom-field::formBuilder')
                </div>
            </div>
        @endif
    </div>
</div>

<div class="modal-footer">
    <input type="button" value="{{ __('Cancel') }}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Create') }}" class="btn btn-primary">
</div>

{{ Form::close() }}

<script>
    var $emailField = $('#emailField');
    var $mobileField = $('#mobileField');
    $(document).on('change', '#select_driver_type', function() {
        var value = $(this).val();
        if (value === 'staff') {
            $('#name').addClass('d-none');
            $('#emailStaff').removeClass('d-none');
            $('#passwordField').hide();
            $emailField.css('background-color', '#e9ecef');
            $mobileField.css('background-color', '#e9ecef');
            $emailField.prop('readonly', true);
            $mobileField.prop('readonly', true);

        } else if (value === 'contractor'){

            $('#emailStaff').addClass('d-none');
            $('#name').removeClass('d-none');
            $('#passwordField').show();
            $emailField.css('background-color', '');
            $mobileField.css('background-color', '');
            $emailField.prop('readonly', false);
            $mobileField.prop('readonly', false);
        }

        $('#emailField').val('');
        $('#mobileField').val('');
        $('.select_person_email').val('');
    });
</script>

<script>
    $(document).on('change', '.select_person_email', function() {
        var userId = $(this).val();
        $.ajax({
            url: '{{ route('driver.getuser') }}',
            type: 'POST',
            data: {
                "user_id": userId,
                "_token": "{{ csrf_token() }}",
            },
            success: function(data) {
                $('#emailField').val(data.email);
                $('#mobileField').val(data.mobile_no);
            }
        });
    });
</script>


