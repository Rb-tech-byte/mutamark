@extends('appointment::layouts.master')
@section('page-title')
    {{ __('Create Appointment') }}
@endsection
@push('css')
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <style>
        .dark_background_color {
            background: #000 !important;
        }
    </style>
@endpush
@php
    $admin_settings = getAdminAllSetting();
    $company_settings = getCompanyAllSetting($workspace->created_by, $workspace->id);
@endphp
@section('content')
    <div class="auth-wrapper auth-v1">
        <div class="bg-auth-side bg-primary"></div>
        <div class="auth-content">
            <nav class="navbar navbar-expand-md navbar-dark default dark_background_color">
                <div class="container-fluid pe-2">
                    <a class="navbar-brand" href="#">

                        <img src="{{ !empty($company_settings['logo_light']) ? get_file($company_settings['logo_light']) : get_file(!empty($admin_settings['logo_light']) ? $admin_settings['logo_light'] : 'uploads/logo/logo_light.png') }}{{ '?' . time() }}"
                            class="navbar-brand-img auth-navbar-brand" style="
                        max-width: 168px;">
                    </a>

                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                        data-bs-target="#navbarTogglerDemo01" aria-controls="navbarTogglerDemo01" aria-expanded="false"
                        aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <div class="collapse navbar-collapse" id="navbarTogglerDemo01">
                        <ul class="navbar-nav align-items-center ms-auto mb-2 mb-lg-0">
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('get.appointment.search', $workspace->slug) }}"
                                    style="color: #0CAF60;">{{ __('Search Appointment') }}</a>
                            </li>
                        </ul>
                    </div>

                </div>
            </nav>

            <div class="row align-items-center justify-content-center text-start">
                <div class="col-xl-12 text-center">
                    <div class="mx-3 mx-md-5 mt-3"></div>
                    <div class="card">
                        <div class="card-body w-100">
                            <div class="">
                                <h4 class="text-primary mb-3">{{ $appointment->name }}</h4>
                            </div>
                            <form method="post"
                                action="{{ route('appointments.store', [$workspace->slug, \Crypt::encrypt($appointment->id)]) }}"
                                class="create-form">
                                @csrf

                                <div class="text-start row">
                                    <div class="col-md-6 form-group">
                                        {{ Form::label('name', __('Name'), ['class' => 'col-form-label']) }}<span
                                            class="text-danger">*</span>
                                        {{ Form::text('name', '', ['class' => 'form-control', 'required' => 'required', 'placeholder' => 'Enter Name']) }}
                                    </div>

                                    <div class="col-md-6 form-group">
                                        {{ Form::label('email', __('Email'), ['class' => 'col-form-label']) }}<span
                                            class="text-danger">*</span>
                                        {{ Form::email('email', '', ['class' => 'form-control', 'required' => 'required', 'placeholder' => 'Enter Email']) }}
                                    </div>

                                    @if (in_array(1, $enable_field))
                                    <div class="col-md-6 form-group">
                                        {{ Form::label('phone', __('Phone'), ['class' => 'col-form-label']) }}<span
                                            class="text-danger">*</span>
                                        {{ Form::text('phone', '', ['class' => 'form-control', 'required' => 'required', 'placeholder' => 'Enter Number']) }}
                                        <div class=" text-xs text-danger">
                                            {{ __('Please add mobile number with country code. (ex. +91)') }}
                                        </div>
                                    </div>
                                    @endif

                                    @if (in_array(2, $enable_field))
                                    <div class="col-md-2 form-group">
                                        {{ Form::label('date', __('Date'), ['class' => 'col-form-label']) }}<span
                                            class="text-danger">*</span>
                                        <input type="text" name="date" class="form-control" id="datepicker"
                                            placeholder="Enter Date" required autocomplete="off">
                                    </div>
                                    @endif

                                    @if (in_array(3, $enable_field))
                                    <div class="col-md-2 form-group">
                                        {{ Form::label('start_time', __('Start Time'), ['class' => 'col-form-label']) }}<span
                                            class="text-danger">*</span>
                                        {{ Form::time('start_time', '', ['class' => 'form-control', 'required' => 'required', 'placeholder' => 'Enter Start Time']) }}
                                    </div>
                                    @endif

                                    @if (in_array(4, $enable_field))
                                    <div class="col-md-2 form-group">
                                        {{ Form::label('end_time', __('End Time'), ['class' => 'col-form-label']) }}<span
                                            class="text-danger">*</span>
                                        {{ Form::time('end_time', '', ['class' => 'form-control', 'required' => 'required', 'placeholder' => 'Enter End Time']) }}
                                    </div>
                                    @endif

                                    @if (count($question) > 0)
                                        <h6 class="mb-4">{{ __('Questions') }}</h6>
                                        @foreach ($question as $q)
                                            @php
                                                $array = explode(',', $q['available_answer']);
                                            @endphp
                                            @foreach ($available_answer as $item)
                                                @if ($q->id == $item)
                                                    <div class="col-md-6">
                                                        <dl>
                                                            <dt>
                                                                <label class="form-check-label form-label"
                                                                    for="question_{{ $q->id }}">{{ $q->question }}
                                                                    @if ($q->is_required == 'on')
                                                                        <span class="text-danger">*</span>
                                                                    @endif
                                                                </label>
                                                            </dt>
                                                            <dd>
                                                                @if ($q->question_type == 'radio')
                                                                    <div class="d-flex radio-check">
                                                                        @foreach ($array as $key => $items)
                                                                            <div
                                                                                class="custom-control custom-radio custom-control-inline">
                                                                                <input type="radio"
                                                                                    id="{{ $q->question . '-' . $items }}"
                                                                                    value="{{ $items }}"
                                                                                    name="question[{{ $q->question }}]"
                                                                                    class="custom-control-input"
                                                                                    @if ($q->is_required == 'on') required @endif>
                                                                                <label class="custom-control-label"
                                                                                    for="{{ $q->question . '-' . $items }}">{{ $items }}</label>
                                                                            </div>
                                                                        @endforeach
                                                                    </div>
                                                                @elseif($q->question_type == 'checkbox')
                                                                    @foreach ($array as $key => $items)
                                                                        <div
                                                                            class="form-check custom-checkbox custom-control custom-control-inline">
                                                                            <input type="checkbox" class="form-check-input"
                                                                                name="question[{{ $q->question }}][]"
                                                                                value="{{ $items }}"
                                                                                id="check_{{ $items }}">
                                                                            <label class="form-check-label"
                                                                                for="check_{{ $items }}">{{ $items }}
                                                                            </label>
                                                                        </div>
                                                                    @endforeach
                                                                @elseif($q->question_type == 'text')
                                                                    <input type="text" class="form-control"
                                                                        name="question[{{ $q->question }}]"
                                                                        id="{{ $q->question }}"
                                                                        @if ($q->is_required == 'on') required @endif>
                                                                @elseif ($q->question_type == 'dropdown')
                                                                    <select class="form-control"
                                                                        name="question[{{ $q->question }}]"
                                                                        @if ($q->is_required == 'on') required @endif>
                                                                        <option value="">{{ __('Select Type') }}
                                                                        </option>
                                                                        @foreach ($array as $key => $items)
                                                                            <option value="{{ $items }}">
                                                                                {{ $items }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                @else
                                                                @endif
                                                            </dd>
                                                        </dl>
                                                    </div>
                                                @endif
                                            @endforeach
                                        @endforeach
                                    @endif

                                    <div class="form-group col-md-6">
                                        <div class="row mt-1">
                                            <label class="form-check-label form-label"
                                                for="meeting">{{ __('Meeting Type') }}
                                            </label>
                                            <div class="d-flex radio-check">
                                                @foreach ($meetings as $key => $meeting)
                                                @php
                                                    $alias_name = Module_Alias_Name($meeting);
                                                    @endphp
                                                    @php
                                                        $is_checked = in_array($meeting, $meeting_type);
                                                @endphp
                                                    <div class="custom-control custom-radio custom-control-inline"
                                                        @if (!$is_checked) style="display: none;" @endif>
                                                        <input type="radio" id="{{ $key }}"
                                                            value="{{ $alias_name }}" name="meeting_type"
                                                            class="custom-control-input">
                                                        <label class="custom-control-label"
                                                            for="{{ $key }}">{{ $alias_name }}</label>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>

                                    <div class="text-center">
                                        <div class="d-block ">
                                            <button class="btn btn-primary">
                                                {{ __('Create Appointment') }}
                                            </button>
                                        </div>
                                    </div>

                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="auth-footer">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-6">
                            <p class="text-muted">{{ env('FOOTER_TEXT') }}</p>
                        </div>
                        <div class="col-6 text-end">

                        </div>
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
            var allowedWeekdays = '{{ $appointment->week_day }}';
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
