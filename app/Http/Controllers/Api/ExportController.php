<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Exports\OrganizationsExport;
use App\Exports\EmployeesExport;
use App\Exports\GroupsExport;
use App\Exports\NotificationsExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ExportController extends Controller
{
    public function organizations(Request $request)
    {
        $filename = 'organizations_' . date('Y-m-d_His') . '.xlsx';
        return Excel::download(new OrganizationsExport($request->all()), $filename);
    }

    public function employees(Request $request)
    {
        $filename = 'employees_' . date('Y-m-d_His') . '.xlsx';
        return Excel::download(new EmployeesExport($request->all()), $filename);
    }

    public function groups(Request $request)
    {
        $filename = 'groups_' . date('Y-m-d_His') . '.xlsx';
        return Excel::download(new GroupsExport($request->all()), $filename);
    }

    public function notifications(Request $request)
    {
        $filename = 'notifications_' . date('Y-m-d_His') . '.xlsx';
        return Excel::download(new NotificationsExport(), $filename);
    }
}