<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Jobs\PollMailboxJob;
use App\Models\Mailbox;
use Illuminate\Console\Command;

class PollMailboxCommand extends Command
{
    protected $signature = 'mailbox:poll {--mailbox= : Poll a specific mailbox by ID}';

    protected $description = 'Poll active mailboxes for new replies';

    public function handle(): int
    {
        $mailboxId = $this->option('mailbox');

        if ($mailboxId) {
            $mailboxes = Mailbox::where('id', $mailboxId)->get();
        } else {
            $mailboxes = Mailbox::where('status', 'active')
                ->orWhere('status', 'warmup')
                ->get();
        }

        if ($mailboxes->isEmpty()) {
            $this->info('No active mailboxes to poll.');

            return self::SUCCESS;
        }

        $this->info("Dispatching poll jobs for {$mailboxes->count()} mailbox(es)...");

        foreach ($mailboxes as $mailbox) {
            PollMailboxJob::dispatch($mailbox);
            $this->line("  - Queued: {$mailbox->email_address}");
        }

        $this->info('All poll jobs dispatched.');

        return self::SUCCESS;
    }
}
