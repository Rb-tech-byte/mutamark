<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\AutomationNotificationService;
use Illuminate\Console\Command;

class RunAutomationAlerts extends Command
{
    protected $signature = 'app:run-automation-alerts {--email : Also send email alerts when mail is configured}';
    protected $description = 'Scan all active company workspaces for low stock, due invoices/bills, pending approvals, and POS closing alerts';

    public function handle(): int
    {
        $sendEmail = (bool) $this->option('email');
        $companyIds = User::where('type', 'company')->pluck('id');
        $total = 0;

        foreach ($companyIds as $companyId) {
            $total += AutomationNotificationService::scanWorkspace((int) $companyId, $sendEmail);
        }

        $this->info("Generated {$total} new automation notification(s) across " . $companyIds->count() . ' workspace(s).');
        return self::SUCCESS;
    }
}

