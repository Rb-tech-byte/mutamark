<?php

namespace Modules\DoubleEntry\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceProduct;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Account\Entities\AccountUtility;
use Modules\Account\Entities\Bill;
use Modules\Account\Entities\ChartOfAccount;
use Modules\Account\Entities\ChartOfAccountParent;
use Modules\Account\Entities\ChartOfAccountSubType;
use Modules\Account\Entities\ChartOfAccountType;
use Modules\Account\Entities\Customer;
use Modules\Account\Entities\Vender;
use Modules\DoubleEntry\Entities\JournalItem;
use Modules\Account\Entities\TransactionLines;

class ReportController extends Controller
{
    public function yearMonth()
    {
        $month[] = __('January');
        $month[] = __('February');
        $month[] = __('March');
        $month[] = __('April');
        $month[] = __('May');
        $month[] = __('June');
        $month[] = __('July');
        $month[] = __('August');
        $month[] = __('September');
        $month[] = __('October');
        $month[] = __('November');
        $month[] = __('December');
        return $month;
    }

    public function yearList()
    {
        $starting_year = date('Y', strtotime('-5 year'));
        $ending_year = date('Y');
        foreach (range($ending_year, $starting_year) as $year) {
            $years[$year] = $year;
        }
        return $years;
    }

