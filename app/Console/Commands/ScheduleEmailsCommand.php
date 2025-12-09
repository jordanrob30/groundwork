<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Campaign;
use App\Services\SendEngineService;
use Illuminate\Console\Command;

class ScheduleEmailsCommand extends Command
{
    protected $signature = 'emails:schedule {--campaign= : Schedule emails for a specific campaign}';

    protected $description = 'Schedule queued emails for sending throughout the day';

    public function handle(SendEngineService $service): int
    {
        $campaignId = $this->option('campaign');

        if ($campaignId) {
            $campaigns = Campaign::where('id', $campaignId)
                ->where('status', Campaign::STATUS_ACTIVE)
                ->get();
        } else {
            $campaigns = Campaign::where('status', Campaign::STATUS_ACTIVE)->get();
        }

        if ($campaigns->isEmpty()) {
            $this->info('No active campaigns to schedule.');

            return self::SUCCESS;
        }

        $totalScheduled = 0;

        foreach ($campaigns as $campaign) {
            $this->line("Scheduling emails for campaign: {$campaign->name}");

            $queued = $service->queueCampaignEmails($campaign);
            $this->line("  - Queued {$queued} new emails");

            $scheduled = $service->scheduleEmailsForDay($campaign);
            $this->line("  - Scheduled {$scheduled} emails for today");

            $totalScheduled += $scheduled;
        }

        $this->info("Total emails scheduled: {$totalScheduled}");

        return self::SUCCESS;
    }
}
