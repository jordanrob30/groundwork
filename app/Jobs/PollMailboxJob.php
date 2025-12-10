<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Exceptions\MailboxConnectionException;
use App\Metrics\Collectors\CampaignFlowCollector;
use App\Models\Mailbox;
use App\Services\ReplyDetectionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class PollMailboxJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 60;

    public function __construct(
        public Mailbox $mailbox
    ) {}

    public function handle(ReplyDetectionService $service): void
    {
        // Add mailbox context to logs
        Log::shareContext([
            'mailbox_id' => $this->mailbox->id,
            'mailbox_email' => $this->mailbox->email ?? 'unknown',
        ]);

        Log::info('PollMailboxJob started');

        $startTime = microtime(true);

        try {
            $processed = $service->poll($this->mailbox);
            $duration = microtime(true) - $startTime;

            Log::info('PollMailboxJob completed', [
                'messages_processed' => $processed,
                'duration_ms' => round($duration * 1000, 2),
            ]);

            // Record metrics
            try {
                $collector = app(CampaignFlowCollector::class);
                $collector->recordPollingDuration($this->mailbox->id, $duration);
                $collector->incrementPollingMessages($this->mailbox->id, $processed);
            } catch (\Throwable $e) {
                Log::debug('Failed to record polling metrics', ['error' => $e->getMessage()]);
            }
        } catch (MailboxConnectionException $e) {
            Log::error('PollMailboxJob failed - connection error', [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        // Add mailbox context to logs
        Log::shareContext([
            'mailbox_id' => $this->mailbox->id,
            'mailbox_email' => $this->mailbox->email ?? 'unknown',
        ]);

        Log::error('PollMailboxJob failed', [
            'error' => $exception->getMessage(),
            'exception_class' => get_class($exception),
        ]);

        $this->mailbox->update([
            'status' => 'error',
            'error_message' => $exception->getMessage(),
            'last_error_at' => now(),
        ]);
    }
}
