@extends('layouts.main')
@section('page-title')
    {{ __('Manage Maintenance') }}
@endsection
@section('page-breadcrumb')
    {{ __('Maintenance') }}
@endsection
@push('css')
    @include('layouts.includes.datatable-css')
@endpush
@section('page-action')
    <div class="d-flex">
        @stack('addButtonHook')
        @permission('maintenance create')
            <a class="btn btn-sm btn-primary" data-ajax-popup="true" data-size="lg" data-title="{{ __('Create  Maintenance') }}"
                data-url="{{ route('maintenance.create') }}" data-bs-toggle="tooltip" data-bs-original-title="{{ __('Create') }}">
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
                                {{ Form::label('service_type', __('Service Type'), ['class' => 'form-label']) }}
                                {{ Form::text('service_type', isset($_GET['service_type']) ? $_GET['service_type'] : '', ['class' => 'form-control', 'placeholder' => 'Search Service Type']) }}
                            </div>
                        </div>
                        <div class="col-xl-2 col-lg-3 col-md-6 col-sm-12 col-12">
                            <div class="btn-box">
                                {{ Form::label('service_name', __('Maintenance Service Name'), ['class' => 'form-label']) }}
                                {{ Form::text('service_name', isset($_GET['service_name']) ? $_GET['service_name'] : '', ['class' => 'form-control select', 'placeholder' => 'Search Maintenance Service Name']) }}
                            </div>
                        </div>
                        <div class="col-xl-2 col-lg-3 col-md-6 col-sm-12 col-12">
                            <div class="btn-box">
                                {{ Form::label('maintenance_type', __('Maintenance Type'), ['class' => 'form-label']) }}
                                {{ Form::select('maintenance_type', $MaintenanceType, isset($_GET['maintenance_type']) ? $_GET['maintenance_type'] : '', ['class' => 'form-control select']) }}
                            </div>
                        </div>
                        <div class="col-xl-2 col-lg-3 col-md-6 col-sm-12 col-12">
                            <div class="btn-box">
                                {{ Form::label('priority', __('Priority'), ['class' => 'form-label']) }}
                                {{ Form::select('priority', ['High' => 'High', 'Medium' => 'Medium', 'Low' => 'Low'], isset($_GET['priority']) ? $_GET['priority'] : '', ['class' => 'form-control select', 'placeholder' => 'Select Priority']) }}
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

