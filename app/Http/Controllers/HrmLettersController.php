<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\HrmLetterTemplateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;

class HrmLettersController extends Controller
{
    public function index()
    {
        abort_unless(Auth::check(), 403);

        $employees = collect();
        if (class_exists(\Workdo\Hrm\Models\Employee::class) && Schema::hasTable('employees')) {
            $employees = \Workdo\Hrm\Models\Employee::with(['user:id,name,email', 'department:id,department_name', 'designation:id,designation_name', 'branch:id,branch_name'])
                ->where('created_by', creatorId())
                ->get()
                ->map(fn ($e) => [
                    'id' => $e->id,
                    'employee_id' => $e->employee_id ?? ('EMP-' . $e->id),
                    'name' => $e->user?->name ?? '',
                    'email' => $e->user?->email ?? '',
                    'address' => $e->address ?? '',
                    'department' => $e->department?->department_name ?? '',
                    'designation' => $e->designation?->designation_name ?? '',
                    'branch' => $e->branch?->branch_name ?? '',
                    'salary' => $e->salary ?? '',
                ])
                ->values();
        } else {
            $employees = User::where('created_by', creatorId())
                ->where('type', '!=', 'client')
                ->get(['id', 'name', 'email'])
                ->map(fn ($u) => [
                    'id' => $u->id,
                    'employee_id' => 'EMP-' . $u->id,
                    'name' => $u->name,
                    'email' => $u->email,
                    'address' => '',
                    'department' => '',
                    'designation' => '',
                    'branch' => '',
                    'salary' => '',
                ]);
        }

        return Inertia::render('Hrm/Letters/Index', [
            'categories' => HrmLetterTemplateService::categories(),
            'templates' => HrmLetterTemplateService::templates(),
            'employees' => $employees,
        ]);
    }

    public function print(Request $request)
    {
        abort_unless(Auth::check(), 403);

        $code = (string) $request->input('code', 'EMP-APP');
        $payload = json_decode((string) $request->input('payload', '{}'), true);
        if (!is_array($payload)) {
            $payload = $request->all();
        }

        $rendered = HrmLetterTemplateService::render($code, $payload);

        return Inertia::render('Hrm/Letters/Print', [
            'letter' => $rendered,
        ]);
    }
}

