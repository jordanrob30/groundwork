<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Mailbox;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MailboxCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Mailbox $mailbox
    ) {}
}
