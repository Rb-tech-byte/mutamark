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

        $docTypes = [
            ['DOC001 - National ID (NIDA)', 'National identification document', 1],
            ['DOC002 - Birth Certificate', 'Employee birth certificate', 0],
            ['DOC003 - Academic Certificate', 'Education qualifications', 1],
            ['DOC004 - Professional Certificate', 'Professional qualifications', 0],
            ['DOC005 - CV / Resume', 'Employee curriculum vitae', 1],
            ['DOC006 - Employment Contract', 'Signed employment agreement', 1],
            ['DOC007 - Appointment Letter', 'Employment appointment letter', 1],
            ['DOC008 - Promotion Letter', 'Employee promotion documentation', 0],
            ['DOC009 - Confirmation Letter', 'Confirmation of employment', 0],
            ['DOC010 - Transfer Letter', 'Employee transfer documentation', 0],
            ['DOC011 - Medical Certificate', 'Medical documentation', 0],
            ['DOC012 - Passport', 'Passport identification', 0],
            ['DOC013 - Driving Licence', 'Driving licence', 0],
            ['DOC014 - Work Permit', 'Work authorization document', 0],
            ['DOC015 - Tax Identification (TIN)', 'TRA identification document', 1],
            ['DOC016 - Social Security Number', 'NSSF/PSSSF registration', 1],
            ['DOC017 - Bank Details', 'Employee banking information', 1],
            ['DOC018 - Resignation Letter', 'Employee resignation', 0],
            ['DOC019 - Retirement Letter', 'Retirement documentation', 0],
            ['DOC020 - Other Documents', 'Other employee documents', 0],
        ];

        $awardTypes = [
            ['AWD001 - Employee of the Month', 'Monthly outstanding performance'],
            ['AWD002 - Employee of the Year', 'Annual outstanding performance'],
            ['AWD003 - Best Performer', 'Excellent job performance'],
            ['AWD004 - Long Service Award', 'Recognition for long service'],
            ['AWD005 - Innovation Award', 'Innovative ideas and solutions'],
            ['AWD006 - Leadership Award', 'Outstanding leadership'],
            ['AWD007 - Teamwork Award', 'Excellent teamwork'],
            ['AWD008 - Outstanding Attendance', 'Excellent attendance record'],
            ['AWD009 - Certificate of Appreciation', 'Recognition certificate'],
            ['AWD010 - Financial Reward', 'Monetary recognition'],
            ['AWD011 - Promotion Recognition', 'Recognition of career advancement'],
            ['AWD012 - Special Recognition', 'Other special achievements'],
        ];

        $terminationTypes = [
            'TER001 - Resignation',
            'TER002 - Retirement',
            'TER003 - Contract Expiry',
            'TER004 - Mutual Agreement',
            'TER005 - Dismissal',
            'TER006 - Redundancy',
            'TER007 - Death',
            'TER008 - Incapacity',
            'TER009 - Probation Termination',
            'TER010 - Abscondment',
            'TER011 - Early Retirement',
            'TER012 - Other',
        ];

        $warningTypes = [
            'WAR001 - Verbal Warning',
            'WAR002 - First Written Warning',
            'WAR003 - Second Written Warning',
            'WAR004 - Final Written Warning',
            'WAR005 - Attendance Warning',
            'WAR006 - Misconduct Warning',
            'WAR007 - Performance Warning',
            'WAR008 - Negligence Warning',
            'WAR009 - Insubordination Warning',
            'WAR010 - Policy Violation Warning',
        ];

        $complaintTypes = [
            'COM001 - Salary Complaint',
            'COM002 - Allowance Complaint',
            'COM003 - Workplace Harassment',
            'COM004 - Discrimination',
            'COM005 - Working Conditions',
            'COM006 - Workload Complaint',
            'COM007 - Leave Complaint',
            'COM008 - Promotion Complaint',
            'COM009 - Transfer Complaint',
            'COM010 - Management Complaint',
            'COM011 - Disciplinary Complaint',
            'COM012 - Health and Safety',
            'COM013 - Interpersonal Conflict',
            'COM014 - Other',
        ];

        $holidayTypes = [
            'HOL001 - National Public Holiday',
            'HOL002 - Religious Holiday',
            'HOL003 - Government Holiday',
            'HOL004 - Regional Holiday',
            'HOL005 - Organizational Holiday',
            'HOL006 - Special Public Holiday',
            'HOL007 - Half-Day Holiday',
            'HOL008 - Other',
        ];

        $docCategories = [
            'CAT001 - Personal Documents',
            'CAT002 - Recruitment Documents',
            'CAT003 - Employment Documents',
            'CAT004 - Academic Documents',
            'CAT005 - Professional Documents',
            'CAT006 - Payroll Documents',
            'CAT007 - Leave Documents',
            'CAT008 - Disciplinary Documents',
            'CAT009 - Performance Documents',
            'CAT010 - Promotion Documents',
            'CAT011 - Transfer Documents',
            'CAT012 - Medical Documents',
            'CAT013 - Retirement Documents',
            'CAT014 - Termination Documents',
            'CAT015 - Training Documents',
            'CAT016 - Other Documents',
        ];

        $announcementCategories = [
            'ANC001 - General Announcement',
            'ANC002 - HR Announcement',
            'ANC003 - Job Vacancy',
            'ANC004 - Recruitment',
            'ANC005 - Training',
            'ANC006 - Promotion',
            'ANC007 - Staff Meeting',
            'ANC008 - Policy Updates',
            'ANC009 - Public Holidays',
            'ANC010 - Payroll Notice',
            'ANC011 - Organizational Changes',
            'ANC012 - Emergency Notice',
            'ANC013 - Retirement Notice',
            'ANC014 - Other',
        ];

        $eventTypes = [
            'EVT001 - Staff Meeting',
            'EVT002 - Management Meeting',
            'EVT003 - Training Session',
            'EVT004 - Workshop',
            'EVT005 - Seminar',
            'EVT006 - Interview',
            'EVT007 - Performance Review',
            'EVT008 - Disciplinary Hearing',
            'EVT009 - Medical Examination',
            'EVT010 - Orientation',
            'EVT011 - Team Building',
            'EVT012 - Retirement Event',
            'EVT013 - Annual General Meeting',
            'EVT014 - Employee Engagement',
            'EVT015 - Other',
        ];

        $allowanceTypes = [
            ['ALL001 - Housing Allowance', 'Housing-related allowance'],
            ['ALL002 - Transport Allowance', 'Transportation allowance'],
            ['ALL003 - Travel Allowance', 'Official travel allowance'],
            ['ALL004 - Subsistence Allowance', 'Daily subsistence allowance'],
            ['ALL005 - Meal Allowance', 'Meal-related allowance'],
            ['ALL006 - Responsibility Allowance', 'Additional responsibility allowance'],
            ['ALL007 - Acting Allowance', 'Allowance for acting appointments'],
            ['ALL008 - Overtime Allowance', 'Approved overtime payment'],
            ['ALL009 - Communication Allowance', 'Telephone and communication costs'],
            ['ALL010 - Risk Allowance', 'Allowance for hazardous duties'],
            ['ALL011 - Hardship Allowance', 'Allowance for designated hardship areas'],
            ['ALL012 - Night Duty Allowance', 'Approved night-duty allowance'],
            ['ALL013 - Field Allowance', 'Fieldwork-related allowance'],
            ['ALL014 - Special Duty Allowance', 'Special assignment allowance'],
            ['ALL015 - Other Allowance', 'Other approved allowances'],
        ];

        $deductionTypes = [
            ['DED001 - PAYE', 'Pay As You Earn income tax'],
            ['DED002 - NSSF', 'National Social Security Fund contribution'],
            ['DED003 - PSSSF', 'Public Service Social Security Fund contribution'],
            ['DED004 - Health Insurance', 'Applicable health insurance contribution'],
            ['DED005 - Salary Advance', 'Recovery of salary advances'],
            ['DED006 - Staff Loan', 'Recovery of employee loans'],
            ['DED007 - SACCOS Contribution', 'Employee SACCOS deductions'],
            ['DED008 - Union Subscription', 'Trade union contributions'],
            ['DED009 - Court Order', 'Court-authorized deductions'],
            ['DED010 - Insurance Premium', 'Approved insurance deductions'],
            ['DED011 - Other Statutory Deduction', 'Other applicable statutory deductions'],
            ['DED012 - Other Deduction', 'Other authorized deductions'],
        ];

        $loanTypes = [
            ['LOA001 - Salary Advance', 'Advance payment against salary'],
            ['LOA002 - Emergency Loan', 'Loan for emergency situations'],
            ['LOA003 - Personal Loan', 'General personal employee loan'],
            ['LOA004 - Education Loan', 'Loan for education expenses'],
            ['LOA005 - Medical Loan', 'Loan for medical expenses'],
            ['LOA006 - Housing Loan', 'Housing-related employee loan'],
            ['LOA007 - Vehicle Loan', 'Vehicle purchase loan'],
            ['LOA008 - Furniture Loan', 'Furniture purchase loan'],
            ['LOA009 - Computer Loan', 'Computer or IT equipment loan'],
            ['LOA010 - Staff Development Loan', 'Professional development loan'],
            ['LOA011 - Other Loan', 'Other approved employee loans'],
        ];

        $now = now();

        foreach ($companyIds as $companyId) {
            if (Schema::hasTable('employee_document_types')) {
                foreach ($docTypes as [$name, $desc, $req]) {
                    DB::table('employee_document_types')->updateOrInsert(
                        ['created_by' => $companyId, 'document_name' => $name],
                        ['description' => $desc, 'is_required' => $req, 'creator_id' => $companyId, 'updated_at' => $now, 'created_at' => $now]
                    );
                }
            }

            if (Schema::hasTable('award_types')) {
                foreach ($awardTypes as [$name, $desc]) {
                    DB::table('award_types')->updateOrInsert(
                        ['created_by' => $companyId, 'name' => $name],
                        ['description' => $desc, 'creator_id' => $companyId, 'updated_at' => $now, 'created_at' => $now]
                    );
                }
            }

            if (Schema::hasTable('termination_types')) {
                foreach ($terminationTypes as $name) {
                    DB::table('termination_types')->updateOrInsert(
                        ['created_by' => $companyId, 'termination_type' => $name],
                        ['creator_id' => $companyId, 'updated_at' => $now, 'created_at' => $now]
                    );
                }
            }

            if (Schema::hasTable('warning_types')) {
                foreach ($warningTypes as $name) {
                    DB::table('warning_types')->updateOrInsert(
                        ['created_by' => $companyId, 'warning_type_name' => $name],
                        ['creator_id' => $companyId, 'updated_at' => $now, 'created_at' => $now]
                    );
                }
            }

            if (Schema::hasTable('complaint_types')) {
                foreach ($complaintTypes as $name) {
                    DB::table('complaint_types')->updateOrInsert(
                        ['created_by' => $companyId, 'complaint_type' => $name],
                        ['creator_id' => $companyId, 'updated_at' => $now, 'created_at' => $now]
                    );
                }
            }

            if (Schema::hasTable('holiday_types')) {
                foreach ($holidayTypes as $name) {
                    DB::table('holiday_types')->updateOrInsert(
                        ['created_by' => $companyId, 'holiday_type' => $name],
                        ['creator_id' => $companyId, 'updated_at' => $now, 'created_at' => $now]
                    );
                }
            }

            if (Schema::hasTable('document_categories')) {
                foreach ($docCategories as $name) {
                    DB::table('document_categories')->updateOrInsert(
                        ['created_by' => $companyId, 'document_type' => $name],
                        ['status' => 1, 'creator_id' => $companyId, 'updated_at' => $now, 'created_at' => $now]
                    );
                }
            }

            if (Schema::hasTable('announcement_categories')) {
                foreach ($announcementCategories as $name) {
                    DB::table('announcement_categories')->updateOrInsert(
                        ['created_by' => $companyId, 'announcement_category' => $name],
                        ['creator_id' => $companyId, 'updated_at' => $now, 'created_at' => $now]
                    );
                }
            }

            if (Schema::hasTable('event_types')) {
                foreach ($eventTypes as $name) {
                    DB::table('event_types')->updateOrInsert(
                        ['created_by' => $companyId, 'event_type' => $name],
                        ['creator_id' => $companyId, 'updated_at' => $now, 'created_at' => $now]
                    );
                }
            }

            if (Schema::hasTable('allowance_types')) {
                foreach ($allowanceTypes as [$name, $desc]) {
                    DB::table('allowance_types')->updateOrInsert(
                        ['created_by' => $companyId, 'name' => $name],
                        ['description' => $desc, 'creator_id' => $companyId, 'updated_at' => $now, 'created_at' => $now]
                    );
                }
            }

            if (Schema::hasTable('deduction_types')) {
                foreach ($deductionTypes as [$name, $desc]) {
                    DB::table('deduction_types')->updateOrInsert(
                        ['created_by' => $companyId, 'name' => $name],
                        ['description' => $desc, 'creator_id' => $companyId, 'updated_at' => $now, 'created_at' => $now]
                    );
                }
            }

            if (Schema::hasTable('loan_types')) {
                foreach ($loanTypes as [$name, $desc]) {
                    DB::table('loan_types')->updateOrInsert(
                        ['created_by' => $companyId, 'name' => $name],
                        ['description' => $desc, 'creator_id' => $companyId, 'updated_at' => $now, 'created_at' => $now]
                    );
                }
            }
        }
    }
}
