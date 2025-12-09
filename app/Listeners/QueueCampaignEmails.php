<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\CampaignActivated;
use App\Services\SendEngineService;

class QueueCampaignEmails
{
    public function __construct(
        protected SendEngineService $sendEngine
    ) {}

    public function handle(CampaignActivated $event): void
    {
        $campaign = $event->campaign;

        // Queue emails for pending leads
        $this->sendEngine->queueCampaignEmails($campaign);

        // Schedule them for sending throughout the day
        $this->sendEngine->scheduleEmailsForDay($campaign);
    }
}
