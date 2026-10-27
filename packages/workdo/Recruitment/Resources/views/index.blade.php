@extends('layouts.main')

@section('page-title')
    {{ __('Dashboard') }}
@endsection

@section('page-breadcrumb')
    {{ __('Recruitment') }}
@endsection

@push('css')
    <link rel="stylesheet" href="{{ asset('Modules/Recruitment/Resources/assets/css/main.css') }}" />
@endpush
@push('scripts')
    <script src="{{ asset('Modules/Recruitment/Resources/assets/js/main.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/apexcharts.min.js') }}"></script>
    <script>
        (function() {
            var etitle;
            var etype;
            var etypeclass;
            var calendar = new FullCalendar.Calendar(document.getElementById('event_calendar'), {
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'timeGridDay,timeGridWeek,dayGridMonth'
                },
                buttonText: {
                    timeGridDay: "{{ __('Day') }}",
                    timeGridWeek: "{{ __('Week') }}",
                    dayGridMonth: "{{ __('Month') }}"
                },
                themeSystem: 'bootstrap',
                initialDate: '{{ $transdate }}',
                slotDuration: '00:10:00',
                navLinks: true,
                droppable: true,
                selectable: true,
                selectMirror: true,
                editable: true,
                dayMaxEvents: true,
                handleWindowResize: true,
                events: {!! json_encode($calenderTasks) !!},
            });
            calendar.render();
        })();
    </script>

    <script>
        var WorkedHoursChart = (function() {
            var $chart = $('#job_stage');

            function init($this) {
                var options = {
                    chart: {
                        height: 270,
                        type: 'bar',
                        zoom: {
                            enabled: false
                        },
                        toolbar: {
                            show: false
                        },
                        shadow: {
                            enabled: false,
                        },

                    },
                    plotOptions: {
                        bar: {
                            columnWidth: '30%',
                            borderRadius: 10,
                            dataLabels: {
                                position: 'top',
                            },
                        }
                    },
                    stroke: {
                        show: true,
                        width: 1,
                        colors: ['#fff']
                    },
                    series: [{
                        name: 'Platform',
                        data: {!! json_encode($dealStageData) !!},
                    }],
                    xaxis: {
                        labels: {
                            // format: 'MMM',
                            style: {
                                colors: '#293240',
                                fontSize: '12px',
                                fontFamily: "sans-serif",
                                cssClass: 'apexcharts-xaxis-label',
                            },
                        },
                        axisBorder: {
                            show: false
                        },
                        axisTicks: {
                            show: true,
                            borderType: 'solid',
                            color: '#f2f2f2',
                            height: 6,
                            offsetX: 0,
                            offsetY: 0
                        },
                        title: {
                            text: 'Platform'
                        },
                        categories: {!! json_encode($dealStageName) !!},
                    },
                    yaxis: {
                        labels: {
                            style: {
                                color: '#f2f2f2',
                                fontSize: '12px',
                                fontFamily: "Open Sans",
                            },
                        },
                        axisBorder: {
                            show: false
                        },
                        axisTicks: {
                            show: true,
                            borderType: 'solid',
                            color: '#f2f2f2',
                            height: 6,
                            offsetX: 0,
                            offsetY: 0
                        }
                    },
                    fill: {
                        type: 'solid',
                        opacity: 1

                    },
                    markers: {
                        size: 4,
                        opacity: 0.7,
                        strokeColor: "#000",
                        strokeWidth: 3,
                        hover: {
                            size: 7,
                        }
                    },
                    grid: {
                        borderColor: '#f2f2f2',
                        strokeDashArray: 5,
                    },
                    dataLabels: {
                        enabled: false
                    }
                }
                // Get data from data attributes
                var dataset = $this.data().dataset,
                    labels = $this.data().labels,
                    color = $this.data().color,
                    height = $this.data().height,
                    type = $this.data().type;

                // Init chart
                var chart = new ApexCharts($this[0], options);
                // Draw chart
                setTimeout(function() {
                    chart.render();
                }, 300);
            }

            // Events
            if ($chart.length) {
                $chart.each(function() {
                    init($(this));
                });
            }
        })();
    </script>

    <script>
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
@endpush

