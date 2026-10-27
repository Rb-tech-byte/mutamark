@extends('layouts.main')
@section('page-title')
    {{ __('Profit & Loss') }}
@endsection
@section('page-breadcrumb')
    {{__('Profit and Loss')}}
@endsection
@push('css')
    <link rel="stylesheet" href="{{ asset('Modules/DoubleEntry/Resources/assets/css/app.css') }}" id="main-style-link">
@endpush

@push('scripts')

    <script src="{{ asset('Modules/DoubleEntry/Resources/assets/js/html2pdf.bundle.min.js') }}"></script>

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

@section('page-action')
    <div>
        <input type="hidden" name="start_date" class="start_date">
        <input type="hidden" name="end_date" class="end_date">
        <a href="#" onclick="saveAsPDF()" class="btn btn-sm btn-primary me-1" data-bs-toggle="tooltip"
           title="{{ __('Print') }}"
           data-original-title="{{ __('Print') }}"><i class="ti ti-printer"></i></a>

        <div class="float-end" id="filter">
            <button id="filter" class="btn btn-sm btn-primary"><i class="ti ti-filter"></i></button>
        </div>

        <div class="float-end me-1">
            <a href="{{ route('report.profit.loss' , 'horizontal')}}" class="btn btn-sm btn-primary"
               data-bs-toggle="tooltip" title="{{ __('Horizontal View') }}"
               data-original-title="{{ __('Horizontal View') }}"><i class="ti ti-separator-vertical"></i></a>
        </div>
    </div>
@endsection

