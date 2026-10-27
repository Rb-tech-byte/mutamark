@php
    $admin_settings = getAdminAllSetting($workspace->created_by, $workspace->id);
    $company_settings = getCompanyAllSetting($workspace->created_by, $workspace->id);

@endphp

<div class="container">
    <div class="card">
        <div class="card-body">
            {{ Form::open(array('route' => array('bookings.frontend.appointment.store', $slug), 'id'=>'payment_form', 'enctype' => 'multipart/form-data')) }}
            <div class="row">
                <div class="col-xxl-6">
                    <div class="vendor-shedule">
                        <h4 class="mb-3">{{__('Choose Time Slot')}}</h4>
                        <div class="TimeSlot">
                            <div class="row">
                                @if (!empty($timeSlots))
                                    @foreach ($timeSlots as $time)
                                        <div class="col-sm-3 col-6">
                                            <div class="single time-slot">
                                                <p class="time-slot-text" data-start="{{ $time['start'] }}" data-end="{{ $time['end'] }}">
                                                    {{ $time['start'] }} - {{ $time['end'] }}
                                                </p>
                                            </div>
                                        </div>
                                    @endforeach
                                    {{ Form::hidden('start_time', null, ['class' => 'start-time-input']) }}
                                    {{ Form::hidden('end_time', null, ['class' => 'end-time-input']) }}
                                @else
                                    <p>{{ __('No available time slots for the selected date. Please choose another date.') }}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <h4>{{'Your Location'}}</h4>
                        <div class="row">
                            <div class="form-group col-md-6 col-12 mb-3">
                                {{ Form::label('your_country', __('Country'),['class'=>'form-label']) }}
                                {{ Form::text('your_country', '', ['class' => 'form-control','placeholder'=> 'Enter Country' ,'required'=>'required']) }}
                            </div>
                            <div class="form-group col-md-6 col-12 mb-3">
                                {{ Form::label('your_state', __('State'), ['class' => 'form-label']) }}
                                {{ Form::text('your_state', '', ['class' => 'form-control ','placeholder'=> 'Enter State' , 'required' => 'required']) }}
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group col-md-6 col-12 mb-3">
                                {{ Form::label('your_city', __('City'), ['class' => 'form-label']) }}
                                {{ Form::text('your_city', '', ['class' => 'form-control ','placeholder'=> 'Enter City' , 'required' => 'required']) }}
                            </div>
                            <div class="form-group col-md-6 col-12 mb-3">
                                {{ Form::label('your_address', __('Address'), ['class' => 'form-label']) }}
                                {{ Form::text('your_address', '', ['class' => 'form-control ','placeholder'=> 'Enter Address' , 'required' => 'required']) }}
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group col-md-6 col-12 mb-3">
                                {{ Form::label('your_zip_code', __('Zip code'), ['class' => 'form-label']) }}
                                {{ Form::number('your_zip_code', '', ['class' => 'form-control ','placeholder'=> 'Enter Zip code' , 'required' => 'required']) }}
                            </div>
                        </div>
                    </div>
                    <br>
                    @php
                        $workspace = App\Models\WorkSpace::where('slug', $slug)->first();
                        $settings  = getCompanyAllSetting($workspace->created_by, $workspace->id);
                    @endphp
                    <div class="row">
                        <h4>{{'Our Location'}}</h4>
                        <div class="row">
                            <div class="form-group col-md-6 col-12 mb-3">
                                {{ Form::label('our_country', __('Country'),['class'=>'form-label']) }}
                                {{ Form::text('our_country', isset($settings['bookings_our_country']) ? $settings['bookings_our_country'] :'', ['class' => 'form-control','placeholder'=> 'Enter Country' ,'required'=>'required', 'readonly' => 'readonly']) }}
                            </div>
                            <div class="form-group col-md-6 col-12 mb-3">
                                {{ Form::label('our_state', __('State'), ['class' => 'form-label']) }}
                                {{ Form::text('our_state',isset($settings['bookings_our_state']) ? $settings['bookings_our_state'] :'', ['class' => 'form-control ','placeholder'=> 'Enter State' , 'required' => 'required', 'readonly' => 'readonly']) }}
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group col-md-6 col-12 mb-3">
                                {{ Form::label('our_city', __('City'), ['class' => 'form-label']) }}
                                {{ Form::text('our_city',isset($settings['bookings_our_city']) ? $settings['bookings_our_city'] :'', ['class' => 'form-control ','placeholder'=> 'Enter City' , 'required' => 'required', 'readonly' => 'readonly']) }}
                            </div>
                            <div class="form-group col-md-6 col-12 mb-3">
                                {{ Form::label('our_zip_code', __('Zip code'), ['class' => 'form-label']) }}
                                {{ Form::number('our_zip_code',isset($settings['bookings_our_zip_code']) ? $settings['bookings_our_zip_code'] :'', ['class' => 'form-control ','placeholder'=> 'Enter Zip code' , 'required' => 'required', 'readonly' => 'readonly']) }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xxl-6 mt-2">
                    <div class="row">
                        <div class="col-12 summary-bg">
                            <h4>{{'Summary'}}</h4>
                            <div class="row d-flex align-items-center">
                                <div class="col-auto">
                                    <h6>{{__('Date')}}</h6>
                                    <p class="m-0">{{ $formattedSelectedDate }}</p>
                                </div>
                                <div class="col-auto">
                                    <h6>{{__('Time')}}</h6>
                                    <p class="m-0" id="selected-time-slot">{{ ('No time selected') }}</p>
                                </div>
                                <div class="col-auto">
                                    <h6>{{__('Price')}}</h6>
                                    <p class="m-0">{{ currency_format_with_sym($serivceID->price,$workspace->created_by, $workspace->id) ?? '-' }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="form-group col-md-6 col-12 mb-3">
                            <label class="form-label">{{ __('Customer type') }}</label>
                            <div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" id="account_type_custom" name="customer" required="required" value="walk_in" {{ !empty($booking) ? ($booking->customer == 'custom' ? 'checked' : '') : '' }}>
                                    <label class="form-check-label" for="account_type_custom">
                                        {{ __('Walk-in') }}
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" id="account_type_client" name="customer" required="required" value="client" {{ !empty($booking) ? ($booking->customer == 'client' ? 'checked' : '') : '' }}>
                                    <label class="form-check-label" for="account_type_client">
                                        {{ __('Login') }}
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="form-group col-md-6 col-12 mb-3" id="customname">
                            <label class="form-label">{{ __('Name') }}</label>
                            <input class="form-control"
                                type="text" name="name" placeholder="{{ __('Name') }}">
                        </div>
                        <div class="form-group col-md-6 col-12 mb-3">
                            <label class="form-label">{{ __('Email') }}</label>
                            <input class="form-control email"
                                type="email" name="email" required="" placeholder="{{ __('Email') }}"
                                id="emailAddressField">
                        </div>
                        <div class="form-group col-md-6 col-12 mb-3">
                            <label class="form-label">{{ __('Mobile Number') }}</label>
                            <input class="form-control"
                                type="number" name="number" required="" placeholder="{{ __('Mobile Number') }}"
                                id="mobileField">
                        </div>

                        <div class="form-group col-md-6">
                            <label class="require form-label">{{ __('Select Payment Option') }}</label>

                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="payment_option" id="online_payment" value="Online" required>
                                <label class="form-check-label" for="online_payment">
                                    {{ __('Online') }}
                                </label>
                            </div>

                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="payment_option" id="offline_payment" value="Offline" required>
                                <label class="form-check-label" for="offline_payment">
                                    {{ __('Offline') }}
                                </label>
                            </div>
                        </div>

                        <div id="offlineSection" style="display:none;">
                            <!-- Your offline payment code here -->
                            <div class="row justify-content-between">
                                <div class="col-md-6 col-12">
                                    <h5><b>{{ __('Payment Method') }}</b></h5><br>
                                    @stack('bookings_payment')
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <input type="hidden" name="service" value="{{$serivceID->item}}">
            <input type="hidden" name="staff" value="{{$staff}}">
            <input type="hidden" name="package" value="{{$package}}">
            <input type="hidden" name="date" value="{{$formattedSelectedDate}}">

            <div class="text-end mt-3">
                {{ Form::submit('Book Now', ['id' => 'submitBtn', 'class' => 'btn btn-primary']) }}
            </div>
            {{ Form::close() }}
        </div>
    </div>
</div>


<script>
    $(document).ready(function() {
        $(".single").click(function() {
            $(".single").removeClass("selected");
            $(this).addClass("selected");
        });
    });
</script>

<script>
    $(document).ready(function() {
        $(document).ready(function() {
            $('.time-slot').on('click', function() {
                $('.time-slot').removeClass('active');
                $(this).addClass('active');

                var startTime = $(this).find('.time-slot-text').data('start');
                var endTime = $(this).find('.time-slot-text').data('end');

                $('.start-time-input').val(startTime);
                $('.end-time-input').val(endTime);
            });
        });

        // Handle form submission
        $('#bookingForm').submit(function(event) {
            event.preventDefault();

            var startTime = $('.start-time-input').val();
            var endTime = $('.end-time-input').val();

            $(this).unbind('submit').submit();
        });
    });
</script>

<script>
    $(document).ready(function() {
    $('.time-slot-text').on('click', function() {
      var startTime = $(this).data('start');
      var endTime = $(this).data('end');

      var selectedTime = startTime + ' - ' + endTime;

      $('.start-time-input').val(startTime);
      $('.end-time-input').val(endTime);

      $('#selected-time-slot').text(selectedTime);

      $('.time-slot-text').removeClass('selected');
      $(this).addClass('selected');
    });
  });
</script>

<script>
    $(document).ready(function() {
        $('input[name="payment_option"]').change(function() {

            if ($(this).val() == 'Offline') {
                $('#offlineSection').hide();
            } else {
                $('#offlineSection').show();
            }
        });
    });
</script>

