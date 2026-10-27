@extends('layouts.main')
@section('page-title')
    {{ __('Manage Driver') }}
@endsection
@section('page-breadcrumb')
    {{ __('Driver') }}
@endsection
@push('css')
    @include('layouts.includes.datatable-css')
@endpush
@section('page-action')
    <div class="d-flex">
        @stack('addButtonHook')
            <a href="{{ route('driver.grid') }}" class="btn btn-sm btn-primary btn-icon me-2"
                data-bs-toggle="tooltip"title="{{ __('Grid View') }}">
                <i class="ti ti-layout-grid text-white"></i>
            </a>
        @permission('driver create')
            <a class="btn btn-sm btn-primary" data-ajax-popup="true" data-size="lg" data-title="{{ __('Create Driver') }}"
                data-url="{{ route('driver.create') }}" data-bs-toggle="tooltip" data-bs-original-title="{{ __('Create') }}">
                <i class="ti ti-plus"></i>
            </a>
        @endpermission
    </div>
@endsection

@section('content')
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
