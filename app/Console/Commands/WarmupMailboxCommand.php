<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Mailbox;
use App\Services\MailboxService;
use Illuminate\Console\Command;

class WarmupMailboxCommand extends Command
{
    protected $signature = 'mailbox:warmup';

    protected $description = 'Update warm-up progress for all mailboxes';

    public function handle(MailboxService $service): int
    {
        $mailboxes = Mailbox::where('warmup_enabled', true)
            ->whereIn('status', [Mailbox::STATUS_ACTIVE, Mailbox::STATUS_WARMUP])
            ->get();

        if ($mailboxes->isEmpty()) {
            $this->info('No mailboxes in warm-up mode.');

            return self::SUCCESS;
        }

        $this->info("Processing {$mailboxes->count()} mailboxes...");

        foreach ($mailboxes as $mailbox) {
            $previousLimit = $mailbox->getCurrentDailyLimit();
            $service->incrementWarmupDay($mailbox);
            $newLimit = $mailbox->getCurrentDailyLimit();

            $this->line("  {$mailbox->email_address}: Day {$mailbox->warmup_day}, limit: {$previousLimit} -> {$newLimit}");

            if (! $mailbox->warmup_enabled) {
                $this->info("  -> Warm-up complete for {$mailbox->email_address}!");
            }
        }

        $this->info('Warm-up update complete.');

        return self::SUCCESS;
    }
}
