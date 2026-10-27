@extends('layouts.main')
@section('page-title')
    {{ __('Manage Customer') }}
@endsection
@section('page-breadcrumb')
    {{ __('Customer') }}
@endsection
@push('css')
    @include('layouts.includes.datatable-css')
@endpush
@section('page-action')
    <div>
        @permission('bookingcustomers create')
            <a class="btn btn-sm btn-primary" data-ajax-popup="true" data-size="lg" data-title="{{ __('Create Customer') }}"
                data-url="{{ route('booking-customers.create') }}" data-bs-toggle="tooltip"
                data-bs-original-title="{{ __('Create') }}">
                <i class="ti ti-plus"></i>
            </a>
        @endpermission
    </div>
@endsection
@push('css')
    <link rel="stylesheet" href="{{ asset('packages/workdo/Bookings/src/Resources/assets/css/backend.css') }}">
@endpush
@section('content')
    <div class="row">
        <div class="col-sm-12">
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

