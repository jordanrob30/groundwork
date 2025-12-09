<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Lead extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';

    public const STATUS_QUEUED = 'queued';

    public const STATUS_CONTACTED = 'contacted';

    public const STATUS_REPLIED = 'replied';

    public const STATUS_CALL_BOOKED = 'call_booked';

    public const STATUS_CONVERTED = 'converted';

    public const STATUS_UNSUBSCRIBED = 'unsubscribed';

    public const STATUS_BOUNCED = 'bounced';

    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_QUEUED,
        self::STATUS_CONTACTED,
        self::STATUS_REPLIED,
        self::STATUS_CALL_BOOKED,
        self::STATUS_CONVERTED,
        self::STATUS_UNSUBSCRIBED,
        self::STATUS_BOUNCED,
    ];

    protected $fillable = [
        'campaign_id',
        'email',
        'first_name',
        'last_name',
        'company',
        'role',
        'linkedin_url',
        'custom_field_1',
        'custom_field_2',
        'custom_field_3',
        'custom_field_4',
        'custom_field_5',
        'status',
        'current_sequence_step',
        'last_contacted_at',
        'replied_at',
        'bounced_at',
        'unsubscribed_at',
    ];

    protected $casts = [
        'current_sequence_step' => 'integer',
        'last_contacted_at' => 'datetime',
        'replied_at' => 'datetime',
        'bounced_at' => 'datetime',
        'unsubscribed_at' => 'datetime',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function sentEmails(): HasMany
    {
        return $this->hasMany(SentEmail::class);
    }

    public function responses(): HasMany
    {
        return $this->hasMany(Response::class);
    }

    public function callBooking(): HasOne
    {
        return $this->hasOne(CallBooking::class);
    }

    public function getFullNameAttribute(): string
    {
        $name = trim(($this->first_name ?? '') . ' ' . ($this->last_name ?? ''));

        return $name !== '' ? $name : ($this->email ?? 'Unknown');
    }

    public function getDisplayNameAttribute(): string
    {
        if ($this->first_name) {
            return $this->first_name;
        }

        return explode('@', $this->email)[0];
    }

    public function getCustomFieldsAttribute(): array
    {
        return array_filter([
            'custom_field_1' => $this->custom_field_1,
            'custom_field_2' => $this->custom_field_2,
            'custom_field_3' => $this->custom_field_3,
            'custom_field_4' => $this->custom_field_4,
            'custom_field_5' => $this->custom_field_5,
        ]);
    }

    public function canBeContacted(): bool
    {
        return in_array($this->status, [
            self::STATUS_PENDING,
            self::STATUS_CONTACTED,
        ]);
    }

    public function isActive(): bool
    {
        return ! in_array($this->status, [
            self::STATUS_UNSUBSCRIBED,
            self::STATUS_BOUNCED,
        ]);
    }

    public function markAsContacted(): void
    {
        $this->update([
            'status' => self::STATUS_CONTACTED,
            'last_contacted_at' => now(),
            'current_sequence_step' => $this->current_sequence_step + 1,
        ]);
    }

    public function markAsReplied(): void
    {
        $this->update([
            'status' => self::STATUS_REPLIED,
            'replied_at' => now(),
        ]);
    }

    public function markAsBounced(): void
    {
        $this->update([
            'status' => self::STATUS_BOUNCED,
            'bounced_at' => now(),
        ]);
    }

    public function markAsUnsubscribed(): void
    {
        $this->update([
            'status' => self::STATUS_UNSUBSCRIBED,
            'unsubscribed_at' => now(),
        ]);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeActive($query)
    {
        return $query->whereNotIn('status', [
            self::STATUS_UNSUBSCRIBED,
            self::STATUS_BOUNCED,
        ]);
    }

    public function scopeContactable($query)
    {
        return $query->whereIn('status', [
            self::STATUS_PENDING,
            self::STATUS_CONTACTED,
        ]);
    }

    public function scopeReplied($query)
    {
        return $query->where('status', self::STATUS_REPLIED);
    }

    public function scopeForCampaign($query, int $campaignId)
    {
        return $query->where('campaign_id', $campaignId);
    }
}
