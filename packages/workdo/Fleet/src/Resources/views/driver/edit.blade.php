@if (!empty($driver))
    {{ Form::model($driver, ['route' => ['driver.update', $driver->id], 'method' => 'PUT', 'enctype' => 'multipart/form-data','class'=>'needs-validation','novalidate']) }}
@else
    {{ Form::open(['route' => ['driver.store'], 'method' => 'post','class'=>'needs-validation','novalidate']) }}
@endif
@if (!empty($user->id))
    <input type="hidden" name="user_id" value="{{ $user->id }}">
@endif
<div class="modal-body">
    <div class="row">
            <div class="form-group col-md-6 col-12">
                <label class= "form-label">{{ __('Type') }}</label><x-required></x-required>
                <select class="form-select" id="select_driver_type" name="select_driver_type" required>
                    <option selected>{{ __('Select Type') }}</option>
                    <option value="contractor"
                        {{ !empty($user) ? ($user->type == 'contractor' ? 'selected' : '') : '' }}>
                        {{ __('Contractor') }}</option>
                    <option value="staff"
                        {{ !empty($user) ? ($user->type == 'staff' ? 'selected' : '') : '' }}>
                        {{ __('Staff') }}</option>
                </select>
            </div>
            <div class="form-group col-md-6 col-12" id="customname">
                <label class="form-label">{{ __('Name') }}</label>
                <input class="form-control" type="text" name="name"
                    value="{{ !empty($driver->name) ? $driver->name : $user->name }}" placeholder="{{ __('Enter Name') }}">
                <div class="invalid-feedback">
                    {{ $errors->first('name') }}
                </div>
            </div>
            <div class="form-group col-md-6 col-12 d-none" id="emailStaff">
                <label class="form-label">{{ __('Select') }}</label>
                <select class="form-control select_person_email" name="staff_id">
                    <option value="">{{ __('Select Staff') }}</option>
                    @foreach ($staffs as $id => $name)
                        <option value="{{ $id }}" @if ( !empty($user) && $user->id == $id) selected @endif>
                            {{ $name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-6 col-12">
                <label class="form-label">{{ __('Email') }}</label>
                <input class="form-control email" type="email" name="email" required=""
                    value="{{ isset($driver->email) ? $driver->email : $user->email }}" placeholder="{{ __('Enter Email') }}"
                    id="emailField">
                <div class="invalid-feedback">
                    {{ $errors->first('email') }}
                </div>
            </div>
                <x-mobile divClass="col-md-6" name="phone" value="{{ isset($driver->phone) ? $driver->phone : $user->mobile_no }}" class="form-control" label="{{ __('Mobile Number') }}" placeholder="{{ __('Mobile Number') }}" id="mobileField" required="required"></x-mobile>
        @if (!empty($driver) && $driver->select_driver_type == 'staff')
            <div class="col-md-6" id="passwordField" style="display: none;">
                <div class="form-group">
                    <label class="form-label">{{ __('Password') }}</label>
                    <input class="form-control" type="password" name="password"
                        placeholder="{{ __('Enter Password') }}" id="password">
                </div>
            </div>
        @endif

        <div class="col-md-6">
            <div class="form-group">
                {!! Form::label('dob', __('Date of Birth'), ['class' => 'form-label']) !!}<x-required></x-required>
                {{ Form::date('dob', null, ['class' => 'form-control current_date', 'required' => 'required', 'autocomplete' => 'off', 'placeholder' => 'Select Date of Birth','max' => date('Y-m-d')]) }}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {!! Form::label('join_date', __('Join Date'), ['class' => 'form-label']) !!}<x-required></x-required>
                {{ Form::date('join_date', null, ['class' => 'form-control current_date', 'required' => 'required', 'autocomplete' => 'off', 'placeholder' => 'Select join date']) }}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('lincese_number', __('Licence Number'), ['class' => 'form-label']) }}<x-required></x-required>
                {{ Form::text('lincese_number', null, ['class' => 'form-control', 'placeholder' => __('Enter Licence Number'), 'required' => 'required']) }}
                @error('lincese_number')
                    <small class="invalid-lincese_number" role="alert">
                        <strong class="text-danger">{{ $message }}</strong>
                    </small>
                @enderror
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('lincese_type', __('Licence Type'), ['class' => 'form-label']) }}<x-required></x-required>
                {{ Form::select('lincese_type', $lincese_type, null, ['class' => 'form-control', 'required' => 'required']) }}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {!! Form::label('expiry_date', __('Licence Expire Date'), ['class' => 'form-label']) !!}<x-required></x-required>
                {{ Form::date('expiry_date', null, ['class' => 'form-control current_date', 'required' => 'required', 'autocomplete' => 'off', 'placeholder' => 'Select Issue Date']) }}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('driver_status', __('Driver Status'), ['class' => 'form-label']) }}<x-required></x-required>
                {{ Form::select('driver_status', ['Active' => 'Active', 'Inactive' => 'Inactive'], null, ['class' => 'form-control', 'required' => 'required', 'placeholder' => 'Select Driver Status']) }}

            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {!! Form::label('Working_time', __('Working Time'), ['class' => 'form-label']) !!}<x-required></x-required>
                {{ Form::text('Working_time', isset($driver->Working_time) ? $driver->Working_time : '', ['class' => 'form-control current_date', 'required' => 'required', 'pattern' => '^(0?[1-9]|1[0-2]):[0-5][0-9](AM|PM|am|pm)\s-\s(0?[1-9]|1[0-2]):[0-5][0-9](AM|PM|
                am|pm)$', 'placeholder' => '10:00AM - 6:00PM']) }}
            </div>
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
        @if (!empty($customFields))
            @if (module_is_active('CustomField') && !$customFields->isEmpty())
                <div class="col-md-12">
                    <div class="tab-pane fade show" id="tab-2" role="tabpanel">
                        @include('custom-field::formBuilder', ['fildedata' => $driver->customField])
                    </div>
                </div>
            @endif
        @endif
    </div>
</div>

<div class="modal-footer">
    <input type="button" value="{{ __('Cancel') }}" class="btn  btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Update') }}" class="btn  btn-primary">
</div>

{{ Form::close() }}
@if(!empty($user) && $user->type == 'staff')
<script>
    $( document ).ready(function() {
        $('.select_person_email').trigger('change');
    });
</script>
@endif
<script>
    var $emailField = $('#emailField');
    var $mobileField = $('#mobileField');

    $(document).on('change', '#select_driver_type', function() {
        var value = $(this).val();
        $('#emailField').val('');
        $('#mobileField').val('');
        getdriver(value);
    });

    $(document).ready(function() {
        var value = $('#select_driver_type').val();
        getdriver(value);
    });

    function getdriver(value) {

        if (value === 'staff') {
            $('#customname').addClass('d-none');
            $('#emailStaff').removeClass('d-none');
            $('#passwordField').hide();

            $emailField.css('background-color', '#e9ecef');
            $mobileField.css('background-color', '#e9ecef');

            $emailField.prop('readonly', true);
            $mobileField.prop('readonly', true);

        } else if (value === 'contractor') {
            $('#emailStaff').addClass('d-none');
            $('#customname').removeClass('d-none');
            $('#passwordField').show();

            $emailField.css('background-color', '');
            $mobileField.css('background-color', '');

            $emailField.prop('readonly', false);
            $mobileField.prop('readonly', false);
        }
    }
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
