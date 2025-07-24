<?php

namespace App\Providers;

use App\Models\Task;
use App\Observers\ActivityObserver;
use App\Policies\ActivityPolicy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Spatie\Activitylog\Models\Activity;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Activity::class, ActivityPolicy::class);

        // observe all model
        // Task::observe(ActivityObserver::class);
    }
}
