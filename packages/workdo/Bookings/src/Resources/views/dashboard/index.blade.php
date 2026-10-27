@extends('layouts.main')
@section('page-title')
    {{ __('Dashboard') }}
@endsection
@section('page-breadcrumb')
    {{ __('Bookings') }}
@endsection
@push('css')
    <link rel="stylesheet" href="{{ asset('packages/workdo/Bookings/src/Resources/assets/css/main.css') }}">
@endpush
@section('content')
    @if (session('status'))
        <div class="alert alert-success" role="alert">
            {{ session('status') }}
        </div>
    @endif
    @if (\Auth::user()->type == 'company')
        <div class="row row-gap mb-4">
            <div class="col-xxl-6 col-12">
                <div class="row row-gap">
                    <div class="col-md-12 col-12">
                        <div class="dashboard-card">
                            <img src="{{ asset('assets/images/layer.png')}}" class="dashboard-card-layer" alt="layer">
                            <div class="card-inner">
                                <div class="card-content">
                                    <h2>{{$workspace->name}}</h2>
                                    <p>{{_('Simplify scheduling and enhance efficiency with seamless booking integration in Dash SaaS.')}}</p>
                                    <div class="btn-wrp d-flex gap-3">
                                        <a href="#" class="btn btn-primary d-flex align-items-center gap-1 cp_link" tabindex="0" data-link="{{ route('bookings.frontend.appointment',$workspace->slug) }}" data-bs-whatever="{{ __('Bookings Link') }}" data-bs-toggle="tooltip" data-bs-original-title="{{ __('Create Appointment') }}" title="{{ __('Click to copy link') }}">
                                            <i class="ti ti-link text-white"></i>
                                        <span>{{ __('Copy Link') }}</span></a>
                                        {{-- <a href="javascript:" class="btn btn-primary" tabindex="0">
                                            <i class="ti ti-share text-white"></i>
                                        </a> --}}
                                    </div>
                                </div>
                                <div class="card-icon  d-flex align-items-center justify-content-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="71" height="71" viewBox="0 0 71 71" fill="none">
                                        <path d="M64.3438 12.1033V19.1367H2.21875V12.1033C2.21875 9.55172 4.28219 7.48828 6.83375 7.48828H59.7287C62.2803 7.48828 64.3438 9.55172 64.3438 12.1033Z" fill="#18BF6B"/>
                                        <path d="M30.5078 31.3398H36.0547V36.8867H30.5078V31.3398Z" fill="#18BF6B"/>
                                        <path d="M12.7578 31.3398V36.8867H18.3047V31.3398H12.7578ZM12.7578 31.3398V36.8867H18.3047V31.3398H12.7578ZM2.21875 22.4648V60.5608C2.21875 63.1123 4.28219 65.1758 6.83375 65.1758H41.4241C39.7822 62.6242 38.8281 59.5623 38.8281 56.3008C38.8281 47.1152 46.2831 39.6602 55.4688 39.6602C58.7303 39.6602 61.7922 40.6142 64.3438 42.2561V22.4648H2.21875ZM21.6328 56.3008C21.6328 57.2105 20.8784 57.9648 19.9688 57.9648H11.0938C10.1841 57.9648 9.42969 57.2105 9.42969 56.3008V47.4258C9.42969 46.5161 10.1841 45.7617 11.0938 45.7617H19.9688C20.8784 45.7617 21.6328 46.5161 21.6328 47.4258V56.3008ZM21.6328 38.5508C21.6328 39.4605 20.8784 40.2148 19.9688 40.2148H11.0938C10.1841 40.2148 9.42969 39.4605 9.42969 38.5508V29.6758C9.42969 28.7661 10.1841 28.0117 11.0938 28.0117H19.9688C20.8784 28.0117 21.6328 28.7661 21.6328 29.6758V38.5508ZM39.3828 38.5508C39.3828 39.4605 38.6284 40.2148 37.7188 40.2148H28.8438C27.9341 40.2148 27.1797 39.4605 27.1797 38.5508V29.6758C27.1797 28.7661 27.9341 28.0117 28.8438 28.0117H37.7188C38.6284 28.0117 39.3828 28.7661 39.3828 29.6758V38.5508ZM12.7578 54.6367H18.3047V49.0898H12.7578V54.6367ZM12.7578 36.8867H18.3047V31.3398H12.7578V36.8867Z" fill="#18BF6B"/>
                                        <path d="M33.2812 13.5898C32.3625 13.5898 31.6172 12.8446 31.6172 11.9258V3.05078C31.6172 2.132 32.3625 1.38672 33.2812 1.38672C34.2 1.38672 34.9453 2.132 34.9453 3.05078V11.9258C34.9453 12.8446 34.2 13.5898 33.2812 13.5898Z" fill="#18BF6B"/>
                                        <path d="M11.0938 13.5898C10.175 13.5898 9.42969 12.8446 9.42969 11.9258V3.05078C9.42969 2.132 10.175 1.38672 11.0938 1.38672C12.0125 1.38672 12.7578 2.132 12.7578 3.05078V11.9258C12.7578 12.8446 12.0125 13.5898 11.0938 13.5898Z" fill="#18BF6B"/>
                                        <path d="M55.4688 13.5898C54.55 13.5898 53.8047 12.8446 53.8047 11.9258V3.05078C53.8047 2.132 54.55 1.38672 55.4688 1.38672C56.3875 1.38672 57.1328 2.132 57.1328 3.05078V11.9258C57.1328 12.8446 56.3875 13.5898 55.4688 13.5898Z" fill="#18BF6B"/>
                                        <path opacity="0.6" d="M55.4688 42.9883C48.1247 42.9883 42.1562 48.9567 42.1562 56.3008C42.1562 63.6448 48.1247 69.6133 55.4688 69.6133C62.8128 69.6133 68.7812 63.6448 68.7812 56.3008C68.7812 48.9567 62.8128 42.9883 55.4688 42.9883ZM59.9062 57.9648H55.4688C54.5591 57.9648 53.8047 57.2105 53.8047 56.3008V49.6445C53.8047 48.7348 54.5591 47.9805 55.4688 47.9805C56.3784 47.9805 57.1328 48.7348 57.1328 49.6445V54.6367H59.9062C60.8159 54.6367 61.5703 55.3911 61.5703 56.3008C61.5703 57.2105 60.8159 57.9648 59.9062 57.9648Z" fill="#18BF6B"/>
                                        <rect x="12.6211" y="31.1611" width="5.91667" height="5.91667" fill="#18BF6B"/>
                                        <path d="M17.4284 32.4609L14.7399 34.85L13.7388 33.8125C13.6583 33.7319 13.5505 33.6873 13.4387 33.6883C13.3268 33.6893 13.2197 33.7358 13.1406 33.8179C13.0615 33.8999 13.0166 34.0109 13.0156 34.1269C13.0147 34.2429 13.0577 34.3546 13.1354 34.4381L14.4156 35.7654C14.4916 35.8441 14.5935 35.8901 14.7008 35.8944C14.8082 35.8987 14.9131 35.8608 14.9947 35.7884L17.9818 33.1338C18.0268 33.0968 18.0641 33.0508 18.0915 32.9986C18.119 32.9463 18.1359 32.8888 18.1414 32.8296C18.1469 32.7704 18.1409 32.7106 18.1236 32.6538C18.1063 32.5971 18.0781 32.5446 18.0408 32.4994C18.0035 32.4542 17.9577 32.4173 17.9063 32.3908C17.8549 32.3644 17.7989 32.349 17.7416 32.3455C17.6843 32.342 17.6269 32.3506 17.5728 32.3706C17.5188 32.3907 17.4692 32.4218 17.4271 32.4622L17.4284 32.4609Z" fill="white"/>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xxl-6 col-12">
                <div class="row d-flex dashboard-wrp">
                    <div class="col-md-4 col-sm-6 col-12 d-flex flex-wrap">
                        <div class="dashboard-project-card">
                            <div class="card-inner  d-flex justify-content-between">
                                <div class="card-content">
                                    <div class="theme-avtar bg-white">
                                        <i class="ti ti-clipboard-list text-danger"></i>
                                    </div>
                                    <h3 class="mt-3 mb-0 text-danger">{{ __('Total Pending Appointment') }}</h3>
                                </div>
                                <h3 class="mb-0">{{ $data['pendingappointment'] }}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-6 col-12 d-flex flex-wrap">
                        <div class="dashboard-project-card">
                            <div class="card-inner  d-flex justify-content-between">
                                <div class="card-content">
                                    <div class="theme-avtar bg-white">
                                        <i class="ti ti-clipboard-x"></i>
                                    </div>
                                <h3 class="mt-3 mb-0">{{ __('Cancelled Appointment') }}</h3>
                                </div>
                                <h3 class="mb-0">{{ $data['cancelledappointment'] }}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-6 col-12 d-flex flex-wrap">
                        <div class="dashboard-project-card">
                            <div class="card-inner  d-flex justify-content-between">
                                <div class="card-content">
                                    <div class="theme-avtar bg-white">
                                        <i class="ti ti-clipboard-check"></i>
                                    </div>
                                    <h3 class="mt-3 mb-0">{{ __('Completed Appointment') }}</h3>
                                </div>
                                <h3 class="mb-0">{{ $data['completedappointment'] }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
    <div class="row">

        <div class="col-xxl-12">
            <div class="row">
                <div class="col-xxl-8">
                    <div class="card">
                        <div class="card-header">
                            <h5>{{ __("Calendar") }}</h5>
                        </div>
                        <div class="card-body">
                            <div id='calendar' class='calendar'></div>
                        </div>
                    </div>
                </div>
                <div class="col-xxl-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mt-1 mb-0">{{ __('Booking') }}</h5>
                        </div>
                            <div id="callchart" data-color="primary"  data-height="230"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')

    <script src="{{ asset('packages/workdo/Bookings/src/Resources/assets/js/main.min.js') }}"></script>
    <script src="{{ asset('packages/workdo/Bookings/src/Resources/assets/js/apexcharts.min.js') }}"></script>

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

        $('.cp_link').on('click', function() {
            var value = $(this).attr('data-link');
            var $temp = $("<input>");
            $("body").append($temp);
            $temp.val(value).select();
            document.execCommand("copy");
            $temp.remove();
            toastrs('Success', '{{ __('Link Copy on Clipboard') }}', 'success')
        });

    </script>
    <script>
        (function() {
            var chartBarOptions = {
                series: [{
                    name: '{{ __('Appointment') }}',
                    data: {!! json_encode($chartcall['appointment']) !!}
                }, ],

                chart: {
                    height: 150,
                    type: 'area',

                    dropShadow: {
                        enabled: true,
                        color: '#000',
                        top: 18,
                        left: 7,
                        blur: 10,
                        opacity: 0.2
                    },
                    toolbar: {
                        show: false
                    }
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    width: 2,
                    curve: 'smooth'
                },
                title: {
                    text: '',
                    align: 'left'
                },
                xaxis: {
                    categories: {!! json_encode($chartcall['appointment_date']) !!},
                    title: {
                        text: '{{ __('Days') }}'
                    }
                },
                colors: ['#ffa21d', '#FF3A6E'],

                grid: {
                    strokeDashArray: 4,
                },
                legend: {
                    show: false,
                },

                yaxis: {
                    title: {
                        text: '{{ __('Appointment') }}'
                    },
                    tickAmount: 3,
                    min: 1,
                    max: 30,
                }
            };
            var arChart = new ApexCharts(document.querySelector("#callchart"), chartBarOptions);
            arChart.render();
        })();
    </script>

@endpush
