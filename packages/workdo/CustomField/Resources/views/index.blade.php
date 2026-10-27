@extends('layouts.main')
@section('page-title')
    {{ __('Manage Custom Field') }}
@endsection
@section('page-breadcrumb')
    {{ __('Custom Field') }}
@endsection
@section('page-action')
    @permission('customfield create')
        <div class="float-end">
            <a href="#" data-url="{{ route('custom-field.create') }}" data-bs-toggle="tooltip" title="{{ __('Create') }}"
                data-ajax-popup="true" data-title="{{ __('Create New Custom Field') }}" class="btn btn-sm btn-primary">
                <i class="ti ti-plus"></i>
            </a>
        </div>
    @endpermission
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table mb-0 pc-dt-simple" id="custom_field">
                            <thead>
                                <tr>
                                    <th> {{ __('Custom Field') }}</th>
                                    <th> {{ __('Type') }}</th>
                                    <th> {{ __('Module') }}</th>
                                    <th> {{ __('Rule') }}</th>

                                    @if (Laratrust::hasPermission('customfield edit') || Laratrust::hasPermission('customfield delete'))
                                        <th width="10%"> {{ __('Action') }}</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($custom_fields as $field)
                                    <tr>
                                        <td>{{ $field->name }}</td>
                                        <td>{{ $field->type }}</td>
                                        <td>
                                            <div class="page-header">
                                                <ul class="breadcrumb  m-1">
                                                    <li class="breadcrumb-item"> {{ ucfirst( Module_Alias_Name($field->module)) }}</li>
                                                    @if (!empty($field->sub_module))
                                                        <li class="breadcrumb-item"> {{ ucfirst($field->sub_module) }} </li>
                                                    @endif
                                                </ul>
                                            </div>
                                        </td>
                                        <td>
                                            <h6 class="float-left mr-1">
                                                @if ($field->is_required == 1)
                                                    <div class="badge bg-success p-2 px-3 rounded status-badge7">{{ __('Required') }}</div>
                                                @else
                                                    <div class="badge bg-danger p-2 px-3 rounded status-badge7">{{ __('Not Required') }}
                                                    </div>
                                                @endif
                                            </h6>
                                        </td>
                                        @if (Laratrust::hasPermission('customfield edit') || Laratrust::hasPermission('customfield delete'))
                                            <td class="Action">
                                                <span>
                                                    @permission('customfield edit')
                                                        <div class="action-btn bg-info ms-2">
                                                            <a href="#" class="mx-3 btn btn-sm align-items-center"
                                                                data-url="{{ route('custom-field.edit', $field->id) }}"
                                                                data-ajax-popup="true"
                                                                data-title="{{ __('Edit Custom Field') }}"
                                                                data-bs-toggle="tooltip" title="{{ __('Edit') }}"
                                                                data-original-title="{{ __('Edit') }}">
                                                                <i class="ti ti-pencil text-white"></i>
                                                            </a>
                                                        </div>
                                                    @endpermission
                                                    @permission('customfield delete')
                                                        <div class="action-btn bg-danger ms-2">
                                                            {!! Form::open([
                                                                'method' => 'DELETE',
                                                                'route' => ['custom-field.destroy', $field->id],
                                                                'id' => 'delete-form-' . $field->id,
                                                            ]) !!}
                                                            <a href="#"
                                                                class="mx-3 btn btn-sm align-items-center show_confirm"
                                                                data-bs-toggle="tooltip" title="{{ __('Delete') }}"
                                                                data-original-title="{{ __('Delete') }}"
                                                                data-confirm="{{__('Are You Sure?')}}" data-text="{{__('This action can not be undone. Do you want to continue?')}}"
                                                                data-confirm-yes="document.getElementById('delete-form-{{ $field->id }}').submit();">
                                                                <i class="ti ti-trash text-white"></i>
                                                            </a>
                                                            {!! Form::close() !!}
                                                        </div>
                                                    @endpermission
                                                </span>
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
