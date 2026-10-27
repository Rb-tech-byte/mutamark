@extends('layouts.main')
@section('page-title')
    {{ __('Manage Contract') }}
@endsection
@section('page-breadcrumb')
 {{__('Contact')}}
@endsection
@section('page-action')
<div>
    <a href="{{ route('contract.index') }}" class="btn btn-sm btn-primary btn-icon" data-bs-toggle="tooltip"
        title="{{ __('List View') }}">
        <i class="ti ti-list text-white"></i>
    </a>

    @permission('contract create')
        <a data-url="{{ route('contract.create') }}" data-size="lg" data-ajax-popup="true" data-bs-toggle="tooltip"data-title="{{__('Create New Contract')}}"title="{{__('Create')}}" class="btn btn-sm btn-primary">
            <i class="ti ti-plus"></i>
        </a>
    @endpermission
</div>
@endsection
@push('scripts')
    <script src="{{ asset('js/letter.avatar.js') }}"></script>
@endpush
@section('content')
<div class="row">
    <div class="col-xl-3 col-sm-6 col-12">
        <div class="card comp-card">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col">
                        <h6 class="m-b-20">{{__('Total Contracts')}}</h6>
                        <h3 class="text-primary">{{ $cnt_contract['total'] }}</h3>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-handshake bg-success text-white"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-sm-6 col-12">
        <div class="card comp-card">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col">
                        <h6 class="m-b-20">{{__('This Month Total Contracts')}}</h6>
                        <h3 class="text-info">{{ $cnt_contract['this_month'] }}</h3>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-handshake bg-info text-white"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-sm-6 col-12">
        <div class="card comp-card">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col">
                        <h6 class="m-b-20">{{__('This Week Total Contracts')}}</h6>
                        <h3 class="text-warning">{{ $cnt_contract['this_week'] }}</h3>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-handshake bg-warning text-white"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-sm-6 col-12">
        <div class="card comp-card">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col">
                        <h6 class="m-b-20">{{__('Last 30 Days Total Contracts')}}</h6>
                        <h3 class="text-danger">{{ $cnt_contract['last_30days'] }}</h3>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-handshake bg-danger text-white"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @foreach ($contracts as $contract)
        <div class="col-md-3">
            <div class="card">
                <div class="card-header border-0 pb-0">
                    <div class="d-flex align-items-center">
                        <h6>
                            <a href="{{route('contract.show',$contract->id)}}" >
                                {{Modules\Contract\Entities\Contract::contractNumberFormat($contract->contract_id)}}
                            </a>
                        </h6>
                    </div>
                    <div class="card-header-right">
                        <div class="btn-group card-option">
                            <button type="button" class="btn dropdown-toggle"
                                data-bs-toggle="dropdown" aria-haspopup="true"
                                aria-expanded="false">
                                <i class="feather icon-more-vertical"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end">
                                @if (Laratrust::hasPermission('contract create') || Laratrust::hasPermission('contract show') || Laratrust::hasPermission('contract edit') || Laratrust::hasPermission('contract delete') || Laratrust::hasPermission('contract template create'))
                                @if (module_is_active('ContractTemplate'))
                                        @permission('contract template create')
                                            <a class="dropdown-item" data-ajax-popup="true"
                                                data-size="md" data-title="{{ __('Save As Contract Template') }}"
                                                data-url="{{ route('contract-template.create',['contract_id'=> $contract->id,'type'=>'template']) }}">
                                                <i class="ti ti-bookmark"></i> <span>{{ __('Save as template') }}</span>
                                            </a>
                                        @endpermission
                                @endif  
                                
                                @permission('contract create')
                                        <a data-url="{{ route('contracts.copy',$contract->id) }}" data-size="lg" data-ajax-popup="true"  class="dropdown-item"
                                            data-bs-whatever="{{ __('Duplicate') }}" data-bs-toggle="tooltip"
                                            data-title="{{ __('Duplicate Contract') }}"><i class="ti ti-copy"></i>
                                            {{ __('Duplicate') }}</a>
                                    @endpermission
                                    @permission('contract edit')
                                        <a data-url="{{ route('contract.edit', $contract->id) }}" data-size="lg" data-ajax-popup="true"  class="dropdown-item"
                                            data-bs-whatever="{{ __('Edit Contract') }}" data-bs-toggle="tooltip"
                                            data-title="{{ __('Edit Contract') }}"><i class="ti ti-pencil"></i>
                                            {{ __('Edit') }}</a>
                                    @endpermission
                                    @permission('contract show')
                                        <a href="{{ route('contract.show', $contract->id) }}" class="dropdown-item"
                                            data-size="md" data-bs-whatever="{{ __('Contract Details') }}"
                                            data-bs-toggle="tooltip"
                                            data-title="{{ __('Contract Details') }}"><i class="ti ti-eye"></i>
                                            {{ __('Details') }}</a>
                                    @endpermission

                                    @permission('contract delete')
                                        {!! Form::open(['method' => 'DELETE', 'route' => ['contract.destroy', $contract->id]]) !!}
                                        <a href="#!" class="dropdown-item show_confirm" data-bs-toggle="tooltip" >
                                            <i class="ti ti-trash"></i>{{ __('Delete') }}
                                        </a>
                                        {!! Form::close() !!}
                                    @endpermission
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-2 justify-content-between">
                        <div class="col-12">
                            <div class="text-center client-box">
                                <div class="avatar-parent-child mb-3">
                                    <img alt="user-image" class="img-fluid rounded-circle" @if (!empty($contract->avatar)) src="{{ !empty($contract->avatar) ? get_file($contract->avatar) : asset(url('./assets/img/clients/160x160/img-1.png')) }}" @else  avatar="{{ $contract->subject }}" @endif>
                                </div>
                                <a href="{{ route('contract.show',$contract->id) }}"  data-title="{{__('Contact Details')}}" class="action-item text-primary mt-2">
                                    {{ ucfirst($contract->subject) }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
    <div class="col-md-3">
        <a class="btn-addnew-project"  data-ajax-popup="true" data-size="lg" data-title="{{ __('Create New Contract') }}" data-url="{{ route('contract.create') }}">
            <div class="badge bg-primary proj-add-icon">
                <i class="ti ti-plus"></i>
            </div>
            <h6 class="mt-4 mb-2">{{__('New Contract')}}</h6>
            <p class="text-muted text-center">{{__('Click here to add New Contract')}}</p>
        </a>
     </div>
</div>
@endsection
