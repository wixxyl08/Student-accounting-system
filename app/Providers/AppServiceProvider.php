<?php

namespace App\Providers;

use App\Models\Employee;
use App\Models\Group;
use App\Models\Organization;
use App\Models\Program;
use App\Observers\EmployeeObserver;
use App\Observers\GroupObserver;
use App\Observers\OrganizationObserver;
use App\Observers\ProgramObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Organization::observe(OrganizationObserver::class);
        Employee::observe(EmployeeObserver::class);
        Program::observe(ProgramObserver::class);
        Group::observe(GroupObserver::class);
    }
}