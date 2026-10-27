@extends('layouts.main')
@section('page-title')
    {{ __('Manage Insurance') }}
@endsection
@section('page-breadcrumb')
    {{ __('Insurance') }}
@endsection
@push('css')
    @include('layouts.includes.datatable-css')
@endpush
@section('page-action')
    <div class="d-flex">
        @stack('addButtonHook')
        @permission('insurance create')
            <a class="btn btn-sm btn-primary" data-ajax-popup="true" data-size="lg" data-title="{{ __('Create Insurance') }}"
                data-url="{{ route('insurance.create') }}" data-bs-toggle="tooltip" data-bs-original-title="{{ __('Create') }}">
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
                                {{ Form::label('insurance_provider', __('Insurance Provider Name'), ['class' => 'form-label']) }}
                                {{ Form::text('insurance_provider', isset($_GET['insurance_provider']) ? $_GET['insurance_provider'] : '', ['class' => 'form-control select', 'placeholder' => __('Search Insurance Provider Name')]) }}
                            </div>
                        </div>
                        <div class="col-xl-2 col-lg-3 col-md-6 col-sm-12 col-12">
                            <div class="btn-box">
                                {{ Form::label('policy_number', __('Policy Number'), ['class' => 'form-label']) }}
                                {{ Form::text('policy_number', isset($_GET['policy_number']) ? $_GET['policy_number'] : '', ['class' => 'form-control select', 'placeholder' => __('Search Policy Number')]) }}
                            </div>
                        </div>
                        <div class="col-xl-2 col-lg-3 col-md-6 col-sm-12 col-12">
                            <div class="btn-box">
                                {{ Form::label('vehicle_name', __('Vehicle Name'), ['class' => 'form-label']) }}
                                {{ Form::select('vehicle_name', $vehicle, isset($_GET['vehicle_name']) ? $_GET['vehicle_name'] : '', ['class' => 'form-control select', 'placeholder' => __('Select Vehicle Name')]) }}
                            </div>
                        </div>
                        <div class="col-auto float-end mt-4 d-flex">
                            <div class="row">
                                <div class="col-auto">
                                    <a  class="btn btn-sm btn-primary me-1"
                                            data-bs-toggle="tooltip" title="{{ __('Apply') }}" id="applyfilter"
                                            data-original-title="{{ __('apply') }}">
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
