@extends('layouts.main')
@section('page-title')
    {{ __('Manage Staff') }}
@endsection
@section('page-breadcrumb')
    {{ __('Staff') }}
@endsection
@push('css')
    @include('layouts.includes.datatable-css')
@endpush
@section('page-action')
    <div>
        @permission('booking staffs create')
            <a href="#" class="btn btn-sm btn-primary" data-size="lg" data-url="{{ route('booking-staff.create') }}"
                data-ajax-popup="true" data-title="{{ __('Create Staff') }}" data-bs-toggle="tooltip"
                data-bs-original-title="{{ __('Create') }}">
                <i class="ti ti-plus"></i>
            </a>
        @endpermission
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        {{-- <table class="table mb-0 pc-dt-simple" id="assets">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('Name') }}</th>
                                    <th>{{ __('Service') }}</th>
                                    @if (Laratrust::hasPermission('booking staffs edit') || Laratrust::hasPermission('booking staffs delete'))
                                        <th width="200px">{{ __('Action') }}</th>
                                    @endif
                                </tr>
                            </thead>

                            <tbody>
                                 @foreach ($bookingstaffs as $index => $bookingstaff)
                                    <tr>
                                        <th scope="row">{{++$index}}</th>
                                        <td> {{ isset($bookingstaff->staffs->name) ? $bookingstaff->staffs->name : '-' }}</td>
                                        <td> {{ $bookingstaff->services()->implode(' , ') }} </td>

                                         @if (Laratrust::hasPermission('booking staffs edit') || Laratrust::hasPermission('booking staffs delete'))
                                            <td class="Action">
                                                <span>
                                                     @permission('booking staffs edit')
                                                        <div class="action-btn bg-info ms-2">
                                                            <a class="mx-3 btn btn-sm  align-items-center"
                                                                data-url="{{ route('booking-staff.edit', $bookingstaff->id) }}"
                                                                data-ajax-popup="true" data-size="lg" data-bs-toggle="tooltip"
                                                                title="" data-title="{{ __('Edit Customer') }}"
                                                                data-bs-original-title="{{ __('Edit') }}">
                                                                <i class="ti ti-pencil text-white"></i>
                                                            </a>
                                                        </div>
                                                     @endpermission
                                                     @permission('booking staffs delete')
                                                        <div class="action-btn bg-danger ms-2">
                                                            {{ Form::open(['route' => ['booking-staff.destroy', $bookingstaff->id], 'class' => 'm-0']) }}
                                                            @method('DELETE')
                                                            <a class="mx-3 btn btn-sm  align-items-center bs-pass-para show_confirm"
                                                                data-bs-toggle="tooltip" title=""
                                                                data-bs-original-title="Delete" aria-label="Delete"
                                                                data-confirm="{{ __('Are You Sure?') }}"
                                                                data-text="{{ __('This action can not be undone. Do you want to continue?') }}"
                                                                data-confirm-yes="delete-form-{{ $bookingstaff->id }}"><i
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
                        </table> --}}
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

