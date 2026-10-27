@extends('layouts.main')

@section('page-title')
    {{__('Contract Type')}}
@endsection

@section('page-action')
    <div>
        @permission('contracttype create')
            <a class="btn btn-sm btn-primary btn-icon" data-bs-toggle="tooltip" data-bs-placement="top" title="{{__('Create')}}" data-ajax-popup="true" data-size="md" data-title="{{__('Create Contract Type')}}" data-url="{{route('contract_type.create')}}"><i class="ti ti-plus text-white"></i></a>
        @endpermission
    </div>
@endsection

@section('page-breadcrumb')
    {{__('Contract Type')}}
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table mb-0    ">
                            <thead>
                                <tr>
                                    <th>{{__('Contract Type')}}</th>
                                    @if (Laratrust::hasPermission('contracttype edit') || Laratrust::hasPermission('contracttype delete'))
                                        <th width="250px">{{__('Action')}}</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($contractTypes as $contractType)
                                <tr>
                                    <td>{{ $contractType->name }}</td>
                                    @if (Laratrust::hasPermission('contracttype edit') || Laratrust::hasPermission('contracttype delete'))
                                        <td class="Action">
                                            <span>
                                                @permission('contracttype edit')
                                                    <div class="action-btn bg-info ms-2">
                                                        <a data-size="md" data-url="{{ URL::to('contract_type/'.$contractType->id.'/edit') }}" data-ajax-popup="true" data-title="{{__('Edit Contract Type')}}" class="mx-3 btn btn-sm d-inline-flex align-items-center" data-bs-toggle="tooltip" data-bs-placement="top" title="{{__('Edit')}}" ><i class="ti ti-pencil text-white"></i></a>
                                                    </div>
                                                @endpermission
                                                @permission('contracttype delete')
                                                    <div class="action-btn bg-danger ms-2">
                                                        {!! Form::open(['method' => 'DELETE', 'route' => ['contract_type.destroy', $contractType->id]]) !!}
                                                            <a href="#!" class="mx-3 btn btn-sm d-inline-flex align-items-center show_confirm" data-bs-toggle="tooltip" data-bs-placement="top" title="{{__('Delete')}}">
                                                            <span class="text-white"> <i class="ti ti-trash"></i></span></a>
                                                        {!! Form::close() !!}
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
