@extends('layouts.main')

@section('page-title')
    {{ __('Manage Inventory') }}
@endsection

@section('page-breadcrumb')
    {{ __('Inventory') }}
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table mb-0 pc-dt-simple" id="assets">
                            <thead>
                                <tr>
                                    <th>{{ __('Id') }}</th>
                                    <th>{{ __('Quantity') }}</th>
                                    <th>{{ __('Type') }}</th>
                                    <th>{{ __('Description') }}</th>
                                    <th>{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($inventorys as $key => $inventory)
                                <tr class="font-style">
                                    <td>{{ ++$key }}</td>
                                    <td>{{ $inventory->quantity}}</td>
                                    <td>{{ $inventory->type }}</td>
                                    <td>{{ $inventory->description }}</td>
                                    <td class="Action">
                                        <span>
                                            {{-- @can('setsalary edit') --}}
                                            <div class="action-btn bg-warning ms-2">
                                                <a href="{{ route('inventory.view', [$inventory->feild_id,$inventory->type]) }}" class="mx-3 btn btn-sm align-items-center" data-bs-toggle="tooltip" title="" data-bs-original-title="{{ __('View') }}">
                                                    <i class="ti ti-eye text-white"></i>
                                                </a>
                                                {{-- , 'type' => $inventory->type --}}
                                            </div>
                                            {{-- @endcan --}}
                                        </span>
                                    </td>
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

