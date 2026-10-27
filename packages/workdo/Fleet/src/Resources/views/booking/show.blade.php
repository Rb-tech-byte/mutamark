@extends('layouts.main')
@section('page-title')
    {{ __('Booking Detail') }}
@endsection
@section('page-breadcrumb')
    {{ __('Booking') }},
    {{ __('Detail') }}
@endsection

@section('page-action')
    <div class="float-end">
        @permission('payment booking manage')
            @if ($paid_amount != 0)
                <a href="#" class="btn btn-sm btn-primary" data-ajax-popup="true" data-size="md" data-title="{{ __('Add Payment') }}"
                data-url="{{ route('Addpayment.create',$bookings->id) }}" data-bs-toggle="tooltip" data-bs-original-title="{{ __('Add Payment') }}">
                <i class="mr-2 ti ti-report-money"></i>
            </a>
            @endif
        @endpermission
    </div>
@endsection

@section('content')
    <div class="row">
        <!-- [ sample-page ] start -->
        <div class="col-sm-12">
            <div class="row">
                <div class="col-xxl-7">
                    <div class="row">
                        <div class="col-lg-4 col-6">
                            <div class="card">
                                <div class="card-body">
                                    <div class="theme-avtar badge bg-primary">
                                        <i class="ti ti-list-check"></i>
                                    </div>

                                    <p class="text-muted text-sm mt-4 mb-2"></p>
                                    <h6 class="mb-3 text-primary">{{ __('Total Amount') }}</h6>
                                    <h3 class="mb-0 text-primary">{{ currency_format_with_sym($bookings->total_price) }}
                                    </h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-6">
                            <div class="card">
                                <div class="card-body">
                                    <div class="theme-avtar badge bg-info">
                                        <i class="ti ti-receipt-2"></i>
                                    </div>
                                    <p class="text-muted text-sm mt-4 mb-2"></p>
                                    <h6 class="mb-3 text-info">{{ __('Paid Amount') }}</h6>
                                    <h3 class="mb-0 text-info">{{ currency_format_with_sym($total_amount['pay_amount']) }}
                                    </h3>
                                    {{-- <h3 class="mb-0 text-info">-</h3> --}}
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-6">
                            <div class="card">
                                <div class="card-body">
                                    <div class="theme-avtar badge bg-warning">
                                        <i class="ti ti-report-money"></i>
                                    </div>
                                    <p class="text-muted text-sm mt-4 mb-2">{{ __('') }}</p>
                                    <h6 class="mb-3 text-warning">{{ __('Pending Amount') }}</h6>
                                    <h3 class="mb-0 text-warning">{{ currency_format_with_sym($paid_amount) }}</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xxl-5">
                    <div class="card">
                        <div class="row">
                            <div class="col-6 card-body pt-0" style="margin-top: 15px; margin-bottom: -21px;">
                                <h5>{{ __('Customer Info') }}</h5>
                                <address class="mb-0 text-sm">
                                    <dl class="row mt-4 align-items-center">
                                        <dt class="col-sm-4 h6 text-sm">{{ __('Name') }}</dt>
                                        <dd class="col-sm-8 text-sm">
                                            {{ !empty($bookings->BookingUser) ? $bookings->BookingUser->name : '' }}
                                        </dd>
                                        <dt class="col-sm-4 h6 text-sm">{{ __('Email') }}</dt>
                                        <dd class="col-sm-8 text-sm">
                                            {{ !empty($bookings->BookingUser) ? $bookings->BookingUser->email : '' }}</dd>

                                        <dt class="col-sm-4 h6 text-sm">{{ __('Mobile No.') }}</dt>
                                        @if ($bookings->BookingUser->phone)
                                            <dd class="col-sm-8 text-sm">
                                                {{ !empty($bookings->BookingUser) ? $bookings->BookingUser->phone : '' }}
                                            </dd>
                                        @else
                                            <dd class="col-sm-8 text-sm">-</dd>
                                        @endif
                                        <dt class="col-sm-4 h6 text-sm">{{ __('Address') }}</dt>
                                        @if ($bookings->BookingUser->address)
                                            <dd class="col-sm-8 text-sm">
                                                {{ !empty($bookings->BookingUser) ? $bookings->BookingUser->address : '' }}
                                            </dd>
                                        @else
                                            <dd class="col-sm-8 text-sm">-</dd>
                                        @endif
                                    </dl>
                                </address>
                            </div>
                            <div class="col-6 d-flex">
                                <div class="col-6 card-body pt-0 " style="margin-top: 15px; margin-bottom: -21px;">
                                    <h5>{{ __('Driver Info') }}</h5>
                                    <address class="mb-0 text-sm">
                                        <dl class="row mt-4 align-items-center">
                                            <dt class="col-sm-4 h6 text-sm">{{ __('Name') }}</dt>

                                        <dd class="col-sm-8 text-sm">{{$bookings->vehicle->driver->name}}</dd>

                                        <dt class="col-sm-4 h6 text-sm">{{ __('Email') }}</dt>
                                        <dd class="col-sm-8 text-sm">{{$bookings->vehicle->driver->email}}</dd>

                                        <dt class="col-sm-4 h6 text-sm">{{ __('Mobile No.') }}</dt>
                                        @if ($bookings->vehicle->driver->phone)
                                        <dd class="col-sm-8 text-sm"> {{$bookings->vehicle->driver->phone}}</dd>
                                        @else
                                        <dd class="col-sm-8 text-sm">-</dd>
                                        @endif
                                        <dt class="col-sm-4 h6 text-sm">{{ __('Vehicle') }}</dt>
                                        <dd class="col-sm-8 text-sm"> {{ $bookings->vehicle->name }}</dd>
                                        </dl>
                                    </address>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-12">
            <div class="row">
                <div class="col-md-7">
                    <div class="card">
                        <div class="card-header">
                            <div class="d-flex align-items-center justify-content-between">
                                <h5>{{ __('Payment Details') }} </h5>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive" style="height: 260px; overflow:auto">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>{{ __('#') }}</th>
                                            <th>{{ __('Amount') }}</th>
                                            <th>{{ __('Description') }}</th>
                                            <th>{{ __('Paid Time') }}</th>
                                            <th>{{ __('Action') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($payment as $index => $pay)
                                            <tr>
                                                <td scope="row">{{ ++$index }}</td>
                                                <td>{{ $pay->pay_amount }}</td>
                                                <td>{{ $pay->description }}</td>
                                                <td>{{ $pay->created_at }}</td>
                                                @permission('payment booking delete')
                                                    <td>
                                                        <div class="action-btn">
                                                            {{ Form::open(['route' => ['payment.delete', $pay->id], 'class' => 'm-0']) }}
                                                            @method('DELETE')
                                                            <a class="mx-3 btn btn-sm  align-items-center bs-pass-para show_confirm bg-danger"
                                                                data-bs-toggle="tooltip" title="" data-bs-original-title="Delete"
                                                                aria-label="Delete" data-confirm="{{ __('Are You Sure?') }}"
                                                                data-text="{{ __('This action can not be undone. Do you want to continue?') }}"
                                                                data-confirm-yes="delete-form-{{ $pay->id }}"><i
                                                                    class="ti ti-trash text-white text-white"></i></a>
                                                            {{ Form::close() }}
                                                        </div>
                                                    </td>
                                                @endpermission
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="card">
                        <div class="card-header">
                            <h5>{{ __('Trip Details') }}</h5>
                        </div>
                        <div class="card-body">
                            <div class="row timeline-wrapper">
                                <div class="row">
                                    <h6 class="text-muted col-5">
                                        {{ $bookings->start_address }}<br>{{ $bookings->start_date }}</h6>
                                    <h5 class="col-2">{{ __('To') }}</h5>
                                    <h6 class="text-muted col-5">{{ $bookings->end_address }}<br>{{ $bookings->end_date }}
                                    </h6>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
