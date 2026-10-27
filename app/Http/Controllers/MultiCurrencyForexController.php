<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use App\Models\CurrencyExchangeRateHistory;
use App\Models\ForexRevaluation;
use App\Services\MultiCurrencyForexService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Workdo\Account\Models\BankAccount;

class MultiCurrencyForexController extends Controller
{
    /**
     * Display Multi-Currency Exchange Rates & Unrealized Forex Gain/Loss Engine (SRS Modules 8 & 28).
     */
    public function index(Request $request)
    {
        if (!Auth::user()->can('manage-account')) {
            return back()->with('error', __('Permission denied.'));
        }

        $creatorId = creatorId();

        // 1. Ensure initial currencies exist
        if (Currency::count() === 0) {
            MultiCurrencyForexService::seedCurrencies($creatorId);
        }

        // 2. Fetch all currencies
        $currencies = Currency::orderBy('is_base', 'desc')
            ->orderBy('code', 'asc')
            ->get();

        // 3. Fetch foreign monetary holdings & real-time gain/loss
        $holdingsData = MultiCurrencyForexService::getForeignHoldings($creatorId);

        // 4. Fetch revaluations ledger
        $revaluations = ForexRevaluation::where('created_by', $creatorId)
            ->with(['journalEntry', 'reversalJournalEntry', 'bankAccount', 'revaluer'])
            ->latest('revaluation_date')
            ->get();

        // 5. Fetch recent exchange rate histories (last 30 days)
        $rateHistories = CurrencyExchangeRateHistory::where('created_by', $creatorId)
            ->latest('rate_date')
            ->take(50)
            ->get();

        // 6. Fetch eligible foreign currency bank accounts for revaluation
        $foreignBankAccounts = BankAccount::where('created_by', $creatorId)
            ->where('is_active', true)
            ->where('currency', '!=', 'TZS')
            ->get(['id', 'account_name', 'account_number', 'bank_name', 'currency', 'current_balance', 'currency_rate']);

        // 7. Calculate KPI summary metrics
        $baseCurrency = $currencies->firstWhere('is_base', true) ?? (object)['code' => 'TZS', 'name' => 'Tanzanian Shilling'];
        $foreignCurrenciesCount = $currencies->where('is_base', false)->where('is_active', true)->count();
        $totalForeignHoldingsTzs = $holdingsData['total_foreign_holdings_tzs'];
        $totalUnrealizedGainLossTzs = $holdingsData['total_unrealized_gain_loss_tzs'];
        $postedRevaluationsCount = $revaluations->where('status', 'posted')->count();

        return Inertia::render('Accounting/MultiCurrency/Index', [
            'currencies' => $currencies,
            'base_currency' => $baseCurrency,
            'foreign_currencies_count' => $foreignCurrenciesCount,
            'holdings' => $holdingsData['holdings'],
            'total_foreign_holdings_tzs' => $totalForeignHoldingsTzs,
            'total_unrealized_gain_loss_tzs' => $totalUnrealizedGainLossTzs,
            'revaluations' => $revaluations,
            'posted_revaluations_count' => $postedRevaluationsCount,
            'rate_histories' => $rateHistories,
            'foreign_bank_accounts' => $foreignBankAccounts,
        ]);
    }

    /**
     * Store (Create) a new Foreign Currency (CUD: Create).
     */
    public function storeCurrency(Request $request)
    {
        if (!Auth::user()->can('manage-account')) {
            return back()->with('error', __('Permission denied.'));
        }

        $validated = $request->validate([
            'code' => 'required|string|size:3|unique:currencies,code',
            'name' => 'required|string|max:100',
            'symbol' => 'required|string|max:10',
            'exchange_rate' => 'required|numeric|gt:0',
            'buy_rate' => 'nullable|numeric|gt:0',
            'sell_rate' => 'nullable|numeric|gt:0',
            'auto_sync' => 'nullable|boolean',
        ]);

        $creatorId = creatorId();
        $midRate = (float) $validated['exchange_rate'];
        $buyRate = !empty($validated['buy_rate']) ? (float)$validated['buy_rate'] : round($midRate * 0.996, 4);
        $sellRate = !empty($validated['sell_rate']) ? (float)$validated['sell_rate'] : round($midRate * 1.004, 4);

        $currency = Currency::create([
            'code' => strtoupper($validated['code']),
            'name' => $validated['name'],
            'symbol' => $validated['symbol'],
            'exchange_rate' => $midRate,
            'buy_rate' => $buyRate,
            'sell_rate' => $sellRate,
            'bot_indicative_rate' => $midRate,
            'is_base' => false,
            'is_active' => true,
            'auto_sync' => (bool) ($validated['auto_sync'] ?? true),
            'last_synced_at' => now(),
            'creator_id' => Auth::id(),
            'created_by' => $creatorId,
        ]);

        CurrencyExchangeRateHistory::create([
            'currency_code' => $currency->code,
            'rate_date' => now()->toDateString(),
            'exchange_rate' => $midRate,
            'buy_rate' => $buyRate,
            'sell_rate' => $sellRate,
            'bot_indicative_rate' => $midRate,
            'source' => 'manual',
            'creator_id' => Auth::id(),
            'created_by' => $creatorId,
        ]);

        return redirect()->back()->with('success', __('Currency :code has been successfully created.', ['code' => $currency->code]));
    }

