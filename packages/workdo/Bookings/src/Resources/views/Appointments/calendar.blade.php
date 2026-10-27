@extends('layouts.main')
@section('page-title')
    {{ __('Appointments') }}
@endsection
@section('page-breadcrumb')
    {{ __('Appointments') }}
@endsection
@push('css')
    <link rel="stylesheet" href="{{ asset('packages/workdo/Bookings/src/Resources/assets/css/main.css') }}">
@endpush
@section('page-action')
    <div>
        <a href="{{ route('booking-appointments.index') }}" class="btn btn-sm btn-primary btn-icon"
            data-bs-toggle="tooltip"title="{{ __('list View') }}">
            <i class="ti ti-list text-white"></i>
        </a>
        <a href="{{ route('booking-appointments.kanban') }}" class="btn btn-sm btn-primary btn-icon"
            data-bs-toggle="tooltip"title="{{ __('Kanban View') }}">
            <i class="ti ti-layout-kanban"></i>
        </a>
        <a href="#" class="btn btn-sm btn-primary" data-size="lg" data-url="{{ route('booking-appointments.create') }}"
            data-ajax-popup="true" data-title="{{ __('Create Appointments') }}" data-bs-toggle="tooltip"
            data-bs-original-title="{{ __('Create') }}">
            <i class="ti ti-plus"></i>
        </a>
    </div>
@endsection
@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card mt-3">
            <div class="card-header">
                <h5>{{ __("Calendar") }}</h5>
            </div>
            <div class="card-body">
                <div id='calendar' class='calendar'></div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card mt-3">
            <div class="card-header">
                <h5>{{ __('Current Month Booking') }}</h5>
            </div>
            <div class="card-body">
                <ul class="event-cards list-group list-group-flush w-100">
                    <div class="row align-items-center justify-content-between">
                        <div class=" align-items-center">
                            @forelse ($current_month_booking as $item)
                            <li class="list-group-item card mb-3">
                                <div class="row align-items-center justify-content-between">
                                    <div class="col-auto mb-3 mb-sm-0">
                                        <div class="d-flex align-items-center">
                                            <div class="theme-avtar bg-primary"
                                                style="background-color: {{ !empty($item->getrotarole->color) ? $item->getrotarole->color : '#8492a6' }} !important">
                                                <i class="ti ti-building-bank"></i>
                                            </div>
                                            <div class="ms-3">
                                                <h6 class="m-0">
                                                    {{ Workdo\Bookings\Entities\BookingsAppointment::appointmentNumberFormat($item->appointment_id) ?? 'Deleted User'}}
                                                    <small class="text-muted text-xs">
                                                    </small>
                                                </h6>
                                                <small class="text-muted">
                                                    {{ date('Y M d', strtotime($item->date)) }}
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        @empty
                            <li class="list-group-item card mb-3">
                                <div class="row align-items-center justify-content-between">
                                    <div class="col-auto mb-3 mb-sm-0">
                                        <div class="d-flex align-items-center">
                                            {{ __('No Booking Found.') }}
                                        </div>
                                    </div>
                                </div>
                            </li>
                        @endforelse
                        </div>
                    </div>
                </ul>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
    <script src="{{ asset('packages/workdo/Bookings/src/Resources/assets/js/main.min.js') }}"></script>

<script type="text/javascript">
    (function() {
        var etitle;
        var etype;
        var etypeclass;
        var calendar = new FullCalendar.Calendar(document.getElementById('calendar'), {
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            buttonText: {
                timeGridDay: "{{ __('Day') }}",
                timeGridWeek: "{{ __('Week') }}",
                dayGridMonth: "{{ __('Month') }}"
            },
            themeSystem: 'bootstrap',
            slotDuration: '00:10:00',
            navLinks: true,
            droppable: true,
            selectable: true,
            selectMirror: true,
            editable: true,
            dayMaxEvents: true,
            handleWindowResize: true,
            events: {!! json_encode($beauty_book) !!},
            eventRender: function(info) {

                var tooltip = new Tooltip(info.el, {
                    title: 'Appointment ID: ' + info.event.title,

                });
            }
        });
        calendar.render();
    })();

</script>

@endpush


