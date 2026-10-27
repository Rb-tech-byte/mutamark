@php
    $color = !empty(company_setting('color')) ? company_setting('color') : 'theme-3'
@endphp
<html lang="en" dir="{{ company_setting('site_rtl') == 'on' ? 'rtl' : '' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="{{ asset('assets/css/plugins/main.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/plugins/style.css') }}">
    <!-- font css -->
    <link rel="stylesheet" href="{{ asset('assets/fonts/tabler-icons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/fonts/feather.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/fonts/fontawesome.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/fonts/material.css') }}">
    <!-- vendor css -->
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}" id="main-style-link">
    <link rel="stylesheet" href="{{ asset('Modules/DoubleEntry/Resources/assets/css/app.css') }}" id="main-style-link">
    <title>{{env('APP_NAME')}} - {{__('Payable Report')}}</title>
    @if (company_setting('site_rtl') == 'on')
        <link rel="stylesheet" href="{{ asset('assets/css/style-rtl.css') }}">
    @endif

</head>
<script src="{{ asset('js/jquery.min.js') }}"></script>
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

<script>
    $(document).ready(function () {
        $("#filter").click(function () {
            $("#show_filter").toggle();
        });
    });
</script>

<script>
    window.print();
    window.onafterprint = back;

    function back() {
        window.close();
        window.history.back();
    }
</script>

