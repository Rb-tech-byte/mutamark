@extends('layouts.main')
@section('page-title')
    {{ __('Manage Vehicle') }}
@endsection
@section('page-breadcrumb')
    {{ __('Vehicle') }}
@endsection
@push('css')
    @include('layouts.includes.datatable-css')
@endpush
@section('page-action')
    <div>
        @permission('vehicle create')
            <a class="btn btn-sm btn-primary" data-ajax-popup="true" data-size="lg" data-title="{{ __('Create Vehicle') }}"
                data-url="{{ route('vehicle.create') }}" data-bs-toggle="tooltip" data-bs-original-title="{{ __('Create') }}">
                <i class="ti ti-plus"></i>
            </a>
        @endpermission
    </div>
@endsection

@section('content')
    <div class="col-sm-12 col-lg-12 col-xl-12 col-md-12">
        <div class=" mt-2" id="" style="">
            <div class="card">
                <div class="card-body">

                    <div class="row align-items-center justify-content-end">
                        <div class="col-xl-2 col-lg-3 col-md-6 col-sm-12 col-12">
                            <div class="btn-box">
                                {{ Form::label('name', __('Name'), ['class' => 'form-label']) }}
                                {{ Form::text('name', isset($_GET['name']) ? $_GET['name'] : '', ['class' => 'form-control select', 'placeholder' => __('Search Name')]) }}
                            </div>
                        </div>
                        <div class="col-xl-2 col-lg-3 col-md-6 col-sm-12 col-12">
                            <div class="btn-box">
                                {{ Form::label('vehicle_type', __('Vehicle Type'), ['class' => 'form-label']) }}
                                {{ Form::select('vehicle_type', $vehicleTypes, isset($_GET['vehicle_type']) ? $_GET['vehicle_type'] : '', ['class' => 'form-control select', 'placeholder' => __('Select Vehicle Type')]) }}
                            </div>
                        </div>
                        <div class="col-xl-2 col-lg-3 col-md-6 col-sm-12 col-12">
                            <div class="btn-box">
                                {{ Form::label('fuel_type', __('Fule Type'), ['class' => 'form-label']) }}
                                {{ Form::select('fuel_type', $fuelType, isset($_GET['fuel_type']) ? $_GET['fuel_type'] : '', ['class' => 'form-control select', 'placeholder' => __('Select Fuel Type')]) }}
                            </div>
                        </div>

                        <div class="col-auto float-end mt-4 d-flex">
                            <div class="row">
                                <div class="col-auto">
                                    <a class="btn btn-sm btn-primary me-1" data-bs-toggle="tooltip" title="{{ __('Apply') }}"
                                        id="applyfilter" data-original-title="{{ __('apply') }}">
                                        <span class="btn-inner--icon"><i class="ti ti-search"></i></span>
                                    </a>
                                    <a href="#!" class="btn btn-sm btn-danger " data-bs-toggle="tooltip"
                                        title="{{ __('Reset') }}" id="clearfilter"
                                        data-original-title="{{ __('Reset') }}">
                                        <span class="btn-inner--icon"><i class="ti ti-trash-off text-white-off "></i></span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        {{ $dataTable->table(['width' => '100%']) }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    @include('layouts.includes.datatable-js')
    {{ $dataTable->scripts() }}
@endpush

