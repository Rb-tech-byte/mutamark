<?php

namespace Database\Seeders;

use App\Models\ClearingForwarding\CFCustomsRule;
use App\Models\User;
use Illuminate\Database\Seeder;

class CFCustomsRulesSeeder extends Seeder
{
    public function run(): void
    {
        $companyIds = User::where('type', 'company')->pluck('id');
        if ($companyIds->isEmpty()) {
            $companyIds = collect([1, 2]);
        }

        $defaultRules = [
            [
                'cpc_code' => 'C40',
                'procedure_name' => 'Direct Import for Home Consumption',
                'country_code' => 'TZ',
                'import_duty_rate' => 25.00,
                'vat_rate' => 18.00,
                'excise_duty_rate' => 0.00,
                'rdl_rate' => 1.50,
                'notes' => 'Standard TRA direct import duty, VAT (18%), and Railway Development Levy (1.5%).',
            ],
            [
                'cpc_code' => 'C40-RM',
                'procedure_name' => 'Import of Industrial Raw Materials (EAC CET 0%)',
                'country_code' => 'TZ',
                'import_duty_rate' => 0.00,
                'vat_rate' => 18.00,
                'excise_duty_rate' => 0.00,
                'rdl_rate' => 1.50,
                'notes' => 'Raw materials qualifying for 0% EAC Common External Tariff.',
            ],
            [
                'cpc_code' => 'T8',
                'procedure_name' => 'Direct Transit Cargo (Bond Exempt)',
                'country_code' => 'TZ',
                'import_duty_rate' => 0.00,
                'vat_rate' => 0.00,
                'excise_duty_rate' => 0.00,
                'rdl_rate' => 0.00,
                'notes' => 'Transit cargo through Dar/Tanga port under customs transit bond (T1).',
            ],
            [
                'cpc_code' => 'E10',
                'procedure_name' => 'Direct Export of Domestic Goods',
                'country_code' => 'TZ',
                'import_duty_rate' => 0.00,
                'vat_rate' => 0.00,
                'excise_duty_rate' => 0.00,
                'rdl_rate' => 0.00,
                'notes' => 'Zero-rated export declaration.',
            ],
            [
                'cpc_code' => 'S7',
                'procedure_name' => 'Entry into Bonded Warehouse',
                'country_code' => 'TZ',
                'import_duty_rate' => 0.00,
                'vat_rate' => 0.00,
                'excise_duty_rate' => 0.00,
                'rdl_rate' => 0.00,
                'notes' => 'Duties suspended while cargo is held in licensed customs bonded warehouse.',
            ],
            [
                'cpc_code' => 'EX3',
                'procedure_name' => 'Re-Export of Previously Imported Goods',
                'country_code' => 'TZ',
                'import_duty_rate' => 0.00,
                'vat_rate' => 0.00,
                'excise_duty_rate' => 0.00,
                'rdl_rate' => 0.00,
                'notes' => 'Re-exportation from bonded warehouse or temporary importation.',
            ],
        ];

        foreach ($companyIds as $companyId) {
            foreach ($defaultRules as $rule) {
                CFCustomsRule::firstOrCreate(
                    [
                        'created_by' => $companyId,
                        'cpc_code' => $rule['cpc_code'],
                        'country_code' => $rule['country_code'],
                    ],
                    array_merge($rule, [
                        'created_by' => $companyId,
                        'is_active' => true,
                    ])
                );
            }
        }
    }
}

