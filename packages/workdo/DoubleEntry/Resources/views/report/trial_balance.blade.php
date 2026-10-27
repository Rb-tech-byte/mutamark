@extends('layouts.main')
@section('page-title')
    {{ __('Trial Balance') }}
@endsection
@section('page-breadcrumb')
    {{__('Trial Balance')}}
@endsection
@push('css')
    <link rel="stylesheet" href="{{ asset('Modules/DoubleEntry/Resources/assets/css/app.css') }}" id="main-style-link">
@endpush
@push('scripts')
    <script type="text/javascript" src="{{ asset('js/html2pdf.bundle.min.js') }}"></script>
    <script>
        var filename = $('#filename').val();
        function saveAsPDF() {
            var printContents = document.getElementById('printableArea').innerHTML;
            var originalContents = document.body.innerHTML;
            document.body.innerHTML = printContents;
            window.print();
            document.body.innerHTML = originalContents;
        }
    </script>
    <script>
        $(document).ready(function () {
            $("#filter").click(function () {
                $("#show_filter").toggle();
            });
        });
    </script>
@endpush

@section('page-action')

    <div>
        <input type="hidden" name="start_date" class="start_date">
        <input type="hidden" name="end_date" class="end_date">
        <a href="#" onclick="saveAsPDF()"  class="btn btn-sm btn-primary me-1" data-bs-toggle="tooltip" title="{{ __('Print') }}"
           data-original-title="{{ __('Print') }}"><i class="ti ti-printer"></i></a>

        <div class="float-end" id="filter">
            <button id="filter" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" title="{{ __('Filter') }}"><i
                        class="ti ti-filter"></i></button>
        </div>
    </div>
@endsection

