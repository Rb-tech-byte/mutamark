
@extends('layouts.main')
@section('page-title')
    {{ __('Manage Vehicle Type') }}
@endsection
@section('page-breadcrumb')
{{ __('Vehicle Type') }}
@endsection
@section('page-action')
<div>
    @permission('vehicletype create')
    <a  class="btn btn-sm btn-primary" data-ajax-popup="true" data-size="md" data-title="{{ __('Create Vehicle Type') }}" data-url="{{route('vehicleType.create')}}" data-bs-toggle="tooltip"  data-bs-original-title="{{ __('Create') }}">
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
                    <table class="table mb-0 " >
                        <thead>
                            <tr>
                                <th>{{__('Vehicle Type Name')}}</th>
                                @if (Laratrust::hasPermission('vehicletype edit') || Laratrust::hasPermission('vehicletype delete'))
                                    <th width="200px">{{ __('Action') }}</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($VehicleTypes as $VehicleType)
                            <tr>
                                <td>{{ $VehicleType->name }}</td>
                                @if (Laratrust::hasPermission('vehicletype edit') || Laratrust::hasPermission('vehicletype delete'))
                                    <td class="Action">
                                        <span>
                                            @permission('vehicletype edit')
                                            <div class="action-btn me-2">
                                                <a  class="btn btn-sm  align-items-center bg-info"
                                                    data-url="{{ route('vehicleType.edit', $VehicleType->id) }}"
                                                    data-ajax-popup="true" data-size="md" data-bs-toggle="tooltip" title=""
                                                    data-title="{{ __('Edit Vehicle Type') }}"
                                                    data-bs-original-title="{{ __('Edit') }}">
                                                    <i class="ti ti-pencil text-white"></i>
                                                </a>
                                            </div>
                                            @endpermission
                                            @permission('vehicletype delete')
                                            <div class="action-btn">
                                                {{Form::open(array('route'=>array('vehicleType.destroy', $VehicleType->id),'class' => 'm-0'))}}
                                                @method('DELETE')
                                                    <a
                                                        class="btn btn-sm  align-items-center bs-pass-para show_confirm bg-danger"
                                                        data-bs-toggle="tooltip" title="" data-bs-original-title="Delete"
                                                        aria-label="Delete" data-confirm="{{__('Are You Sure?')}}" data-text="{{__('This action can not be undone. Do you want to continue?')}}"  data-confirm-yes="delete-form-{{$VehicleType->id}}"><i
                                                            class="ti ti-trash text-white text-white"></i></a>
                                                {{Form::close()}}
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

