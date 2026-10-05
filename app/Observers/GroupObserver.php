<?php

namespace App\Observers;

use App\Models\ActivityLog;
use App\Models\Group;

class GroupObserver
{
    public function created(Group $group): void
    {
        ActivityLog::log('created', $group);
    }

    public function updated(Group $group): void
    {
        ActivityLog::log('updated', $group);
    }

    public function deleted(Group $group): void
    {
        ActivityLog::log('deleted', $group);
    }
}