@push('css')
    <link rel="stylesheet" href="{{ asset('Modules/Recruitment/Resources/assets/css/custom.css') }}">
@endpush
@section('content')
    <div class="row">
        @php
            $class = '';
            if (count($arrCount) < 3) {
                $class = 'col-lg-4 col-md-4';
            } else {
                $class = 'col-lg-3 col-md-3';
            }
        @endphp
        <div class="col-xxl-7">
            <div class="row">
                <div class="col-lg-3 col-sm-6 col-12">
                    <div class="card">
                        <div class="card-body" style="height: 185px;">
                            <div class="theme-avtar bg-danger">
                                <i class="fas fa-link"></i>
                            </div>
                            <h6 class="mb-3 mt-4">
                                {{ __(!empty($workspace) ? $workspace->name : 'Rajodiya infotech') }} </h6>
                            <div class="stats my-4"><a href="#" class="btn btn-primary btn-sm text-sm cp_link"
                                    data-link="{{ route('career', $workspace->slug) }}"
                                    data-bs-whatever="{{ __('Career Link') }}" data-bs-toggle="tooltip"
                                    data-bs-original-title="{{ __('Career Page') }}"
                                    title="{{ __('Click to copy link') }}">
                                    <i class="ti ti-link"></i>
                                    {{ __('Career Page') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                @if (isset($arrCount['job_published']))
                    <div class="{{ $class }} col-6">
                        <div class="card dash1-card">
                            <div class="card-body mb-2">
                                <div class="theme-avtar bg-success">
                                    <i class="ti ti-user"></i>
                                </div>
                                <p class="text-muted text-m mt-4 mb-4">{{ __('Total Job Published') }}</p>
                                <h3 class="mb-0">{{ $arrCount['job_published'] }}</h3>
                            </div>
                        </div>
                    </div>
                @endif


                @if (isset($arrCount['job_expired']))
                    <div class="{{ $class }} col-6">
                        <div class="card dash1-card">
                            <div class="card-body mb-2">
                                <div class="theme-avtar bg-info">
                                    <i class="ti ti-user"></i>
                                </div>
                                <p class="text-muted text-m mt-4 mb-4">{{ __('Total Job Expired') }}</p>
                                <h3 class="mb-0">{{ $arrCount['job_expired'] }}</h3>
                            </div>
                        </div>
                    </div>
                @endif

                @if (isset($arrCount['job_candidate']))
                    <div class="{{ $class }} col-6">
                        <div class="card dash1-card">
                            <div class="card-body mb-2">
                                <div class="theme-avtar bg-warning">
                                    <i class="ti ti-users"></i>
                                </div>
                                <p class="text-muted text-m mt-4 mb-4">{{ __('Total Job Candidates') }}</p>
                                <h3 class="mb-0">{{ $arrCount['job_candidate'] }}</h3>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <div class="card">
                <div class="card-header">
                    <h5>{{ __('Interview Schedule') }}</h5>
                </div>
                <div class="card-body">
                    <div class="w-100" id='event_calendar'></div>
                </div>
            </div>
        </div>

        <div class="col-xxl-5">
            <div class="card">
                <div class="card-header">
                    <h5>{{ __('Recently Created Jobs') }}</h5>
                </div>
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>{{ __('Job Name') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Created At') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($jobs as $job)
                                    <tr>
                                        <td>{{ $job->title }}</td>
                                        <td>
                                            @if ($job->status === 'active')
                                                {{ __('Active') }}
                                            @else
                                                {{ __('Not Active') }}
                                            @endif
                                        </td>
                                        <td>{{ $job->created_at }}</td>
                                    </tr>
                                @empty
                                    @include('layouts.nodatafound')
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            @if (!empty($dealStageData))
                <div class="card">
                    <div class="card-header ">
                        <h5>{{ __('Job Application by stage') }}</h5>
                    </div>
                    <div class="card-body p-2">
                        <div id="job_stage" data-color="primary" data-height="230"></div>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
