<?php

namespace App\Observers;

use App\Models\ActivityLog;
use App\Models\Organization;

class OrganizationObserver
{
    public function created(Organization $organization): void
    {
        ActivityLog::log('created', $organization);
    }

    public function updated(Organization $organization): void
    {
        ActivityLog::log('updated', $organization);
    }

    public function deleted(Organization $organization): void
    {
        ActivityLog::log('deleted', $organization);
    }
}