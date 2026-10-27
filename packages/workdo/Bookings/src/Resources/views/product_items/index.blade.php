@extends('layouts.main')
@section('page-title')
{{ __('Manage Items') }}
@endsection
@section('page-breadcrumb')
{{ __('Items') }}
@endsection
@push('css')
    @include('layouts.includes.datatable-css')
@endpush
@section('page-action')
<div>
    <a href="{{route('bookings-item',['item_type'=>'bookings'])}}" class="btn btn-sm btn-primary btn-icon" data-bs-toggle="tooltip" data-bs-placement="top" data-title="{{ __('Create Product') }}" title="{{__('Create')}}"><i class="ti ti-plus text-white"></i></a>
</div>
@endsection

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
