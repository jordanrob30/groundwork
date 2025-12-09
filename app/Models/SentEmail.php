<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SentEmail extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';

    public const STATUS_QUEUED = 'queued';

    public const STATUS_SENDING = 'sending';

    public const STATUS_SENT = 'sent';

    public const STATUS_FAILED = 'failed';

    public const STATUS_BOUNCED = 'bounced';

    public const BOUNCE_TYPE_HARD = 'hard';

    public const BOUNCE_TYPE_SOFT = 'soft';

    protected $fillable = [
        'mailbox_id',
        'campaign_id',
        'lead_id',
        'template_id',
        'message_id',
        'subject',
        'body',
        'status',
        'sequence_step',
        'scheduled_for',
        'sent_at',
        'opened_at',
        'clicked_at',
        'bounced_at',
        'bounce_type',
        'error_message',
    ];

    protected $casts = [
        'sequence_step' => 'integer',
        'scheduled_for' => 'datetime',
        'sent_at' => 'datetime',
        'opened_at' => 'datetime',
        'clicked_at' => 'datetime',
        'bounced_at' => 'datetime',
    ];

    public function mailbox(): BelongsTo
    {
        return $this->belongsTo(Mailbox::class);
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(EmailTemplate::class, 'template_id');
    }

    public function response(): HasOne
    {
        return $this->hasOne(Response::class);
    }

    public function messageReferences(): HasMany
    {
        return $this->hasMany(MessageReference::class);
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isQueued(): bool
    {
        return $this->status === self::STATUS_QUEUED;
    }

    public function isSent(): bool
    {
        return $this->status === self::STATUS_SENT;
    }

    public function isBounced(): bool
    {
        return $this->status === self::STATUS_BOUNCED;
    }

    public function markAsQueued(): void
    {
        $this->update(['status' => self::STATUS_QUEUED]);
    }

    public function markAsSending(): void
    {
        $this->update(['status' => self::STATUS_SENDING]);
    }

    public function markAsSent(): void
    {
        $this->update([
            'status' => self::STATUS_SENT,
            'sent_at' => now(),
        ]);
    }

    public function markAsFailed(string $errorMessage): void
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'error_message' => $errorMessage,
        ]);
    }

    public function markAsBounced(string $type = self::BOUNCE_TYPE_HARD): void
    {
        $this->update([
            'status' => self::STATUS_BOUNCED,
            'bounced_at' => now(),
            'bounce_type' => $type,
        ]);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeQueued($query)
    {
        return $query->where('status', self::STATUS_QUEUED);
    }

    public function scopeSent($query)
    {
        return $query->where('status', self::STATUS_SENT);
    }

    public function scopeScheduledFor($query, $date)
    {
        return $query->whereDate('scheduled_for', $date);
    }

    public function scopeReadyToSend($query)
    {
        return $query->where('status', self::STATUS_QUEUED)
            ->where(function ($q) {
                $q->whereNull('scheduled_for')
                    ->orWhere('scheduled_for', '<=', now());
            });
    }
}
