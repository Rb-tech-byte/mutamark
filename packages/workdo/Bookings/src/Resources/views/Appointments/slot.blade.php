<div class="container">
    <div class="row">
        <div class="col-lg-12 col-12">
            <div class="vendor-shedule">
                <h4 class="mb-3">{{__('Choose Time Slot')}}</h4>
                <div class="TimeSlot">
                    <div class="row">
                        @if (!empty($timeSlots))
                            @foreach ($timeSlots as $time)
                                <div class="col-sm-3 col-6">
                                    <div class="single time-slot" @if ($time['available']) style="color: red; pointer-events: none;" @endif>
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
        </div>

        <div class="col-lg-12 col-12 mt-2">
            <div class="row">
                <h4>{{'Your Location'}}</h4>
                <div class="form-group col-md-6 col-12 mb-3">
                    {{ Form::label('your_country', __('Country'),['class'=>'form-label']) }}<x-required></x-required>
                    {{ Form::text('your_country', isset($appointment->your_country) ? $appointment->your_country :'', ['class' => 'form-control','placeholder'=> 'Enter Country' ,'required'=>'required']) }}
                </div>
                <div class="form-group col-md-6 col-12 mb-3">
                    {{ Form::label('your_state', __('State'), ['class' => 'form-label']) }}<x-required></x-required>
                    {{ Form::text('your_state',isset($appointment->your_state) ? $appointment->your_state : '', ['class' => 'form-control ','placeholder'=> 'Enter State' , 'required' => 'required']) }}
                </div>
                <div class="form-group col-md-6 col-12 mb-3">
                    {{ Form::label('your_city', __('City'), ['class' => 'form-label']) }}<x-required></x-required>
                    {{ Form::text('your_city',isset($appointment->your_city) ? $appointment->your_city : '', ['class' => 'form-control ','placeholder'=> 'Enter City' , 'required' => 'required']) }}
                </div>
                <div class="form-group col-md-6 col-12 mb-3">
                    {{ Form::label('your_address', __('Address'), ['class' => 'form-label']) }}<x-required></x-required>
                    {{ Form::text('your_address',isset($appointment->your_address) ? $appointment->your_address : '', ['class' => 'form-control ','placeholder'=> 'Enter Address' , 'required' => 'required']) }}
                </div>
                <div class="form-group col-md-6 col-12 mb-3">
                    {{ Form::label('your_zip_code', __('Zip code'), ['class' => 'form-label']) }}<x-required></x-required>
                    {{ Form::number('your_zip_code',isset($appointment->your_zip_code) ? $appointment->your_zip_code : '', ['class' => 'form-control ','placeholder'=> 'Enter Zip code' , 'required' => 'required']) }}
                </div>
            </div>
            <br>
            @php
                $settings = getCompanyAllSetting();
            @endphp
            <div class="row">
                <h4>{{'Our Location'}}</h4>
                <div class="form-group col-md-6 col-12 mb-3">
                    {{ Form::label('our_country', __('Country'),['class'=>'form-label']) }}
                    {{ Form::text('our_country', isset($settings['bookings_our_country']) ? $settings['bookings_our_country'] :'', ['class' => 'form-control','placeholder'=> 'Enter Country' ,'required'=>'required', 'readonly' => 'readonly']) }}
                </div>
                <div class="form-group col-md-6 col-12 mb-3">
                    {{ Form::label('our_state', __('State'), ['class' => 'form-label']) }}
                    {{ Form::text('our_state',isset($settings['bookings_our_state']) ? $settings['bookings_our_state'] :'', ['class' => 'form-control ','placeholder'=> 'Enter State' , 'required' => 'required', 'readonly' => 'readonly']) }}
                </div>
                <div class="form-group col-md-6 col-12 mb-3">
                    {{ Form::label('our_city', __('City'), ['class' => 'form-label']) }}
                    {{ Form::text('our_city',isset($settings['bookings_our_city']) ? $settings['bookings_our_city'] :'', ['class' => 'form-control ','placeholder'=> 'Enter City' , 'required' => 'required', 'readonly' => 'readonly']) }}
                </div>
                <div class="form-group col-md-6 col-12 mb-3">
                    {{ Form::label('our_zip_code', __('Zip code'), ['class' => 'form-label']) }}
                    {{ Form::number('our_zip_code',isset($settings['bookings_our_zip_code']) ? $settings['bookings_our_zip_code'] :'', ['class' => 'form-control ','placeholder'=> 'Enter Zip code' , 'required' => 'required', 'readonly' => 'readonly']) }}
                </div>
            </div>
            <br>
            <div class="row">
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
                        <p class="m-0">{{ currency_format_with_sym($serivceID->price) ?? '-' }}</p>
                    </div>
                </div>
            </div>
            </div>
        </div>
    </div>
    <div class="text-end mt-3">
        {{ Form::submit('Book Now', ['id' => 'submitBtn', 'class' => 'btn btn-primary']) }}
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
