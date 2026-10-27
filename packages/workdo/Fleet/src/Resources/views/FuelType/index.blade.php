@extends('layouts.main')
@section('page-title')
    {{ __('Manage Fuel Type') }}
@endsection
@section('page-breadcrumb')
    {{ __('Fuel Type') }}
@endsection
@section('page-action')
    <div>
        @permission('fueltype create')
            <a class="btn btn-sm btn-primary" data-ajax-popup="true" data-size="md" data-title="{{ __('Create Fuel Type') }}"
                data-url="{{ route('fuelType.create') }}" data-bs-toggle="tooltip"
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
                                    <th>{{ __('Fuel Type Name') }}</th>
                                    @if (Laratrust::hasPermission('fueltype edit') || Laratrust::hasPermission('fueltype delete'))
                                        <th width="200px">{{ __('Action') }}</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($fuelTypes as $fuelType)
                                    <tr>
                                        <td>{{ $fuelType->name }}</td>
                                        @if (Laratrust::hasPermission('fueltype edit') || Laratrust::hasPermission('fueltype delete'))
                                            <td class="Action">
                                                <span>
                                                    @permission('fueltype edit')
                                                        <div class="action-btn me-2">
                                                            <a class="btn btn-sm  align-items-center bg-info"
                                                                data-url="{{ route('fuelType.edit', $fuelType->id) }}"
                                                                data-ajax-popup="true" data-size="md" data-bs-toggle="tooltip"
                                                                title="" data-title="{{ __('Edit Fuel Type') }}"
                                                                data-bs-original-title="{{ __('Edit') }}">
                                                                <i class="ti ti-pencil text-white"></i>
                                                            </a>
                                                        </div>
                                                    @endpermission
                                                    @permission('fueltype delete')
                                                        <div class="action-btn">
                                                            {{ Form::open(['route' => ['fuelType.destroy', $fuelType->id], 'class' => 'm-0']) }}
                                                            @method('DELETE')
                                                            <a class="btn btn-sm  align-items-center bs-pass-para show_confirm bg-danger"
                                                                data-bs-toggle="tooltip" title=""
                                                                data-bs-original-title="Delete" aria-label="Delete"
                                                                data-confirm="{{ __('Are You Sure?') }}"
                                                                data-text="{{ __('This action can not be undone. Do you want to continue?') }}"
                                                                data-confirm-yes="delete-form-{{ $fuelType->id }}"><i
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
