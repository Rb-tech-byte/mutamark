@extends('appointment::layouts.master')
@push('css')
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <style>
        .dark_background_color {
            background: #000 !important;
        }
    </style>
@endpush
@section('page-title')
    {{ __('Appointment') }} - {{ $appointment->unique_id }}
@endsection
@section('content')
    <div class="auth-wrapper auth-v1">
        <div class="bg-auth-side bg-primary"></div>
        <div class="auth-content">
            <div class="row align-items-center justify-content-center text-start">
                <div class="col-xl-12 text-start">
                    <div class="mx-3 mx-md-5">
                        <div class="card-header">
                            <h5 class="text-white">{{ __('Appointment') }} - {{ $appointment->unique_id }}</h5>
                        </div>
                    </div>
                    <div class="card p-4">
                        @csrf
                        <div class="card mb-3">
                            <div class="card-header">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6>{{ $appointment->appointment->name }}
                                            <small>({{ $appointment->created_at->diffForHumans() }})</small>
                                        </h6>
                                    </div>
                                    <div class="col-md-6">
                                        @if ($appointment->status == 'Pending')
                                            <div class="badge bg-warning p-2 rounded status-badge5 text-end"
                                                style="margin-left: 580px; font-size: small;">
                                                {{ $appointment->status }}</div>
                                        @elseif($appointment->status == 'Approved')
                                            <div class="badge bg-success p-2 rounded status-badge5 text-end"
                                                style="margin-left: 580px; font-size: small;">
                                                {{ $appointment->status }}</div>
                                        @elseif($appointment->status == 'Complete')
                                            <div class="badge bg-info p-2 rounded status-badge5 text-end"
                                                style="margin-left: 580px; font-size: small;">
                                                {{ $appointment->status }}</div>
                                        @else
                                            <div class="badge bg-danger p-2 rounded status-badge5 text-end"
                                                style="margin-left: 580px; font-size: small;">
                                                {{ $appointment->status }}</div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="card-body w-100">
                                <div class="form-group col-md-12">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p>{{ __('Name ') }}: {!! !empty($appointment->name) ? $appointment->name : '--' !!}</p>
                                            <p>{{ __('Email ') }}: {!! !empty($appointment->email) ? $appointment->email : '--' !!}</p>
                                            <p>{{ __('Contact No ') }}: {!! !empty($appointment->phone) ? $appointment->phone : '--' !!}</p>
                                            <p>{{ __('Meeting Type ') }}: {!! !empty($appointment->meeting_type) ? $appointment->meeting_type : '--' !!}</p>
                                        </div>
                                        <div class="col-md-6">
                                            <p>{{ __('Attendance staff ') }}: {!! !empty($appointment->user_id) ? $appointment->users->name : '--' !!}</p>
                                            <p>{{ __('Date ') }}: {!! company_date_formate($appointment->date, $appointment->created_by, $appointment->workspace) !!}</p>
                                            <p>{{ __('Start Time ') }}: {!! company_Time_formate($appointment->start_time, $appointment->created_by, $appointment->workspace) !!}</p>
                                            <p>{{ __('End Time ') }}: {!! company_Time_formate($appointment->end_time, $appointment->created_by, $appointment->workspace) !!}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if ($appointment->status == 'Approved' || $appointment->status == 'Pending')
                            <div class="card mb-3">
                                <div class="card-body w-100">
                                    <form method="post"
                                        action="{{ route('appointment.cancel_form', [$workspace->slug, $appointment->unique_id]) }}">
                                        @csrf
                                        <div class="row">
                                            <div class="form-group col-md-6">
                                                <h6>{{ __('Appointment Cancel Form') }}</h6>
                                            </div>
                                            <div class="form-group col-md-12">
                                                <label
                                                    class="require form-label">{{ __('why cancel appointment ?') }}</label>
                                                <textarea name="cancel_description"
                                                    class="form-control {{ $errors->has('cancel_description') ? ' is-invalid' : '' }}">{{ old('cancel_description') }}</textarea>
                                                <div class="invalid-feedback">
                                                    {{ $errors->first('cancel_description') }}
                                                </div>
                                                <p class="text-danger d-none" id="skill_validation">
                                                    {{ __('Reason filed is required.') }}</p>
                                            </div>
                                        </div>

                                        <div class="text-center">
                                            <div class="d-block ">
                                                <input type="hidden" name="status" value="Cancel" />
                                                <button
                                                    class="btn btn-submit btn-primary mt-2">{{ __('Submit') }}</button>
                                            </div>
                                        </div>

                                    </form>
                                </div>
                            </div>
                        @elseif($appointment->status == 'Complete')
                            <div class="text-start">
                                <div class="btn btn-submit btn-primary mt-2">
                                    <a href="#ticket-info" class="" type="button" data-bs-toggle="collapse"
                                        data-bs-toggle="tooltip" data-bs-placement="top"
                                        style="color: white">{{ __('Callback Appointment') }}</a>
                                </div>
                            </div>
                            {{ Form::model($appointment, ['route' => ['appointment.callback', $workspace->slug, \Crypt::encrypt($appointment->id)], 'id' => 'ticket-info', 'class' => 'collapse mt-3', 'method' => 'POST']) }}
                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label class="require form-label">{{ __('Reason for callback') }}</label>
                                    <textarea name="callback_description"
                                        class="form-control {{ $errors->has('callback_description') ? ' is-invalid' : '' }}">{{ old('callback_description') }}</textarea>
                                    <div class="invalid-feedback">
                                        {{ $errors->first('callback_description') }}
                                    </div>
                                    <p class="text-danger d-none" id="skill_validation">
                                        {{ __('Reason filed is required.') }}</p>
                                </div>

                                <div class="col-md-2 form-group">
                                    {{ Form::label('date', __('Date'), ['class' => 'col-form-label']) }}<span
                                        class="text-danger">*</span>
                                    <input type="text" name="date" class="form-control" id="datepicker"
                                        placeholder="Enter Date" required autocomplete="off">
                                </div>

                                <div class="col-md-2 form-group">
                                    {{ Form::label('start_time', __('Start Time'), ['class' => 'col-form-label']) }}<span
                                        class="text-danger">*</span>
                                    {{ Form::time('start_time', '', ['class' => 'form-control', 'required' => 'required', 'placeholder' => 'Enter Start Time']) }}
                                </div>

                                <div class="col-md-2 form-group">
                                    {{ Form::label('end_time', __('End Time'), ['class' => 'col-form-label']) }}<span
                                        class="text-danger">*</span>
                                    {{ Form::time('end_time', '', ['class' => 'form-control', 'required' => 'required', 'placeholder' => 'Enter End Time']) }}
                                </div>

                            </div>

                            <div class="text-center">
                                <div class="d-block ">
                                    <button class="btn btn-primary">
                                        {{ __('Create') }}
                                    </button>
                                </div>
                            </div>

                            {{ Form::close() }}
                        @endif
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection

{{-- Today Date script --}}
@push('script')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            // Define an array of weekdays to be disabled (0 = Sunday, 1 = Monday, etc.)
            var allowedWeekdays = '{{ $week_day->week_day }}';
            var allowedWeekdayss = allowedWeekdays.split(', ');
            $("#datepicker").datepicker({
                beforeShowDay: function(date) {
                    const selectedDate = new Date(date);
                    const selectedWeekday = selectedDate.toLocaleString('en-US', {
                        weekday: 'long'
                    }).toLowerCase();
                    return [allowedWeekdayss.indexOf(selectedWeekday) !== -
                        1
                    ]; // Return true to enable, false to disable
                },
                minDate: 0
            });
        });

        $('form').on('submit', function() {
            // Get the selected date from the datepicker
            var selectedDate = $('#datepicker').datepicker('getDate');

            // Format the date as YYYY-MM-DD
            var formattedDate = $.datepicker.formatDate('yy-mm-dd', selectedDate);

            // Set the formatted date as the value of the date input field
            $('#datepicker').val(formattedDate);
        });
    </script>
@endpush
