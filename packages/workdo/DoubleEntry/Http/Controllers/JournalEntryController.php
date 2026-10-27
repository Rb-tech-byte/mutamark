<?php

namespace Modules\DoubleEntry\Http\Controllers;

use App\Models\Setting;
use Illuminate\DoubleEntry\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Account\Entities\AccountUtility;
use Modules\Account\Entities\BankAccount;
use Modules\Account\Entities\ChartOfAccount;
use Modules\DoubleEntry\Entities\JournalEntry;
use Modules\DoubleEntry\Entities\JournalItem;
use Modules\Account\Entities\TransactionLines;
use Modules\DoubleEntry\Events\CreateJournalAccount;
use Modules\DoubleEntry\Events\DestroyJournalAccount;
use Modules\DoubleEntry\Events\UpdateJournalAccount;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Rawilk\Settings\Support\Context;



class JournalEntryController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */

    public function index()
    {
        if (Auth::user()->isAbleTo('journalentry manage')) {
            $journalEntries = JournalEntry::where('created_by', '=', creatorId())->where('workspace', getActiveWorkSpace())->get();

            return view('doubleentry::journalEntry.index', compact('journalEntries'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */


    function journalNumber()
    {
        $latest = JournalEntry::where('created_by', '=', creatorId())->where('workspace', getActiveWorkSpace())->latest()->first();
        if (!$latest) {
            return 1;
        }

        return $latest->journal_id + 1;
    }


    public function create()
    {
        if (Auth::user()->isAbleTo('journalentry create')) {
            //            $accounts = ChartOfAccount::select(\DB::raw('CONCAT(code, " - ", name) AS code_name, id'))
            //                ->where('created_by', creatorId())->where('workspace', getActiveWorkSpace())->get()
            //                ->pluck('code_name', 'id');

            $chartAccounts = ChartOfAccount::select('chart_of_accounts.id', 'chart_of_accounts.code', 'chart_of_accounts.name', 'chart_of_accounts.parent')
                ->where('parent', '=', 0)
                ->where('created_by', creatorId())
                ->where('workspace', getActiveWorkSpace())
                ->get()->toarray();


            $subAccounts = ChartOfAccount::select('chart_of_accounts.id', 'chart_of_accounts.code', 'chart_of_accounts.name', 'chart_of_account_parents.account');
            $subAccounts->leftjoin('chart_of_account_parents', 'chart_of_accounts.parent', 'chart_of_account_parents.id');
            $subAccounts->where('chart_of_accounts.parent', '!=', 0);
            $subAccounts->where('chart_of_accounts.created_by', creatorId());
            $subAccounts->where('chart_of_accounts.workspace', getActiveWorkSpace());
            $subAccounts = $subAccounts->get()->toArray();

            $journalId = $this->journalNumber();

            if (module_is_active('CustomField')) {
                $customFields =  \Modules\CustomField\Entities\CustomField::where('workspace_id', getActiveWorkSpace())->where('module', '=', 'DoubleEntry')->where('sub_module', 'Journal Account')->get();
            } else {
                $customFields = null;
            }

            return view('doubleentry::journalEntry.create', compact('chartAccounts', 'subAccounts', 'journalId', 'customFields'));
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }


    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        if (Auth::user()->isAbleTo('journalentry create')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'date' => 'required',
                    'accounts' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $accounts = $request->accounts;


            $totalDebit = 0;
            $totalCredit = 0;
            for ($i = 0; $i < count($accounts); $i++) {
                $debit = isset($accounts[$i]['debit']) ? $accounts[$i]['debit'] : 0;
                $credit = isset($accounts[$i]['credit']) ? $accounts[$i]['credit'] : 0;
                $totalDebit += $debit;
                $totalCredit += $credit;
            }

            if ($totalCredit != $totalDebit) {
                return redirect()->back()->with('error', __('Debit and Credit must be Equal.'));
            }

            $journal = new JournalEntry();
            $journal->journal_id = $this->journalNumber();
            $journal->date = $request->date;
            $journal->reference = $request->reference;
            $journal->description = $request->description;
            $journal->workspace = getActiveWorkSpace();
            $journal->created_by = creatorId();
            $journal->save();

            for ($i = 0; $i < count($accounts); $i++) {
                $journalItem = new JournalItem();
                $journalItem->journal = $journal->id;
                $journalItem->account = $accounts[$i]['account'];
                $journalItem->description = $accounts[$i]['description'];
                $journalItem->debit = isset($accounts[$i]['debit']) ? $accounts[$i]['debit'] : 0;
                $journalItem->credit = isset($accounts[$i]['credit']) ? $accounts[$i]['credit'] : 0;
                $journalItem->workspace = getActiveWorkSpace();
                $journalItem->created_by = creatorId();
                $journalItem->save();

                //start - save data for transaction lines
                if ($journalItem->debit > 0) {
                    $transaction_type = 'Debit';
                    $transaction_amount = $journalItem->debit;
                } else {
                    $transaction_type = 'Credit';
                    $transaction_amount = $journalItem->credit;
                }
                $data = [
                    'account_id' => $journalItem->account,
                    'transaction_type' => $transaction_type,
                    'transaction_amount' => $transaction_amount,
                    'reference' => 'Journal Entry',
                    'reference_id' => $journal->id,
                    'reference_sub_id' => $journalItem->id,
                    'date' => $journal->date,
                ];
                AccountUtility::addTransactionLines($data);

                //end - save data for transaction lines

                $bankAccounts = [];
                if (module_is_active('Account')) {
                    $bankAccounts = \Modules\Account\Entities\BankAccount::where('chart_account_id', '=', $accounts[$i]['account'])->get();
                }

                if (!empty($bankAccounts)) {
                    foreach ($bankAccounts as $bankAccount) {
                        $old_balance = $bankAccount->opening_balance;
                        if ($journalItem->debit > 0) {
                            $new_balance = $old_balance - $journalItem->debit;
                        }
                        if ($journalItem->credit > 0) {
                            $new_balance = $old_balance + $journalItem->credit;
                        }
                        if (isset($new_balance)) {
                            $bankAccount->opening_balance = $new_balance;
                            $bankAccount->save();
                        }
                    }
                }
            }

            if (module_is_active('CustomField')) {
                \Modules\CustomField\Entities\CustomField::saveData($journal, $request->customField);
            }

            event(new CreateJournalAccount($request, $journal));

            return redirect()->route('journal-entry.index')->with('success', __('Journal entry successfully created.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show(JournalEntry $journalEntry)
    {

        if (Auth::user()->isAbleTo('journalentry show')) {
            if ($journalEntry->created_by == creatorId()) {
                $accounts = $journalEntry->accounts;

                $settings['company_name'] = company_setting('company_name');
                $settings['company_telephone'] = company_setting('company_telephone');
                $settings['company_address'] = company_setting('company_address');
                $settings['company_city'] = company_setting('company_city');
                $settings['company_state'] = company_setting('company_state');
                $settings['company_country'] = company_setting('company_country');

                if(module_is_active('CustomField')){
                    $journalEntry->customField = \Modules\CustomField\Entities\CustomField::getData($journalEntry, 'DoubleEntry','Journal Account');
                    $customFields      = \Modules\CustomField\Entities\CustomField::where('workspace_id', '=', getActiveWorkSpace())->where('module', '=', 'DoubleEntry')->where('sub_module','Journal Account')->get();
                }else{
                    $customFields = null;
                }

                return view('doubleentry::journalEntry.view', compact('journalEntry', 'accounts', 'settings','customFields'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */


    public function edit(JournalEntry $journalEntry)
    {
        if (Auth::user()->isAbleTo('journalentry edit')) {


            $chartAccounts = ChartOfAccount::select('chart_of_accounts.id', 'chart_of_accounts.code', 'chart_of_accounts.name', 'chart_of_accounts.parent')
                ->where('parent', '=', 0)
                ->where('created_by', creatorId())->where('workspace', getActiveWorkSpace())->get()
                ->toarray();

            $subAccounts = ChartOfAccount::select('chart_of_accounts.id', 'chart_of_accounts.code', 'chart_of_accounts.name', 'chart_of_account_parents.account');
            $subAccounts->leftjoin('chart_of_account_parents', 'chart_of_accounts.parent', 'chart_of_account_parents.id');
            $subAccounts->where('chart_of_accounts.parent', '!=', 0);
            $subAccounts->where('chart_of_accounts.created_by', creatorId());
            $subAccounts->where('chart_of_accounts.workspace', getActiveWorkSpace());
            $subAccounts = $subAccounts->get()->toArray();

            if (module_is_active('CustomField')) {
                $journalEntry->customField = \Modules\CustomField\Entities\CustomField::getData($journalEntry, 'DoubleEntry', 'Journal Account');
                $customFields             = \Modules\CustomField\Entities\CustomField::where('workspace_id', '=', getActiveWorkSpace())->where('module', '=', 'DoubleEntry')->where('sub_module', 'Journal Account')->get();
            } else {
                $customFields = null;
            }
            return view('doubleentry::journalEntry.edit', compact('chartAccounts', 'journalEntry', 'subAccounts','customFields'));
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */

    public function update(Request $request, JournalEntry $journalEntry)
    {

        //        dd($journalEntry);
        if (Auth::user()->isAbleTo('journalentry edit')) {
            if ($journalEntry->created_by == creatorId()) {
                $validator = \Validator::make(
                    $request->all(),
                    [
                        'date' => 'required',
                        'accounts' => 'required',
                    ]
                );
                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();

                    return redirect()->back()->with('error', $messages->first());
                }

                $accounts = $request->accounts;

                $totalDebit = 0;
                $totalCredit = 0;
                for ($i = 0; $i < count($accounts); $i++) {
                    $debit = isset($accounts[$i]['debit']) ? $accounts[$i]['debit'] : 0;
                    $credit = isset($accounts[$i]['credit']) ? $accounts[$i]['credit'] : 0;
                    $totalDebit += $debit;
                    $totalCredit += $credit;
                }

                if ($totalCredit != $totalDebit) {
                    return redirect()->back()->with('error', __('Debit and Credit must be Equal.'));
                }

                $journalEntry->date = $request->date;
                $journalEntry->reference = $request->reference;
                $journalEntry->description = $request->description;
                $journalEntry->created_by = creatorId();
                $journalEntry->save();

                for ($i = 0; $i < count($accounts); $i++) {
                    $journalItem = JournalItem::find($accounts[$i]['id']);

                    if ($journalItem == null) {
                        $journalItem = new JournalItem();
                        $journalItem->journal = $journalEntry->id;
                    }

                    if (isset($accounts[$i]['account'])) {
                        $journalItem->account = $accounts[$i]['account'];
                    }

                    $journalItem->description = $accounts[$i]['description'];
                    $journalItem->debit = isset($accounts[$i]['debit']) ? $accounts[$i]['debit'] : 0;
                    $journalItem->credit = isset($accounts[$i]['credit']) ? $accounts[$i]['credit'] : 0;
                    $journalItem->save();


                    //start - save data for transaction lines
                    if ($journalItem->debit > 0) {
                        $transaction_type = 'Debit';
                        $transaction_amount = $journalItem->debit;
                    } else {
                        $transaction_type = 'Credit';
                        $transaction_amount = $journalItem->credit;
                    }
                    $data = [
                        'account_id' => $journalItem->account,
                        'transaction_type' => $transaction_type,
                        'transaction_amount' => $transaction_amount,
                        'reference' => 'Journal Entry',
                        'reference_id' => $journalEntry->id,
                        'reference_sub_id' => $journalItem->id,
                        'date' => $journalEntry->date,
                    ];
                    AccountUtility::addTransactionLines($data);

                    //end - save data for transaction lines

                    $bankAccounts = [];
                    if (module_is_active('Account')) {
                        $bankAccounts = \Modules\Account\Entities\BankAccount::where('chart_account_id', '=', $accounts[$i]['account'])->get();
                    }
                    if (!empty($bankAccounts)) {
                        foreach ($bankAccounts as $bankAccount) {
                            $old_balance = $bankAccount->opening_balance;
                            if ($journalItem->debit > 0) {
                                $new_balance = $old_balance - $journalItem->debit;
                            }
                            if ($journalItem->credit > 0) {
                                $new_balance = $old_balance + $journalItem->credit;
                            }
                            if (isset($new_balance)) {
                                $bankAccount->opening_balance = $new_balance;
                                $bankAccount->save();
                            }
                        }
                    }
                }

                if (module_is_active('CustomField')) {
                    \Modules\CustomField\Entities\CustomField::saveData($journalEntry, $request->customField);
                }

                event(new UpdateJournalAccount($request, $journalEntry));

                return redirect()->route('journal-entry.index')->with('success', __('Journal entry successfully updated.'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */


    public function destroy(JournalEntry $journalEntry)
    {

        if (Auth::user()->isAbleTo('journalentry delete')) {
            if ($journalEntry->created_by == creatorId()) {
                $journalItems = JournalItem::where('journal', '=', $journalEntry->id)->get();
                if (module_is_active('CustomField')) {
                    $customFields = \Modules\CustomField\Entities\CustomField::where('module', 'DoubleEntry')->where('sub_module', 'Journal Account')->get();
                    foreach ($customFields as $customField) {
                        $value = \Modules\CustomField\Entities\CustomFieldValue::where('record_id', '=', $journalEntry->id)->where('field_id', $customField->id)->first();
                        if (!empty($value)) {
                            $value->delete();
                        }
                    }
                }
                foreach ($journalItems as $journalItem) {
                    $account = $journalItem->account;

                    //item delete to this table
                    TransactionLines::where('reference_id', $journalEntry->id)->where('reference_sub_id', $journalItem->id)
                        ->where('reference', 'Journal Entry')->delete();

                    if (module_is_active('Account')) {
                        $bankAccounts = \Modules\Account\Entities\BankAccount::where('chart_account_id', '=', $account)->get();

                        foreach ($bankAccounts as $bankAccount) {
                            $old_balance = $bankAccount->opening_balance;

                            if ($journalItem->debit > 0) {
                                $new_balance = $old_balance - $journalItem->debit;
                            }
                            if ($journalItem->credit > 0) {
                                $new_balance = $old_balance + $journalItem->credit;
                            }

                            if (isset($new_balance)) {
                                $bankAccount->opening_balance = $new_balance;
                                $bankAccount->save();
                            }
                        }
                    }

                    // Delete the individual JournalItem record
                    $journalItem->delete();
                }

                // Finally, delete the JournalEntry record
                $journalEntry->delete();


                event(new DestroyJournalAccount($journalEntry));

                return redirect()->route('journal-entry.index')->with('success', __('Journal entry successfully deleted.'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function journalDestroy($item_id)
    {
        if (Auth::user()->isAbleTo('journalentry delete')) {
            $journal = JournalItem::find($item_id);

            //item delete to this table
            TransactionLines::where('reference_id', $journal->journal)->where('reference_sub_id', $journal->id)
                ->where('reference', 'Journal Entry')->delete();

            $journal->delete();

            return redirect()->back()->with('success', __('Journal account successfully deleted.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function setting(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'journal_prefix' => 'required',
            ]
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first());
        } else {

            $post = $request->all();
            unset($post['_token']);

            foreach ($post as $key => $value) {
                // Define the data to be updated or inserted
                $data = [
                    'key' => $key,
                    'workspace' => getActiveWorkSpace(),
                    'created_by' => creatorId(),
                ];
                // Check if the record exists, and update or insert accordingly
                Setting::updateOrInsert($data, ['value' => $value]);
            }
            // Settings Cache forget
            comapnySettingCacheForget();

            return redirect()->back()->with('success', 'Journal setting save sucessfully.');
        }
    }
}
