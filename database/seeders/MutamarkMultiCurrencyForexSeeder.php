<?php

namespace Database\Seeders;

use App\Models\Currency;
use App\Models\CurrencyExchangeRateHistory;
use App\Models\ForexRevaluation;
use App\Services\MultiCurrencyForexService;
use Illuminate\Database\Seeder;
use Workdo\Account\Models\BankAccount;

class MutamarkMultiCurrencyForexSeeder extends Seeder
{
    public function run(): void
    {
        $creatorId = 2; // Default admin/company

        // 1. Seed supported currencies
        MultiCurrencyForexService::seedCurrencies($creatorId);

        // 2. Configure Bank Account #2 as a USD Foreign Currency Treasury Account
        $usdBank = BankAccount::where('id', 2)->first();
        if ($usdBank) {
            $usdBank->account_name = 'MUTAMARK ENTERPRISES (CRDB USD DOMICILIARY)';
            $usdBank->bank_name = 'CRDB Bank Plc';
            $usdBank->currency = 'USD';
            $usdBank->currency_rate = 2580.000000; // Prior book rate
            $usdBank->current_balance = 15000.00; // $15,000.00 USD
            $usdBank->save();
        }

        // 3. Configure a third bank account or add a EUR account
        $eurBank = BankAccount::where('id', 3)->first();
        if ($eurBank) {
            $eurBank->account_name = 'MUTAMARK ENTERPRISES (NMB EUR OPERATIONS)';
            $eurBank->bank_name = 'NMB Bank Plc';
            $eurBank->currency = 'EUR';
            $eurBank->currency_rate = 2840.000000; // Prior book rate
            $eurBank->current_balance = 5000.00; // €5,000.00 EUR
            $eurBank->save();
        }

        // 4. Seed an initial posted IAS 21 Forex Revaluation
        if ($usdBank) {
            MultiCurrencyForexService::runRevaluation(
                $usdBank->id,
                now()->subDays(5)->toDateString(),
                'Q3 Period-End IAS 21 Foreign Currency Revaluation against BoT indicative rate.',
                $creatorId,
                $creatorId
            );
        }
    }
}
