@extends('layouts.main')
@section('page-title')
    {{ __('Manage Extra Services') }}
@endsection
@section('page-breadcrumb')
    {{ __('Extra Services') }}
@endsection
@push('css')
@endpush
@section('page-action')
    <div>
        @permission('bookings extra services create')
            <a href="#" class="btn btn-sm btn-primary" data-size="lg" data-url="{{ route('bookings-extra-services.create') }}"
                data-ajax-popup="true" data-title="{{ __('Create Extra Services') }}" data-bs-toggle="tooltip"
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
                    <div class="table-responsive overflow_hidden">
                        <table class="table mb-0 pc-dt-simple" id="assets">
                            <thead class="thead-light">
                                <tr>
                                    <th>{{ __('Name') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    </th>
                                    @if (Laratrust::hasPermission('bookings extra services edit') || Laratrust::hasPermission('bookings extra services delete'))
                                        <th scope="col">{{ __('Action') }}</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($extraservices as $extraservice)
                                    <tr>
                                        <td>{{ isset($extraservice->name) ? $extraservice->name : '' }}</td>
                                        <td>{{ isset($extraservice->status) ? $extraservice->status : ''}}</td>
                                        @if (Laratrust::hasPermission('bookings extra services edit') || Laratrust::hasPermission('bookings extra services delete'))
                                            <td>
                                                @permission('bookings extra services edit')
                                                    <div class="action-btn me-2">
                                                        <a data-url="{{ route('bookings-extra-services.edit', $extraservice->id) }}"
                                                            class="bg-info btn btn-sm align-items-center text-white "
                                                            data-bs-toggle="tooltip" data-ajax-popup="true"
                                                            data-title="{{ __('Edit Extra Service') }}"title="{{ __('Edit') }}"><i
                                                                class="ti ti-pencil"></i></a>
                                                    </div>
                                                @endpermission
                                                @permission('bookings extra services delete')
                                                    <div class="action-btn me-2">
                                                        {!! Form::open(['method' => 'DELETE', 'route' => ['bookings-extra-services.destroy', $extraservice->id]]) !!}
                                                        <a href="#!"
                                                            class="bg-danger btn btn-sm   align-items-center text-white show_confirm"
                                                            data-bs-toggle="tooltip" title='Delete'>
                                                            <i class="ti ti-trash"></i>
                                                        </a>
                                                        {!! Form::close() !!}
                                                    </div>
                                                @endpermission
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
@endsection
