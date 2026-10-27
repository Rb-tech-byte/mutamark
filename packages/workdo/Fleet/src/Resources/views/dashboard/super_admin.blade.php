@extends('layouts.admin')
@section('page-title')
    {{__('Dashboard')}}
@endsection
@push('script-page')
    <script src="{{ asset('assets/js/plugins/apexcharts.min.js') }}"></script>
    <script>

        (function () {
            var chartBarOptions = {
            series: [
                {
                    name: 'Order',
                    data:{!! json_encode($chartData['data']) !!},
                },
            ],
            chart: {
                height: 250,
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
                categories: {!! json_encode($chartData['label']) !!},
                title: {
                    text: ''
                }
            },
            colors: ['#1260CC'],

            grid: {
                strokeDashArray: 4,
            },
            legend: {
                show: false,
            },
            yaxis: {
                title: {
                    text: ''
                },

            }
        };
        var arChart = new ApexCharts(document.querySelector("#chart-sales"), chartBarOptions);
        arChart.render();
        })();


    </script>
@endpush
@section('content')
    <div class="row">
        <div class="col-sm-12 mt-3">
            <div class="row">
                <div class="col-xxl-7">
                    <div class="row">
                        <div class="col-lg-4 col-4">
                            <div class="card">
                                <div class="card-body" style="min-height: 200px">
                                    <div class="theme-avtar bg-primary mb-3">
                                        <i class="ti ti-users"></i>
                                    </div>
                                    <p class="text-muted text-sm mt-4 mb-2">{{__('Paid Users')}} : <span class="text-dark">{{number_format($user['total_paid_user'])}}</span></p>
                                    <h6 class="mb-3">{{__('Total Users')}}</h6>
                                    <h3 class="mb-0">{{$user['total_user']}}</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-4">
                            <div class="card">
                                <div class="card-body" style="min-height: 200px">
                                    <div class="theme-avtar bg-info mb-3">
                                        <i class="ti ti-shopping-cart-plus"></i>
                                    </div>
                                    <p class="text-muted text-sm mt-4 mb-2">{{__('Total Order Amount')}} : <span class="text-dark">{{number_format($user['total_orders_price'])}}</span></p>
                                    <h6 class="mb-3">{{__('Total Orders')}}</h6>
                                    <h3 class="mb-0">{{$user['total_orders']}}</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-4">
                            <div class="card">
                                <div class="card-body" style="min-height: 200px">
                                    <div class="theme-avtar bg-danger mb-3">
                                        <i class="ti ti-trophy"></i>
                                    </div>
                                    <p class="text-muted text-sm mt-4 mb-2">{{__('Total Purchase Plan')}} : <span class="text-dark">{{number_format($user['most_purchese_plan'])}}</span></p>
                                    <h6 class="mb-3">{{__('Total Plans')}}</h6>
                                    <h3 class="mb-0">{{$user['total_plan']}}</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xxl-5">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between">
                            <h5 class="">{{__('Tasks Overview')}}</h5>
                            <h6 class="last-day-text">{{__('Last 7 Days')}}</h6>
                        </div>
                        <div class="card-body p-1 ">
                            <div id="chart-sales" height="150" class="p-3"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
