<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Models\Timesheet;
use App\Models\User;
use Workdo\Taskly\Models\Project;
use Workdo\Taskly\Models\ProjectTask;

class ProjectTimesheetController extends Controller
{
    protected function getCreatorId()
    {
        return Auth::check() ? (creatorId() ?: 2) : 2;
    }

    public function index(Request $request)
    {
        $creatorId = $this->getCreatorId();

        $timesheets = Timesheet::with(['user:id,name,email', 'project:id,name', 'task:id,title,priority'])
            ->where('created_by', $creatorId)
            ->latest('date')
            ->get();

        $projects = Project::where('created_by', $creatorId)
            ->select('id', 'name', 'budget', 'status')
            ->get();

        $employees = User::where('created_by', $creatorId)
            ->where('type', '!=', 'superadmin')
            ->select('id', 'name', 'email')
            ->get();

        $tasks = ProjectTask::where('created_by', $creatorId)
            ->select('id', 'title', 'project_id')
            ->get();

        $totalHours = (float) $timesheets->sum('hours') + ($timesheets->sum('minutes') / 60);
        $totalCost = (float) $timesheets->sum('total_cost');

        $stats = [
            'total_hours' => round($totalHours, 1),
            'total_cost' => $totalCost,
            'approved_entries' => $timesheets->where('status', 'Approved')->count(),
            'pending_entries' => $timesheets->where('status', 'Pending')->count(),
        ];

        return Inertia::render('Projects/Timesheets', [
            'timesheets' => $timesheets,
            'projects' => $projects,
            'employees' => $employees,
            'tasks' => $tasks,
            'stats' => $stats,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'project_id' => 'required|exists:projects,id',
            'task_id' => 'nullable|exists:project_tasks,id',
            'date' => 'required|date',
            'hours' => 'required|numeric|min:0|max:24',
            'minutes' => 'nullable|numeric|min:0|max:59',
            'hourly_rate' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
            'type' => 'nullable|string|max:50',
        ]);

        $creatorId = $this->getCreatorId();
        $hours = (float)$validated['hours'];
        $minutes = (float)($validated['minutes'] ?? 0);
        $rate = (float)$validated['hourly_rate'];
        $totalCost = ($hours + ($minutes / 60)) * $rate;

        $timesheet = new Timesheet();
        $timesheet->fill($validated);
        $timesheet->total_cost = $totalCost;
        $timesheet->type = $validated['type'] ?? 'Project Milestone Execution';
        $timesheet->status = 'Approved';
        $timesheet->created_by = $creatorId;
        $timesheet->save();

        return back()->with('success', __('Project timesheet & labor cost logged successfully.'));
    }

    public function updateStatus(Request $request, $id)
    {
        $creatorId = $this->getCreatorId();
        $timesheet = Timesheet::where('created_by', $creatorId)->findOrFail($id);
        $timesheet->status = $request->input('status', 'Approved');
        $timesheet->save();

        return back()->with('success', __('Timesheet status updated successfully.'));
    }

    public function destroy($id)
    {
        $creatorId = $this->getCreatorId();
        $timesheet = Timesheet::where('created_by', $creatorId)->findOrFail($id);
        $timesheet->delete();

        return back()->with('success', __('Timesheet entry removed successfully.'));
    }
}
