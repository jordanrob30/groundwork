<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MailboxSendingStat extends Model
{
    use HasFactory;

    protected $fillable = [
        'mailbox_id',
        'date',
        'emails_sent',
        'emails_delivered',
        'emails_bounced',
        'emails_failed',
        'bounce_rate',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'emails_sent' => 'integer',
            'emails_delivered' => 'integer',
            'emails_bounced' => 'integer',
            'emails_failed' => 'integer',
            'bounce_rate' => 'decimal:2',
        ];
    }

    public function mailbox(): BelongsTo
    {
        return $this->belongsTo(Mailbox::class);
    }

    public function calculateBounceRate(): float
    {
        if ($this->emails_sent === 0) {
            return 0.0;
        }

        return round(($this->emails_bounced / $this->emails_sent) * 100, 2);
    }

    public function updateBounceRate(): void
    {
        $this->bounce_rate = $this->calculateBounceRate();
        $this->save();
    }
}
