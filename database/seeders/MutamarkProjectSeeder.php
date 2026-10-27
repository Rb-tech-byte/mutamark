<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Timesheet;
use Workdo\Taskly\Models\Project;
use Workdo\Taskly\Models\ProjectMilestone;
use Workdo\Taskly\Models\ProjectTask;
use Workdo\Taskly\Models\ProjectBug;
use Workdo\Taskly\Models\ProjectUser;
use Workdo\Taskly\Models\TaskStage;
use Workdo\Taskly\Models\BugStage;

class MutamarkProjectSeeder extends Seeder
{
    public function run(): void
    {
        $companyUser = User::where('email', 'company@example.com')->first() ?? User::find(2);
        if (!$companyUser) {
            $this->command->error("Company user not found.");
            return;
        }

        $creatorId = $companyUser->id;
        $workspace = function_exists('getActiveWorkSpace') ? (getActiveWorkSpace() ?: 1) : 1;

        // 1. Ensure Task Stages exist
        $stages = ['To Do', 'In Progress', 'Review', 'Done'];
        $stageModels = [];
        $order = 1;
        foreach ($stages as $stName) {
            $stage = TaskStage::firstOrCreate(
                ['name' => $stName, 'created_by' => $creatorId],
                ['order' => $order++, 'color' => '#3b82f6', 'complete' => $stName === 'Done' ? 1 : 0, 'creator_id' => $creatorId]
            );
            $stageModels[$stName] = $stage;
        }

        // 2. Ensure Bug Stages exist
        $bStages = ['Unconfirmed', 'Confirmed', 'In Progress', 'Resolved'];
        $bugStageModels = [];
        $bOrder = 1;
        foreach ($bStages as $bstName) {
            $bStage = BugStage::firstOrCreate(
                ['name' => $bstName, 'created_by' => $creatorId],
                ['order' => $bOrder++, 'color' => '#ef4444', 'complete' => $bstName === 'Resolved' ? 1 : 0, 'creator_id' => $creatorId]
            );
            $bugStageModels[$bstName] = $bStage;
        }

        // Clean existing mock projects if re-seeding
        Project::where('created_by', $creatorId)->where('name', 'like', '%Dar es Salaam%')->delete();
        Project::where('created_by', $creatorId)->where('name', 'like', '%Dodoma%')->delete();
        Project::where('created_by', $creatorId)->where('name', 'like', '%Mwanza%')->delete();

        // 3. Projects
        $proj1 = Project::create([
            'name' => 'Dar es Salaam Metro WAN & 96-Core Fiber Backbone',
            'description' => 'Turnkey deployment of metro underground fiber ducts, splicing cabinets, and 10Gbps optical transport network across Ilala, Kinondoni, and Temeke.',
            'budget' => 185000000.00,
            'start_date' => now()->subMonths(2)->toDateString(),
            'end_date' => now()->addMonths(3)->toDateString(),
            'status' => 'Ongoing',
            'creator_id' => $creatorId,
            'created_by' => $creatorId,
        ]);

        $proj2 = Project::create([
            'name' => 'Dodoma Government City Data Center Server Deployment',
            'description' => 'Installation and configuration of redundant Cisco Nexus switches, high-density server rack PDUs, and cold aisle containment.',
            'budget' => 95000000.00,
            'start_date' => now()->subMonth()->toDateString(),
            'end_date' => now()->addMonths(2)->toDateString(),
            'status' => 'Ongoing',
            'creator_id' => $creatorId,
            'created_by' => $creatorId,
        ]);

        $proj3 = Project::create([
            'name' => 'Mwanza Lake-Zone Hybrid Solar & Power Backup Integration',
            'description' => 'Deployment of 20kVA Victron hybrid solar inverters, lithium iron phosphate battery banks, and remote telemetry for regional repeater stations.',
            'budget' => 42000000.00,
            'start_date' => now()->subMonths(3)->toDateString(),
            'end_date' => now()->subDays(5)->toDateString(),
            'status' => 'Finished',
            'creator_id' => $creatorId,
            'created_by' => $creatorId,
        ]);

        // Assign User to Projects
        foreach ([$proj1, $proj2, $proj3] as $p) {
            DB::table('project_users')->insertOrIgnore([
                'project_id' => $p->id,
                'user_id' => $creatorId,
            ]);
        }

        // 4. Project Milestones
        $m1 = ProjectMilestone::create([
            'project_id' => $proj1->id,
            'title' => 'Civil Trenching & Underground Ducting (Ilala to Posta)',
            'status' => 'complete',
            'cost' => 55000000.00,
            'summary' => 'Excavation of 14km micro-trenching, warning tape placement and manhole installations.',
            'progress' => 100,
            'start_date' => now()->subMonths(2)->toDateString(),
            'end_date' => now()->subMonth()->toDateString(),
        ]);

        $m2 = ProjectMilestone::create([
            'project_id' => $proj1->id,
            'title' => 'Optical Fiber Cable Blowing & Fusion Splicing (48 Cores)',
            'status' => 'in_progress',
            'cost' => 75000000.00,
            'summary' => 'Compressed air cable blowing through micro-ducts and ODF fusion termination.',
            'progress' => 65,
            'start_date' => now()->subMonth()->toDateString(),
            'end_date' => now()->addMonth()->toDateString(),
        ]);

        $m3 = ProjectMilestone::create([
            'project_id' => $proj1->id,
            'title' => 'OTDR Power Loss Testing & Final Network Commissioning',
            'status' => 'incomplete',
            'cost' => 55000000.00,
            'summary' => 'Comprehensive bi-directional OTDR loss certification and 10G link latency verification.',
            'progress' => 10,
            'start_date' => now()->addMonth()->toDateString(),
            'end_date' => now()->addMonths(3)->toDateString(),
        ]);

        // 5. Project Tasks
        $task1 = ProjectTask::create([
            'project_id' => $proj1->id,
            'milestone_id' => $m2->id,
            'title' => 'Splice Core 1-24 at Posta Main Distribution Frame',
            'priority' => 'High',
            'stage_id' => $stageModels['In Progress']->id,
            'assigned_to' => json_encode([$creatorId]),
            'duration' => '16 hours',
            'description' => 'Precision fusion splicing with heat shrink protection sleeves in main telecom room.',
            'creator_id' => $creatorId,
            'created_by' => $creatorId,
        ]);

        $task2 = ProjectTask::create([
            'project_id' => $proj1->id,
            'milestone_id' => $m2->id,
            'title' => 'Optical Attenuation dB Check on Kinondoni Segment',
            'priority' => 'High',
            'stage_id' => $stageModels['To Do']->id,
            'assigned_to' => json_encode([$creatorId]),
            'duration' => '8 hours',
            'description' => 'Verify dB loss is strictly under 0.22 dB/km across all singlemode cores.',
            'creator_id' => $creatorId,
            'created_by' => $creatorId,
        ]);

        $task3 = ProjectTask::create([
            'project_id' => $proj2->id,
            'title' => 'Mount Cisco Nexus 9300 Switches and Redundant Power Feeds',
            'priority' => 'High',
            'stage_id' => $stageModels['Review']->id,
            'assigned_to' => json_encode([$creatorId]),
            'duration' => '12 hours',
            'description' => 'Dual PSU connection to A+B ATS power sources with 10Gbps SFP+ uplinks.',
            'creator_id' => $creatorId,
            'created_by' => $creatorId,
        ]);

        // 6. Project Bugs / Issues
        ProjectBug::create([
            'project_id' => $proj1->id,
            'title' => 'Optical Reflection Spike at Junction Box JB-04 (Posta Loop)',
            'priority' => 'High',
            'stage_id' => $bugStageModels['In Progress']->id,
            'assigned_to' => json_encode([$creatorId]),
            'description' => 'OTDR indicates reflectance of -32dB at 8.4km mark. Requires re-cleaning and re-splicing tray 2.',
            'creator_id' => $creatorId,
            'created_by' => $creatorId,
        ]);

        ProjectBug::create([
            'project_id' => $proj2->id,
            'title' => 'LACP Trunk Mismatch on Server Rack Interface Gi1/0/48',
            'priority' => 'Medium',
            'stage_id' => $bugStageModels['Resolved']->id,
            'assigned_to' => json_encode([$creatorId]),
            'description' => 'Native VLAN mismatch resolved after updating port-channel configuration to allow VLAN 100, 200.',
            'creator_id' => $creatorId,
            'created_by' => $creatorId,
        ]);

        // 7. Project Timesheets & Labor Costing (in TZS)
        Timesheet::where('created_by', $creatorId)->delete();

        $timesheetEntries = [
            [
                'user_id' => $creatorId,
                'project_id' => $proj1->id,
                'task_id' => $task1->id,
                'date' => now()->subDays(3)->toDateString(),
                'hours' => 8,
                'minutes' => 0,
                'hourly_rate' => 35000.00, // 35,000 TZS / hr
                'total_cost' => 8 * 35000.00, // 280,000 TZS
                'type' => 'Fiber Optics Engineering',
                'status' => 'Approved',
                'notes' => 'Completed fusion splicing on first 24 cores of ODF-A at Posta exchange.',
            ],
            [
                'user_id' => $creatorId,
                'project_id' => $proj1->id,
                'task_id' => $task1->id,
                'date' => now()->subDays(2)->toDateString(),
                'hours' => 7,
                'minutes' => 30,
                'hourly_rate' => 35000.00,
                'total_cost' => 7.5 * 35000.00, // 262,500 TZS
                'type' => 'Fiber Optics Engineering',
                'status' => 'Approved',
                'notes' => 'Heat shrink protection labeling and cable management in rack ODF-B.',
            ],
            [
                'user_id' => $creatorId,
                'project_id' => $proj2->id,
                'task_id' => $task3->id,
                'date' => now()->subDay()->toDateString(),
                'hours' => 6,
                'minutes' => 0,
                'hourly_rate' => 45000.00, // 45,000 TZS / hr (Senior Infrastructure Architect)
                'total_cost' => 6 * 45000.00, // 270,000 TZS
                'type' => 'Data Center Architecture',
                'status' => 'Approved',
                'notes' => 'Configured VPC peer link and redundant VRRP gateways for Dodoma DC core.',
            ],
            [
                'user_id' => $creatorId,
                'project_id' => $proj2->id,
                'task_id' => $task3->id,
                'date' => now()->toDateString(),
                'hours' => 5,
                'minutes' => 0,
                'hourly_rate' => 45000.00,
                'total_cost' => 5 * 45000.00, // 225,000 TZS
                'type' => 'Data Center Architecture',
                'status' => 'Approved',
                'notes' => 'Simulated power outage failover test between UPS feeds A and B with zero packet loss.',
            ],
            [
                'user_id' => $creatorId,
                'project_id' => $proj3->id,
                'task_id' => null,
                'date' => now()->subDays(10)->toDateString(),
                'hours' => 8,
                'minutes' => 0,
                'hourly_rate' => 30000.00, // 30,000 TZS / hr
                'total_cost' => 8 * 30000.00, // 240,000 TZS
                'type' => 'Solar Power Engineering',
                'status' => 'Approved',
                'notes' => 'Final acceptance signoff and inverter telemetry calibration in Mwanza.',
            ],
        ];

        foreach ($timesheetEntries as $entry) {
            Timesheet::create(array_merge($entry, [
                'workspace_id' => $workspace,
                'created_by' => $creatorId,
            ]));
        }

        $this->command->info("Mutamark Projects, Milestones, Tasks, and Timesheet Costing seeded successfully in TZS!");
    }
}
