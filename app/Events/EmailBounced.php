<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\SentEmail;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EmailBounced
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public SentEmail $sentEmail
    ) {}
}
