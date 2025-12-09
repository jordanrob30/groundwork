<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Campaign;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LeadsImported
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Campaign $campaign,
        public int $count
    ) {}
}