@section('content')
    <div class="row justify-content-center">
        <div class="col-sm-8">
            <div class="mt-2 " id="multiCollapseExample1">
                <div class="card" id="show_filter" style="display:none;">
                    <div class="card-body">
                        {{ Form::open(['route' => ['report.profit.loss'], 'method' => 'GET', 'id' => 'report_profit_loss']) }}
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
                                            {{ Form::date('start_date', $filter['startDateRange'], ['class' => 'startDate form-control']) }}
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
                                           onclick="document.getElementById('report_profit_loss').submit(); return false;"
                                           data-bs-toggle="tooltip" title="{{ __('Apply') }}"
                                           data-original-title="{{ __('apply') }}">
                                            <span class="btn-inner--icon"><i class="ti ti-search"></i></span>
                                        </a>

                                        <a href="{{ route('report.profit.loss') }}" class="btn btn-sm btn-danger "
                                           data-bs-toggle="tooltip" title="{{ __('Reset') }}"
                                           data-original-title="{{ __('Reset') }}">
                                            <span class="btn-inner--icon">
                                                <i class="ti ti-trash-off text-white-off "></i></span>
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
        $authUser = creatorId();
        $user = App\Models\User::find($authUser);
    @endphp

    <div class="row justify-content-center" id="printableArea">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body {{ $collapseView == 'expand' ? 'collapse-view' : '' }}">
                    <div class="account-main-title mb-5">
                        <h5>{{ 'Profit & Loss of ' . $user->name . ' as of ' . $filter['startDateRange'] . ' to ' . $filter['endDateRange'] }}
                        </h5>
                    </div>
                    <div
                        class="account-title d-flex align-items-center justify-content-between border-top border-bottom py-2">
                        <h6 class="mb-0">{{ __('Account') }}</h6>
                        <h6 class="mb-0 text-center">{{ _('Account Code') }}</h6>
                        <h6 class="mb-0 text-end">{{ __('Total') }}</h6>

                    </div>
                    @php
                        $totalIncome = 0;
                        $netProfit = 0;
                        $totalCosts = 0;
                        $grossProfit = 0;
                    @endphp

                    @foreach ($totalAccounts as $accounts)
                        @if ($accounts['Type'] == 'Income')
                            <div class="account-main-inner border-bottom py-2">
                                <p class="fw-bold mb-2">{{ $accounts['Type'] }}</p>

                                @foreach ($accounts['account'] as $records)
                                    @if ($collapseView == 'collapse')
                                        @foreach ($records as $key => $record)
                                            @if ($record['account'] == 'parentTotal')
                                                <div
                                                    class="account-inner d-flex align-items-center justify-content-between ps-3">
                                                    <div class="mb-2 ms-2 account-arrow">
                                                        <div class="">
                                                            <a
                                                                href="{{ route('report.profit.loss', ['vertical', 'expand']) }}"><i
                                                                    class="ti ti-chevron-down account-icon"></i></a>
                                                        </div>
                                                        <a href="{{ route('report.ledger', $record['account_id']) }}?account={{ $record['account_id'] }}"
                                                           class="text-primary">{{ str_replace('Total ', '', $record['account_name']) }}</a>
                                                    </div>
                                                    <p class="mb-2 ms-3 text-center">
                                                        {{ $record['account_code'] }}
                                                    </p>
                                                    <p class="text-primary mb-2 float-end text-end">
                                                        {{ currency_format_with_sym($record['netAmount']) }}
                                                    </p>
                                                </div>
                                            @endif

                                            @if (!preg_match('/\btotal\b/i', $record['account_name']) && $record['account'] == '')
                                                <div
                                                    class="account-inner d-flex align-items-center justify-content-between ps-3">

                                                    <p class="mb-2 ms-4"><a
                                                            href="{{ route('report.ledger', $record['account_id']) }}?account={{ $record['account_id'] }}"
                                                            class="text-primary">{{ $record['account_name'] }}</a>
                                                    </p>
                                                    <p class="mb-2 text-center">{{ $record['account_code'] }}</p>
                                                    <p class="text-primary mb-2 float-end text-end">
                                                        {{ currency_format_with_sym($record['netAmount']) }}</p>
                                                </div>
                                            @endif
                                            @php
                                                if ($record['account_name'] === 'Total Income') {
                                                    $totalIncome = $record['netAmount'];
                                                }

                                                if ($record['account_name'] == 'Total Costs of Goods Sold') {
                                                    $totalCosts = $record['netAmount'];
                                                }
                                                $grossProfit = $totalIncome - $totalCosts;
                                            @endphp
                                        @endforeach
                                    @else
                                        @foreach ($records as $key => $record)
                                            @if ($record['account'] == 'parent' || $record['account'] == 'parentTotal')
                                                <div
                                                    class="account-inner d-flex align-items-center justify-content-between ps-3">
                                                    @if ($record['account'] == 'parent')
                                                        <div class="mb-2 ms-2 account-arrow">
                                                            <div class="">
                                                                <a
                                                                    href="{{ route('report.profit.loss', ['vertical', 'collapse']) }}"><i
                                                                        class="ti ti-chevron-down account-icon"></i></a>
                                                            </div>
                                                            <a href="{{ route('report.ledger', $record['account_id']) }}?account={{ $record['account_id'] }}"
                                                               class="{{ $record['account'] == 'parent' ? 'text-primary' : 'text-dark' }} fw-bold">{{ $record['account_name'] }}</a>
                                                        </div>
                                                    @else
                                                        <p class="mb-2"><a href="#"
                                                                           class="text-dark fw-bold">{{ $record['account_name'] }}</a>
                                                        </p>
                                                    @endif
                                                    <p class="mb-2 ms-3 text-center">
                                                        {{ $record['account_code'] }}
                                                    </p>
                                                    <p class="text-dark fw-bold mb-2 float-end text-end">
                                                        {{ currency_format_with_sym($record['netAmount']) }}
                                                    </p>
                                                </div>
                                            @endif

                                            @if (
                                                (!preg_match('/\btotal\b/i', $record['account_name']) && ($record['account'] == '') ||
                                                    $record['account'] == 'subAccount'))
                                                <div
                                                    class="account-inner d-flex align-items-center justify-content-between ps-3">

                                                    <p class="mb-2 ms-4"><a
                                                            href="{{ route('report.ledger', $record['account_id']) }}?account={{ $record['account_id'] }}"
                                                            class="text-primary">{{ $record['account_name'] }}</a>
                                                    </p>
                                                    <p class="mb-2 text-center">{{ $record['account_code'] }}</p>
                                                    <p class="text-primary mb-2 float-end text-end">
                                                        {{ currency_format_with_sym($record['netAmount']) }}</p>
                                                </div>
                                            @endif
                                            @php
                                                if ($record['account_name'] === 'Total Income') {
                                                    $totalIncome = $record['netAmount'];
                                                }

                                                if ($record['account_name'] == 'Total Costs of Goods Sold') {
                                                    $totalCosts = $record['netAmount'];
                                                }
                                                $grossProfit = $totalIncome - $totalCosts;
                                            @endphp
                                        @endforeach
                                    @endif
                                @endforeach

                                <div class="account-inner d-flex align-items-center justify-content-between">
                                    <p class="fw-bold mb-2">
                                        {{ $record['account_name'] ? $record['account_name'] : '' }}
                                    </p>
                                    <p class="fw-bold mb-2 text-end">
                                        {{ $record['netAmount'] ? currency_format_with_sym($record['netAmount']) : currency_format_with_sym(0) }}
                                    </p>
                                </div>
                            </div>
                        @endif
                        @if ($accounts['Type'] == 'Costs of Goods Sold')
                            <div class="account-main-inner border-bottom py-2">
                                <p class="fw-bold mb-2">{{ $accounts['Type'] }}</p>

                                @foreach ($accounts['account'] as $records)
                                    @if ($collapseView == 'collapse')
                                        @foreach ($records as $key => $record)
                                            @php
                                                if ($record['netAmount'] > 0) {
                                                    $netAmount = $record['netAmount'];
                                                } else {
                                                    $netAmount = -$record['netAmount'];
                                                }
                                            @endphp
                                            @if ($record['account'] == 'parentTotal')
                                                <div
                                                    class="account-inner d-flex align-items-center justify-content-between ps-3">
                                                    <div class="mb-2 ms-2 account-arrow">
                                                        <div class="">
                                                            <a
                                                                href="{{ route('report.profit.loss', ['vertical', 'expand']) }}"><i
                                                                    class="ti ti-chevron-down account-icon"></i></a>
                                                        </div>
                                                        <a href="{{ route('report.ledger', $record['account_id']) }}?account={{ $record['account_id'] }}"
                                                           class="text-primary">{{ str_replace('Total ', '', $record['account_name']) }}</a>
                                                    </div>
                                                    <p class="mb-2 ms-3 text-center">
                                                        {{ $record['account_code'] }}
                                                    </p>
                                                    <p class="text-primary mb-2 float-end text-end">
                                                        {{ currency_format_with_sym($netAmount) }}
                                                    </p>
                                                </div>
                                            @endif

                                            @if (
                                                !preg_match('/\btotal\b/i', $record['account_name']) &&
                                                    $record['account'] == '' &&
                                                    $record['account'] != 'subAccount')
                                                <div
                                                    class="account-inner d-flex align-items-center justify-content-between ps-3">

                                                    <p class="mb-2 ms-4"><a
                                                            href="{{ route('report.ledger', $record['account_id']) }}?account={{ $record['account_id'] }}"
                                                            class="text-primary">{{ $record['account_name'] }}</a>
                                                    </p>
                                                    <p class="mb-2 text-center">{{ $record['account_code'] }}</p>
                                                    <p class="text-primary mb-2 float-end text-end">
                                                        {{ currency_format_with_sym($netAmount) }}</p>
                                                </div>
                                            @endif
                                            @php
                                                if ($record['account_name'] === 'Total Income') {
                                                    $totalIncome = $netAmount;
                                                }

                                                if ($record['account_name'] == 'Total Costs of Goods Sold') {
                                                    $totalCosts = $netAmount;
                                                }
                                                $grossProfit = $totalIncome - $totalCosts;
                                            @endphp
                                        @endforeach
                                    @else
                                        @foreach ($records as $key => $record)
                                            @php
                                                if ($record['netAmount'] > 0) {
                                                    $netAmount = $record['netAmount'];
                                                } else {
                                                    $netAmount = -$record['netAmount'];
                                                }
                                            @endphp
                                            @if ($record['account'] == 'parent' || $record['account'] == 'parentTotal')
                                                <div
                                                    class="account-inner d-flex align-items-center justify-content-between ps-3">
                                                    @if ($record['account'] == 'parent')
                                                        <div class="mb-2 ms-2 account-arrow">
                                                            <div class="">
                                                                <a
                                                                    href="{{ route('report.profit.loss', ['vertical', 'collapse']) }}"><i
                                                                        class="ti ti-chevron-down account-icon"></i></a>
                                                            </div>
                                                            <a href="{{ route('report.ledger', $record['account_id']) }}?account={{ $record['account_id'] }}"
                                                               class="{{ $record['account'] == 'parent' ? 'text-primary' : 'text-dark' }} fw-bold">{{ $record['account_name'] }}</a>
                                                        </div>
                                                    @else
                                                        <p class="mb-2"><a href="#"
                                                                           class="text-dark fw-bold">{{ $record['account_name'] }}</a>
                                                        </p>
                                                    @endif
                                                    <p class="mb-2 ms-3 text-center">
                                                        {{ $record['account_code'] }}
                                                    </p>
                                                    <p class="text-dark fw-bold mb-2 float-end text-end">
                                                        {{ currency_format_with_sym($netAmount) }}
                                                    </p>
                                                </div>
                                            @endif

                                            @if (
                                                (!preg_match('/\btotal\b/i', $record['account_name']) && $record['account'] == ''))
                                                <div
                                                    class="account-inner d-flex align-items-center justify-content-between ps-3">

                                                    <p class="mb-2 ms-4"><a
                                                            href="{{ route('report.ledger', $record['account_id']) }}?account={{ $record['account_id'] }}"
                                                            class="text-primary">{{ $record['account_name'] }}</a>
                                                    </p>
                                                    <p class="mb-2 text-center">{{ $record['account_code'] }}</p>
                                                    <p class="text-primary mb-2 float-end text-end">
                                                        {{ currency_format_with_sym($netAmount) }}</p>
                                                </div>
                                            @endif
                                            @php
                                                if ($record['account_name'] === 'Total Income') {
                                                    $totalIncome = $netAmount;
                                                }

                                                if ($record['account_name'] == 'Total Costs of Goods Sold') {
                                                    $totalCosts = $netAmount;
                                                }
                                                $grossProfit = $totalIncome - $totalCosts;
                                            @endphp
                                        @endforeach
                                    @endif
                                @endforeach

                                <div class="account-inner d-flex align-items-center justify-content-between">
                                    <p class="fw-bold mb-2">
                                        {{ $record['account_name'] ? $record['account_name'] : '' }}
                                    </p>
                                    <p class="fw-bold mb-2 text-end">
                                        {{ $record['netAmount'] ? currency_format_with_sym($netAmount) : currency_format_with_sym(0) }}
                                    </p>
                                </div>
                            </div>
                        @endif
                    @endforeach

                    @if ($grossProfit > 0)
                        <div class="account-inner d-flex align-items-center justify-content-between border-bottom">
                            <p></p>
                            <p class="fw-bold mb-2 text-center">{{ __('Gross Profit') }}</p>
                            <p class="text-primary mb-2 float-end text-end">
                                {{ currency_format_with_sym($grossProfit) }}</p>
                        </div>
                    @endif

                    @foreach ($totalAccounts as $accounts)
                        @if ($accounts['Type'] == 'Expenses')
                            <div class="account-main-inner border-bottom py-2">
                                <p class="fw-bold mb-2">{{ $accounts['Type'] }}</p>

                                @foreach ($accounts['account'] as $records)
                                    @if ($collapseView == 'collapse')
                                        @foreach ($records as $key => $record)
                                            @php
                                                if ($record['netAmount'] > 0) {
                                                    $netAmount = $record['netAmount'];
                                                } else {
                                                    $netAmount = -$record['netAmount'];
                                                }
                                            @endphp
                                            @if ($record['account'] == 'parentTotal')
                                                <div
                                                    class="account-inner d-flex align-items-center justify-content-between ps-3">
                                                    <div class="mb-2 ms-2 account-arrow">
                                                        <div class="">
                                                            <a
                                                                href="{{ route('report.profit.loss', ['vertical', 'expand']) }}"><i
                                                                    class="ti ti-chevron-down account-icon"></i></a>
                                                        </div>
                                                        <a href="{{ route('report.ledger', $record['account_id']) }}?account={{ $record['account_id'] }}"
                                                           class="text-primary">{{ str_replace('Total ', '', $record['account_name']) }}</a>
                                                    </div>
                                                    <p class="mb-2 ms-3 text-center">
                                                        {{ $record['account_code'] }}
                                                    </p>
                                                    <p class="text-primary mb-2 float-end text-end">
                                                        {{ currency_format_with_sym($netAmount) }}
                                                    </p>
                                                </div>
                                            @endif

                                            @if (
                                                !preg_match('/\btotal\b/i', $record['account_name']) &&
                                                    $record['account'] == '')
                                                <div
                                                    class="account-inner d-flex align-items-center justify-content-between ps-3">

                                                    <p class="mb-2 ms-4"><a
                                                            href="{{ route('report.ledger', $record['account_id']) }}?account={{ $record['account_id'] }}"
                                                            class="text-primary">{{ $record['account_name'] }}</a>
                                                    </p>
                                                    <p class="mb-2 text-center">{{ $record['account_code'] }}</p>
                                                    <p class="text-primary mb-2 float-end text-end">
                                                        {{ currency_format_with_sym($netAmount) }}</p>
                                                </div>
                                            @endif
                                            @php
                                                if ($record['account_name'] === 'Total Income') {
                                                    $totalIncome = $record['netAmount'];
                                                }

                                                if ($record['account_name'] == 'Total Costs of Goods Sold') {
                                                    $totalCosts = $record['netAmount'];
                                                }
                                                $grossProfit = $totalIncome - $totalCosts;
                                            @endphp
                                        @endforeach
                                    @else
                                        @foreach ($records as $key => $record)
                                            @php
                                                if ($record['netAmount'] > 0) {
                                                    $netAmount = $record['netAmount'];
                                                } else {
                                                    $netAmount = -$record['netAmount'];
                                                }
                                            @endphp
                                            @if ($record['account'] == 'parent' || $record['account'] == 'parentTotal')
                                                <div
                                                    class="account-inner d-flex align-items-center justify-content-between ps-3">
                                                    @if ($record['account'] == 'parent')
                                                        <div class="mb-2 ms-2 account-arrow">
                                                            <div class="">
                                                                <a
                                                                    href="{{ route('report.profit.loss', ['vertical', 'collapse']) }}"><i
                                                                        class="ti ti-chevron-down account-icon"></i></a>
                                                            </div>
                                                            <a href="{{ route('report.ledger', $record['account_id']) }}?account={{ $record['account_id'] }}"
                                                               class="{{ $record['account'] == 'parent' ? 'text-primary' : 'text-dark' }} fw-bold">{{ $record['account_name'] }}</a>
                                                        </div>
                                                    @else
                                                        <p class="mb-2"><a href="#"
                                                                           class="text-dark fw-bold">{{ $record['account_name'] }}</a>
                                                        </p>
                                                    @endif
                                                    <p class="mb-2 ms-3 text-center">
                                                        {{ $record['account_code'] }}
                                                    </p>
                                                    <p class="text-dark fw-bold mb-2 float-end text-end">
                                                        {{ currency_format_with_sym($netAmount) }}
                                                    </p>
                                                </div>
                                            @endif

                                            @if (
                                                (!preg_match('/\btotal\b/i', $record['account_name']) && $record['account'] == '') ||
                                                    $record['account'] == 'subAccount')
                                                <div
                                                    class="account-inner d-flex align-items-center justify-content-between ps-3">

                                                    <p class="mb-2 ms-4"><a
                                                            href="{{ route('report.ledger', $record['account_id']) }}?account={{ $record['account_id'] }}"
                                                            class="text-primary">{{ $record['account_name'] }}</a>
                                                    </p>
                                                    <p class="mb-2 text-center">{{ $record['account_code'] }}</p>
                                                    <p class="text-primary mb-2 float-end text-end">
                                                        {{ currency_format_with_sym($netAmount) }}</p>
                                                </div>
                                            @endif
                                            @php

                                                if ($record['account_name'] == 'Total Expenses') {
                                                    $totalCosts = $netAmount;
                                                }
                                                $netProfit = $grossProfit - $totalCosts;
                                            @endphp
                                        @endforeach
                                    @endif
                                @endforeach

                                <div class="account-inner d-flex align-items-center justify-content-between">
                                    <p class="fw-bold mb-2">
                                        {{ $record['account_name'] ? $record['account_name'] : '' }}
                                    </p>
                                    <p class="fw-bold mb-2 text-end">
                                        {{ $record['netAmount'] ? currency_format_with_sym($netAmount) : currency_format_with_sym(0) }}
                                    </p>
                                </div>
                            </div>

                            <div class="account-inner d-flex align-items-center justify-content-between border-bottom">
                                <p></p>
                                <p class="fw-bold mb-2 text-center">{{ __('Net Profit/Loss') }}</p>
                                <p class="text-primary mb-2 float-end text-end">
                                    {{ currency_format_with_sym($netProfit) }}</p>
                            </div>
                        @endif
                    @endforeach

                </div>
            </div>
        </div>
    </div>
@endsection
