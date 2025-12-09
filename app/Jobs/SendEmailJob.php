<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\SentEmail;
use App\Services\SendEngineService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\RateLimited;
use Illuminate\Queue\SerializesModels;

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
        if (! $this->sentEmail->isQueued()) {
            return;
        }

        $mailbox = $this->sentEmail->mailbox;
        if ($mailbox->hasReachedDailyLimit()) {
            $this->release(3600);

            return;
        }

        $service->send($this->sentEmail);
    }

    public function failed(\Throwable $exception): void
    {
        $this->sentEmail->markAsFailed($exception->getMessage());
    }
}