@section('content')
    <div class="row justify-content-center">
        <div class="col-sm-8">
            <div class="mt-2 " id="multiCollapseExample1">
                <div class="card" id="show_filter" style="display:none;">
                    <div class="card-body">
                        {{ Form::open(['route' => ['report.trial.balance'], 'method' => 'GET', 'id' => 'report_trial_balance']) }}
                        <div class="row align-items-center justify-content-end">
                            <div class="col-xl-10">
                                <div class="row">
                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                        <div class="btn-box">
                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                        <div class="btn-box">
                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                        <div class="btn-box">
                                            {{ Form::label('start_date', __('Start Date'), ['class' => 'form-label']) }}
                                            {{ Form::date('start_date',$filter['startDateRange'], ['class' => 'startDate form-control']) }}
                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                        <div class="btn-box">
                                            {{ Form::label('end_date', __('End Date'), ['class' => 'form-label']) }}
                                            {{ Form::date('end_date', $filter['endDateRange'], ['class' => 'endDate form-control']) }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-auto mt-4">
                                <div class="row">
                                    <div class="col-auto">
                                        <a href="#" class="btn btn-sm btn-primary"
                                           onclick="document.getElementById('report_trial_balance').submit(); return false;"
                                           data-bs-toggle="tooltip" title="{{ __('Apply') }}"
                                           data-original-title="{{ __('apply') }}">
                                            <span class="btn-inner--icon"><i class="ti ti-search"></i></span>
                                        </a>
                                        <a href="{{ route('report.trial.balance') }}" class="btn btn-sm btn-danger "
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

    @php
        $authUser =  creatorId();
        $user = App\Models\User::find($authUser);
    @endphp

    <div class="row justify-content-center" id="printableArea">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body {{ $view == 'collapse' ? 'collapse-view' : '' }}">
                    <div class="account-main-title mb-5">
                        <h5>{{'Trial Balance of ' . $user->name . ' as of ' . $filter['startDateRange'] . ' to ' . $filter['endDateRange'] }}
                        </h5>
                    </div>
                    <div class="account-title d-flex align-items-center justify-content-between border-top border-bottom py-2">
                        <h6 class="mb-0">{{ __('Account') }}</h6>
                        <h6 class="mb-0 text-center">{{ __('Account Code') }}</h6>
                        <h6 class="mb-0 text-end me-5">{{ __('Debit') }}</h6>
                        <h6 class="mb-0 text-end">{{ __('Credit') }}</h6>
                    </div>
                    @php
                        $totalCredit = 0;
                        $totalDebit = 0;
                    @endphp

                    @foreach ($totalAccounts as $type => $accounts)
                        <div class="account-main-inner border-bottom py-2">
                            <p class="fw-bold ps-2 mb-2">{{ $type }}</p>
                            @if ($view == 'collapse')
                                @foreach ($accounts as $key => $record)
                                    @if ($record['account'] == 'parentTotal')
                                        <div class="account-inner d-flex align-items-center justify-content-between ps-3">
                                            <div class="mb-2 account-arrow">
                                                <a href="{{ route('report.trial.balance', ['expand']) }}"><i
                                                        class="ti ti-chevron-down account-icon"></i></a>
                                                <a href="{{ route('report.ledger', $record['account_id']) }}?account={{ $record['account_id'] }}"
                                                   class="text-primary ">{{ str_replace('Total ', '', $record['account_name']) }}</a>
                                            </div>

                                            <p class="mb-2 text-center">{{ $record['account_code'] }}</p>
                                            <p class="text-primary  mb-2 text-end me-5">
                                                {{ currency_format_with_sym($record['totalDebit']) }}</p>
                                            <p class="text-primary mb-2  float-end text-end">
                                                {{ currency_format_with_sym($record['totalCredit']) }}</p>
                                        </div>
                                    @elseif($record['account'] == 'parentTotal' || $record['account'] == '')
                                        <div class="account-inner d-flex align-items-center justify-content-between ps-3">
                                            <p class="mb-2 ms-3"><a
                                                    href="{{ route('report.ledger', $record['account_id']) }}?account={{ $record['account_id'] }}"
                                                    class="text-primary">{{ $record['account_name'] }}</a>
                                            </p>
                                            <p class="mb-2 text-center">{{ $record['account_code'] }}</p>
                                            <p class="text-primary mb-2 text-end me-5">
                                                {{ currency_format_with_sym($record['totalDebit']) }}</p>
                                            <p class="text-primary mb-2 float-end text-end">
                                                {{ currency_format_with_sym($record['totalCredit']) }}</p>
                                        </div>
                                    @endif
                                    @php
                                        if($record['account'] != 'parent' && $record['account'] != 'subAccount')
                                        {
                                        $totalDebit += $record['totalDebit'];
                                        $totalCredit += $record['totalCredit'];
                                        }
                                    @endphp
                                @endforeach
                            @else
                                @foreach ($accounts as $key => $record)
                                    @if ($record['account'] == 'parent')
                                        <div class="account-inner d-flex align-items-center justify-content-between ps-3">
                                            <div class="mb-2 account-arrow">
                                                <a href="{{ route('report.trial.balance', ['collapse']) }}"><i
                                                        class="ti ti-chevron-down account-icon"></i></a>
                                                <a href="{{ route('report.ledger', $record['account_id']) }}?account={{ $record['account_id'] }}"
                                                   class="text-primary fw-bold">{{ str_replace('Total ', '', $record['account_name']) }}</a>
                                            </div>

                                            <p class="mb-2 text-center">{{ $record['account_code'] }}</p>
                                            <p class="text-primary fw-bold mb-2 text-end me-5">
                                                {{ currency_format_with_sym($record['totalDebit']) }}</p>
                                            <p class="text-primary mb-2 fw-bold float-end text-end">
                                                {{ currency_format_with_sym($record['totalCredit']) }}</p>
                                        </div>
                                    @elseif($record['account'] == 'parentTotal')
                                        <div class="account-inner d-flex align-items-center justify-content-between ps-3">
                                            <p class="mb-2"><a href="#"
                                                               class="text-dark fw-bold">{{ $record['account_name'] }}</a>
                                            </p>
                                            <p class="mb-2 ms-3 text-center">{{ $record['account_code'] }}</p>
                                            <p class="text-dark fw-bold mb-2 text-end me-5">
                                                {{ currency_format_with_sym($record['totalDebit']) }}</p>
                                            <p class="text-dark fw-bold mb-2 float-end text-end">
                                                {{ currency_format_with_sym($record['totalCredit']) }}</p>
                                        </div>
                                    @else
                                        <div class="account-inner d-flex align-items-center justify-content-between ps-3">
                                            <p class="mb-2 ms-3"><a
                                                    href="{{ route('report.ledger', $record['account_id']) }}?account={{ $record['account_id'] }}"
                                                    class="text-primary">{{ $record['account_name'] }}</a>
                                            </p>
                                            <p class="mb-2 text-center">{{ $record['account_code'] }}</p>
                                            <p class="text-primary mb-2 text-end me-5">
                                                {{ currency_format_with_sym($record['totalDebit']) }}</p>
                                            <p class="text-primary mb-2 float-end text-end">
                                                {{ currency_format_with_sym($record['totalCredit']) }}</p>
                                        </div>
                                    @endif
                                    @php
                                        if($record['account'] != 'parent' && $record['account'] != 'subAccount')
                                        {
                                            $totalDebit += $record['totalDebit'];
                                            $totalCredit += $record['totalCredit'];
                                        }

                                    @endphp
                                @endforeach
                            @endif
                        </div>
                    @endforeach

                    @if ($totalAccounts != [])
                        <div
                            class="account-title d-flex align-items-center justify-content-between border-top border-bottom py-2 px-2 pe-0">
                            <h6 class="fw-bold mb-0">{{ 'Total' }}</h6>
                            <h6 class="fw-bold mb-0">{{ '' }}</h6>
                            <h6 class="fw-bold mb-0 text-end me-5">{{ currency_format_with_sym($totalDebit) }}</h6>
                            <h6 class="fw-bold mb-0 text-end">{{ currency_format_with_sym($totalCredit) }}</h6>

                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

@endsection


@push('scripts')
    <script>
        $(document).ready(function () {
            callback();
            function callback() {
                var start_date = $(".startDate").val();
                var end_date = $(".endDate").val();
                $('.start_date').val(start_date);
                $('.end_date').val(end_date);
            }
        });
    </script>
@endpush
