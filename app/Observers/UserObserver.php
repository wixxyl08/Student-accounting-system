<?php

namespace App\Observers;

use App\Models\ActivityLog;
use App\Models\User;

class UserObserver
{
    public function created(User $user): void
    {
        // Логируется в контроллере — чтобы не было дубля
    }

    public function updated(User $user): void
    {
        // Логируется в контроллере — чтобы не было дубля
    }

    public function deleted(User $user): void
    {
        // Логируется в контроллере — чтобы не было дубля
    }
}