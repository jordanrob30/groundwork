<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Metrics\Collectors\CampaignFlowCollector;
use App\Models\SentEmail;
use App\Services\SendEngineService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\RateLimited;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public array $backoff = [60, 300, 900];

    public function __construct(
        public SentEmail $sentEmail
    ) {
        $this->queue = 'emails';
    }

    public function middleware(): array
    {
        return [
            new RateLimited('email-sending'),
        ];
    }

    public function handle(SendEngineService $service): void
    {
        // Add campaign context to logs
        Log::shareContext([
            'campaign_id' => $this->sentEmail->campaign_id,
            'mailbox_id' => $this->sentEmail->mailbox_id,
            'sent_email_id' => $this->sentEmail->id,
        ]);

        Log::info('SendEmailJob started', [
            'recipient' => $this->sentEmail->recipient_email ?? 'unknown',
        ]);

        if (! $this->sentEmail->isQueued()) {
            Log::info('SendEmailJob skipped - email not in queued state');

            return;
        }

        $mailbox = $this->sentEmail->mailbox;
        if ($mailbox->hasReachedDailyLimit()) {
            Log::warning('SendEmailJob rate limited - mailbox daily limit reached');
            $this->release(3600);

            return;
        }

        $startTime = microtime(true);
        $success = $service->send($this->sentEmail);
        $duration = microtime(true) - $startTime;

        Log::info('SendEmailJob completed', [
            'success' => $success,
            'duration_ms' => round($duration * 1000, 2),
        ]);

        // Record metrics
        try {
            $collector = app(CampaignFlowCollector::class);
            $collector->incrementEmailSent(
                $this->sentEmail->campaign_id,
                $this->sentEmail->mailbox_id,
                $success ? 'success' : 'failed'
            );
            $collector->recordSendDuration(
                $this->sentEmail->campaign_id,
                $this->sentEmail->mailbox_id,
                $duration
            );
        } catch (\Throwable $e) {
            Log::debug('Failed to record email send metrics', ['error' => $e->getMessage()]);
        }
    }

    public function failed(\Throwable $exception): void
    {
        // Add campaign context to logs
        Log::shareContext([
            'campaign_id' => $this->sentEmail->campaign_id,
            'mailbox_id' => $this->sentEmail->mailbox_id,
            'sent_email_id' => $this->sentEmail->id,
        ]);

        Log::error('SendEmailJob failed', [
            'error' => $exception->getMessage(),
            'exception_class' => get_class($exception),
        ]);

        $this->sentEmail->markAsFailed($exception->getMessage());

        // Record failed metric
        try {
            $collector = app(CampaignFlowCollector::class);
            $collector->incrementEmailSent(
                $this->sentEmail->campaign_id,
                $this->sentEmail->mailbox_id,
                'failed'
            );
        } catch (\Throwable $e) {
            Log::debug('Failed to record email failure metric', ['error' => $e->getMessage()]);
        }
    }
}