    public function ledgerReport(Request $request, $account = '')
    {

        if (Auth::user()->isAbleTo('report ledger')) {

            if (!empty($request->start_date) && !empty($request->end_date)) {
                $start = $request->start_date;
                $end = $request->end_date;
            } else {
                $start = date('Y-m-01');
                $end = date('Y-m-t');
            }
            if (!empty($request->account)) {
                $chart_accounts = ChartOfAccount::where('id', $request->account)->where('created_by', creatorId())->get();
                $accounts = ChartOfAccount::select('chart_of_accounts.id', 'chart_of_accounts.code', 'chart_of_accounts.name', 'chart_of_accounts.parent')
                    ->where('parent', '=', 0)->where('workspace', getActiveWorkSpace())
                    ->where('created_by', creatorId())->get()
                    ->toarray();

            } else {
                $chart_accounts = ChartOfAccount::where('created_by', creatorId())->get();
                $accounts = ChartOfAccount::select('chart_of_accounts.id', 'chart_of_accounts.code', 'chart_of_accounts.name', 'chart_of_accounts.parent')
                    ->where('parent', '=', 0)->where('workspace', getActiveWorkSpace())
                    ->where('created_by', creatorId())->get()
                    ->toarray();
            }

            $subAccounts = ChartOfAccount::select('chart_of_accounts.id', 'chart_of_accounts.code', 'chart_of_accounts.name', 'chart_of_account_parents.account');
            $subAccounts->leftjoin('chart_of_account_parents', 'chart_of_accounts.parent', 'chart_of_account_parents.id');
            $subAccounts->where('chart_of_accounts.parent', '!=', 0);
            $subAccounts->where('chart_of_accounts.created_by', creatorId());
            $subAccounts->where('chart_of_accounts.workspace', getActiveWorkSpace());
            $subAccounts = $subAccounts->get()->toArray();

            $balance = 0;
            $debit = 0;
            $credit = 0;
            $filter['balance'] = $balance;
            $filter['credit'] = $credit;
            $filter['debit'] = $debit;
            $filter['startDateRange'] = $start;
            $filter['endDateRange'] = $end;
            return view('doubleentry::report.ledger', compact('filter', 'accounts', 'chart_accounts', 'subAccounts'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function balanceSheet(Request $request, $view = '', $collapseview = 'expand')
    {
        if (Auth::user()->isAbleTo('report balance sheet')) {
            if (!empty($request->start_date) && !empty($request->end_date)) {
                $start = $request->start_date;
                $end = $request->end_date;
            } else {
                $start = NULL;
                $end = NULL;
            }

            $types = ChartOfAccountType::where('workspace', getActiveWorkSpace())->where('created_by', creatorId())->whereIn('name', ['Assets', 'Liabilities', 'Equity'])->get();
            $totalAccounts = [];
            foreach ($types as $type) {
                $subTypes = ChartOfAccountSubType::where('type', $type->id)->get();

                $subTypeArray = [];
                foreach ($subTypes as $subType) {
                    $parentAccounts = ChartOfAccountParent::where('workspace', getActiveWorkSpace())->where('sub_type', $subType->id)->get();
                    // $parentAccountArray = [];
                    // $parentAccountArrayTotal = [];

                    $totalParentAccountArray = [];
                    if ($parentAccounts->isNotEmpty()) {
                        foreach ($parentAccounts as $parentAccount) {
                            $totalArray = [];
                            $parentAccountArray = [];
                            $parentAccountArrayTotal = [];

                            $parentAccs = TransactionLines::select('chart_of_accounts.id', 'chart_of_accounts.code', 'chart_of_accounts.name', \DB::raw('sum(debit) as totalDebit'), \DB::raw('sum(credit) as totalCredit'));
                            $parentAccs->leftjoin('chart_of_accounts', 'transaction_lines.account_id', 'chart_of_accounts.id');
                            $parentAccs->leftjoin('chart_of_account_types', 'chart_of_accounts.type', 'chart_of_account_types.id');
                            $parentAccs->where('chart_of_accounts.type', $type->id);
                            $parentAccs->where('chart_of_accounts.sub_type', $subType->id);
                            $parentAccs->where('chart_of_accounts.name', $parentAccount->name);
                            $parentAccs->where('transaction_lines.workspace', getActiveWorkSpace());
                            $parentAccs->where('transaction_lines.created_by', creatorId());
                            if ($start && $end) {
                                $parentAccs->where('transaction_lines.date', '>=', $start);
                                $parentAccs->where('transaction_lines.date', '<=', $end);
                            }
                            $parentAccs->groupBy('account_id');
                            $parentAccs = $parentAccs->get()->toArray();

                            $accounts = TransactionLines::select('chart_of_accounts.id', 'chart_of_accounts.code', 'chart_of_accounts.name', \DB::raw('sum(debit) as totalDebit'), \DB::raw('sum(credit) as totalCredit'));
                            $accounts->leftjoin('chart_of_accounts', 'transaction_lines.account_id', 'chart_of_accounts.id');
                            $accounts->where('chart_of_accounts.type', $type->id);
                            $accounts->where('chart_of_accounts.sub_type', $subType->id);
                            $accounts->where('chart_of_accounts.parent', $parentAccount->id);
                            $accounts->where('transaction_lines.workspace', getActiveWorkSpace());
                            $accounts->where('transaction_lines.created_by', creatorId());
                            if ($start && $end) {
                                $accounts->where('transaction_lines.date', '>=', $start);
                                $accounts->where('transaction_lines.date', '<=', $end);
                            }
                            $accounts->groupBy('account_id');
                            $accounts = $accounts->get()->toArray();

                            if ($parentAccs == [] && $accounts != []) {

                                $parentAccs = ChartOfAccount::select('chart_of_accounts.id', 'chart_of_accounts.code', 'chart_of_accounts.name', \DB::raw('0 as totalDebit'), \DB::raw('0 as totalCredit'));
                                $parentAccs->leftjoin('chart_of_account_parents', 'chart_of_accounts.id', 'chart_of_account_parents.account');
                                $parentAccs->where('chart_of_accounts.type', $type->id);
                                $parentAccs->where('chart_of_accounts.sub_type', $subType->id);
                                $parentAccs->where('chart_of_accounts.name', $parentAccount->name);
                                $parentAccs->where('chart_of_accounts.workspace', getActiveWorkSpace());
                                $parentAccs->where('chart_of_accounts.created_by', creatorId());
                                $parentAccs = $parentAccs->get()->toArray();
                            }

                            if ($parentAccs != [] && $accounts == []) {

                                $parentAccs = [];
                            }

                            $parenttotalBalance = 0;
                            $parentcreditTotal = 0;
                            $parenntdebitTotal = 0;
                            $parenttotalAmount = 0;

                            foreach ($parentAccs as $account) {
                                $Balance = $account['totalCredit'] - $account['totalDebit'];
                                $parenttotalBalance += $Balance;

                                $data = [
                                    'account_id' => $account['id'],
                                    'account_code' => $account['code'],
                                    'account_name' => $account['name'],
                                    'account' => 'parent',
                                    'totalCredit' => 0,
                                    'totalDebit' => 0,
                                    'netAmount' => $Balance,
                                ];

                                $parentAccountArray[] = $data;
                                $parentcreditTotal += $data['totalCredit'];
                                $parenntdebitTotal += $data['totalDebit'];
                                $parenttotalAmount += $data['netAmount'];
                            }

                            foreach ($accounts as $account) {
                                $Balance = $account['totalCredit'] - $account['totalDebit'];
                                $parenttotalBalance += $Balance;

                                if ($Balance != 0) {
                                    $data = [
                                        'account_id' => $account['id'],
                                        'account_code' => $account['code'],
                                        'account_name' => $account['name'],
                                        'account' => 'subAccount',
                                        'totalCredit' => 0,
                                        'totalDebit' => 0,
                                        'netAmount' => $Balance,
                                    ];

                                    $parentAccountArray[] = $data;
                                    $parentcreditTotal += $data['totalCredit'];
                                    $parenntdebitTotal += $data['totalDebit'];
                                    $parenttotalAmount += $data['netAmount'];
                                }
                            }

                            if (!empty($parentAccountArray)) {
                                $dataTotal = [
                                    'account_id' => $parentAccount->account,
                                    'account_code' => '',
                                    'account' => 'parentTotal',
                                    'account_name' => 'Total ' . $parentAccount->name,
                                    'totalCredit' => $parentcreditTotal,
                                    'totalDebit' => $parenntdebitTotal,
                                    'netAmount' => $parenttotalAmount,
                                ];

                                $parentAccountArrayTotal[] = $dataTotal;
                                $totalArray = array_merge($parentAccountArray, $parentAccountArrayTotal);
                                $totalParentAccountArray[] = $totalArray;
                            }

                        }

                    }
                    if ($totalParentAccountArray != []) {
                        $accounts = TransactionLines::select('chart_of_accounts.id', 'chart_of_accounts.code', 'chart_of_accounts.name', \DB::raw('sum(debit) as totalDebit'), \DB::raw('sum(credit) as totalCredit'));
                        $accounts->leftjoin('chart_of_accounts', 'transaction_lines.account_id', 'chart_of_accounts.id');
                        $accounts->leftjoin('chart_of_account_types', 'chart_of_accounts.type', 'chart_of_account_types.id');
                        $accounts->leftjoin('chart_of_account_parents', 'chart_of_accounts.name', 'chart_of_account_parents.name');
                        $accounts->where('chart_of_accounts.type', $type->id);
                        $accounts->where('chart_of_accounts.sub_type', $subType->id);
                        $accounts->where('chart_of_account_parents.account');
                        $accounts->where('chart_of_accounts.parent', '=', 'chart_of_account_parents.id');
                        $accounts->where('transaction_lines.workspace', getActiveWorkSpace());
                        $accounts->where('transaction_lines.created_by', creatorId());
                        if ($start && $end) {
                            $accounts->where('transaction_lines.date', '>=', $start);
                            $accounts->where('transaction_lines.date', '<=', $end);
                        }
                        $accounts->groupBy('account_id');
                        $accounts = $accounts->get()->toArray();
                    } else {
                        $accounts = TransactionLines::select('chart_of_accounts.id', 'chart_of_accounts.code', 'chart_of_accounts.name', \DB::raw('sum(debit) as totalDebit'), \DB::raw('sum(credit) as totalCredit'));
                        $accounts->leftjoin('chart_of_accounts', 'transaction_lines.account_id', 'chart_of_accounts.id');
                        $accounts->leftjoin('chart_of_account_types', 'chart_of_accounts.type', 'chart_of_account_types.id');
                        $accounts->where('chart_of_accounts.type', $type->id);
                        $accounts->where('chart_of_accounts.sub_type', $subType->id);
                        $accounts->where('transaction_lines.workspace', getActiveWorkSpace());
                        $accounts->where('transaction_lines.created_by', creatorId());
                        if ($start && $end) {
                            $accounts->where('transaction_lines.date', '>=', $start);
                            $accounts->where('transaction_lines.date', '<=', $end);
                        }
                        $accounts->groupBy('account_id');
                        $accounts = $accounts->get()->toArray();
                    }

                    $totalBalance = 0;
                    $creditTotal = 0;
                    $debitTotal = 0;
                    $totalAmount = 0;
                    $accountArray = [];

                    foreach ($accounts as $account) {
                        $Balance = $account['totalCredit'] - $account['totalDebit'];
                        $totalBalance += $Balance;
                        if ($Balance != 0) {
                            $data['account_id'] = $account['id'];
                            $data['account_code'] = $account['code'];
                            $data['account_name'] = $account['name'];
                            $data['account'] = '';
                            $data['totalCredit'] = 0;
                            $data['totalDebit'] = 0;
                            $data['netAmount'] = $Balance;
                            $accountArray[][] = $data;
                            $creditTotal += $data['totalCredit'];
                            $debitTotal += $data['totalDebit'];
                            $totalAmount += $data['netAmount'];
                        }
                    }

                    $totalAccountArray = [];
                    if ($accountArray != []) {
                        $dataTotal['account_id'] = '';
                        $dataTotal['account_code'] = '';
                        $dataTotal['account'] = '';
                        $dataTotal['account_name'] = 'Total ' . $subType->name;
                        $dataTotal['totalCredit'] = $creditTotal;
                        $dataTotal['totalDebit'] = $debitTotal;

                        if (isset($totalParentAccountArray) && $totalParentAccountArray != []) {

                            $netAmount = 0;

                            foreach ($totalParentAccountArray as $innerArray) {
                                $lastElement = end($innerArray);

                                $netAmount += $lastElement['netAmount'];
                            }

                            $dataTotal['netAmount'] = $netAmount + $totalAmount;
                        } else {
                            $dataTotal['netAmount'] = $totalAmount;
                        }
                        $accountArrayTotal[][] = $dataTotal;
                        $totalAccountArray = array_merge($totalParentAccountArray, $accountArray, $accountArrayTotal);

                    } elseif ($totalParentAccountArray != []) {
                        $dataTotal['account_id'] = '';
                        $dataTotal['account_code'] = '';
                        $dataTotal['account'] = '';
                        $dataTotal['account_name'] = 'Total ' . $subType->name;
                        $dataTotal['totalCredit'] = $creditTotal;
                        $dataTotal['totalDebit'] = $debitTotal;
                        $netAmount = 0;

                        foreach ($totalParentAccountArray as $innerArray) {
                            $lastElement = end($innerArray);

                            $netAmount += $lastElement['netAmount'];
                        }
                        $dataTotal['netAmount'] = $netAmount;
                        $accountArrayTotal[][] = $dataTotal;
                        $totalAccountArray = array_merge($totalParentAccountArray, $accountArrayTotal);
                    }

                    if ($totalAccountArray != []) {
                        $subTypeData['subType'] = ($totalAccountArray != []) ? $subType->name : '';
                        $subTypeData['account'] = $totalAccountArray;
                        $subTypeArray[] = ($subTypeData['account'] != [] && $subTypeData['subType'] != []) ? $subTypeData : [];
                    }
                }
                $totalAccounts[$type->name] = $subTypeArray;
            }

            $filter['startDateRange'] = $start;
            $filter['endDateRange'] = $end;

            if ($request->view == 'horizontal' || $view == 'horizontal') {
                return view('doubleentry::report.balance_sheet_horizontal', compact('filter', 'totalAccounts', 'collapseview'));
            } elseif ($view == '' || $view == 'vertical') {
                return view('doubleentry::report.balance_sheet', compact('filter', 'totalAccounts', 'collapseview'));
            } else {
                return redirect()->back();
            }
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }


    public function profitLoss(Request $request, $view = '', $collapseView = 'expand')
    {
        if (Auth::user()->isAbleTo('report profit loss')) {
            if (!empty($request->start_date) && !empty($request->end_date)) {
                $start = $request->start_date;
                $end = $request->end_date;
            } else {
                $start = NULL;
                $end = NULL;
            }

            $types = ChartOfAccountType::where('created_by', creatorId())->where('workspace', getActiveWorkSpace())->whereIn('name', ['Income', 'Expenses', 'Costs of Goods Sold'])->get();
            $subTypeArray = [];
            $totalAccounts = [];
            foreach ($types as $type) {

                $parentAccounts = ChartOfAccountParent::where('type', $type->id)->where('created_by', creatorId())->where('workspace', getActiveWorkSpace())->get();

                $totalParentAccountArray = [];
                if ($parentAccounts->isNotEmpty()) {
                    foreach ($parentAccounts as $parentAccount) {
                        $totalArray = [];
                        $parentAccountArray = [];
                        $parentAccountArrayTotal = [];

                        $parentAccs = TransactionLines::select('chart_of_accounts.id', 'chart_of_accounts.code', 'chart_of_accounts.name', \DB::raw('sum(debit) as totalDebit'), \DB::raw('sum(credit) as totalCredit'));
                        $parentAccs->leftjoin('chart_of_accounts', 'transaction_lines.account_id', 'chart_of_accounts.id');
                        $parentAccs->leftjoin('chart_of_account_types', 'chart_of_accounts.type', 'chart_of_account_types.id');
                        $parentAccs->where('chart_of_accounts.type', $type->id);
                        $parentAccs->where('chart_of_accounts.name', $parentAccount->name);
                        $parentAccs->where('transaction_lines.created_by', creatorId());
                        $parentAccs->where('transaction_lines.workspace', getActiveWorkSpace());
                        if ($start && $end) {
                            $parentAccs->where('transaction_lines.date', '>=', $start);
                            $parentAccs->where('transaction_lines.date', '<=', $end);
                        }
                        $parentAccs->groupBy('account_id');
                        $parentAccs = $parentAccs->get()->toArray();

                        $accounts = TransactionLines::select('chart_of_accounts.id', 'chart_of_accounts.code', 'chart_of_accounts.name', \DB::raw('sum(debit) as totalDebit'), \DB::raw('sum(credit) as totalCredit'));
                        $accounts->leftjoin('chart_of_accounts', 'transaction_lines.account_id', 'chart_of_accounts.id');
                        $accounts->where('chart_of_accounts.type', $type->id);
                        $accounts->where('chart_of_accounts.parent', $parentAccount->id);
                        $accounts->where('transaction_lines.created_by', creatorId());
                        $accounts->where('transaction_lines.workspace', getActiveWorkSpace());
                        if ($start && $end) {
                            $accounts->where('transaction_lines.date', '>=', $start);
                            $accounts->where('transaction_lines.date', '<=', $end);
                        }
                        $accounts->groupBy('account_id');
                        $accounts = $accounts->get()->toArray();

                        if ($parentAccs == [] && $accounts != []) {

                            $parentAccs = ChartOfAccount::select('chart_of_accounts.id', 'chart_of_accounts.code', 'chart_of_accounts.name', \DB::raw('0 as totalDebit'), \DB::raw('0 as totalCredit'));
                            $parentAccs->leftjoin('chart_of_account_parents', 'chart_of_accounts.id', 'chart_of_account_parents.account');
                            $parentAccs->where('chart_of_accounts.type', $type->id);
                            $parentAccs->where('chart_of_accounts.name', $parentAccount->name);
                            $parentAccs->where('chart_of_accounts.created_by', creatorId());
                            $parentAccs->where('chart_of_accounts.workspace', getActiveWorkSpace());
                            $parentAccs = $parentAccs->get()->toArray();
                        }
                        if ($parentAccs != [] && $accounts == []) {

                            $parentAccs = [];
                        }

                        $parenttotalBalance = 0;
                        $parentcreditTotal = 0;
                        $parenntdebitTotal = 0;
                        $parenttotalAmount = 0;

                        foreach ($parentAccs as $account) {
                            $Balance = $account['totalCredit'] - $account['totalDebit'];
                            $parenttotalBalance += $Balance;

                            $data = [
                                'account_id' => $account['id'],
                                'account_code' => $account['code'],
                                'account_name' => $account['name'],
                                'account' => 'parent',
                                'totalCredit' => 0,
                                'totalDebit' => 0,
                                'netAmount' => $Balance,
                            ];

                            $parentAccountArray[] = $data;
                            $parentcreditTotal += $data['totalCredit'];
                            $parenntdebitTotal += $data['totalDebit'];
                            $parenttotalAmount += $data['netAmount'];
                        }

                        foreach ($accounts as $account) {
                            $Balance = $account['totalCredit'] - $account['totalDebit'];
                            $parenttotalBalance += $Balance;

                            if ($Balance != 0) {
                                $data = [
                                    'account_id' => $account['id'],
                                    'account_code' => $account['code'],
                                    'account_name' => $account['name'],
                                    'account' => 'subAccount',
                                    'totalCredit' => 0,
                                    'totalDebit' => 0,
                                    'netAmount' => $Balance,
                                ];

                                $parentAccountArray[] = $data;
                                $parentcreditTotal += $data['totalCredit'];
                                $parenntdebitTotal += $data['totalDebit'];
                                $parenttotalAmount += $data['netAmount'];
                            }
                        }

                        if (!empty($parentAccountArray)) {
                            $dataTotal = [
                                'account_id' => $parentAccount->account,
                                'account_code' => '',
                                'account' => 'parentTotal',
                                'account_name' => 'Total ' . $parentAccount->name,
                                'totalCredit' => $parentcreditTotal,
                                'totalDebit' => $parenntdebitTotal,
                                'netAmount' => $parenttotalAmount,
                            ];

                            $parentAccountArrayTotal[] = $dataTotal;
                            $totalArray = array_merge($parentAccountArray, $parentAccountArrayTotal);
                            $totalParentAccountArray[] = $totalArray;
                        }

                    }

                }

                if ($totalParentAccountArray != []) {
                    $accounts = TransactionLines::select('chart_of_accounts.id', 'chart_of_accounts.code', 'chart_of_accounts.name', \DB::raw('sum(debit) as totalDebit'), \DB::raw('sum(credit) as totalCredit'));
                    $accounts->leftjoin('chart_of_accounts', 'transaction_lines.account_id', 'chart_of_accounts.id');
                    $accounts->leftjoin('chart_of_account_types', 'chart_of_accounts.type', 'chart_of_account_types.id');
                    $accounts->leftjoin('chart_of_account_parents', 'chart_of_accounts.name', 'chart_of_account_parents.name');
                    $accounts->where('chart_of_accounts.type', $type->id);
                    $accounts->where('chart_of_account_parents.account');
                    $accounts->where('chart_of_accounts.parent', '=', 'chart_of_account_parents.id');
                    $accounts->where('transaction_lines.created_by', creatorId());
                    $accounts->where('transaction_lines.workspace', getActiveWorkSpace());
                    if ($start && $end) {
                        $accounts->where('transaction_lines.date', '>=', $start);
                        $accounts->where('transaction_lines.date', '<=', $end);
                    }
                    $accounts->groupBy('account_id');
                    $accounts = $accounts->get()->toArray();
                } else {
                    $accounts = TransactionLines::select('chart_of_accounts.id', 'chart_of_accounts.code', 'chart_of_accounts.name', \DB::raw('sum(debit) as totalDebit'), \DB::raw('sum(credit) as totalCredit'));
                    $accounts->leftjoin('chart_of_accounts', 'transaction_lines.account_id', 'chart_of_accounts.id');
                    $accounts->leftjoin('chart_of_account_types', 'chart_of_accounts.type', 'chart_of_account_types.id');
                    $accounts->where('chart_of_accounts.type', $type->id);
                    $accounts->where('transaction_lines.created_by', creatorId());
                    $accounts->where('transaction_lines.workspace', getActiveWorkSpace());
                    if ($start && $end) {
                        $accounts->where('transaction_lines.date', '>=', $start);
                        $accounts->where('transaction_lines.date', '<=', $end);
                    }
                    $accounts->groupBy('account_id');
                    $accounts = $accounts->get()->toArray();
                }

                $totalBalance = 0;
                $creditTotal = 0;
                $debitTotal = 0;
                $totalAmount = 0;
                $accountArray = [];
                foreach ($accounts as $account) {
                    $Balance = $account['totalCredit'] - $account['totalDebit'];
                    $totalBalance += $Balance;
                    if ($Balance != 0) {
                        $data['account_id'] = $account['id'];
                        $data['account_code'] = $account['code'];
                        $data['account_name'] = $account['name'];
                        $data['account'] = '';
                        $data['totalCredit'] = 0;
                        $data['totalDebit'] = 0;
                        $data['netAmount'] = $Balance;
                        $accountArray[][] = $data;
                        $creditTotal += $data['totalCredit'];
                        $debitTotal += $data['totalDebit'];
                        $totalAmount += $data['netAmount'];
                    }
                }

                $totalAccountArray = [];

                if ($accountArray != []) {
                    $dataTotal['account_id'] = '';
                    $dataTotal['account_code'] = '';
                    $dataTotal['account'] = '';
                    $dataTotal['account_name'] = 'Total ' . $type->name;
                    $dataTotal['totalCredit'] = $creditTotal;
                    $dataTotal['totalDebit'] = $debitTotal;
                    $dataTotal['netAmount'] = $totalAmount;
                    $accountArray[][] = $dataTotal;
                    $totalAccountArray = array_merge($totalParentAccountArray, $accountArray);

                } elseif ($totalParentAccountArray != []) {

                    $dataTotal['account_id'] = '';
                    $dataTotal['account_code'] = '';
                    $dataTotal['account'] = '';
                    $dataTotal['account_name'] = 'Total ' . $type->name;
                    $dataTotal['totalCredit'] = $creditTotal;
                    $dataTotal['totalDebit'] = $debitTotal;
                    $netAmount = 0;
                    foreach ($totalParentAccountArray as $innerArray) {
                        $lastElement = end($innerArray);

                        $netAmount += $lastElement['netAmount'];
                    }
                    $dataTotal['netAmount'] = $netAmount;
                    $accountArrayTotal[][] = $dataTotal;
                    $totalAccountArray = array_merge($totalParentAccountArray, $accountArrayTotal);
                }
                if ($totalAccountArray != []) {
                    $subTypeData['Type'] = ($totalAccountArray != []) ? $type->name : '';
                    $subTypeData['account'] = $totalAccountArray;
                    $subTypeArray[] = ($subTypeData['account'] != []) ? $subTypeData : [];
                }
                $totalAccounts = $subTypeArray;
            }

            $filter['startDateRange'] = $start;
            $filter['endDateRange'] = $end;
            if ($request->view == 'horizontal' || $view == 'horizontal') {
                return view('doubleentry::report.profit_loss_horizontal', compact('filter', 'totalAccounts', 'collapseView'));
            } elseif ($view == '' || $view == 'vertical') {
                return view('doubleentry::report.profit_loss', compact('filter', 'totalAccounts', 'collapseView'));
            } else {
                return redirect()->back();
            }

        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function trialBalance(Request $request, $view = "expand")
    {
        if (Auth::user()->isAbleTo('report trial balance')) {

            if (!empty($request->start_date) && !empty($request->end_date)) {
                $start = $request->start_date;
                $end = $request->end_date;
            }else {
                $start = NULL;
                $end = NULL;
            }
            $types = ChartOfAccountType::where('created_by', creatorId())->where('workspace', getActiveWorkSpace())->get();
            $totalAccounts = [];
            $totalAccount = [];
            foreach ($types as $type) {

                // $type->id = 4;
                $parentAccounts = ChartOfAccountParent::where('type', $type->id)->where('created_by', creatorId())->where('workspace', getActiveWorkSpace())->get();

                $totalParentAccountArray = [];
                if ($parentAccounts->isNotEmpty()) {
                    foreach ($parentAccounts as $parentAccount) {
                        $totalArray = [];
                        $parentAccountArray = [];
                        $parentAccountArrayTotal = [];

                        $parentAccs = TransactionLines::select('chart_of_accounts.id', 'chart_of_accounts.code', 'chart_of_accounts.name', \DB::raw('sum(debit) as totalDebit'), \DB::raw('sum(credit) as totalCredit'));
                        $parentAccs->leftjoin('chart_of_accounts', 'transaction_lines.account_id', 'chart_of_accounts.id');
                        $parentAccs->leftjoin('chart_of_account_types', 'chart_of_accounts.type', 'chart_of_account_types.id');
                        $parentAccs->where('chart_of_accounts.type', $type->id);
                        $parentAccs->where('chart_of_accounts.name', $parentAccount->name);
                        $parentAccs->where('transaction_lines.created_by', creatorId());
                        $parentAccs->where('transaction_lines.workspace', getActiveWorkSpace());
                        if ($start && $end) {
                            $parentAccs->where('transaction_lines.date', '>=', $start);
                            $parentAccs->where('transaction_lines.date', '<=', $end);
                        }
                        $parentAccs->groupBy('account_id');
                        $parentAccs = $parentAccs->get()->toArray();

                        $accounts = TransactionLines::select('chart_of_accounts.id', 'chart_of_accounts.code', 'chart_of_accounts.name', \DB::raw('sum(debit) as totalDebit'), \DB::raw('sum(credit) as totalCredit'));
                        $accounts->leftjoin('chart_of_accounts', 'transaction_lines.account_id', 'chart_of_accounts.id');
                        $accounts->where('chart_of_accounts.type', $type->id);
                        $accounts->where('chart_of_accounts.parent', $parentAccount->id);
                        $accounts->where('transaction_lines.created_by', creatorId());
                        $accounts->where('transaction_lines.workspace', getActiveWorkSpace());
                        if ($start && $end) {
                            $accounts->where('transaction_lines.date', '>=', $start);
                            $accounts->where('transaction_lines.date', '<=', $end);
                        }
                        $accounts->groupBy('account_id');
                        $accounts = $accounts->get()->toArray();

                        if ($parentAccs == [] && $accounts != []) {

                            $parentAccs = ChartOfAccount::select('chart_of_accounts.id', 'chart_of_accounts.code', 'chart_of_accounts.name', \DB::raw('0 as totalDebit'), \DB::raw('0 as totalCredit'));
                            $parentAccs->leftjoin('chart_of_account_parents', 'chart_of_accounts.id', 'chart_of_account_parents.account');
                            $parentAccs->where('chart_of_accounts.type', $type->id);
                            $parentAccs->where('chart_of_accounts.name', $parentAccount->name);
                            $parentAccs->where('chart_of_accounts.created_by', creatorId());
                            $parentAccs->where('chart_of_accounts.workspace', getActiveWorkSpace());
                            $parentAccs = $parentAccs->get()->toArray();
                        } elseif ($parentAccs != [] && $accounts == []) {
                            $parentAccs = [];

                        }

                        $parenttotalBalance = 0;
                        $parentcreditTotal = 0;
                        $parenntdebitTotal = 0;
                        $parenttotalAmount = 0;

                        foreach ($parentAccs as $account) {
                            $Balance = $account['totalCredit'] - $account['totalDebit'];
                            $parenttotalBalance += $Balance;

                            $data = [
                                'account_id' => $account['id'],
                                'account_code' => $account['code'],
                                'account_name' => $account['name'],
                                'account' => 'parent',
                                'totalCredit' => $account['totalCredit'],
                                'totalDebit' => $account['totalDebit'],
                            ];

                            $parentAccountArray[] = $data;
                            $parentcreditTotal += $data['totalCredit'];
                            $parenntdebitTotal += $data['totalDebit'];
                        }

                        foreach ($accounts as $account) {
                            $Balance = $account['totalCredit'] - $account['totalDebit'];

                            if ($Balance != 0) {
                                $data = [
                                    'account_id' => $account['id'],
                                    'account_code' => $account['code'],
                                    'account_name' => $account['name'],
                                    'account' => 'subAccount',
                                    'totalCredit' => $account['totalCredit'],
                                    'totalDebit' => $account['totalDebit'],
                                ];

                                $parentAccountArray[] = $data;
                                $parentcreditTotal += $data['totalCredit'];
                                $parenntdebitTotal += $data['totalDebit'];
                            }
                        }

                        if (!empty($parentAccountArray)) {

                            $dataTotal = [
                                'account_id' => $parentAccount->account,
                                'account_code' => '',
                                'account' => 'parentTotal',
                                'account_name' => 'Total ' . $parentAccount->name,
                                'totalCredit' => $parentcreditTotal,
                                'totalDebit' => $parenntdebitTotal,
                            ];

                            $parentAccountArrayTotal[] = $dataTotal;
                        }

                        if ($parentAccountArray != []) {
                            $totalArray = array_merge($parentAccountArray, $parentAccountArrayTotal);
                            $totalParentAccountArray[] = $totalArray;
                        }
                    }
                }

                if ($totalParentAccountArray != []) {
                    $accounts = TransactionLines::select('chart_of_accounts.id as account_id', 'chart_of_accounts.code as account_code', 'chart_of_accounts.name as account_name', \DB::raw('sum(debit) as totalDebit'), \DB::raw('sum(credit) as totalCredit'));
                    $accounts->leftjoin('chart_of_accounts', 'transaction_lines.account_id', 'chart_of_accounts.id');
                    $accounts->leftjoin('chart_of_account_types', 'chart_of_accounts.type', 'chart_of_account_types.id');
                    $accounts->leftjoin('chart_of_account_parents', 'chart_of_accounts.name', 'chart_of_account_parents.name');
                    $accounts->where('chart_of_accounts.type', $type->id);
                    $accounts->where('chart_of_account_parents.account');
                    $accounts->where('chart_of_accounts.parent', '=', 'chart_of_account_parents.id');
                    $accounts->where('transaction_lines.created_by', creatorId());
                    $accounts->where('transaction_lines.workspace', getActiveWorkSpace());
                    if ($start && $end) {
                        $accounts->where('transaction_lines.date', '>=', $start);
                        $accounts->where('transaction_lines.date', '<=', $end);
                    }
                    $accounts->groupBy('account_id');
                    $accounts = $accounts->get()->toArray();
                } else {
                    $accounts = TransactionLines::select('chart_of_accounts.id as account_id', 'chart_of_accounts.code as account_code', 'chart_of_accounts.name as account_name', \DB::raw('sum(debit) as totalDebit'), \DB::raw('sum(credit) as totalCredit'));
                    $accounts->leftjoin('chart_of_accounts', 'transaction_lines.account_id', 'chart_of_accounts.id');
                    $accounts->leftjoin('chart_of_account_types', 'chart_of_accounts.type', 'chart_of_account_types.id');
                    $accounts->where('chart_of_accounts.type', $type->id);
                    $accounts->where('transaction_lines.created_by', creatorId());
                    $accounts->where('transaction_lines.workspace', getActiveWorkSpace());
                    if ($start && $end) {
                        $accounts->where('transaction_lines.date', '>=', $start);
                        $accounts->where('transaction_lines.date', '<=', $end);
                    }
                    $accounts->groupBy('account_id');
                    $accounts = $accounts->get()->toArray();
                }

                $name = $type->name;
                if (isset($totalAccount[$name])) {
                    $totalAccount[$name]["totalCredit"] += $accounts["totalCredit"];
                    $totalAccount[$name]["totalDebit"] += $accounts["totalDebit"];
                } else {
                    $totalAccount[$name] = $accounts;
                }
                if ($totalParentAccountArray != []) {
                    $totalAccount[$name] = array_merge_recursive($totalAccount[$name], $totalParentAccountArray[0]);
                }
            }

            foreach ($totalAccount as $category => $entries) {
                foreach ($entries as $entry) {
                    $name = $entry['account_name'];
                    if (!isset($totalAccounts[$category][$name])) {
                        $totalAccounts[$category][$name] = [
                            'account_id' => $entry['account_id'],
                            'account_code' => $entry['account_code'],
                            'account_name' => $name,
                            'account' => isset($entry['account']) ? $entry['account'] : '',
                            'totalDebit' => 0,
                            'totalCredit' => 0,
                        ];
                    }
                    if ($entry['totalDebit'] < 0) {
                        $totalAccounts[$category][$name]['totalDebit'] += 0;
                        $totalAccounts[$category][$name]['totalCredit'] += -$entry['totalDebit'];
                    } else {
                        $totalAccounts[$category][$name]['totalDebit'] += $entry['totalDebit'];
                        $totalAccounts[$category][$name]['totalCredit'] += $entry['totalCredit'];
                    }
                }
            }

            $filter['startDateRange'] = $start;
            $filter['endDateRange'] = $end;
            return view('doubleentry::report.trial_balance', compact('filter', 'totalAccounts', 'view'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }



    public function salesReport(Request $request)
    {
        if (!empty($request->start_date) && !empty($request->end_date)) {
            $start = $request->start_date;
            $end = $request->end_date;
        } else {
            $start = NULL;
            $end = NULL;
        }

        $invoiceItems = InvoiceProduct::select('product_services.name', \DB::raw('sum(invoice_products.quantity) as quantity'), \DB::raw('sum(invoice_products.price) as price'), \DB::raw('sum(invoice_products.price)/sum(invoice_products.quantity) as avg_price'));
        $invoiceItems->leftjoin('product_services', 'product_services.id', 'invoice_products.product_id');
        $invoiceItems->where('product_services.created_by', creatorId());
        $invoiceItems->where('product_services.workspace_id', getActiveWorkSpace());
        if ($start && $end) {
            $invoiceItems->where('invoice_products.created_at', '>=', $start);
            $invoiceItems->where('invoice_products.created_at', '<=', $end);
        }
        $invoiceItems->groupBy('invoice_products.product_id');
        $invoiceItems = $invoiceItems->get()->toArray();

        $invoiceCustomers = Invoice::select(
            'customers.name',
            \DB::raw('count(invoices.customer_id) as invoice_count'),
            \DB::raw('sum(invoice_products.price) as price'),
            \DB::raw('sum(invoice_products.price * (taxes.rate / 100 )) as total_tax')
        );

        $invoiceCustomers->leftJoin('customers', 'customers.id', 'invoices.customer_id');
        $invoiceCustomers->leftJoin('invoice_products', 'invoice_products.invoice_id', 'invoices.id');
        $invoiceCustomers->leftJoin('taxes', \DB::raw('FIND_IN_SET(taxes.id, invoice_products.tax)'), '>', \DB::raw('0'));
        $invoiceCustomers->where('invoices.created_by', creatorId());
        $invoiceCustomers->where('invoices.workspace', getActiveWorkSpace());
        if ($start && $end) {
            $invoiceCustomers->where('invoices.created_at', '>=', $start);
            $invoiceCustomers->where('invoices.created_at', '<=', $end);
        }
        $invoiceCustomers->groupBy('invoices.customer_id');
        $invoiceCustomers = $invoiceCustomers->get()->toArray();

        $filter['startDateRange'] = $start;
        $filter['endDateRange'] = $end;

        return view('doubleentry::report.sales_report', compact('filter', 'invoiceItems', 'invoiceCustomers'));
    }

    public function salesReportPrint(Request $request)
    {


        if (!empty($request->start_date) && !empty($request->end_date)) {
            $start = $request->start_date;
            $end = $request->end_date;
        } else {
            $start = NULL;
            $end = NULL;
        }
        $customer = Customer::where('workspace', '=', getActiveWorkSpace())->get()->pluck('name', 'id');

        $invoiceItems = InvoiceProduct::select('product_services.name', \DB::raw('sum(invoice_products.quantity) as quantity')
            , \DB::raw('sum(invoice_products.price) as price'),
            \DB::raw('sum(invoice_products.price)/sum(invoice_products.quantity) as avg_price'));
        $invoiceItems->leftjoin('product_services', 'product_services.id', 'invoice_products.product_id');
        $invoiceItems->leftjoin('invoices', 'invoices.id', '=', 'invoice_products.invoice_id');
        $invoiceItems->where('product_services.created_by', creatorId());
        $invoiceItems->where('product_services.workspace_id', getActiveWorkSpace());
        if ($start && $end) {
            $invoiceItems->where('invoice_products.created_at', '>=', $start);
            $invoiceItems->where('invoice_products.created_at', '<=', $end);
        }
        $invoiceItems->groupBy('invoice_products.product_id');

        if (!empty($request->customer)) {
            $invoiceItems->where('customer_id', '=', $request->customer);
            $cust = Customer::find($request->customer);
            $filter['customer'] = !empty($cust) ? $cust->name : '';
        }
        $invoiceItems = $invoiceItems->get()->toArray();

        $invoiceCustomers = Invoice::select(
            'customers.name',
            \DB::raw('count(invoices.customer_id) as invoice_count'),
            \DB::raw('sum(invoice_products.price) as price'),
            \DB::raw('sum(invoice_products.price * (taxes.rate / 100 )) as total_tax')
        );

        $invoiceCustomers->leftJoin('customers', 'customers.id', 'invoices.customer_id');
        $invoiceCustomers->leftJoin('invoice_products', 'invoice_products.invoice_id', 'invoices.id');
        $invoiceCustomers->leftJoin('taxes', \DB::raw('FIND_IN_SET(taxes.id, invoice_products.tax)'), '>', \DB::raw('0'));
        $invoiceCustomers->where('invoices.created_by', creatorId());
        $invoiceCustomers->where('invoices.workspace', getActiveWorkSpace());
        if ($start && $end) {
            $invoiceCustomers->where('invoices.created_at', '>=', $start);
            $invoiceCustomers->where('invoices.created_at', '<=', $end);
        }

        $invoiceCustomers->groupBy('invoices.customer_id');

        if (!empty($request->customer)) {
            $invoiceCustomers->where('invoices.customer_id', '=', $request->customer);
            $cust = Customer::find($request->customer);
            $filter['customer'] = !empty($cust) ? $cust->name : '';
        }
        $invoiceCustomers = $invoiceCustomers->get()->toArray();

        $filter['startDateRange'] = $start;
        $filter['endDateRange'] = $end;
        $filter['customer'] = $customer;
        $reportName = $request->report;

        return view('doubleentry::report.sales_report_receipt', compact('filter', 'invoiceItems', 'invoiceCustomers', 'reportName', 'customer'));
    }

    public function ReceivablesReport(Request $request)
    {

        if (!empty($request->start_date) && !empty($request->end_date)) {
            $start = $request->start_date;
            $end = $request->end_date;
        } else {
            $start = NULL;
            $end = NULL;
        }

        $customers = Customer::where('workspace', '=', getActiveWorkSpace())->get()->pluck('name', 'id');


        $receivableCustomers = Invoice::select('customers.name')->selectRaw('sum((invoice_products.price * invoice_products.quantity) - invoice_products.discount) as price')
            ->selectRaw('sum((invoice_payments.amount)) as pay_price')
            ->selectRaw('(SELECT SUM((price * quantity - discount) * (taxes.rate / 100)) FROM invoice_products
             LEFT JOIN taxes ON FIND_IN_SET(taxes.id, invoice_products.tax) > 0
             WHERE invoice_products.invoice_id = invoices.id) as total_tax')
            ->selectRaw('(SELECT SUM(credit_notes.amount) FROM credit_notes
             WHERE credit_notes.invoice = invoices.id) as credit_price')
            ->leftJoin('customers', 'customers.id', 'invoices.customer_id')
            ->leftJoin('invoice_payments', 'invoice_payments.invoice_id', 'invoices.id')
            ->leftJoin('invoice_products', 'invoice_products.invoice_id', 'invoices.id');
            if (!empty($request->customer)) {
                $receivableCustomers->where('invoices.customer_id', $request->customer);
                $cust = Customer::find($request->customer);
                $filter['customer'] = !empty($cust) ? $cust->name : '';
            }
            $receivableCustomers->where('invoices.created_by', creatorId());
            $receivableCustomers->where('invoices.workspace', getActiveWorkSpace());
            if ($start && $end) {
                $receivableCustomers->where('invoices.issue_date', '>=', $start);
                $receivableCustomers->where('invoices.issue_date', '<=', $end);
            }
            $receivableCustomers->groupBy('invoices.invoice_id');
            $receivableCustomers = $receivableCustomers->get()->toArray();


        $receivableSummariesInvoice = Invoice::select('customers.name')
            ->selectRaw('(invoices.invoice_id) as invoice')
            ->selectRaw('sum((invoice_products.price * invoice_products.quantity) - invoice_products.discount) as price')
            ->selectRaw('sum((invoice_payments.amount)) as pay_price')
            ->selectRaw('(SELECT SUM((price * quantity - discount) * (taxes.rate / 100)) FROM invoice_products
             LEFT JOIN taxes ON FIND_IN_SET(taxes.id, invoice_products.tax) > 0
             WHERE invoice_products.invoice_id = invoices.id) as total_tax')
            ->selectRaw('invoices.issue_date as issue_date')
            ->selectRaw('invoices.status as status')
            ->leftJoin('customers', 'customers.id', 'invoices.customer_id')
            ->leftJoin('invoice_payments', 'invoice_payments.invoice_id', 'invoices.id')
            ->leftJoin('invoice_products', 'invoice_products.invoice_id', 'invoices.id');
        if (!empty($request->customer)) {
            $receivableSummariesInvoice->where('invoices.customer_id', $request->customer);
            $cust = Customer::find($request->customer);
            $filter['customer'] = !empty($cust) ? $cust->name : '';
        }
        $receivableSummariesInvoice->where('invoices.created_by', creatorId());
        $receivableSummariesInvoice->where('invoices.workspace', getActiveWorkSpace());
        if ($start && $end) {
            $receivableSummariesInvoice->where('invoices.issue_date', '>=', $start);
            $receivableSummariesInvoice->where('invoices.issue_date', '<=', $end);
        }
        $receivableSummariesInvoice->groupBy('invoices.invoice_id');
        $receivableSummariesInvoice = $receivableSummariesInvoice->get()->toArray();

        $receivableSummariesCredit = \Modules\Account\Entities\CreditNote::select('customers.name')
            ->selectRaw('null as invoice')
            ->selectRaw('(credit_notes.amount) as price')
            ->selectRaw('0 as pay_price')
            ->selectRaw('0 as total_tax')
            ->selectRaw('credit_notes.date as issue_date')
            ->selectRaw('5 as status')
            ->leftJoin('customers', 'customers.id', 'credit_notes.customer')
            ->leftJoin('invoice_products', 'invoice_products.invoice_id', 'credit_notes.invoice')
            ->leftJoin('invoices', 'invoices.id', 'credit_notes.invoice');
        if (!empty($request->customer)) {
            $receivableSummariesCredit->where('invoices.customer_id', $request->customer);
            $cust = Customer::find($request->customer);
            $filter['customer'] = !empty($cust) ? $cust->name : '';
        }
        $receivableSummariesCredit->where('invoices.created_by', creatorId());
        $receivableSummariesCredit->where('invoices.workspace', getActiveWorkSpace());
        if ($start && $end) {
            $receivableSummariesCredit->where('credit_notes.date', '>=', $start);
            $receivableSummariesCredit->where('credit_notes.date', '<=', $end);
        }
        $receivableSummariesCredit->groupBy('credit_notes.id');
        $receivableSummariesCredit = $receivableSummariesCredit->get()->toArray();

        $receivableSummaries = (array_merge($receivableSummariesCredit, $receivableSummariesInvoice));

        $receivableDetailsInvoice = Invoice::select('customers.name')
            ->selectRaw('(invoices.invoice_id) as invoice')
            ->selectRaw('sum(invoice_products.price) as price')
            ->selectRaw('(invoice_products.quantity) as quantity')
            ->selectRaw('(product_services.name) as product_name')
            ->selectRaw('invoices.issue_date as issue_date')
            ->selectRaw('invoices.status as status')
            ->leftJoin('customers', 'customers.id', 'invoices.customer_id')
            ->leftJoin('invoice_products', 'invoice_products.invoice_id', 'invoices.id')
            ->leftJoin('product_services', 'product_services.id', 'invoice_products.product_id');
        if (!empty($request->customer)) {
            $receivableDetailsInvoice->where('invoices.customer_id', $request->customer);
            $cust = Customer::find($request->customer);
            $filter['customer'] = !empty($cust) ? $cust->name : '';
        }
        $receivableDetailsInvoice->where('invoices.created_by', creatorId());
        $receivableDetailsInvoice->where('invoices.workspace', getActiveWorkSpace());
        if ($start && $end) {
            $receivableDetailsInvoice->where('invoices.issue_date', '>=', $start);
            $receivableDetailsInvoice->where('invoices.issue_date', '<=', $end);
        }
        $receivableDetailsInvoice->groupBy('invoices.invoice_id', 'product_services.name');
        $receivableDetailsInvoice = $receivableDetailsInvoice->get()->toArray();

        $receivableDetailsCredit = \Modules\Account\Entities\CreditNote::select('customers.name')
            ->selectRaw('null as invoice')
            ->selectRaw('(credit_notes.id) as invoices')
            ->selectRaw('(credit_notes.amount) as price')
            ->selectRaw('(product_services.name) as product_name')
            ->selectRaw('credit_notes.date as issue_date')
            ->selectRaw('5 as status')
            ->leftJoin('customers', 'customers.id', 'credit_notes.customer')
            ->leftJoin('invoice_products', 'invoice_products.invoice_id', 'credit_notes.invoice')
            ->leftJoin('product_services', 'product_services.id', 'invoice_products.product_id')
            ->leftJoin('invoices', 'invoices.id', 'credit_notes.invoice');
        if (!empty($request->customer)) {
            $receivableDetailsCredit->where('invoices.customer_id', $request->customer);
            $cust = Customer::find($request->customer);
            $filter['customer'] = !empty($cust) ? $cust->name : '';
        }
        $receivableDetailsCredit->where('invoices.created_by', creatorId());
        $receivableDetailsCredit->where('invoices.workspace', getActiveWorkSpace());
        if ($start && $end) {
            $receivableDetailsCredit->where('credit_notes.date', '>=', $start);
            $receivableDetailsCredit->where('credit_notes.date', '<=', $end);
        }
        $receivableDetailsCredit->groupBy('credit_notes.id', 'product_services.name');
        $receivableDetailsCredit = $receivableDetailsCredit->get()->toArray();

        $mergedArray = [];
        foreach ($receivableDetailsCredit as $item) {
            $invoices = $item["invoices"];

            if (!isset($mergedArray[$invoices])) {
                $mergedArray[$invoices] = [
                    "name" => $item["name"],
                    "invoice" => $item["invoice"],
                    "invoices" => $invoices,
                    "price" => $item["price"],
                    "quantity" => 0,
                    "product_name" => "",
                    "issue_date" => "",
                    "status" => 0,
                ];
            }

            if (!strstr($mergedArray[$invoices]["product_name"], $item["product_name"])) {
                if ($mergedArray[$invoices]["product_name"] !== "") {
                    $mergedArray[$invoices]["product_name"] .= ", ";
                }
                $mergedArray[$invoices]["product_name"] .= $item["product_name"];
            }

            $mergedArray[$invoices]["issue_date"] = $item["issue_date"];
            $mergedArray[$invoices]["status"] = $item["status"];
        }

        $receivableDetailsCredits = array_values($mergedArray);

        $receivableDetails = (array_merge($receivableDetailsInvoice, $receivableDetailsCredits));

        $agingSummary = Invoice::select('customers.name', 'invoices.due_date as due_date', 'invoices.status as status', 'invoices.invoice_id as invoice_id')
            ->selectRaw('sum((invoice_products.price * invoice_products.quantity) - invoice_products.discount) as price')
            ->selectRaw('sum((invoice_payments.amount)) as pay_price')
            ->selectRaw('(SELECT SUM((price * quantity - discount) * (taxes.rate / 100)) FROM invoice_products
             LEFT JOIN taxes ON FIND_IN_SET(taxes.id, invoice_products.tax) > 0
             WHERE invoice_products.invoice_id = invoices.id) as total_tax')
            ->selectRaw('(SELECT SUM(credit_notes.amount) FROM credit_notes
             WHERE credit_notes.invoice = invoices.id) as credit_price')
            ->leftJoin('customers', 'customers.id', 'invoices.customer_id')
            ->leftJoin('invoice_payments', 'invoice_payments.invoice_id', 'invoices.id')
            ->leftJoin('invoice_products', 'invoice_products.invoice_id', 'invoices.id')
            ->where('invoices.created_by', creatorId())
            ->where('invoices.workspace', getActiveWorkSpace());
        if (!empty($request->customer)) {
            $agingSummary->where('invoices.customer_id', $request->customer);
            $cust = Customer::find($request->customer);
            $filter['customer'] = !empty($cust) ? $cust->name : '';
        }
        if ($start && $end) {
            $agingSummary->where('invoices.issue_date', '>=', $start);
            $agingSummary->where('invoices.issue_date', '<=', $end);
        }
        $agingSummary->groupBy('invoices.invoice_id');
        $agingSummary = $agingSummary->get()->toArray();


        $agingSummaries = [];

        $today = date("Y-m-d");
        foreach ($agingSummary as $item) {

            $name = $item["name"];
            $price = floatval(($item["price"] + $item['total_tax']) - ($item['pay_price'] + $item['credit_price']));
            $dueDate = $item["due_date"];

            if (!isset($agingSummaries[$name])) {
                $agingSummaries[$name] = [
                    'current' => 0.0,
                    "1_15_days" => 0.0,
                    "16_30_days" => 0.0,
                    "31_45_days" => 0.0,
                    "greater_than_45_days" => 0.0,
                    "total_due" => 0.0,
                ];
            }


            $daysDifference = date_diff(date_create($dueDate), date_create($today));
            $daysDifference = $daysDifference->format("%R%a");

            if ($daysDifference <= 0) {
                $agingSummaries[$name]["current"] += $price;
            } elseif ($daysDifference >= 1 && $daysDifference <= 15) {
                $agingSummaries[$name]["1_15_days"] += $price;
            } elseif ($daysDifference >= 16 && $daysDifference <= 30) {
                $agingSummaries[$name]["16_30_days"] += $price;
            } elseif ($daysDifference >= 31 && $daysDifference <= 45) {
                $agingSummaries[$name]["31_45_days"] += $price;
            } elseif ($daysDifference > 45) {
                $agingSummaries[$name]["greater_than_45_days"] += $price;
            }

            $agingSummaries[$name]["total_due"] += $price;
        }


        $currents = [];
        $days1to15 = [];
        $days16to30 = [];
        $days31to45 = [];
        $moreThan45 = [];

        foreach ($agingSummary as $item) {
            $dueDate = $item["due_date"];
            $price = floatval($item["price"]);
            $total_tax = floatval($item["total_tax"]);
            $credit_price = floatval($item["credit_price"]);
            $payPrice = $item["pay_price"] ? floatval($item["pay_price"]) : 0;

            $daysDifference = date_diff(date_create($dueDate), date_create($today));
            $daysDifference = $daysDifference->format("%R%a");
            $balanceDue = ($price + $total_tax) - ($payPrice + $credit_price);
            $totalPrice = $price + $total_tax;

            if ($daysDifference <= 0) {
                $item["total_price"] = $totalPrice;
                $item["balance_due"] = $balanceDue;
                $currents[] = $item;
            } elseif ($daysDifference >= 1 && $daysDifference <= 15) {
                $item["total_price"] = $totalPrice;
                $item["balance_due"] = $balanceDue;
                $item['age'] = intval(str_replace(array('+', '-'), '', $daysDifference));
                $days1to15[] = $item;
            } elseif ($daysDifference >= 16 && $daysDifference <= 30) {
                $item["total_price"] = $totalPrice;
                $item["balance_due"] = $balanceDue;
                $item['age'] = intval(str_replace(array('+', '-'), '', $daysDifference));
                $days16to30[] = $item;
            } elseif ($daysDifference >= 31 && $daysDifference <= 45) {
                $item["total_price"] = $totalPrice;
                $item["balance_due"] = $balanceDue;
                $item['age'] = intval(str_replace(array('+', '-'), '', $daysDifference));
                $days31to45[] = $item;
            } else {
                $item["total_price"] = $totalPrice;
                $item["balance_due"] = $balanceDue;
                $item['age'] = intval(str_replace(array('+', '-'), '', $daysDifference));
                $moreThan45[] = $item;
            }
        }

        $filter['startDateRange'] = $start;
        $filter['endDateRange'] = $end;

        return view('doubleentry::report.receivable_report', compact('filter', 'receivableCustomers', 'receivableSummaries', 'receivableDetails', 'agingSummaries', 'currents', 'days1to15', 'days16to30', 'days31to45', 'moreThan45', 'customers'));
    }

    public function ReceivablesPrint(Request $request)
    {
        $start = null;
        $end = null;
        $customer = null;
        $customers = null;

        if (!empty($request->start_date) && !empty($request->end_date)) {
            $start = $request->start_date;
            $end = $request->end_date;
        }

        if (!empty($request->customer)) {
            $customer = Customer::where('id', $request->customer)->first();
        }

        if (empty($request->customer)) {
            $customers = Customer::where('workspace', getActiveWorkSpace())->pluck('name', 'id');
        }


        $receivableCustomers = Invoice::select('customers.name')->selectRaw('sum((invoice_products.price * invoice_products.quantity) - invoice_products.discount) as price')
            ->selectRaw('sum((invoice_payments.amount)) as pay_price')
            ->selectRaw('(SELECT SUM((price * quantity - discount) * (taxes.rate / 100)) FROM invoice_products
             LEFT JOIN taxes ON FIND_IN_SET(taxes.id, invoice_products.tax) > 0
             WHERE invoice_products.invoice_id = invoices.id) as total_tax')
            ->selectRaw('(SELECT SUM(credit_notes.amount) FROM credit_notes
             WHERE credit_notes.invoice = invoices.id) as credit_price')
            ->leftJoin('customers', 'customers.id', 'invoices.customer_id')
            ->leftJoin('invoice_payments', 'invoice_payments.invoice_id', 'invoices.id')
            ->leftJoin('invoice_products', 'invoice_products.invoice_id', 'invoices.id')
            ->where('invoices.created_by', creatorId())
            ->where('invoices.workspace', getActiveWorkSpace());
        if (!empty($customer)) {
            $receivableCustomers->where('invoices.customer_id', $customer->id);
            $filter['customer'] = $customer->name;
        }
        if ($start && $end) {
            $receivableCustomers->where('invoices.issue_date', '>=', $start);
            $receivableCustomers->where('invoices.issue_date', '<=', $end);
        }
        $receivableCustomers->groupBy('invoices.invoice_id');
        $receivableCustomers = $receivableCustomers->get()->toArray();


        $receivableSummariesInvoice = Invoice::select('customers.name')
            ->selectRaw('(invoices.invoice_id) as invoice')
            ->selectRaw('sum((invoice_products.price * invoice_products.quantity) - invoice_products.discount) as price')
            ->selectRaw('sum((invoice_payments.amount)) as pay_price')
            ->selectRaw('(SELECT SUM((price * quantity - discount) * (taxes.rate / 100)) FROM invoice_products
             LEFT JOIN taxes ON FIND_IN_SET(taxes.id, invoice_products.tax) > 0
             WHERE invoice_products.invoice_id = invoices.id) as total_tax')
            ->selectRaw('invoices.issue_date as issue_date')
            ->selectRaw('invoices.status as status')
            ->leftJoin('customers', 'customers.id', 'invoices.customer_id')
            ->leftJoin('invoice_payments', 'invoice_payments.invoice_id', 'invoices.id')
            ->leftJoin('invoice_products', 'invoice_products.invoice_id', 'invoices.id');
        if (!empty($request->customer)) {
            $receivableSummariesInvoice->where('invoices.customer_id', $request->customer);
            $cust = Customer::find($request->customer);
            $filter['customer'] = !empty($cust) ? $cust->name : '';
        }
        $receivableSummariesInvoice->where('invoices.created_by', creatorId());
        $receivableSummariesInvoice->where('invoices.workspace', getActiveWorkSpace());
        if ($start && $end) {
            $receivableSummariesInvoice->where('invoices.issue_date', '>=', $start);
            $receivableSummariesInvoice->where('invoices.issue_date', '<=', $end);
        }
        $receivableSummariesInvoice->groupBy('invoices.invoice_id');
        $receivableSummariesInvoice = $receivableSummariesInvoice->get()->toArray();

        $receivableSummariesCredit = \Modules\Account\Entities\CreditNote::select('customers.name')
            ->selectRaw('null as invoice')
            ->selectRaw('(credit_notes.amount) as price')
            ->selectRaw('0 as pay_price')
            ->selectRaw('0 as total_tax')
            ->selectRaw('credit_notes.date as issue_date')
            ->selectRaw('5 as status')
            ->leftJoin('customers', 'customers.id', 'credit_notes.customer')
            ->leftJoin('invoice_products', 'invoice_products.invoice_id', 'credit_notes.invoice')
            ->leftJoin('invoices', 'invoices.id', 'credit_notes.invoice');
        if (!empty($request->customer)) {
            $receivableSummariesCredit->where('invoices.customer_id', $request->customer);
            $cust = Customer::find($request->customer);
            $filter['customer'] = !empty($cust) ? $cust->name : '';
        }
        $receivableSummariesCredit->where('invoices.created_by', creatorId());
        $receivableSummariesCredit->where('invoices.workspace', getActiveWorkSpace());
        if ($start && $end) {
            $receivableSummariesCredit->where('credit_notes.date', '>=', $start);
            $receivableSummariesCredit->where('credit_notes.date', '<=', $end);
        }
        $receivableSummariesCredit->groupBy('credit_notes.id');
        $receivableSummariesCredit = $receivableSummariesCredit->get()->toArray();


        $receivableSummaries = (array_merge($receivableSummariesCredit, $receivableSummariesInvoice));

        $receivableDetailsInvoice = Invoice::select('customers.name')
            ->selectRaw('(invoices.invoice_id) as invoice')
            ->selectRaw('sum(invoice_products.price) as price')
            ->selectRaw('(invoice_products.quantity) as quantity')
            ->selectRaw('(product_services.name) as product_name')
            ->selectRaw('invoices.issue_date as issue_date')
            ->selectRaw('invoices.status as status')
            ->leftJoin('customers', 'customers.id', 'invoices.customer_id')
            ->leftJoin('invoice_products', 'invoice_products.invoice_id', 'invoices.id')
            ->leftJoin('product_services', 'product_services.id', 'invoice_products.product_id');
        if (!empty($request->customer)) {
            $receivableDetailsInvoice->where('invoices.customer_id', $request->customer);
            $cust = Customer::find($request->customer);
            $filter['customer'] = !empty($cust) ? $cust->name : '';
        }
        $receivableDetailsInvoice->where('invoices.created_by', creatorId());
        $receivableDetailsInvoice->where('invoices.workspace', getActiveWorkSpace());
        if ($start && $end) {
            $receivableDetailsInvoice->where('invoices.issue_date', '>=', $start);
            $receivableDetailsInvoice->where('invoices.issue_date', '<=', $end);
        }
        $receivableDetailsInvoice->groupBy('invoices.invoice_id', 'product_services.name');
        $receivableDetailsInvoice = $receivableDetailsInvoice->get()->toArray();

        $receivableDetailsCredit = \Modules\Account\Entities\CreditNote::select('customers.name')
            ->selectRaw('null as invoice')
            ->selectRaw('(credit_notes.id) as invoices')
            ->selectRaw('(credit_notes.amount) as price')
            ->selectRaw('(product_services.name) as product_name')
            ->selectRaw('credit_notes.date as issue_date')
            ->selectRaw('5 as status')
            ->leftJoin('customers', 'customers.id', 'credit_notes.customer')
            ->leftJoin('invoice_products', 'invoice_products.invoice_id', 'credit_notes.invoice')
            ->leftJoin('product_services', 'product_services.id', 'invoice_products.product_id')
            ->leftJoin('invoices', 'invoices.id', 'credit_notes.invoice');
        if (!empty($request->customer)) {
            $receivableDetailsCredit->where('invoices.customer_id', $request->customer);
            $cust = Customer::find($request->customer);
            $filter['customer'] = !empty($cust) ? $cust->name : '';
        }
        $receivableDetailsCredit->where('invoices.created_by', creatorId());
        $receivableDetailsCredit->where('invoices.workspace', getActiveWorkSpace());
        if ($start && $end) {
            $receivableDetailsCredit->where('credit_notes.date', '>=', $start);
            $receivableDetailsCredit->where('credit_notes.date', '<=', $end);
        }
        $receivableDetailsCredit->groupBy('credit_notes.id', 'product_services.name');
        $receivableDetailsCredit = $receivableDetailsCredit->get()->toArray();


        $mergedArray = [];
        foreach ($receivableDetailsCredit as $item) {
            $invoices = $item["invoices"];

            if (!isset($mergedArray[$invoices])) {
                $mergedArray[$invoices] = [
                    "name" => $item["name"],
                    "invoice" => $item["invoice"],
                    "invoices" => $invoices,
                    "price" => $item["price"],
                    "quantity" => 0,
                    "product_name" => "",
                    "issue_date" => "",
                    "status" => 0,
                ];
            }

            if (!strstr($mergedArray[$invoices]["product_name"], $item["product_name"])) {
                if ($mergedArray[$invoices]["product_name"] !== "") {
                    $mergedArray[$invoices]["product_name"] .= ", ";
                }
                $mergedArray[$invoices]["product_name"] .= $item["product_name"];
            }

            $mergedArray[$invoices]["issue_date"] = $item["issue_date"];
            $mergedArray[$invoices]["status"] = $item["status"];
        }

        $receivableDetailsCredits = array_values($mergedArray);

        $receivableDetails = (array_merge($receivableDetailsInvoice, $receivableDetailsCredits));

        $agingSummary = Invoice::select('customers.name', 'invoices.due_date as due_date', 'invoices.status as status', 'invoices.invoice_id as invoice_id')
            ->selectRaw('sum((invoice_products.price * invoice_products.quantity) - invoice_products.discount) as price')
            ->selectRaw('sum((invoice_payments.amount)) as pay_price')
            ->selectRaw('(SELECT SUM((price * quantity - discount) * (taxes.rate / 100)) FROM invoice_products
             LEFT JOIN taxes ON FIND_IN_SET(taxes.id, invoice_products.tax) > 0
             WHERE invoice_products.invoice_id = invoices.id) as total_tax')
            ->selectRaw('(SELECT SUM(credit_notes.amount) FROM credit_notes
             WHERE credit_notes.invoice = invoices.id) as credit_price')
            ->leftJoin('customers', 'customers.id', 'invoices.customer_id')
            ->leftJoin('invoice_payments', 'invoice_payments.invoice_id', 'invoices.id')
            ->leftJoin('invoice_products', 'invoice_products.invoice_id', 'invoices.id')
            ->where('invoices.created_by', creatorId())
            ->where('invoices.workspace', getActiveWorkSpace());
        if (!empty($request->customer)) {
            $agingSummary->where('invoices.customer_id', $request->customer);
            $cust = Customer::find($request->customer);
            $filter['customer'] = !empty($cust) ? $cust->name : '';
        }
        if ($start && $end) {
            $agingSummary->where('invoices.issue_date', '>=', $start);
            $agingSummary->where('invoices.issue_date', '<=', $end);
        }
        $agingSummary->groupBy('invoices.invoice_id');
        $agingSummary = $agingSummary->get()->toArray();

        $agingSummaries = [];

        $today = date("Y-m-d");
        foreach ($agingSummary as $item) {
            $name = $item["name"];
            $price = floatval(($item["price"] + $item['total_tax']) - ($item['pay_price'] + $item['credit_price']));
            $dueDate = $item["due_date"];

            if (!isset($agingSummaries[$name])) {
                $agingSummaries[$name] = [
                    'current' => 0.0,
                    "1_15_days" => 0.0,
                    "16_30_days" => 0.0,
                    "31_45_days" => 0.0,
                    "greater_than_45_days" => 0.0,
                    "total_due" => 0.0,
                ];
            }

            $daysDifference = date_diff(date_create($dueDate), date_create($today));
            $daysDifference = $daysDifference->format("%R%a");

            if ($daysDifference <= 0) {
                $agingSummaries[$name]["current"] += $price;
            } elseif ($daysDifference >= 1 && $daysDifference <= 15) {
                $agingSummaries[$name]["1_15_days"] += $price;
            } elseif ($daysDifference >= 16 && $daysDifference <= 30) {
                $agingSummaries[$name]["16_30_days"] += $price;
            } elseif ($daysDifference >= 31 && $daysDifference <= 45) {
                $agingSummaries[$name]["31_45_days"] += $price;
            } elseif ($daysDifference > 45) {
                $agingSummaries[$name]["greater_than_45_days"] += $price;
            }

            $agingSummaries[$name]["total_due"] += $price;
        }

        $currents = [];
        $days1to15 = [];
        $days16to30 = [];
        $days31to45 = [];
        $moreThan45 = [];

        foreach ($agingSummary as $item) {
            $dueDate = $item["due_date"];
            $price = floatval($item["price"]);
            $total_tax = floatval($item["total_tax"]);
            $credit_price = floatval($item["credit_price"]);
            $payPrice = $item["pay_price"] ? floatval($item["pay_price"]) : 0;

            $daysDifference = date_diff(date_create($dueDate), date_create($today));
            $daysDifference = $daysDifference->format("%R%a");
            $balanceDue = ($price + $total_tax) - ($payPrice + $credit_price);
            $totalPrice = $price + $total_tax;
            if ($daysDifference <= 0) {
                $item["total_price"] = $totalPrice;
                $item["balance_due"] = $balanceDue;
                $currents[] = $item;
            } elseif ($daysDifference >= 1 && $daysDifference <= 15) {
                $item["total_price"] = $totalPrice;
                $item["balance_due"] = $balanceDue;
                $item['age'] = intval(str_replace(array('+', '-'), '', $daysDifference));
                $days1to15[] = $item;
            } elseif ($daysDifference >= 16 && $daysDifference <= 30) {
                $item["total_price"] = $totalPrice;
                $item["balance_due"] = $balanceDue;
                $item['age'] = intval(str_replace(array('+', '-'), '', $daysDifference));
                $days16to30[] = $item;
            } elseif ($daysDifference >= 31 && $daysDifference <= 45) {
                $item["total_price"] = $totalPrice;
                $item["balance_due"] = $balanceDue;
                $item['age'] = intval(str_replace(array('+', '-'), '', $daysDifference));
                $days31to45[] = $item;
            } else {
                $item["total_price"] = $totalPrice;
                $item["balance_due"] = $balanceDue;
                $item['age'] = intval(str_replace(array('+', '-'), '', $daysDifference));
                $moreThan45[] = $item;
            }
        }

        $filter['startDateRange'] = $start;
        $filter['endDateRange'] = $end;
        $reportName = $request->report;


        return view('doubleentry::report.receivable_report_receipt', compact(
                'filter',
                'receivableCustomers',
                'receivableSummaries',
                'moreThan45',
                'days31to45',
                'days16to30',
                'days1to15',
                'currents',
                'reportName',
                'receivableDetails',
                'agingSummaries'
            )
        );
    }

    public function PayablesReport(Request $request)
    {

        if (!empty($request->start_date) && !empty($request->end_date)) {
            $start = $request->start_date;
            $end = $request->end_date;
        } else {
            $start = NULL;
            $end = NULL;
        }

        $vendor = Vender::where('workspace', getActiveWorkSpace())->get()->pluck('name', 'id');

        $payableVendors = Bill::select('vendors.name')
            ->selectRaw('sum((bill_products.price * bill_products.quantity) - bill_products.discount) as price')
            ->selectRaw('sum((bill_payments.amount)) as pay_price')
            ->selectRaw('(SELECT SUM((price * quantity - discount) * (taxes.rate / 100)) FROM bill_products
            LEFT JOIN taxes ON FIND_IN_SET(taxes.id, bill_products.tax) > 0
            WHERE bill_products.bill_id = bills.id) as total_tax')
            ->selectRaw('(SELECT SUM(debit_notes.amount) FROM debit_notes
            WHERE debit_notes.bill = bills.id) as debit_price')
            ->leftJoin('vendors', 'vendors.id', 'bills.vendor_id')
            ->leftJoin('bill_payments', 'bill_payments.bill_id', 'bills.id')
            ->leftJoin('bill_products', 'bill_products.bill_id', 'bills.id');
        if (!empty($request->vendor)) {
            $payableVendors->where('bills.vendor_id', $request->vendor);
            $vend = Vender::find($request->vendor);
            $filter['vendor'] = !empty($vend) ? $vend->name : '';
        }
        $payableVendors->where('bills.created_by', creatorId());
        $payableVendors->where('bills.workspace', getActiveWorkSpace());
        if ($start && $end) {
            $payableVendors->where('bills.bill_date', '>=', $start);
            $payableVendors->where('bills.bill_date', '<=', $end);
        }
        $payableVendors->groupBy('bills.id');
        $payableVendors = $payableVendors->get()->toArray();

        $payableSummariesBill = Bill::select('vendors.name')
            ->selectRaw('(bills.bill_id) as bill')
            ->selectRaw('sum((bill_products.price * bill_products.quantity) - bill_products.discount) as price')
            ->selectRaw('sum((bill_payments.amount)) as pay_price')
            ->selectRaw('(SELECT SUM((price * quantity - discount) * (taxes.rate / 100)) FROM bill_products
               LEFT JOIN taxes ON FIND_IN_SET(taxes.id, bill_products.tax) > 0
                WHERE bill_products.bill_id = bills.id) as total_tax')
            ->selectRaw('bills.bill_date as bill_date')
            ->selectRaw('bills.status as status')
            ->leftJoin('vendors', 'vendors.id', 'bills.vendor_id')
            ->leftJoin('bill_payments', 'bill_payments.bill_id', 'bills.id')
            ->leftJoin('bill_products', 'bill_products.bill_id', 'bills.id');
        if (!empty($request->vendor)) {
            $payableSummariesBill->where('bills.vendor_id', $request->vendor);
            $vend = Vender::find($request->vendor);
            $filter['vendor'] = !empty($vend) ? $vend->name : '';
        }
        $payableSummariesBill->where('bills.created_by', creatorId());
        $payableSummariesBill->where('bills.workspace', getActiveWorkSpace());
        if ($start && $end) {
            $payableSummariesBill->where('bills.bill_date', '>=', $start);
            $payableSummariesBill->where('bills.bill_date', '<=', $end);
        }
        $payableSummariesBill->groupBy('bills.id');
        $payableSummariesBill = $payableSummariesBill->get()->toArray();


        $payableSummariesDebit = \Modules\Account\Entities\DebitNote::select('vendors.name')
            ->selectRaw('null as bill')
            ->selectRaw('debit_notes.amount as price')
            ->selectRaw('0 as pay_price')
            ->selectRaw('0 as total_tax')
            ->selectRaw('debit_notes.date as bill_date')
            ->selectRaw('5 as status')
            ->leftJoin('vendors', 'vendors.id', 'debit_notes.vendor')
            ->leftJoin('bill_products', 'bill_products.bill_id', 'debit_notes.bill')
            ->leftJoin('bills', 'bills.id', 'debit_notes.bill');
        if (!empty($request->vendor)) {
            $payableSummariesDebit->where('bills.vendor_id', $request->vendor);
            $vend = Vender::find($request->vendor);
            $filter['customer'] = !empty($vend) ? $vend->name : '';
        }
        if ($start && $end) {
            $payableSummariesDebit->where('debit_notes.date', '>=', $start);
            $payableSummariesDebit->where('debit_notes.date', '<=', $end);
        }
        $payableSummariesDebit->where('bills.created_by', creatorId());
        $payableSummariesDebit->where('bills.workspace', getActiveWorkSpace());
        $payableSummariesDebit->groupBy('debit_notes.id');
        $payableSummariesDebit = $payableSummariesDebit->get()->toArray();

        $payableSummaries = (array_merge($payableSummariesDebit, $payableSummariesBill));


        $payableDetailsBill = Bill::select('vendors.name')
            ->selectRaw('(bills.bill_id) as bill')
            ->selectRaw('sum(bill_products.price) as price')
            ->selectRaw('(bill_products.quantity) as quantity')
            ->selectRaw('(product_services.name) as product_name')
            ->selectRaw('bills.bill_date as bill_date')
            ->selectRaw('bills.status as status')
            ->leftJoin('vendors', 'vendors.id', 'bills.vendor_id')
            ->leftJoin('bill_products', 'bill_products.bill_id', 'bills.id')
            ->leftJoin('product_services', 'product_services.id', 'bill_products.product_id');

        if (!empty($request->vendor)) {
            $payableDetailsBill->where('bills.vendor_id', $request->vendor);
            $vend = Vender::find($request->vendor);

            $filter['vendor'] = !empty($vend) ? $vend->name : '';
        }
        $payableDetailsBill->where('bills.created_by', creatorId());
        $payableDetailsBill->where('bills.workspace', getActiveWorkSpace());
        if ($start && $end) {
            $payableDetailsBill->where('bills.bill_date', '>=', $start);
            $payableDetailsBill->where('bills.bill_date', '<=', $end);
        }
        $payableDetailsBill->groupBy('bills.bill_id', 'product_services.name');
        $payableDetailsBill = $payableDetailsBill->get()->toArray();

        $payableDetailsDebit = \Modules\Account\Entities\DebitNote::select('vendors.name')
            ->selectRaw('null as bill')
            ->selectRaw('(debit_notes.id) as bills')
            ->selectRaw('(debit_notes.amount) as price')
            ->selectRaw('(product_services.name) as product_name')
            ->selectRaw('debit_notes.date as bill_date')
            ->selectRaw('5 as status')
            ->leftJoin('vendors', 'vendors.id', 'debit_notes.vendor')
            ->leftJoin('bill_products', 'bill_products.bill_id', 'debit_notes.bill')
            ->leftJoin('product_services', 'product_services.id', 'bill_products.product_id')
            ->leftJoin('bills', 'bills.id', 'debit_notes.bill');
        if (!empty($request->vendor)) {
            $payableDetailsDebit->where('bills.vendor_id', $request->vendor);
            $vend = Vender::find($request->vendor);
            $filter['vendor'] = !empty($vend) ? $vend->name : '';
        }
        $payableDetailsDebit->where('bills.created_by', creatorId());
        $payableDetailsDebit->where('bills.workspace', getActiveWorkSpace());

        if ($start && $end) {
            $payableDetailsDebit->where('debit_notes.date', '>=', $start);
            $payableDetailsDebit->where('debit_notes.date', '<=', $end);
        }
        $payableDetailsDebit->groupBy('debit_notes.id', 'product_services.name');
        $payableDetailsDebit = $payableDetailsDebit->get()->toArray();

        $mergedArray = [];
        foreach ($payableDetailsDebit as $item) {
            $invoices = $item["bills"];

            if (!isset($mergedArray[$invoices])) {
                $mergedArray[$invoices] = [
                    "name" => $item["name"],
                    "bill" => $item["bill"],
                    "bills" => $invoices,
                    "price" => $item["price"],
                    "quantity" => 0,
                    "product_name" => "",
                    "bill_date" => "",
                    "status" => 0,
                ];
            }

            if (!strstr($mergedArray[$invoices]["product_name"], $item["product_name"])) {
                if ($mergedArray[$invoices]["product_name"] !== "") {
                    $mergedArray[$invoices]["product_name"] .= ", ";
                }
                $mergedArray[$invoices]["product_name"] .= $item["product_name"];
            }

            $mergedArray[$invoices]["bill_date"] = $item["bill_date"];
            $mergedArray[$invoices]["status"] = $item["status"];
        }

        $payableDetailsDebits = array_values($mergedArray);

        $payableDetails = (array_merge($payableDetailsBill, $payableDetailsDebits));

        $agingSummary = Bill::select(
            'vendors.name',
            'bills.due_date as due_date',
            'bills.status as status',
            'bills.bill_id as bill_id',
            DB::raw('SUM((bill_products.price * bill_products.quantity) - bill_products.discount) as price'),
            DB::raw('SUM((bill_payments.amount)) as pay_price'),
            DB::raw('SUM((bill_products.price * bill_products.quantity - bill_products.discount) * (taxes.rate / 100)) as total_tax'),
            DB::raw('SUM(debit_notes.amount) as credit_price')
        )
            ->leftJoin('vendors', 'vendors.id', '=', 'bills.vendor_id')
            ->leftJoin('bill_payments', 'bill_payments.bill_id', '=', 'bills.id')
            ->leftJoin('bill_products', 'bill_products.bill_id', '=', 'bills.id')
            ->leftJoin('taxes', function ($join) {
                $join->on(DB::raw('FIND_IN_SET(taxes.id, bill_products.tax)'), '>', DB::raw('0'));
            })
            ->leftJoin('debit_notes', 'debit_notes.bill', '=', 'bills.id')
            ->where('bills.created_by', creatorId())
            ->where('bills.workspace', getActiveWorkSpace());
        if (!empty($request->vendor)) {
            $agingSummary->where('bills.vendor_id', $request->vendor);
            $vend = Vender::find($request->vendor);
            $filter['vendor'] = !empty($vend) ? $vend->name : '';
        }
        if ($start && $end) {
            $agingSummary->whereBetween('bills.bill_date', [$start, $end]);
        }

        $agingSummary->groupBy('bills.id');

        $agingSummary = $agingSummary->get();


        $agingSummaries = [];

        $today = date("Y-m-d");
        foreach ($agingSummary as $item) {

            $name = $item["name"];
            $price = floatval(($item["price"] + $item['total_tax']) - ($item['pay_price'] + $item['credit_price']));
            $dueDate = $item["due_date"];

            if (!isset($agingSummaries[$name])) {
                $agingSummaries[$name] = [
                    'current' => 0.0,
                    "1_15_days" => 0.0,
                    "16_30_days" => 0.0,
                    "31_45_days" => 0.0,
                    "greater_than_45_days" => 0.0,
                    "total_due" => 0.0,
                ];
            }


            $daysDifference = date_diff(date_create($dueDate), date_create($today));
            $daysDifference = $daysDifference->format("%R%a");

            if ($daysDifference <= 0) {
                $agingSummaries[$name]["current"] += $price;
            } elseif ($daysDifference >= 1 && $daysDifference <= 15) {
                $agingSummaries[$name]["1_15_days"] += $price;
            } elseif ($daysDifference >= 16 && $daysDifference <= 30) {
                $agingSummaries[$name]["16_30_days"] += $price;
            } elseif ($daysDifference >= 31 && $daysDifference <= 45) {
                $agingSummaries[$name]["31_45_days"] += $price;
            } elseif ($daysDifference > 45) {
                $agingSummaries[$name]["greater_than_45_days"] += $price;
            }

            $agingSummaries[$name]["total_due"] += $price;
        }


        $currents = [];
        $days1to15 = [];
        $days16to30 = [];
        $days31to45 = [];
        $moreThan45 = [];

        foreach ($agingSummary as $item) {
            $dueDate = $item["due_date"];
            $price = floatval($item["price"]);
            $total_tax = floatval($item["total_tax"]);
            $credit_price = floatval($item["credit_price"]);
            $payPrice = $item["pay_price"] ? floatval($item["pay_price"]) : 0;

            $daysDifference = date_diff(date_create($dueDate), date_create($today));
            $daysDifference = $daysDifference->format("%R%a");
            $balanceDue = ($price + $total_tax) - ($payPrice + $credit_price);
            $totalPrice = $price + $total_tax;

            if ($daysDifference <= 0) {
                $item["total_price"] = $totalPrice;
                $item["balance_due"] = $balanceDue;
                $currents[] = $item;
            } elseif ($daysDifference >= 1 && $daysDifference <= 15) {
                $item["total_price"] = $totalPrice;
                $item["balance_due"] = $balanceDue;
                $item['age'] = intval(str_replace(array('+', '-'), '', $daysDifference));
                $days1to15[] = $item;
            } elseif ($daysDifference >= 16 && $daysDifference <= 30) {
                $item["total_price"] = $totalPrice;
                $item["balance_due"] = $balanceDue;
                $item['age'] = intval(str_replace(array('+', '-'), '', $daysDifference));
                $days16to30[] = $item;
            } elseif ($daysDifference >= 31 && $daysDifference <= 45) {
                $item["total_price"] = $totalPrice;
                $item["balance_due"] = $balanceDue;
                $item['age'] = intval(str_replace(array('+', '-'), '', $daysDifference));
                $days31to45[] = $item;
            } else {
                $item["total_price"] = $totalPrice;
                $item["balance_due"] = $balanceDue;
                $item['age'] = intval(str_replace(array('+', '-'), '', $daysDifference));
                $moreThan45[] = $item;
            }
        }

        $filter['startDateRange'] = $start;
        $filter['endDateRange'] = $end;


        return view('doubleentry::report.payable_report', compact('filter', 'payableVendors',
            'payableSummaries', 'payableDetails', 'agingSummaries', 'moreThan45', 'days31to45', 'days16to30', 'days1to15', 'currents', 'vendor'));
    }

    public function PayablesPrint(Request $request)
    {

        $start = null;
        $end = null;
        $vendor = null;
        $vendors = null;

        if (!empty($request->start_date) && !empty($request->end_date)) {
            $start = $request->start_date;
            $end = $request->end_date;
        }

        if (!empty($request->vendor)) {
            $vendor = Vender::where('id', $request->vendor)->first();
        }

        if (empty($request->vendor)) {
            $vendors = Vender::where('workspace', getActiveWorkSpace())->pluck('name', 'id');
        }

        $payableVendors = Bill::select('vendors.name')
            ->selectRaw('sum((bill_products.price * bill_products.quantity) - bill_products.discount) as price')
            ->selectRaw('sum((bill_payments.amount)) as pay_price')
            ->selectRaw('(SELECT SUM((price * quantity - discount) * (taxes.rate / 100)) FROM bill_products
            LEFT JOIN taxes ON FIND_IN_SET(taxes.id, bill_products.tax) > 0
            WHERE bill_products.bill_id = bills.id) as total_tax')
            ->selectRaw('(SELECT SUM(debit_notes.amount) FROM debit_notes
            WHERE debit_notes.bill = bills.id) as debit_price')
            ->leftJoin('vendors', 'vendors.id', 'bills.vendor_id')
            ->leftJoin('bill_payments', 'bill_payments.bill_id', 'bills.id')
            ->leftJoin('bill_products', 'bill_products.bill_id', 'bills.id')
            ->where('bills.created_by', creatorId())
            ->where('bills.workspace', getActiveWorkSpace());
        if (!empty($vendor)) {
            $payableVendors->where('bills.vendor_id', $vendor->id);
            $filter['vendor'] = $vendor->name;
        }

        if ($start && $end) {
            $payableVendors->whereBetween('bills.bill_date', [$start, $end]);
        }

        $payableVendors->groupBy('bills.id');

        $payableVendors = $payableVendors->get();


        $payableSummariesBill = Bill::select('vendors.name')
            ->selectRaw('(bills.bill_id) as bill')
            ->selectRaw('sum((bill_products.price * bill_products.quantity) - bill_products.discount) as price')
            ->selectRaw('sum((bill_payments.amount)) as pay_price')
            ->selectRaw('(SELECT SUM((price * quantity - discount) * (taxes.rate / 100)) FROM bill_products
                    LEFT JOIN taxes ON FIND_IN_SET(taxes.id, bill_products.tax) > 0
                    WHERE bill_products.bill_id = bills.id) as total_tax')
            ->selectRaw('bills.bill_date as bill_date')
            ->selectRaw('bills.status as status')
            ->leftJoin('vendors', 'vendors.id', 'bills.vendor_id')
            ->leftJoin('bill_payments', 'bill_payments.bill_id', 'bills.id')
            ->leftJoin('bill_products', 'bill_products.bill_id', 'bills.id');
        if (!empty($request->vendor)) {
            $payableSummariesBill->where('bills.vendor_id', $request->vendor);
            $vend = Vender::find($request->vendor);
            $filter['vendor'] = !empty($vend) ? $vend->name : '';
        }
        $payableSummariesBill->where('bills.created_by', creatorId());
        $payableSummariesBill->where('bills.workspace', getActiveWorkSpace());
        if ($start && $end) {
            $payableSummariesBill->where('bills.bill_date', '>=', $start);
            $payableSummariesBill->where('bills.bill_date', '<=', $end);
        }

        $payableSummariesBill->groupBy('bills.id');
        $payableSummariesBill = $payableSummariesBill->get()->toArray();

        $payableSummariesDebit = \Modules\Account\Entities\DebitNote::select('vendors.name')
            ->selectRaw('null as bill')
            ->selectRaw('debit_notes.amount as price')
            ->selectRaw('0 as pay_price')
            ->selectRaw('0 as total_tax')
            ->selectRaw('debit_notes.date as bill_date')
            ->selectRaw('5 as status')
            ->leftJoin('vendors', 'vendors.id', 'debit_notes.vendor')
            ->leftJoin('bill_products', 'bill_products.bill_id', 'debit_notes.bill')
            ->leftJoin('bills', 'bills.id', 'debit_notes.bill')
            ->where('bills.created_by', creatorId())
            ->where('bills.workspace', getActiveWorkSpace());
        if ($start && $end) {
            $payableSummariesDebit->where('debit_notes.date', '>=', $start);
            $payableSummariesDebit->where('debit_notes.date', '<=', $end);
        }

        $payableSummariesDebit->groupBy('debit_notes.id');
        $payableSummariesDebit = $payableSummariesDebit->get()->toArray();

        $payableSummaries = (array_merge($payableSummariesDebit, $payableSummariesBill));

        $payableDetailsBill = Bill::select('vendors.name')
            ->selectRaw('(bills.bill_id) as bill')
            ->selectRaw('sum(bill_products.price) as price')
            ->selectRaw('(bill_products.quantity) as quantity')
            ->selectRaw('(product_services.name) as product_name')
            ->selectRaw('bills.bill_date as bill_date')
            ->selectRaw('bills.status as status')
            ->leftJoin('vendors', 'vendors.id', 'bills.vendor_id')
            ->leftJoin('bill_products', 'bill_products.bill_id', 'bills.id')
            ->leftJoin('product_services', 'product_services.id', 'bill_products.product_id');

        if (!empty($request->vendor)) {
            $payableDetailsBill->where('bills.vendor_id', $request->vendor);
            $vend = Vender::find($request->vendor);

            $filter['vendor'] = !empty($vend) ? $vend->name : '';
        }
        $payableDetailsBill->where('bills.created_by', creatorId());
        $payableDetailsBill->where('bills.workspace', getActiveWorkSpace());
        if ($start && $end) {
            $payableDetailsBill->where('bills.bill_date', '>=', $start);
            $payableDetailsBill->where('bills.bill_date', '<=', $end);
        }
        $payableDetailsBill->groupBy('bills.bill_id', 'product_services.name');
        $payableDetailsBill = $payableDetailsBill->get()->toArray();


        $payableDetailsDebit = \Modules\Account\Entities\DebitNote::select('vendors.name')
            ->selectRaw('null as bill')
            ->selectRaw('(debit_notes.id) as bills')
            ->selectRaw('(debit_notes.amount) as price')
            ->selectRaw('(product_services.name) as product_name')
            ->selectRaw('debit_notes.date as bill_date')
            ->selectRaw('5 as status')
            ->leftJoin('vendors', 'vendors.id', 'debit_notes.vendor')
            ->leftJoin('bill_products', 'bill_products.bill_id', 'debit_notes.bill')
            ->leftJoin('product_services', 'product_services.id', 'bill_products.product_id')
            ->leftJoin('bills', 'bills.id', 'debit_notes.bill');
        if (!empty($request->vendor)) {
            $payableDetailsDebit->where('bills.vendor_id', $request->vendor);
            $vend = Vender::find($request->vendor);
            $filter['vendor'] = !empty($vend) ? $vend->name : '';
        }
        $payableDetailsDebit->where('bills.created_by', creatorId());
        $payableDetailsDebit->where('bills.workspace', getActiveWorkSpace());
        if ($start && $end) {
            $payableDetailsDebit->where('debit_notes.date', '>=', $start);
            $payableDetailsDebit->where('debit_notes.date', '<=', $end);
        }
        $payableDetailsDebit->groupBy('debit_notes.id', 'product_services.name');
        $payableDetailsDebit = $payableDetailsDebit->get()->toArray();

        $mergedArray = [];
        foreach ($payableDetailsDebit as $item) {
            $invoices = $item["bills"];

            if (!isset($mergedArray[$invoices])) {
                $mergedArray[$invoices] = [
                    "name" => $item["name"],
                    "bill" => $item["bill"],
                    "bills" => $invoices,
                    "price" => $item["price"],
                    "quantity" => 0,
                    "product_name" => "",
                    "bill_date" => "",
                    "status" => 0,
                ];
            }

            if (!strstr($mergedArray[$invoices]["product_name"], $item["product_name"])) {
                if ($mergedArray[$invoices]["product_name"] !== "") {
                    $mergedArray[$invoices]["product_name"] .= ", ";
                }
                $mergedArray[$invoices]["product_name"] .= $item["product_name"];
            }

            $mergedArray[$invoices]["bill_date"] = $item["bill_date"];
            $mergedArray[$invoices]["status"] = $item["status"];
        }

        $payableDetailsDebits = array_values($mergedArray);

        $payableDetails = (array_merge($payableDetailsBill, $payableDetailsDebits));
        $agingSummary = Bill::select(
            'vendors.name',
            'bills.due_date as due_date',
            'bills.status as status',
            'bills.bill_id as bill_id',
            DB::raw('SUM((bill_products.price * bill_products.quantity) - bill_products.discount) as price'),
            DB::raw('SUM((bill_payments.amount)) as pay_price'),
            DB::raw('SUM((bill_products.price * bill_products.quantity - bill_products.discount) * (taxes.rate / 100)) as total_tax'),
            DB::raw('SUM(debit_notes.amount) as credit_price')
        )
            ->leftJoin('vendors', 'vendors.id', '=', 'bills.vendor_id')
            ->leftJoin('bill_payments', 'bill_payments.bill_id', '=', 'bills.id')
            ->leftJoin('bill_products', 'bill_products.bill_id', '=', 'bills.id')
            ->leftJoin('taxes', function ($join) {
                $join->on(DB::raw('FIND_IN_SET(taxes.id, bill_products.tax)'), '>', DB::raw('0'));
            })
            ->leftJoin('debit_notes', 'debit_notes.bill', '=', 'bills.id')
            ->where('bills.created_by', creatorId())
            ->where('bills.workspace', getActiveWorkSpace());
        if (!empty($vendor)) {
            $agingSummary->where('bills.vendor_id', $vendor->id);
            $filter['vendor'] = $vendor->name;
        }

        if ($start && $end) {
            $agingSummary->whereBetween('bills.bill_date', [$start, $end]);
        }

        $agingSummary->groupBy('bills.id');

        $agingSummary = $agingSummary->get();


        $agingSummaries = [];

        $today = date("Y-m-d");
        foreach ($agingSummary as $item) {

            $name = $item["name"];
            $price = floatval(($item["price"] + $item['total_tax']) - ($item['pay_price'] + $item['credit_price']));
            $dueDate = $item["due_date"];

            if (!isset($agingSummaries[$name])) {
                $agingSummaries[$name] = [
                    'current' => 0.0,
                    "1_15_days" => 0.0,
                    "16_30_days" => 0.0,
                    "31_45_days" => 0.0,
                    "greater_than_45_days" => 0.0,
                    "total_due" => 0.0,
                ];
            }


            $daysDifference = date_diff(date_create($dueDate), date_create($today));
            $daysDifference = $daysDifference->format("%R%a");

            if ($daysDifference <= 0) {
                $agingSummaries[$name]["current"] += $price;
            } elseif ($daysDifference >= 1 && $daysDifference <= 15) {
                $agingSummaries[$name]["1_15_days"] += $price;
            } elseif ($daysDifference >= 16 && $daysDifference <= 30) {
                $agingSummaries[$name]["16_30_days"] += $price;
            } elseif ($daysDifference >= 31 && $daysDifference <= 45) {
                $agingSummaries[$name]["31_45_days"] += $price;
            } elseif ($daysDifference > 45) {
                $agingSummaries[$name]["greater_than_45_days"] += $price;
            }

            $agingSummaries[$name]["total_due"] += $price;
        }


        $currents = [];
        $days1to15 = [];
        $days16to30 = [];
        $days31to45 = [];
        $moreThan45 = [];

        foreach ($agingSummary as $item) {
            $dueDate = $item["due_date"];
            $price = floatval($item["price"]);
            $total_tax = floatval($item["total_tax"]);
            $credit_price = floatval($item["credit_price"]);
            $payPrice = $item["pay_price"] ? floatval($item["pay_price"]) : 0;

            $daysDifference = date_diff(date_create($dueDate), date_create($today));
            $daysDifference = $daysDifference->format("%R%a");
            $balanceDue = ($price + $total_tax) - ($payPrice + $credit_price);
            $totalPrice = $price + $total_tax;

            if ($daysDifference <= 0) {
                $item["total_price"] = $totalPrice;
                $item["balance_due"] = $balanceDue;
                $currents[] = $item;
            } elseif ($daysDifference >= 1 && $daysDifference <= 15) {
                $item["total_price"] = $totalPrice;
                $item["balance_due"] = $balanceDue;
                $item['age'] = intval(str_replace(array('+', '-'), '', $daysDifference));
                $days1to15[] = $item;
            } elseif ($daysDifference >= 16 && $daysDifference <= 30) {
                $item["total_price"] = $totalPrice;
                $item["balance_due"] = $balanceDue;
                $item['age'] = intval(str_replace(array('+', '-'), '', $daysDifference));
                $days16to30[] = $item;
            } elseif ($daysDifference >= 31 && $daysDifference <= 45) {
                $item["total_price"] = $totalPrice;
                $item["balance_due"] = $balanceDue;
                $item['age'] = intval(str_replace(array('+', '-'), '', $daysDifference));
                $days31to45[] = $item;
            } else {
                $item["total_price"] = $totalPrice;
                $item["balance_due"] = $balanceDue;
                $item['age'] = intval(str_replace(array('+', '-'), '', $daysDifference));
                $moreThan45[] = $item;
            }
        }

        $filter['startDateRange'] = $start;
        $filter['endDateRange'] = $end;
        $reportName = $request->report;

        return view('doubleentry::report.payable_report_receipt', compact('filter', 'reportName',
            'payableVendors', 'payableSummaries', 'payableDetails', 'agingSummaries', 'moreThan45', 'days31to45', 'days16to30', 'days1to15', 'currents', 'vendors'));

    }


}
