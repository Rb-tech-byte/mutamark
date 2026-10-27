
@extends('layouts.main')
@section('page-title')
    {{ __('Manage Fuel history Report') }}
@endsection
@section('page-breadcrumb')
    {{ __('Report') }},
    {{ __('Fuel history Report') }}
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
                        {{ Form::open(['route' => ['fuel_history_report.index'], 'method' => 'GET', 'id' => 'report_fuelhistory']) }}
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
                                        {{ Form::select('vehicle', $vehicles , isset($_GET['vehicle']) ? $_GET['vehicle'] : null , array('class' => 'form-control ','placeholder' => 'Select Vehicle')) }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-auto">
                                <div class="row">
                                    <div class="col-auto float-end mt-4 d-flex">
                                        <a  class="btn btn-sm btn-primary me-2"
                                            onclick="document.getElementById('report_fuelhistory').submit(); return false;"
                                            data-bs-toggle="tooltip" title="{{ __('Apply') }}"
                                            data-original-title="{{ __('apply') }}">
                                            <span class="btn-inner--icon"><i class="ti ti-search"></i></span>
                                        </a>
                                        <a href="{{ route('fuel_history_report.index') }}" class="btn btn-sm btn-danger"
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
                                    <th>{{__('Driver name')}}</th>
                                    <th>{{__('Vehicle Name')}}</th>
                                    <th>{{__('Fuelling date and time')}}</th>
                                    <th>{{__('Fuel type')}}</th>
                                    <th>{{__('Gallons/Liters of Fuel')}}</th>
                                    <th>{{__('Cost per Fallon/Liter')}}</th>
                                    <th>{{__('Total Cost')}}</th>
                                    <th>{{__('Reading')}}</th>
                                </tr>
                                </thead>
                                <tbody>
                                    @foreach ($fuelTypes as $fuelType)
                                        <tr>
                                            <td class="text-dark"><span>{{ $fuelType->driver->name}}</span></td>
                                            <td class="text-dark"><span>{{ $fuelType->vehicle->name}}</span></td>
                                            <td class="text-dark"><span>{{ $fuelType->fill_date}}</span></td>
                                            <td class="text-dark"><span>{{ $fuelType->FuelType->name}}</span></td>
                                            <td class="text-dark"><span>{{ $fuelType->quantity}}</span></td>
                                            <td class="text-dark"><span>{{ $fuelType->cost}}</span></td>
                                            <td class="text-dark"><span>{{ $fuelType->total_cost}}</span></td>
                                            <td class="text-dark"><span>{{ $fuelType->odometer_reading}}</span></td>
                                        </tr>
                                        @endforeach
                                        <tr>
                                            <td class="text-dark"><h6>{{__('Total')}}</h6></td>
                                            <td>{{ '-' }}</td>
                                            <td>{{ '-' }}</td>
                                            <td>{{ '-' }}</td>
                                            <td>{{ $total_quantity }}</td>
                                            <td>{{ $total_cost }}</td>
                                            <td>{{ $total_total_cost }}</td>
                                            <td>{{ '-' }}</td>
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
                    name: "{{ __('Gallons/Liters of Fuel') }}",
                    data: {!! json_encode($incExpBarChartData['gallons']) !!}
                }, {
                    name: "{{ __('Cost per Fallon/Liter') }}",
                    data: {!! json_encode($incExpBarChartData['fallon']) !!}
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
                filename: 'Fuel History Report',
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

