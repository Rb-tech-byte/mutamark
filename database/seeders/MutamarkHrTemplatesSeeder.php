<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MutamarkHrTemplatesSeeder extends Seeder
{
    public function run(): void
    {
        $companyIds = User::where('type', 'company')->pluck('id');
        if ($companyIds->isEmpty()) {
            $companyIds = collect([1, 2]);
        }

        foreach ($companyIds as $companyId) {
            if (Schema::hasTable('allowance_types')) {
                DB::table('allowance_types')->updateOrInsert(
                    ['created_by' => $companyId, 'name' => 'Responsibility Allowance (ALL006)'],
                    ['creator_id' => $companyId, 'updated_at' => now(), 'created_at' => now()]
                );
            }

            if (Schema::hasTable('warning_types')) {
                foreach ([
                    'WAR005 - Attendance / Absenteeism',
                    'WAR006 - Performance Standard',
                    'WAR007 - Negligence',
                    'WAR008 - Insubordination',
                    'WAR009 - Policy Violation',
                    'WAR010 - Safety Breach',
                ] as $wt) {
                    DB::table('warning_types')->updateOrInsert(
                        ['created_by' => $companyId, 'warning_type_name' => $wt],
                        ['creator_id' => $companyId, 'updated_at' => now(), 'created_at' => now()]
                    );
                }
            }

            if (Schema::hasTable('termination_types')) {
                foreach ([
                    'TER001 - Resignation',
                    'TER002 - Dismissal',
                    'TER003 - Probation Failure',
                    'TER004 - Medical Incapacity',
                    'TER005 - Redundancy',
                    'TER006 - Contract Expiry',
                    'TER007 - Mutual Agreement',
                    'TER008 - Retirement',
                    'TER009 - Early Retirement',
                    'TER010 - Abscondment',
                    'TER011 - Death in Service',
                    'TER012 - Summary Dismissal',
                ] as $tt) {
                    DB::table('termination_types')->updateOrInsert(
                        ['created_by' => $companyId, 'termination_type' => $tt],
                        ['creator_id' => $companyId, 'updated_at' => now(), 'created_at' => now()]
                    );
                }
            }

            if (Schema::hasTable('award_types')) {
                foreach ([
                    'AWD001 - Employee of the Month',
                    'AWD002 - Innovation Award',
                    'AWD003 - Long Service Award',
                    'AWD004 - Safety Excellence Award',
                    'AWD005 - Customer Service Excellence',
                    'AWD006 - Team Leadership Award',
                ] as $at) {
                    DB::table('award_types')->updateOrInsert(
                        ['created_by' => $companyId, 'name' => $at],
                        ['creator_id' => $companyId, 'updated_at' => now(), 'created_at' => now()]
                    );
                }
            }

            if (Schema::hasTable('document_categories')) {
                foreach ([
                    'CAT009 - Performance & Promotion Documents',
                    'CAT014 - Termination, Clearance & Exit Files',
                ] as $dc) {
                    DB::table('document_categories')->updateOrInsert(
                        ['created_by' => $companyId, 'document_type' => $dc],
                        ['status' => 1, 'creator_id' => $companyId, 'updated_at' => now(), 'created_at' => now()]
                    );
                }
            }
        }
    }
}

