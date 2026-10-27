@extends('layouts.main')
@section('page-title')
    {{ __('Ledger Summary') }}
@endsection
@section('page-breadcrumb')
    {{__('Ledger Summary')}}
@endsection

@push('scripts')
    <script type="text/javascript" src="{{ asset('js/html2pdf.bundle.min.js') }}"></script>
    <script>
        var filename = $('#filename').val();

        function saveAsPDF() {
            var element = document.getElementById('printableArea');
            var opt = {
                margin: 0.3,
                filename: filename,
                image: {
                    type: 'jpeg',
                    quality: 1
                },
                html2canvas: {
                    scale: 4,
                    dpi: 72,
                    letterRendering: true
                },
                jsPDF: {
                    unit: 'in',
                    format: 'A2'
                }
            };
            html2pdf().set(opt).from(element).save();
        }
    </script>
@endpush

@section('page-action')
    <div class="float-end">
        <a href="#" class="btn btn-sm btn-primary" onclick="saveAsPDF()" data-bs-toggle="tooltip"
           title="{{ __('Download') }}" data-original-title="{{ __('Download') }}">
            <span class="btn-inner--icon"><i class="ti ti-download"></i></span>
        </a>
    </div>
@endsection

@php
    $selectAcc = [
        [
            'id' => 0,
            'code' => '',
            'name' => 'Select Account',
            'parent' => 0,
        ],
    ];
    $accounts = array_merge($selectAcc, $accounts);
@endphp

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="mt-2" id="multiCollapseExample1">
                <div class="card">
                    <div class="card-body">
                        {{ Form::open(['route' => ['report.ledger'], 'method' => 'GET', 'id' => 'report_ledger']) }}
                        <div class="row align-items-center justify-content-end">
                            <div class="col-xl-10">
                                <div class="row">
                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                        <div class="btn-box">
                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                        <div class="btn-box">
                                            {{ Form::label('start_date', __('Start Date'), ['class' => 'form-label']) }}
                                            {{ Form::date('start_date', $filter['startDateRange'], ['class' => 'month-btn form-control']) }}
                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                        <div class="btn-box">
                                            {{ Form::label('end_date', __('End Date'), ['class' => 'form-label']) }}
                                            {{ Form::date('end_date', $filter['endDateRange'], ['class' => 'month-btn form-control']) }}
                                        </div>
                                    </div>

                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                        <div class="btn-box">
                                            {{ Form::label('account', __('Account'), ['class' => 'form-label']) }}
                                            <select name="account" class="form-control" required="required">
                                                @foreach ($accounts as $chartAccount)
                                                    <option value="{{ $chartAccount['id'] }}" class="subAccount" {{ isset($_GET['account']) && $chartAccount['id'] == $_GET['account'] ? 'selected' : ''}}> {{ $chartAccount['code'] }} - {{ $chartAccount['name'] }}</option>
                                                    @foreach ($subAccounts as $subAccount)
                                                        @if ($chartAccount['id'] == $subAccount['account'])
                                                            <option value="{{ $subAccount['id'] }}" class="ms-5" {{ isset($_GET['account']) && $_GET['account'] == $subAccount['id'] ? 'selected' : ''}}> &nbsp; &nbsp;&nbsp;  {{ $chartAccount['code'] }} - {{ $subAccount['name'] }}</option>
                                                        @endif
                                                    @endforeach
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-auto">
                                <div class="row">
                                    <div class="col-auto mt-4">
                                        <a href="#" class="btn btn-sm btn-primary"
                                           onclick="document.getElementById('report_ledger').submit(); return false;"
                                           data-bs-toggle="tooltip" title="{{ __('Apply') }}"
                                           data-original-title="{{ __('apply') }}">
                                            <span class="btn-inner--icon"><i class="ti ti-search"></i></span>
                                        </a>
                                        <a href="{{ route('report.ledger') }}" class="btn btn-sm btn-danger"
                                           data-bs-toggle="tooltip" title="{{ __('Reset') }}"
                                           data-original-title="{{ __('Reset') }}">
                                            <span class="btn-inner--icon"><i
                                                    class="ti ti-trash-off text-white-off "></i></span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
    <div id="printableArea">

        <div class="row mb-4">
            <div class="col-12 mb-4">
                <div class="card">
                    <div class="card-body table-border-style">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                <tr>
                                    <th> {{ __('Account Name') }}</th>
                                    <th> {{ __('Name') }}</th>
                                    <th> {{ __('Transaction Type') }}</th>
                                    <th> {{ __('Transaction Date') }}</th>
                                    <th> {{ __('Debit') }}</th>
                                    <th> {{ __('Credit') }}</th>
                                    <th> {{ __('Balance') }}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @php
                                    $balance = 0;
                                    $totalDebit = 0;
                                    $totalCredit = 0;
                                    $accountArrays = [];
                                    foreach ($chart_accounts as $key => $account) {
                                        $chartDatas = \Modules\Account\Entities\AccountUtility::getAccountData($account['id'], $filter['startDateRange'], $filter['endDateRange']);
                                        $chartDatas = $chartDatas->toArray();
                                        $accountArrays[] = $chartDatas;
                                    }
                                @endphp
                                @foreach ($accountArrays as $accounts)
                                    @foreach ($accounts as $account)
                                        <tr>
                                            <td>{{ $account->account_name }}</td>
                                            @if(!empty($account->user_name))
                                                <td>{{ $account->user_name }}</td>
                                            @else
                                                <td>-</td>
                                            @endif
                                            {{--                                            <td>{{($account->reference_id) }}</td>--}}
                                            <td>{{($account->reference) }}</td>
                                            <td>{{ $account->date }}</td>
                                            <td>{{ currency_format_with_sym($account->debit) }}</td>
                                            @php
                                                $total = $account->debit + $account->credit;
                                                if ($account->debit != 0) {
                                                    $balance -= $total;
                                                } else {
                                                    $balance += $total;
                                                }
                                            @endphp
                                            <td>{{ currency_format_with_sym ($account->credit) }}</td>
                                            <td>{{ currency_format_with_sym($balance) }}</td>
                                        </tr>
                                    @endforeach
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
