@extends('layouts.main')
@section('page-title')
    {{__('Manage Journal Entry')}}
@endsection

@section('page-breadcrumb')
    {{__('Journal Entry')}}
@endsection

@section('page-action')
    <div class="float-end">
        @permission('journalentry create')
        <a href="{{ route('journal-entry.create') }}" data-title="{{__('Create New Journal')}}" data-bs-toggle="tooltip"
           title="{{__('Create')}}" class="btn btn-sm btn-primary">
            <i class="ti ti-plus"></i>
        </a>
        @endpermission
    </div>
@endsection

@section('content')

    <div class="row">
        <div class="col-xl-12 mt-2">
            <div class="card">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table mb-0 pc-dt-simple" id="products">
                            <thead>
                            <tr>
                                <th> {{__('Journal ID')}}</th>
                                <th> {{__('Date')}}</th>
                                <th> {{__('Amount')}}</th>
                                <th> {{__('Description')}}</th>
                                @if (Laratrust::hasPermission('journalentry edit') || Laratrust::hasPermission('journalentry delete'))
                                    <th width="10%"> {{__('Action')}}</th>
                                @endif
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($journalEntries as $journalEntry)
                                <tr>
                                    <td class="Id">
                                        <a href="{{ route('journal-entry.show',$journalEntry->id) }}"
                                           class="btn btn-outline-primary">{{ Modules\DoubleEntry\Entities\JournalEntry::journalNumberFormat($journalEntry->journal_id) }}</a>
                                    </td>
                                    <td>{{company_date_formate($journalEntry->date) }}</td>
                                    <td>{{currency_format_with_sym($journalEntry->totalCredit())}}</td>
                                    <td>{{!empty($journalEntry->description)?$journalEntry->description:'-'}}</td>
                                    @if (Laratrust::hasPermission('journalentry edit') || Laratrust::hasPermission('journalentry delete'))
                                        <td class="Action">
                                            @permission('journalentry edit')
                                            <div class="action-btn bg-primary ms-2">
                                                <a data-title="{{__('Edit Journal')}}"
                                                   href="{{ route('journal-entry.edit',[$journalEntry->id]) }}"
                                                   class="mx-3 btn btn-sm align-items-center" data-bs-toggle="tooltip"
                                                   title="{{__('Edit')}}" data-original-title="{{__('Edit')}}">
                                                    <i class="ti ti-pencil text-white"></i>
                                                </a>
                                            </div>
                                            @endpermission

                                            @permission('journalentry delete')
                                            <div class="action-btn bg-danger ms-2">
                                                {!! Form::open(['method' => 'DELETE', 'route' => ['journal-entry.destroy', $journalEntry->id, 'enctype' => 'multipart/form-data']]) !!}
                                                <a href ="#!" class="btn btn-sm align-items-center text-white show_confirm"
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
