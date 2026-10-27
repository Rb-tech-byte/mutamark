@extends('layouts.main')
@section('page-title')
    {{ __('Manage Business Hours') }}
@endsection
@section('page-breadcrumb')
{{ __('Business Hours') }}
@endsection
@push('css')
    <link rel="stylesheet" href="{{ asset('packages/workdo/Bookings/src/Resources/assets/css/custome.css') }}">
@endpush
@section('content')
    {{ Form::open(['route' => ['bookings-business-hours.store'], 'enctype' => 'multipart/form-data']) }}
    <div class="row business-hrs">
        <div class="col-sm-12 mt-3">
            @php
                $days = Workdo\Bookings\Entities\BookingsBusinessHours::$weekdays;
                $company_settings = getCompanyAllSetting();
            @endphp
            <div class="card">
                @foreach ($days as $index => $day)
                    <div>
                        @php
                            $data = Workdo\Bookings\Entities\BookingsBusinessHours::dayWiseData($day);
                        @endphp
                        <table class="table mb-0 mt-3">
                            <tbody class="">
                                <tr>
                                    <td>
                                        <h5 class="text-primary mb-0 capitalize">
                                            {{ ++$index . '.' . $day }}</h5>
                                    </td>
                                    <td>
                                        <input type="time" name="{{ $day }}[start]"
                                            value="{{ !empty($data['start_time']) ? $data['start_time'] : '' }}"
                                            class="form-control day_disable_{{ $index }}"
                                            {{ (!empty($data['day_off']) ? $data['day_off'] : '') == 'on' ? 'disabled' : '' }}>
                                    </td>
                                    <td>
                                        <input type="time" name="{{ $day }}[end]"
                                            value="{{ !empty($data['end_time']) ? $data['end_time'] : '' }}"
                                            class="form-control day_disable_{{ $index }}"
                                            {{ (!empty($data['day_off']) ? $data['day_off'] : '') == 'on' ? 'disabled' : '' }}>
                                    </td>
                                    <td>
                                        <div class="form-control">
                                            <input class="form-check-input day_off" type="checkbox"
                                                data-id={{ $index }} name="{{ $day }}[day_off]"
                                                {{ (!empty($data['day_off']) ? $data['day_off'] : '') == 'on' ? 'checked' : '' }}>
                                            <label class="form-check-label" for="inlineFormCheck ">
                                                {{ __(' Add Day Off') }}
                                            </label>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    {{-- repeater start --}}
                    <div class="repeater day_off_{{ $index }} {{ (!empty($data['day_off']) ? $data['day_off'] : '') == 'on' ? 'd-none' : '' }}" id="{{ $day }}_break_business"
                        data-value="{{ !empty($data['break_hours']) ? $data['break_hours'] : '' }}">
                        <table class="table  mb-0" data-repeater-list="{{ $day }}[repeater]">
                            <tbody class="" data-repeater-item>
                                <tr>
                                    <td>
                                        <p class="text-danger">
                                            {{ __('Break') }}</p>
                                    <td>
                                        <input type="time" name="start" class="form-control repeaterTimeField">
                                    </td>
                                    <td>
                                        <input type="time" name="end" class="form-control repeaterTimeField">
                                    </td>
                                    <td>
                                            <div class="repeater-action-btn action-btn me-2">
                                                <a href="#" class="btn btn-sm bg-danger align-items-center bs-pass-para repeater-action-btn delete-row-btn" data-repeater-delete>
                                                    <i class="ti ti-trash text-white"></i>
                                                </a>
                                            </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <div class="add-break-btn mt-2">
                            <span data-repeater-create="">
                                <i class="fas fa-plus-circle"></i>
                                {{ __('Add Break') }}
                            </span>
                        </div>
                    </div>
                    {{-- repeater end --}}
                @endforeach
                <div class="holidays-btn">
                    @if (module_is_active('Hrm'))
                    <h6>{{ __('Holidays:') }} <span id="totalCapacity"></span></h6>
                    <div class="form-group">
                        <div class="form-switch">
                            <input type="hidden" name="holiday_setting" value="off">
                            <input type="checkbox" class="form-check-input" name="holiday_setting" id="holiday_setting" @if(isset($company_settings['holiday_setting']) && $company_settings['holiday_setting'] == 'on') checked @endif>
                            <label class="form-check-label" for="holiday_setting"></label>
                        </div>
                    </div>
                @endif
                </div>

                <div class="form-group col-md-12 text-end card-footer mb-0">
                    <input class="btn btn-print-invoice  btn-primary " type="submit" value="{{ __('Save Changes') }}">
                </div>
            </div>
        </div>
    </div>
    {{ Form::close() }}
@endsection

@push('scripts')
    <script src="{{ asset('packages/workdo/Bookings/src/Resources/assets/js/repeater.js') }}"></script>
    <script>
        $(document).ready(function() {
            var selector = "body";
            var slides = document.getElementsByClassName("repeater");

            const days = ["monday_break_business", "tuesday_break_business", "wednesday_break_business",
                "thursday_break_business", "friday_break_business", "saturday_break_business",
                "sunday_break_business"
            ];
            for (var i = 0; i < slides.length; i++) {
                var repeaterDayId = '#' + days[i];
                var $repeater = $(repeaterDayId).repeater({
                    initEmpty: true,

                    hide: function(deleteElement) {
                        $(this).remove();
                    },

                    isFirstItemUndeletable: false
                });
                var value = $(repeaterDayId).attr('data-value');
                if (typeof value != 'undefined' && value.length != 0) {
                    console.log($repeater);
                    value = JSON.parse(value);
                    $repeater.setList(value);
                    console.log(value);
                }
                $(document).on('click', '[data-repeater-create]', function() {
                    $(document).on('click', '.repeaterTimeField', function() {
                        $(this).attr('type', 'time');
                    });
                });

            }
        });

        $(document).on('click', '.day_off', function() {

            var data_id = '.day_off_' + $(this).data('id');
                var id = '.day_disable_' + $(this).data('id');
            let isChecked = $(this).is(':checked')

            if (isChecked) {
                $(data_id).addClass("d-none");
                $(id).prop('disabled', true);

            } else {
                $(data_id).removeClass("d-none");
                $(id).prop('disabled', false);
            }
        });

        $(document).ready(function() {
            $(document).on('click', '.delete-row-btn', function() {
                $(this).closest('tr').remove();
            });
        });

        $(document).on('click', '.repeaterTimeField', function() {
            $(this).attr('type', 'time');
        });
    </script>
@endpush
