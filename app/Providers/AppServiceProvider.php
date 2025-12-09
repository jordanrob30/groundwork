<?php

namespace App\Providers;

use App\Events\CampaignActivated;
use App\Events\ResponseReceived;
use App\Listeners\AnalyzeNewResponse;
use App\Listeners\QueueCampaignEmails;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

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
        Event::listen(ResponseReceived::class, AnalyzeNewResponse::class);
        Event::listen(CampaignActivated::class, QueueCampaignEmails::class);

        // Rate limiter for email sending - 60 emails per minute per mailbox
        RateLimiter::for('email-sending', function (object $job) {
            return Limit::perMinute(60)->by($job->sentEmail->mailbox_id);
        });

        // Gate for admin access
        Gate::define('access-admin', function ($user) {
            return $user->isAdmin();
        });
    }
}
