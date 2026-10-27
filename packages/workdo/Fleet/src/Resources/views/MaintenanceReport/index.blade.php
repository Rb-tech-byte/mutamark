
@extends('layouts.main')
@section('page-title')
    {{ __('Manage Maintenance Report') }}
@endsection
@section('page-breadcrumb')
    {{ __('Report') }},
    {{ __('Maintenance Report') }}
@endsection

@section('page-action')
    <div>
        <a  class="btn btn-sm btn-primary" onclick="saveAsPDF()"  data-bs-toggle="tooltip"  data-bs-original-title="{{ __('Download') }}">
            <i class="ti ti-download"></i>
        </a>
    </div>
@endsection
@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class=" multi-collapse mt-2 " id="multiCollapseExample1">
                <div class="card">
                    <div class="card-body">
                        {{ Form::open(['route' => ['maintenance_report.index'], 'method' => 'GET', 'id' => 'report_maintenance']) }}
                        <div class="row align-items-center justify-content-end">
                            <div class="col-xl-10">
                                <div class="row">
                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                        <div class="btn-box">
                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                        <div class="btn-box">
                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                        <div class="btn-box">
                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                        <div class="btn-box">
                                        {{ Form::label('vehicle', __('Vehicle'), ['class' => 'form-label']) }}
                                        {{ Form::select('vehicle', $vehicles, isset($_GET['vehicle']) ? $_GET['vehicle'] : null, array('class' => 'form-control ','placeholder' => 'Select Vehicle')) }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-auto">
                                <div class="row">
                                    <div class="col-auto float-end mt-4 d-flex">
                                        <a  class="btn btn-sm btn-primary me-2"
                                            onclick="document.getElementById('report_maintenance').submit(); return false;"
                                            data-bs-toggle="tooltip" title="{{ __('Apply') }}"
                                            data-original-title="{{ __('apply') }}">
                                            <span class="btn-inner--icon"><i class="ti ti-search"></i></span>
                                        </a>
                                        <a href="{{ route('maintenance_report.index') }}" class="btn btn-sm btn-danger"
                                            data-bs-toggle="tooltip" title="{{ __('Reset') }}"
                                            data-original-title="{{ __('Reset') }}">
                                            <span class="btn-inner--icon"><i class="ti ti-trash-off text-white-off "></i></span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>

        <div class="row">
            <div class="col-12" id="chart-container">
                <div class="card">
                    <div class="card-body">
                        <div class="scrollbar-inner">
                            <span class="float-end text-muted">{{ __('Current Year') . ' - ' . $currentYear }}</span>
                            <div id="incExpBarChart" data-color="primary" data-height="300" ></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div id="printableArea">
            <div class="col-12">
                <div class="card">
                    <div class="card-body table-border-style">
                        <div class="table-responsive">
                            <table class="table ">
                                <thead>
                                <tr>
                                    <th>{{__('Service type')}}</th>
                                    <th>{{__('Maintenance Type')}}</th>
                                    <th>{{__('Maintenance Service Name')}}</th>
                                    <th>{{__('Maintenance Date')}}</th>
                                    <th>{{__('Priority')}}</th>
                                    <th>{{__('Cost')}}</th>
                                    <th>{{__('Charge bear by')}}</th>
                                    <th>{{__('Total Cost')}}</th>
                                </tr>
                                </thead>
                                <tbody>
                                    @foreach ($maintenances as $maintenance)
                                        <tr>
                                            <td class="text-dark"><span>{{ $maintenance->service_type}}</span></td>
                                            <td class="text-dark"><span>{{ $maintenance->MaintenanceType->name}}</span></td>
                                            <td class="text-dark"><span>{{ $maintenance->service_name}}</span></td>
                                            <td class="text-dark"><span>{{ $maintenance->maintenance_date}}</span></td>
                                            <td class="text-dark"><span>{{ $maintenance->priority}}</span></td>
                                            <td class="text-dark"><span>{{ $maintenance->charge}}</span></td>
                                            <td class="text-dark"><span>{{ $maintenance->charge_bear_by}}</span></td>
                                            <td class="text-dark"><span>{{ $maintenance->total_cost}}</span></td>
                                        </tr>
                                        @endforeach

                                        <tr>
                                            <td class="text-dark"><h6>{{__('Total')}}</h6></td>
                                            <td>{{ '-' }}</td>
                                            <td>{{ '-' }}</td>
                                            <td>{{ '-' }}</td>
                                            <td>{{ '-' }}</td>
                                            <td>{{ $totalcharge }}</td>
                                            <td>{{ $totalchargebear }}</td>
                                            <td>{{ $totalcost }}</td>
                                        </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
@endsection
@push('scripts')
    <script src="{{ asset('assets/js/plugins/apexcharts.min.js') }}"></script>
    <script>

        (function() {
            var options = {
                chart: {
                    height: 250,
                    type: 'bar',
                    toolbar: {
                        show: false,
                    },
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    width: 2,
                    curve: 'smooth'
                },
                series: [{
                    name: "{{ __('Total Cost') }}",
                    data: {!! json_encode($incExpBarChartData['charge']) !!}
                }, {
                    name: "{{ __('Total Charge Bear By') }}",
                    data: {!! json_encode($incExpBarChartData['chargebear']) !!}
                },{
                    name: "{{ __('Total Cost') }}",
                    data: {!! json_encode($incExpBarChartData['cost']) !!}
                }],
                xaxis: {
                    categories: {!! json_encode($incExpBarChartData['month']) !!},
                },
                colors: ['#6FD943', '#FFA21D', '#FF3A6E'], // Success, Warning, Danger,

                fill: {
                    type: 'solid',
                },
                grid: {
                    strokeDashArray: 4,
                },
                legend: {
                    show: true,
                    position: 'top',
                    horizontalAlign: 'right',
                },
                markers: {
                    size: 4,
                    colors: ['#6FD943', '#FFA21D', '#FF3A6E'],
                    opacity: 0.9,
                    strokeWidth: 2,
                    hover: {
                        size: 7,
                    }
                }
            };
            var chart = new ApexCharts(document.querySelector("#incExpBarChart"), options);
            chart.render();
        })();

    </script>
    <script src="{{ asset('packages/workdo/Fleet/src/Resources/assets/js/html2pdf.bundle.min.js') }}"></script>
    <script>


        function saveAsPDF() {
            var element = document.getElementById('printableArea');
            var opt = {
                margin: 0.3,
                filename: 'Maintenance Report',
                image: {
                    type: 'jpeg',
                    quality: 1
                },
                html2canvas: {
                    scale: 4,
                    dpi: 72,
                    letterRendering: true
                },
                jsPDF: {
                    unit: 'in',
                    format: 'A2'
                }
            };
            html2pdf().set(opt).from(element).save();
        }
    </script>
@endpush


