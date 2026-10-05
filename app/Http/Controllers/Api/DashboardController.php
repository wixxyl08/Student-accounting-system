<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\Employee;
use App\Models\Enrollment;
use App\Models\Group;
use App\Models\Organization;
use App\Models\Program;

class DashboardController extends Controller
{
    public function index()
    {
        return response()->json([
            'organizations_count' => Organization::count(),
            'employees_count' => Employee::where('status', 'active')->count(),
            'active_groups_count' => Group::whereIn('status', ['recruiting', 'ongoing'])->count(),
            'completed_count' => Enrollment::where('status', 'completed')->count(),
            'programs_count' => Program::where('status', 'active')->count(),
            'contracts_count' => Contract::count(),
        ]);
    }
}