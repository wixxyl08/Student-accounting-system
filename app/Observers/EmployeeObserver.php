<?php

namespace App\Observers;

use App\Models\ActivityLog;
use App\Models\Employee;

class EmployeeObserver
{
    public function created(Employee $employee): void
    {
        ActivityLog::log('created', $employee);
    }

    public function updated(Employee $employee): void
    {
        ActivityLog::log('updated', $employee);
    }

    public function deleted(Employee $employee): void
    {
        ActivityLog::log('deleted', $employee);
    }
}