<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\ResponseReceived;
use App\Jobs\AnalyzeResponseJob;

class AnalyzeNewResponse
{
    public function handle(ResponseReceived $event): void
    {
        $response = $event->response;

        // Skip auto-replies - they don't need AI analysis
        if ($response->is_auto_reply) {
            return;
        }

        // Dispatch the analysis job
        AnalyzeResponseJob::dispatch($response);
    }
}