    /**
     * Update an existing Foreign Currency (CUD: Update).
     */
    public function updateCurrency(Request $request, Currency $currency)
    {
        if (!Auth::user()->can('manage-account')) {
            return back()->with('error', __('Permission denied.'));
        }

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'symbol' => 'required|string|max:10',
            'exchange_rate' => 'required|numeric|gt:0',
            'buy_rate' => 'nullable|numeric|gt:0',
            'sell_rate' => 'nullable|numeric|gt:0',
            'is_active' => 'nullable|boolean',
            'auto_sync' => 'nullable|boolean',
        ]);

        $creatorId = creatorId();
        $newMid = (float) $validated['exchange_rate'];
        $rateChanged = abs((float)$currency->exchange_rate - $newMid) > 0.0001;

        $currency->name = $validated['name'];
        $currency->symbol = $validated['symbol'];
        $currency->exchange_rate = $newMid;
        $currency->buy_rate = !empty($validated['buy_rate']) ? (float)$validated['buy_rate'] : round($newMid * 0.996, 4);
        $currency->sell_rate = !empty($validated['sell_rate']) ? (float)$validated['sell_rate'] : round($newMid * 1.004, 4);
        if (isset($validated['is_active'])) {
            $currency->is_active = (bool) $validated['is_active'];
        }
        if (isset($validated['auto_sync'])) {
            $currency->auto_sync = (bool) $validated['auto_sync'];
        }
        $currency->last_synced_at = now();
        $currency->save();

        if ($rateChanged) {
            CurrencyExchangeRateHistory::updateOrCreate(
                [
                    'currency_code' => $currency->code,
                    'rate_date' => now()->toDateString(),
                ],
                [
                    'exchange_rate' => $newMid,
                    'buy_rate' => $currency->buy_rate,
                    'sell_rate' => $currency->sell_rate,
                    'bot_indicative_rate' => $currency->bot_indicative_rate,
                    'source' => 'manual',
                    'creator_id' => Auth::id(),
                    'created_by' => $creatorId,
                ]
            );
        }

        return redirect()->back()->with('success', __('Currency :code updated successfully.', ['code' => $currency->code]));
    }

    /**
     * Delete an existing Foreign Currency (CUD: Delete).
     */
    public function destroyCurrency(Currency $currency)
    {
        if (!Auth::user()->can('manage-account')) {
            return back()->with('error', __('Permission denied.'));
        }

        if ($currency->is_base) {
            return back()->with('error', __('Cannot delete base functional currency (TZS).'));
        }

        $code = $currency->code;
        $currency->delete();

        return redirect()->back()->with('success', __('Currency :code deleted successfully.', ['code' => $code]));
    }

    /**
     * Auto-sync live Bank of Tanzania (BoT) foreign exchange indicative rates (CUD: Auto-Sync).
     */
    public function syncRates()
    {
        if (!Auth::user()->can('manage-account')) {
            return back()->with('error', __('Permission denied.'));
        }

        $result = MultiCurrencyForexService::syncBankOfTanzaniaRates(creatorId());

        return redirect()->back()->with('success', __('Successfully synchronized :count currencies with Bank of Tanzania (BoT) indicative rates.', [
            'count' => $result['updated_count'],
        ]));
    }

    /**
     * Run IAS 21 Unrealized Forex Gain/Loss Revaluation (CUD: Create).
     */
    public function runRevaluation(Request $request)
    {
        if (!Auth::user()->can('manage-account')) {
            return back()->with('error', __('Permission denied.'));
        }

        $validated = $request->validate([
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'revaluation_date' => 'required|date',
            'notes' => 'nullable|string|max:500',
        ]);

        $creatorId = creatorId();
        $reval = MultiCurrencyForexService::runRevaluation(
            (int) $validated['bank_account_id'],
            $validated['revaluation_date'],
            $validated['notes'] ?? null,
            $creatorId,
            Auth::id()
        );

        $sign = $reval->unrealized_gain_loss >= 0 ? '+' : '';
        return redirect()->back()->with('success', __('Forex Revaluation :num posted successfully. Gain/Loss: :val TZS.', [
            'num' => $reval->revaluation_number,
            'val' => $sign . number_format($reval->unrealized_gain_loss, 2),
        ]));
    }

    /**
     * Reverse a period-end Forex Revaluation (CUD: Update / Reverse).
     */
    public function reverseRevaluation(ForexRevaluation $revaluation)
    {
        if (!Auth::user()->can('manage-account')) {
            return back()->with('error', __('Permission denied.'));
        }

        if ($revaluation->status === 'reversed') {
            return back()->with('error', __('This revaluation is already reversed.'));
        }

        $creatorId = creatorId();
        MultiCurrencyForexService::reverseRevaluation($revaluation, $creatorId, Auth::id());

        return redirect()->back()->with('success', __('Revaluation :num has been reversed with a reversing journal voucher.', [
            'num' => $revaluation->revaluation_number,
        ]));
    }

    /**
     * Delete an unposted / draft Forex Revaluation (CUD: Delete).
     */
    public function destroyRevaluation(ForexRevaluation $revaluation)
    {
        if (!Auth::user()->can('manage-account')) {
            return back()->with('error', __('Permission denied.'));
        }

        if ($revaluation->status === 'posted') {
            return back()->with('error', __('Posted revaluations cannot be directly deleted. Please reverse the revaluation instead to preserve financial audit trail immutability.'));
        }

        $num = $revaluation->revaluation_number;
        $revaluation->delete();

        return redirect()->back()->with('success', __('Revaluation :num deleted successfully.', ['num' => $num]));
    }
}
