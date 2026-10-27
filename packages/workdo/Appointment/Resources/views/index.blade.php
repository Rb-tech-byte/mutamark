@extends('layouts.main')

@section('page-title')
    {{ __('Dashboard') }}
@endsection
@push('css')
    <link rel="stylesheet" href="{{ asset('Modules/Appointment/Resources/assets/css/main.css') }}">
@endpush
@section('page-breadcrumb')
    {{ __('Appointment') }}
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-6 col-md-6 col-sm-6">
            <div class="row">
                <div class="col-xl-3 col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="theme-avtar bg-primary">
                                <i class="fas fa-tasks bg-primary text-white"></i>
                            </div>
                            <p class="text-muted text-sm"></p>
                            <h6 class="mt-4 mb-4">{{ __('Total Appointment') }}</h6>
                            <h3 class="mb-0">{{ $totalAppointments }} <span class="text-success text-sm"></span></h3>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="theme-avtar bg-info">
                                <i class="ti ti-clipboard-check bg-info text-white"></i>
                            </div>
                            <p class="text-muted text-sm "></p>
                            <h6 class="mt-4 mb-4">{{ __('Total Approved') }}</h6>
                            <h3 class="mb-0">{{ $totalArrpvoed }} <span class="text-success text-sm"></span></h3>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="theme-avtar bg-danger">
                                <i class="ti ti-thumb-down bg-danger text-white"></i>
                            </div>
                            <p class="text-muted text-sm"></p>
                            <h6 class="mt-4 mb-4">{{ __('Total Reject') }}</h6>
                            <h3 class="mb-0">{{ $totalReject }} <span class="text-success text-sm"></span></h3>

                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="theme-avtar bg-success">
                                <i class="fas fa-users bg-success text-white"></i>
                            </div>
                            <p class="text-muted text-sm"></p>
                            <h6 class="mt-4 mb-4">{{ __('Total Pending') }}</h6>
                            <h3 class="mb-0">{{ $totalPending }} <span class="text-success text-sm"></span></h3>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card ">
                <div class="card-header">
                    <div>
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-0">
                                    {{ __('Appointment') }}
                                </h5>
                            </div>
                            <div class="float-end">
                                <small><b>{{ $totalArrpvoed }}</b> {{ __('Appointment Approved out of') }}
                                    {{ $totalAppointments }}</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body ">
                    <div class="table-responsive">
                        <table class="table table-centered table-hover mb-0 animated">
                            <tbody>
                                @forelse($appointments as $appointment)
                                    <tr>
                                        <td>
                                            <div class="font-14 my-1">
                                                <a href="{{ route('schedules.show', [\Crypt::encrypt($appointment->appointment_id)]) }}"
                                                    class="text-body">{{ $appointment->appointment_name }}
                                                </a>
                                            </div>

                                            @php($due_date = '<span class="text-' . ($appointment->date < date('Y-m-d') ? 'danger' : 'success') . '">' . date('Y-m-d', strtotime($appointment->date)) . '</span> ')

                                            <span class="text-muted font-13">{{ __('Start Date') }} :
                                                {!! $due_date !!}</span>
                                        </td>
                                        <td>
                                            <span class="text-muted font-13">{{ __('Status') }}</span> <br />
                                            @if ($appointment->status == 'Approved')
                                                <span
                                                    class="badge bg-success p-2 px-3 rounded">{{ __($appointment->status) }}</span>
                                            @else
                                                <span
                                                    class="badge bg-primary p-2 px-3 rounded">{{ __($appointment->status) }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="text-muted font-13">{{ __('Appointment') }}</span>
                                            <div class="font-14 mt-1 font-weight-normal">
                                                {{ $appointment->appointment_name }}</div>
                                        </td>
                                    </tr>
                                @empty
                                    @include('layouts.nodatafound')
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6 col-md-6 col-sm-6">
            {{-- <div class="card">
                <div class="card-header">
                    <h5>{{ __('Appointment Overview') }}</h5>
                </div>
                <div class="card-body p-2">
                    <div id="task-area-chart"></div>
                </div>
            </div> --}}
            <div class="card">
                <div class="card-header">
                    <div class="float-end">
                        <a href="#" data-bs-toggle="tooltip" data-bs-placement="top" title="Refferals"><i
                                class=""></i></a>
                    </div>
                    <h5>{{ __('Appointment Overview') }}</h5>
                </div>
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-xl-6 col-md-8 col-12">
                            <div id="projects-chart"></div>
                        </div>
                        <div class="col-6">

                            <div class="col-6">
                                <span class="d-flex align-items-center mb-2">
                                    <i class="f-10 lh-1 fas fa-circle text-info"></i>
                                    <span class="ms-2 text-sm">{{ __('Total') }}</span>
                                </span>
                            </div>
                            <div class="col-6">
                                <span class="d-flex align-items-center mb-2">
                                    <i class="f-10 lh-1 fas fa-circle text-warning"></i>
                                    <span class="ms-2 text-sm">{{ __('Pending') }}</span>
                                </span>
                            </div>
                            <div class="col-6">
                                <span class="d-flex align-items-center mb-2">
                                    <i class="f-10 lh-1 fas fa-circle text-success"></i>
                                    <span class="ms-2 text-sm">{{ __('Approved') }}</span>
                                </span>
                            </div>
                            <div class="col-6">
                                <span class="d-flex align-items-center mb-2">
                                    <i class="f-10 lh-1 fas fa-circle text-danger"></i>
                                    <span class="ms-2 text-sm">{{ __('Reject') }}</span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-header">
                    <h5>{{ __('Appointments') }}</h5>
                </div>
                <div class="card-body card-635 ">
                    <div id='calendar' class='calendar'></div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('Modules/Appointment/Resources/assets/js/main.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/apexcharts.min.js') }}"></script>

    <script>
        (function() {
            var options = {
                chart: {
                    height: 170,
                    type: 'donut',
                },
                dataLabels: {
                    enabled: false,
                },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '70%',
                        }
                    }
                },
                series: {{ $arrProcessPer }},
                colors: ["#3ec9d6", '#ffa21d', '#6fd943', '#ff3a6e'],
                labels: ["Total", "Pending", "Approved", "Reject"],
                legend: {
                    show: false
                }
            };
            var chart = new ApexCharts(document.querySelector("#projects-chart"), options);
            chart.render();
        })();
    </script>

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
                events: {!! json_encode($events) !!},
            });
            calendar.render();
        })();
    </script>
@endpush
