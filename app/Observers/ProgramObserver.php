<?php

namespace App\Observers;

use App\Models\ActivityLog;
use App\Models\Program;

class ProgramObserver
{
    public function created(Program $program): void
    {
        ActivityLog::log('created', $program);
    }

    public function updated(Program $program): void
    {
        ActivityLog::log('updated', $program);
    }

    public function deleted(Program $program): void
    {
        ActivityLog::log('deleted', $program);
    }
}