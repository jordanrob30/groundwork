<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\EmailTemplate;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TemplateCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public EmailTemplate $template
    ) {}
}
