@extends('layouts.main')
@section('page-title')
    {{ __('Manage Maintenance Type') }}
@endsection
@section('page-breadcrumb')
    {{ __('Maintenance Type') }}
@endsection
@section('page-action')
    <div>
        @permission('maintenanceType create')
            <a class="btn btn-sm btn-primary" data-ajax-popup="true" data-size="md" data-title="{{ __('Create Maintenance Type') }}"
                data-url="{{ route('maintenanceType.create') }}" data-bs-toggle="tooltip"
                data-bs-original-title="{{ __('Create') }}">
                <i class="ti ti-plus"></i>
            </a>
        @endpermission
    </div>
@endsection
@section('content')
    <div class="row">
        <div class="col-sm-3">
            @include('fleet::layouts.fleet_setup')
        </div>
        <div class="col-sm-9">
            <div class="card">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table mb-0 ">
                            <thead>
                                <tr>
                                    <th>{{ __('Maintenance Type Name') }}</th>
                                    @if (Laratrust::hasPermission('maintenanceType edit') || Laratrust::hasPermission('maintenanceType delete'))
                                        <th width="200px">{{ __('Action') }}</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($maintenanceTypes as $maintenanceType)
                                    <tr>
                                        <td>{{ $maintenanceType->name }}</td>
                                        @if (Laratrust::hasPermission('maintenanceType edit') || Laratrust::hasPermission('maintenanceType delete'))
                                            <td class="Action">
                                                <span>
                                                    @permission('maintenanceType edit')
                                                        <div class="action-btn me-2">
                                                            <a class="btn btn-sm  align-items-center bg-info"
                                                                data-url="{{ route('maintenanceType.edit', $maintenanceType->id) }}"
                                                                data-ajax-popup="true" data-size="md" data-bs-toggle="tooltip"
                                                                title="" data-title="{{ __('Edit Maintenance Type') }}"
                                                                data-bs-original-title="{{ __('Edit') }}">
                                                                <i class="ti ti-pencil text-white"></i>
                                                            </a>
                                                        </div>
                                                    @endpermission
                                                    @permission('maintenanceType delete')
                                                        <div class="action-btn">
                                                            {{ Form::open(['route' => ['maintenanceType.destroy', $maintenanceType->id], 'class' => 'm-0']) }}
                                                            @method('DELETE')
                                                            <a class="btn btn-sm  align-items-center bs-pass-para show_confirm bg-danger"
                                                                data-bs-toggle="tooltip" title=""
                                                                data-bs-original-title="Delete" aria-label="Delete"
                                                                data-confirm="{{ __('Are You Sure?') }}"
                                                                data-text="{{ __('This action can not be undone. Do you want to continue?') }}"
                                                                data-confirm-yes="delete-form-{{ $maintenanceType->id }}"><i
                                                                    class="ti ti-trash text-white text-white"></i></a>
                                                            {{ Form::close() }}
                                                        </div>
                                                    @endpermission
                                                </span>
                                            </td>
                                        @endif
                                    </tr>
                                @empty
                                    @include('layouts.nodatafound')
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