<body class="{{ $color }}">
<div class="mt-4">
    @php
        $authUser = creatorId();
        $user = App\Models\User::find($authUser);
    @endphp
    <div class="row">
        <div class="col-12" id="invoice-container">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="tab-content" id="myTabContent2">
                                @if($reportName  == '#vendor_balance')
                                    <table class="table table-flush" id="report-dataTable">
                                        <thead>
                                        <tr>
                                            <th width="33%"> {{ __('Vendor Name') }}</th>
                                            <th width="33%"> {{ __('Billed Amount') }}</th>
                                            <th width="33%"> {{ __('Available Debit') }}</th>
                                            <th class="text-end"> {{ __('Closing Balance') }}</th>
                                        </tr>
                                        </thead>

                                        <tbody>
                                        @php
                                            $mergedArray = [];
                                            foreach ($payableVendors as $item) {
                                                $name = $item['name'];

                                                if (!isset($mergedArray[$name])) {
                                                    $mergedArray[$name] = [
                                                        'name' => $name,
                                                        'price' => 0.0,
                                                        'pay_price' => 0.0,
                                                        'total_tax' => 0.0,
                                                        'debit_price' => 0.0,
                                                    ];
                                                }

                                                $mergedArray[$name]['price'] += floatval($item['price']);
                                                if ($item['pay_price'] !== null) {
                                                    $mergedArray[$name]['pay_price'] += floatval($item['pay_price']);
                                                }
                                                $mergedArray[$name]['total_tax'] += floatval($item['total_tax']);
                                                $mergedArray[$name]['debit_price'] += floatval($item['debit_price']);
                                            }
                                            $resultArray = array_values($mergedArray);
                                            $total = 0;
                                        @endphp
                                        @foreach ($resultArray as $payableVendor)
                                            <tr>
                                                @php
                                                    $vendorBalance = $payableVendor['price'] + $payableVendor['total_tax'] - $payableVendor['pay_price'];
                                                    $balance = $vendorBalance - $payableVendor['debit_price'];
                                                    $total += $balance;
                                                @endphp
                                                <td> {{ $payableVendor['name'] }}</td>
                                                <td> {{ currency_format_with_sym($vendorBalance) }} </td>
                                                <td> {{ !empty($payableVendor['debit_price']) ? currency_format_with_sym($payableVendor['debit_price']) : currency_format_with_sym(0) }}
                                                </td>
                                                <td class="text-end"> {{ currency_format_with_sym($balance) }} </td>
                                            </tr>
                                        @endforeach
                                        @if ($payableVendors != [])
                                            <tr>
                                                <th>{{ __('Total') }}</th>
                                                <td></td>
                                                <td></td>
                                                <th class="text-end">{{ currency_format_with_sym($total) }}</th>
                                            </tr>
                                        @endif
                                        </tbody>
                                    </table>
                                @elseif($reportName == '#payable_summary')
                                    <table class="table table-flush" id="report-dataTable">
                                        <thead>
                                        <tr>
                                            <th>{{ __('Vendor Name') }}</th>
                                            <th>{{ __('Date') }}</th>
                                            <th>{{ __('Transaction') }}</th>
                                            <th>{{ __('Status') }}</th>
                                            <th>{{ __('Transaction Type') }}</th>
                                            <th>{{ __('Total') }}</th>
                                            <th>{{ __('Balance') }}</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @php
                                            $total = 0;
                                            $totalAmount = 0;
                                            function compare($a, $b)
                                            {
                                                return strtotime($b['bill_date']) - strtotime($a['bill_date']);
                                            }
                                            usort($payableSummaries, 'compare');
                                        @endphp
                                        @foreach ($payableSummaries as $payableSummary)
                                            <tr>
                                                @php
                                                    if ($payableSummary['bill']) {
                                                        $payableBalance = $payableSummary['price'] + $payableSummary['total_tax'];
                                                    } else {
                                                        $payableBalance = -$payableSummary['price'];
                                                    }
                                                    $pay_price = ($payableSummary['pay_price'] != null) ? $payableSummary['pay_price'] : 0;
                                                    $balance = $payableBalance - $pay_price;
                                                    $total += $balance;
                                                    $totalAmount += $payableBalance;
                                                @endphp
                                                <td> {{ $payableSummary['name'] }}</td>
                                                <td> {{ $payableSummary['bill_date'] }}</td>
                                                @if ($payableSummary['bill'])
                                                    <td> {{  Modules\Account\Entities\Bill::billNumberFormat($payableSummary['bill']) }}
                                                    </td>
                                                @else
                                                    <td>{{ __('Debit Note') }}</td>
                                                @endif
                                                <td>
                                                    @if ($payableSummary['status'] == 0)
                                                        <span
                                                            class="">{{ __(\App\Models\Invoice::$statues[$payableSummary['status']]) }}</span>
                                                    @elseif($payableSummary['status'] == 1)
                                                        <span
                                                            class="">{{ __(\App\Models\Invoice::$statues[$payableSummary['status']]) }}</span>
                                                    @elseif($payableSummary['status'] == 2)
                                                        <span
                                                            class="">{{ __(\App\Models\Invoice::$statues[$payableSummary['status']]) }}</span>
                                                    @elseif($payableSummary['status'] == 3)
                                                        <span
                                                            class="">{{ __(\App\Models\Invoice::$statues[$payableSummary['status']]) }}</span>
                                                    @elseif($payableSummary['status'] == 4)
                                                        <span
                                                            class="">{{ __(\App\Models\Invoice::$statues[$payableSummary['status']]) }}</span>
                                                    @else
                                                        <span class="p-2 px-3">-</span>
                                                    @endif
                                                </td>
                                                @if ($payableSummary['bill'])
                                                    <td>{{ __('Bill') }}</td>
                                                @else
                                                    <td>{{ __('Debit Note') }}</td>
                                                @endif
                                                <td> {{ currency_format_with_sym($payableBalance) }} </td>
                                                <td> {{ currency_format_with_sym($balance) }} </td>
                                            </tr>
                                        @endforeach
                                        @if ($payableSummaries != [])
                                            <tr>
                                                <th>{{ __('Total') }}</th>
                                                <th></th>
                                                <th></th>
                                                <th></th>
                                                <th></th>
                                                <th>{{ currency_format_with_sym($totalAmount) }}</th>
                                                <th>{{ currency_format_with_sym($total) }}</th>
                                            </tr>
                                        @endif
                                        </tbody>
                                    </table>
                                @elseif($reportName == '#payable_details')
                                    <table class="table table-flush" id="report-dataTable">
                                        <thead>
                                        <tr>
                                            <th>{{ __('Vendor Name') }}</th>
                                            <th>{{ __('Date') }}</th>
                                            <th>{{ __('Transaction') }}</th>
                                            <th>{{ __('Status') }}</th>
                                            <th>{{ __('Transaction Type') }}</th>
                                            <th>{{ __('Item Name') }}</th>
                                            <th>{{ __('Quantity Ordered') }}</th>
                                            <th>{{ __('Item Price') }}</th>
                                            <th>{{ __('Total') }}</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @php
                                            $total = 0;
                                            $totalQuantity = 0;
                                            function compares($a, $b)
                                            {
                                                return strtotime($b['bill_date']) - strtotime($a['bill_date']);
                                            }
                                            usort($payableDetails, 'compares');
                                        @endphp
                                        @foreach ($payableDetails as $payableDetail)
                                            <tr>
                                                @php
                                                    if ($payableDetail['bill']) {
                                                        $receivableBalance = $payableDetail['price'];
                                                    } else {
                                                        $receivableBalance = -$payableDetail['price'];
                                                    }
                                                    if ($payableDetail['bill']) {
                                                        $quantity = $payableDetail['quantity'];
                                                    }
                                                    else {
                                                        $quantity = 0;
                                                    }

                                                    if ($payableDetail['bill']) {
                                                        $itemTotal = $receivableBalance * $payableDetail['quantity'];
                                                    } else {
                                                        $itemTotal = -$payableDetail['price'];
                                                    }

                                                    $total += $itemTotal;
                                                    $totalQuantity += $quantity;
                                                @endphp
                                                <td> {{ $payableDetail['name'] }}</td>
                                                <td> {{ $payableDetail['bill_date'] }}</td>
                                                @if ($payableDetail['bill'])
                                                    <td> {{ Modules\Account\Entities\Bill::billNumberFormat($payableDetail['bill']) }}
                                                    </td>
                                                @else
                                                    <td>{{ __('Debit Note') }}</td>
                                                @endif
                                                <td>
                                                    @if ($payableDetail['status'] == 0)
                                                        <span
                                                            class="">{{ __(\App\Models\Invoice::$statues[$payableDetail['status']]) }}</span>
                                                    @elseif($payableDetail['status'] == 1)
                                                        <span
                                                            class="">{{ __(\App\Models\Invoice::$statues[$payableDetail['status']]) }}</span>
                                                    @elseif($payableDetail['status'] == 2)
                                                        <span
                                                            class="">{{ __(\App\Models\Invoice::$statues[$payableDetail['status']]) }}</span>
                                                    @elseif($payableDetail['status'] == 3)
                                                        <span
                                                            class="">{{ __(\App\Models\Invoice::$statues[$payableDetail['status']]) }}</span>
                                                    @elseif($payableDetail['status'] == 4)
                                                        <span
                                                            class="">{{ __(\App\Models\Invoice::$statues[$payableDetail['status']]) }}</span>
                                                    @else
                                                        <span
                                                            class="p-2 px-3">-</span>
                                                    @endif
                                                </td>
                                                @if ($payableDetail['bill'])
                                                    <td>{{ __('Bill') }}</td>
                                                @else
                                                    <td>{{ __('Debit Note') }}</td>
                                                @endif
                                                <td>{{ $payableDetail['product_name'] }}</td>
                                                <td> {{ $quantity }}</td>
                                                <td>{{ currency_format_with_sym($receivableBalance) }}</td>
                                                <td>{{ currency_format_with_sym($itemTotal) }}</td>

                                            </tr>
                                        @endforeach
                                        @if ($payableDetails != [])
                                            <tr>
                                                <th>{{ __('Total') }}</th>
                                                <th></th>
                                                <th></th>
                                                <th></th>
                                                <th></th>
                                                <th></th>
                                                <th>{{ $totalQuantity }}</th>
                                                <th></th>
                                                <th>{{ currency_format_with_sym($total) }}</th>
                                            </tr>
                                        @endif
                                        </tbody>
                                    </table>
                                @elseif($reportName == '#aging_summary')
                                    <table class="table table-flush" id="report-dataTable">
                                        <thead>
                                        <tr>
                                            <th>{{ __('Vendor Name') }}</th>
                                            <th>{{ __('Current') }}</th>
                                            <th>{{ __('1-15 DAYS') }}</th>
                                            <th>{{ __('16-30 DAYS') }}</th>
                                            <th>{{ __('31-45 DAYS') }}</th>
                                            <th>{{ __('> 45 DAYS') }}</th>
                                            <th>{{ __('Total') }}</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @php
                                            $currentTotal = 0;
                                            $days15 = 0;
                                            $days30 = 0;
                                            $days45 = 0;
                                            $daysMore45 = 0;
                                            $total = 0;
                                        @endphp
                                        @foreach ($agingSummaries as $key => $agingSummary)
                                            <tr>
                                                <td> {{ $key }}</td>
                                                <td>{{ currency_format_with_sym($agingSummary['current']) }}</td>
                                                <td>{{ currency_format_with_sym($agingSummary['1_15_days']) }}</td>
                                                <td>{{ currency_format_with_sym($agingSummary['16_30_days']) }}</td>
                                                <td>{{ currency_format_with_sym($agingSummary['31_45_days']) }}</td>
                                                <td>{{ currency_format_with_sym($agingSummary['greater_than_45_days']) }}</td>
                                                <td>{{ currency_format_with_sym($agingSummary['total_due']) }}</td>
                                            </tr>
                                            @php
                                                $currentTotal += $agingSummary['current'];
                                                $days15 += $agingSummary['1_15_days'];
                                                $days30 += $agingSummary['16_30_days'];
                                                $days45 += $agingSummary['31_45_days'];
                                                $daysMore45 += $agingSummary['greater_than_45_days'];
                                                $total += $agingSummary['total_due'];

                                            @endphp
                                        @endforeach
                                        @if ($agingSummaries != [])
                                            <tr>
                                                <th>{{ __('Total') }}</th>
                                                <th>{{ currency_format_with_sym($currentTotal) }}</th>
                                                <th>{{ currency_format_with_sym($days15) }}</th>
                                                <th>{{ currency_format_with_sym($days30) }}</th>
                                                <th>{{ currency_format_with_sym($days45) }}</th>
                                                <th>{{ currency_format_with_sym($daysMore45) }}</th>
                                                <th>{{ currency_format_with_sym($total) }}</th>
                                            </tr>
                                        @endif
                                        </tbody>
                                    </table>
                                @else
                                    <table class="table table-flush" id="report-dataTable">
                                        <thead>
                                        <tr>
                                            <th>{{ __('Date') }}</th>
                                            <th>{{ __('Transaction') }}</th>
                                            <th>{{ __('Type') }}</th>
                                            <th>{{ __('Status') }}</th>
                                            <th>{{ __('Vendor Name') }}</th>
                                            <th>{{ __('Age') }}</th>
                                            <th>{{ __('Amount') }}</th>
                                            <th>{{ __('Balance Due') }}</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @php
                                            $currentTotal = 0;
                                            $days15 = 0;
                                            $days30 = 0;
                                            $days45 = 0;
                                            $daysMore45 = 0;
                                            $total = 0;
                                        @endphp
                                        @php
                                            $currentTotal = 0;
                                            $currentDue = 0;
                                            $days15Total = 0;
                                            $days15Due = 0;

                                            $days30Total = 0;
                                            $days30Due = 0;

                                            $days45Total = 0;
                                            $days45Due = 0;

                                            $daysMore45Total = 0;
                                            $daysMore45Due = 0;

                                            $total = 0;
                                        @endphp

                                        @if ($moreThan45 != [])
                                            <tr>
                                                <th>{{ __(' > 45 Days') }}</th>
                                            </tr>
                                        @endif
                                        @foreach ($moreThan45 as $value)
                                            @php
                                                $daysMore45Total += $value['total_price'];
                                                $daysMore45Due += $value['balance_due'];
                                            @endphp
                                            <tr>
                                                <td>{{ $value['due_date'] }}</td>
                                                <td>{{ Modules\Account\Entities\Bill::billNumberFormat($value['bill_id']) }}
                                                </td>
                                                <td>{{ __('Bill') }}</td>
                                                <td>
                                                    @if ($value['status'] == 0)
                                                        <span
                                                            class="">{{ __(Modules\Account\Entities\Bill::$statues[$value['status']]) }}</span>
                                                    @elseif($value['status'] == 1)
                                                        <span
                                                            class="">{{ __(Modules\Account\Entities\Bill::$statues[$value['status']]) }}</span>
                                                    @elseif($value['status'] == 2)
                                                        <span
                                                            class="">{{ __(Modules\Account\Entities\Bill::$statues[$value['status']]) }}</span>
                                                    @elseif($value['status'] == 3)
                                                        <span
                                                            class="">{{ __(Modules\Account\Entities\Bill::$statues[$value['status']]) }}</span>
                                                    @elseif($value['status'] == 4)
                                                        <span
                                                            class="">{{ __(Modules\Account\Entities\Bill::$statues[$value['status']]) }}</span>
                                                    @endif
                                                </td>
                                                <td>{{ $value['name'] }}</td>
                                                <td> {{ $value['age'] . __(' Days') }} </td>
                                                <td>{{ currency_format_with_sym($value['total_price']) }}</td>
                                                <td>{{ currency_format_with_sym($value['balance_due']) }}</td>
                                            </tr>
                                        @endforeach
                                        @if ($moreThan45 != [])
                                            <tr>
                                                <th></th>
                                                <th></th>
                                                <th></th>
                                                <th></th>
                                                <th></th>
                                                <th></th>
                                                <th>{{ currency_format_with_sym($daysMore45Total) }}</th>
                                                <th>{{ currency_format_with_sym($daysMore45Due) }}</th>
                                            </tr>
                                        @endif

                                        @if ($days31to45 != [])
                                            <tr>
                                                <th>{{ __(' 31 to 45 Days') }}</th>
                                            </tr>
                                        @endif
                                        @foreach ($days31to45 as $day31to45)
                                            @php
                                                $days45Total += $day31to45['total_price'];
                                                $days45Due += $day31to45['balance_due'];
                                            @endphp
                                            <tr>
                                                <td>{{ $day31to45['due_date'] }}</td>
                                                <td>{{ Modules\Account\Entities\Bill::billNumberFormat($day31to45['bill_id']) }}
                                                </td>
                                                <td>{{ __('Bill') }}</td>
                                                <td>
                                                    @if ($day31to45['status'] == 0)
                                                        <span
                                                            class="">{{ __(Modules\Account\Entities\Bill::$statues[$day31to45['status']]) }}</span>
                                                    @elseif($day31to45['status'] == 1)
                                                        <span
                                                            class="">{{ __(Modules\Account\Entities\Bill::$statues[$day31to45['status']]) }}</span>
                                                    @elseif($day31to45['status'] == 2)
                                                        <span
                                                            class="">{{ __(Modules\Account\Entities\Bill::$statues[$day31to45['status']]) }}</span>
                                                    @elseif($day31to45['status'] == 3)
                                                        <span
                                                            class="">{{ __(Modules\Account\Entities\Bill::$statues[$day31to45['status']]) }}</span>
                                                    @elseif($day31to45['status'] == 4)
                                                        <span
                                                            class="">{{ __(Modules\Account\Entities\Bill::$statues[$day31to45['status']]) }}</span>
                                                    @endif
                                                </td>
                                                <td>{{ $day31to45['name'] }}</td>
                                                <td> {{ $day31to45['age'] . __(' Days') }} </td>
                                                <td>{{ currency_format_with_sym($day31to45['total_price']) }}</td>
                                                <td>{{ currency_format_with_sym($day31to45['balance_due']) }}</td>
                                            </tr>
                                        @endforeach
                                        @if ($days31to45 != [])
                                            <tr>
                                                <th></th>
                                                <th></th>
                                                <th></th>
                                                <th></th>
                                                <th></th>
                                                <th></th>
                                                <th>{{ currency_format_with_sym($days45Total) }}</th>
                                                <th>{{ currency_format_with_sym($days45Due) }}</th>
                                            </tr>
                                        @endif

                                        @if ($days16to30 != [])
                                            <tr>
                                                <th>{{ __(' 16 to 30 Days') }}</th>
                                            </tr>
                                        @endif
                                        @foreach ($days16to30 as $day16to30)
                                            @php
                                                $days30Total += $day16to30['total_price'];
                                                $days30Due += $day16to30['balance_due'];
                                            @endphp
                                            <tr>
                                                <td>{{ $day16to30['due_date'] }}</td>
                                                <td>{{ Modules\Account\Entities\Bill::billNumberFormat($day16to30['bill_id']) }}
                                                </td>
                                                <td>{{ __('Bill') }}</td>
                                                <td>
                                                    @if ($day16to30['status'] == 0)
                                                        <span
                                                            class="">{{ __(Modules\Account\Entities\Bill::$statues[$day16to30['status']]) }}</span>
                                                    @elseif($day16to30['status'] == 1)
                                                        <span
                                                            class="">{{ __(Modules\Account\Entities\Bill::$statues[$day16to30['status']]) }}</span>
                                                    @elseif($day16to30['status'] == 2)
                                                        <span
                                                            class="">{{ __(Modules\Account\Entities\Bill::$statues[$day16to30['status']]) }}</span>
                                                    @elseif($day16to30['status'] == 3)
                                                        <span
                                                            class="">{{ __(Modules\Account\Entities\Bill::$statues[$day16to30['status']]) }}</span>
                                                    @elseif($day16to30['status'] == 4)
                                                        <span
                                                            class="">{{ __(Modules\Account\Entities\Bill::$statues[$day16to30['status']]) }}</span>
                                                    @endif
                                                </td>
                                                <td>{{ $day16to30['name'] }}</td>
                                                <td> {{ $day16to30['age'] . __(' Days') }} </td>
                                                <td>{{ currency_format_with_sym($day16to30['total_price']) }}</td>
                                                <td>{{ currency_format_with_sym($day16to30['balance_due']) }}</td>
                                            </tr>
                                        @endforeach
                                        @if ($days16to30 != [])
                                            <tr>
                                                <th></th>
                                                <th></th>
                                                <th></th>
                                                <th></th>
                                                <th></th>
                                                <th></th>
                                                <th>{{ currency_format_with_sym($days30Total) }}</th>
                                                <th>{{ currency_format_with_sym($days30Due) }}</th>
                                            </tr>
                                        @endif

                                        @if ($days1to15 != [])
                                            <tr>
                                                <th>{{ __(' 1 to 15 Days') }}</th>
                                            </tr>
                                        @endif
                                        @foreach ($days1to15 as $day1to15)
                                            @php
                                                $days15Total += $day1to15['total_price'];
                                                $days15Due += $day1to15['balance_due'];
                                            @endphp
                                            <tr>
                                                <td>{{ $day1to15['due_date'] }}</td>
                                                <td>{{ Modules\Account\Entities\Bill::billNumberFormat($day1to15['bill_id']) }}
                                                </td>
                                                <td>{{ __('Bill') }}</td>
                                                <td>
                                                    @if ($day1to15['status'] == 0)
                                                        <span
                                                            class="">{{ __(Modules\Account\Entities\Bill::$statues[$day1to15['status']]) }}</span>
                                                    @elseif($day1to15['status'] == 1)
                                                        <span
                                                            class="">{{ __(Modules\Account\Entities\Bill::$statues[$day1to15['status']]) }}</span>
                                                    @elseif($day1to15['status'] == 2)
                                                        <span
                                                            class="">{{ __(Modules\Account\Entities\Bill::$statues[$day1to15['status']]) }}</span>
                                                    @elseif($day1to15['status'] == 3)
                                                        <span
                                                            class="">{{ __(Modules\Account\Entities\Bill::$statues[$day1to15['status']]) }}</span>
                                                    @elseif($day1to15['status'] == 4)
                                                        <span
                                                            class="">{{ __(Modules\Account\Entities\Bill::$statues[$day1to15['status']]) }}</span>
                                                    @endif
                                                </td>
                                                <td>{{ $day1to15['name'] }}</td>
                                                <td> {{ $day1to15['age'] . __(' Days') }} </td>
                                                <td>{{ currency_format_with_sym($day1to15['total_price']) }}</td>
                                                <td>{{ currency_format_with_sym($day1to15['balance_due']) }}</td>
                                            </tr>
                                        @endforeach
                                        @if ($days1to15 != [])
                                            <tr>
                                                <th></th>
                                                <th></th>
                                                <th></th>
                                                <th></th>
                                                <th></th>
                                                <th></th>
                                                <th>{{ currency_format_with_sym($days15Total) }}</th>
                                                <th>{{ currency_format_with_sym($days15Due) }}</th>
                                            </tr>
                                        @endif

                                        @if ($currents != [])
                                            <tr>
                                                <th>{{ __('Current') }}</th>
                                            </tr>
                                        @endif
                                        @foreach ($currents as $current)
                                            @php
                                                $currentTotal += $current['total_price'];
                                                $currentDue += $current['balance_due'];
                                            @endphp
                                            <tr>
                                                <td>{{ $current['due_date'] }}</td>
                                                <td>{{ Modules\Account\Entities\Bill::billNumberFormat($current['bill_id']) }}
                                                </td>
                                                <td>{{ __('Bill') }}</td>
                                                <td>
                                                    @if ($current['status'] == 0)
                                                        <span
                                                            class="">{{ __(Modules\Account\Entities\Bill::$statues[$current['status']]) }}</span>
                                                    @elseif($current['status'] == 1)
                                                        <span
                                                            class="">{{ __(Modules\Account\Entities\Bill::$statues[$current['status']]) }}</span>
                                                    @elseif($current['status'] == 2)
                                                        <span
                                                            class="">{{ __(Modules\Account\Entities\Bill::$statues[$current['status']]) }}</span>
                                                    @elseif($current['status'] == 3)
                                                        <span
                                                            class="">{{ __(Modules\Account\Entities\Bill::$statues[$current['status']]) }}</span>
                                                    @elseif($current['status'] == 4)
                                                        <span
                                                            class="">{{ __(Modules\Account\Entities\Bill::$statues[$current['status']]) }}</span>
                                                    @endif
                                                </td>
                                                <td>{{ $current['name'] }}</td>
                                                <td> - </td>
                                                <td>{{ currency_format_with_sym($current['total_price']) }}</td>
                                                <td>{{ currency_format_with_sym($current['balance_due']) }}</td>
                                            </tr>
                                        @endforeach
                                        @if ($currents != [])
                                            <tr>
                                                <th></th>
                                                <th></th>
                                                <th></th>
                                                <th></th>
                                                <th></th>
                                                <th></th>
                                                <th>{{ currency_format_with_sym($currentTotal) }}</th>
                                                <th>{{ currency_format_with_sym($currentDue) }}</th>
                                            </tr>
                                        @endif

                                        @if ($currents != [] || $days1to15 != [] || $days16to30 != [] || $days31to45 != [] || $moreThan45 != [])
                                            <tr>
                                                <th>{{ __('Total') }}</th>
                                                <th></th>
                                                <th></th>
                                                <th></th>
                                                <th></th>
                                                <th></th>
                                                <th>{{ currency_format_with_sym($currentTotal + $days15Total + $days30Total + $days45Total + $daysMore45Total) }}
                                                </th>
                                                <th>{{ currency_format_with_sym($currentDue + $days15Due + $days30Due + $days45Due + $daysMore45Due) }}
                                                </th>
                                            </tr>
                                        @endif

                                        </tbody>
                                    </table>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>

</html>
