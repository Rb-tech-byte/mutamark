@extends('layouts.main')
@section('page-title')
    {{ __('Dashboard') }}
@endsection
@section('page-breadcrumb')
    {{ __('Fleet') }}
@endsection

@section('content')

    @php
        $workspace = App\Models\WorkSpace::where('id', getActiveWorkSpace())->first();
    @endphp
    <div class="row row-gap mb-4">
        <div class="col-xxl-6 col-12 d-xxl-flex">
            <div class="row row-gap">
                <div class="col-md-12 col-12">
                    <div class="dashboard-card">
                        <img src="{{ asset('assets/images/layer.png')}}" class="dashboard-card-layer" alt="layer">
                        <div class="card-inner">
                            <div class="card-content">
                                <h2>{{$workspace->name}}</h2>
                                <p>{{__('Simplify vehicle management with an all-in-one solution for booking and order tracking.')}}</p>
                            </div>
                            <div class="card-icon  d-flex align-items-center justify-content-center">
                                <svg xmlns="http://www.w3.org/2000/svg" width="120" height="99" viewBox="0 0 120 99" fill="none">
                                    <path d="M7.26953 70.0869C7.42121 74.4625 11.0243 77.9756 15.4301 77.9756H20.0973C24.2044 77.9756 27.6137 74.923 28.1808 70.9634H13.0335C11.0289 70.9564 9.0944 70.6502 7.26953 70.0869Z" fill="#18BF6B"/>
                                    <path d="M28.2647 46.4221C28.2647 44.4872 29.8329 42.9169 31.7651 42.9169H37.5664L41.0668 28.8957H15.2457L19.8335 10.5187C20.2232 8.95538 21.621 7.86407 23.2289 7.86407H61.3037C62.9115 7.86407 64.3093 8.95538 64.699 10.5187L65.7865 14.8746H73.0019L71.4898 8.81751C70.3183 4.12978 66.1295 0.853516 61.3037 0.853516H23.2289C18.403 0.853516 14.2142 4.12978 13.0428 8.81984L7.73619 30.0759C3.32805 32.1136 0.261719 36.577 0.261719 41.7484V51.0958C0.261719 58.1695 5.99767 63.9275 13.0568 63.9485H28.2647V62.7801C28.2647 57.9966 30.0056 53.4584 32.9996 49.9274H31.7651C29.8329 49.9274 28.2647 48.3571 28.2647 46.4221ZM17.7636 49.9274C15.8314 49.9274 14.2632 48.3571 14.2632 46.4221C14.2632 44.4872 15.8314 42.9169 17.7636 42.9169C19.6958 42.9169 21.264 44.4872 21.264 46.4221C21.264 48.3571 19.6958 49.9274 17.7636 49.9274Z" fill="#18BF6B" fill-opacity="0.6"/>
                                    <path d="M91.8242 91.9869C92.3913 95.9464 95.8007 98.9991 99.9078 98.9991H104.575C108.981 98.9991 112.584 95.486 112.735 91.1104C110.892 91.6807 108.936 91.9869 106.909 91.9869H91.8242Z" fill="#18BF6B" fill-opacity="0.6"/>
                                    <path d="M42.2734 91.1104C42.4251 95.486 46.0282 98.9991 50.434 98.9991H55.1012C59.2083 98.9991 62.6176 95.9464 63.1847 91.9869H48.1004C46.0725 91.9869 44.117 91.6807 42.2734 91.1104Z" fill="#18BF6B" fill-opacity="0.6"/>
                                    <path d="M112.268 51.1103L106.961 29.8538C105.79 25.1642 101.601 21.8887 96.7736 21.8887H58.2335C53.406 21.8887 49.217 25.1642 48.0462 29.8541L42.7396 51.1103C38.3327 53.1478 35.2656 57.6124 35.2656 62.7836V72.131C35.2656 79.218 41.0233 84.9837 48.1003 84.9837H106.907C113.984 84.9837 119.741 79.218 119.741 72.131V62.7836C119.741 57.6124 116.674 53.1478 112.268 51.1103ZM54.8376 31.5544C55.2281 29.991 56.6242 28.8992 58.2335 28.8992H96.7736C98.3828 28.8992 99.779 29.991 100.169 31.5541L104.757 49.9309H50.2498L54.8376 31.5544ZM52.7675 70.9626C50.8344 70.9626 49.2671 69.3931 49.2671 67.4573C49.2671 65.5214 50.8344 63.952 52.7675 63.952C54.7007 63.952 56.2679 65.5214 56.2679 67.4573C56.2679 69.3931 54.7007 70.9626 52.7675 70.9626ZM88.238 70.9626H66.769C64.8359 70.9626 63.2687 69.3931 63.2687 67.4573C63.2687 65.5214 64.8359 63.952 66.769 63.952H88.238C90.1712 63.952 91.7384 65.5214 91.7384 67.4573C91.7384 69.3931 90.1712 70.9626 88.238 70.9626ZM102.24 70.9626C100.306 70.9626 98.7392 69.3931 98.7392 67.4573C98.7392 65.5214 100.306 63.952 102.24 63.952C104.173 63.952 105.74 65.5214 105.74 67.4573C105.74 69.3931 104.173 70.9626 102.24 70.9626Z" fill="#18BF6B"/>
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
                                    <i class="ti ti-truck text-danger"></i>
                                </div>
                                <h3 class="mt-3 mb-0 text-danger">{{ __('Total Vehicle') }}</h3>
                            </div>
                            <h3 class="mb-0">{{ $vehicles['vehicle'] }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6 col-12 d-flex flex-wrap">
                    <div class="dashboard-project-card">
                        <div class="card-inner  d-flex justify-content-between">
                            <div class="card-content">
                                <div class="theme-avtar bg-white">
                                    <i class="ti ti-battery-2"></i>
                                </div>
                            <h3 class="mt-3 mb-0">{{ __('Total Fuel Amount') }}</h3>
                            </div>
                            <h3 class="mb-0">{{ currency_format_with_sym($fuel['amount']) }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6 col-12 d-flex flex-wrap">
                    <div class="dashboard-project-card">
                        <div class="card-inner  d-flex justify-content-between">
                            <div class="card-content">
                                <div class="theme-avtar bg-white">
                                    <i class="ti ti-battery-1"></i>
                                </div>
                                <h3 class="mt-3 mb-0">{{ __('Total Fuel Quantity') }}</h3>
                            </div>
                            <h3 class="mb-0">{{ $fuel['quantity'] }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-xxl-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center justify-content-between">
                        <h5>{{ __('Latest Fuel') }} </h5>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>{{__('Driver Name')}}</th>
                                    <th>{{__('Vehicle Name')}}</th>
                                    <th>{{__('Date')}}</th>
                                    <th>{{__('Quantity')}}</th>
                                    <th>{{__('Amount')}}</th>
                                    <th>{{__('Odometer Reading')}}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($fuels as $fuel)
                                    <tr>
                                        <td>
                                            {{ !empty($fuel->driver) ? $fuel->driver->name : '' }}

                                        </td>
                                        <td>
                                            {{ !empty($fuel->vehicle) ? $fuel->vehicle->name : '' }}
                                        </td>
                                        <td> {{ $fuel->fill_date }}</td>
                                        <td> {{ $fuel->quantity }}</td>
                                        <td> {{ $fuel->total_cost }}</td>
                                        <td> {{ $fuel->odometer_reading }}</td>
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
    </div>

@endsection
