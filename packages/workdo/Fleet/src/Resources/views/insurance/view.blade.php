@extends('layouts.main')
@section('page-title')
{{ __('Insurance Detail') }}
@endsection
@section('page-breadcrumb')
    {{ __('Insurance') }},
    {{ __('Detail') }}
@endsection

@push('scripts')
<script src="{{ asset('assets/js/plugins/dropzone-amd-module.min.js') }}"></script>
@endpush
@section('content')
    <div class="row justify-content-between align-items-center mb-3">
        <div class="col-md-6">
            <ul class="nav nav-pills nav-fill cust-nav information-tab" id="pills-tab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="details-tab" data-bs-toggle="pill" data-bs-target="#details"
                        type="button">{{ __('Details') }}</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="insurance-booking-tab" data-bs-toggle="pill"
                        data-bs-target="#insurance-booking" type="button">{{ __('Bookings') }}</button>
                </li>
            </ul>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12">
            <div class="tab-content" id="pills-tabContent">
                <div class="tab-pane fade active show" id="details" role="tabpanel" aria-labelledby="pills-user-tab-1">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <dd class="col-lg-6 mt-3 text-lg">
                                        <strong>{{ __('Insurance Provider name:') }}</strong> {{ isset($insurances->insurance_provider) ? $insurances->insurance_provider : '' }}
                                    </dd>
                                    <dd class="col-lg-6 mt-3 text-lg">
                                        <strong>{{ __('Start date:') }}</strong> {{ $insurances->start_date }}
                                    </dd>
                                    <dd class="col-lg-6 mt-3 text-lg">
                                        <strong>{{ __('Recurring date:') }}</strong> {{ !empty($insurances->scheduled_date) ? $insurances->scheduled_date : '' }}
                                    </dd>
                                    <dd class="col-lg-6 mt-3 text-lg">
                                        <strong>{{ __('Insurance Deductible:') }}</strong> {{ $insurances->deductible }}
                                    </dd>
                                    <dd class="col-lg-6 mt-3 text-lg">
                                        <strong>{{ __('Policy number:') }}</strong> {{ $insurances->policy_number }}
                                    </dd>
                                </div>
                                <div class="col-md-6">
                                    <dd class="col-lg-6 mt-3 text-lg">
                                        <strong>{{ __('Vehicle name:') }}</strong> {{ isset($insurances->VehicleName) ? $insurances->VehicleName->name : '' }}
                                    </dd>
                                    <dd class="col-lg-6 mt-3 text-lg">
                                        <strong>{{ __('End date:') }}</strong> {{ $insurances->end_date }}
                                    </dd>
                                    <dd class="col-lg-6 mt-3 text-lg">
                                        <strong>{{ __('Recurring Period:') }}</strong> {{ $insurances->scheduled_period }}
                                    </dd>
                                    <dd class="col-lg-6 mt-3 text-lg">
                                        <strong>{{ __('Initial Charge Payable:') }}</strong> {{ $insurances->charge_payable }}
                                    </dd>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade" id="insurance-booking" role="tabpanel" aria-labelledby="pills-user-tab-3">
                    <div class="col-md-12">
                        <div class="card set-card">
                            <div class="card-header">
                                <div class="row">
                                    <div class="col-6">
                                        <h5>{{ __('Bookings') }}</h5>
                                    </div>
                                    @permission('fleet insurance booking create')
                                        <div class="col-6 text-end">
                                            <a data-url="{{ route('insurance-booking.create', $insurances->id) }}" data-ajax-popup="true" data-size="md"
                                                data-title="{{ __('Create Booking') }}" data-bs-toggle="tooltip" title=""
                                                class="btn btn-sm btn-primary" data-bs-original-title="{{ __('Create') }}">
                                                <i class="ti ti-plus"></i>
                                            </a>
                                        </div>
                                    @endpermission
                                </div>
                            </div>
                            <div class=" card-body table-border-style">
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>{{ __('Start Date') }}</th>
                                                <th>{{ __('End date') }}</th>
                                                <th>{{ __('Amount') }}</th>
                                                @if (Laratrust::hasPermission('fleet insurance booking edit') || Laratrust::hasPermission('fleet insurance booking delete'))
                                                    <th>{{ __('Action') }}</th>
                                                @endif
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($insurance_bookings as $insurance_booking)
                                                <tr>
                                                    <td> {{ isset($insurance_booking->start_date) ? $insurance_booking->start_date : '' }}</td>
                                                    <td> {{ isset($insurance_booking->end_date) ? $insurance_booking->end_date : '' }}</td>
                                                    <td> {{ isset($insurance_booking->amount) ? $insurance_booking->amount : '' }}</td>
                                                    @if (Laratrust::hasPermission('fleet insurance booking edit') || Laratrust::hasPermission('fleet insurance booking delete'))
                                                        <td class="Action">
                                                            <span>
                                                                @permission('fleet insurance booking edit')
                                                                    <div class="action-btn me-2">
                                                                        <a class="btn btn-sm  align-items-center bg-info"
                                                                            data-url="{{ route('insurance-booking.edit', $insurance_booking->id) }}"
                                                                            data-ajax-popup="true" data-size="md" data-bs-toggle="tooltip"
                                                                            title="" data-title="{{ __('Edit Booking') }}"
                                                                            data-bs-original-title="{{ __('Edit') }}">
                                                                            <i class="ti ti-pencil text-white"></i>
                                                                        </a>
                                                                    </div>
                                                                @endpermission
                                                                @permission('fleet insurance booking delete')
                                                                    <div class="action-btn">
                                                                        {{ Form::open(['route' => ['insurance-booking.destroy', $insurance_booking->id], 'class' => 'm-0']) }}
                                                                        @method('DELETE')
                                                                        <a class="btn btn-sm  align-items-center bs-pass-para show_confirm bg-danger"
                                                                            data-bs-toggle="tooltip" title=""
                                                                            data-bs-original-title="Delete" aria-label="Delete"
                                                                            data-confirm="{{ __('Are You Sure?') }}"
                                                                            data-text="{{ __('This action can not be undone. Do you want to continue?') }}"
                                                                            data-confirm-yes="delete-form-{{ $insurance_booking->id }}"><i
                                                                                class="ti ti-trash text-white text-white"></i></a>
                                                                        {{ Form::close() }}
                                                                    </div>
                                                                @endpermission
                                                            </span>
                                                        </td>
                                                    @endif
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
