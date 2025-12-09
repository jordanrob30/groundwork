<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Exceptions\MailboxConnectionException;
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
        try {
            $processed = $service->poll($this->mailbox);

            Log::info("Polled mailbox {$this->mailbox->id}, processed {$processed} messages");
        } catch (MailboxConnectionException $e) {
            Log::error("Failed to poll mailbox {$this->mailbox->id}: {$e->getMessage()}");
            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        $this->mailbox->update([
            'status' => 'error',
            'error_message' => $exception->getMessage(),
            'last_error_at' => now(),
        ]);
    }
}
