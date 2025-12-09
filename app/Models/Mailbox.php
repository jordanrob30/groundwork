<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Mailbox extends Model
{
    use HasFactory;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_PAUSED = 'paused';

    public const STATUS_ERROR = 'error';

    public const STATUS_WARMUP = 'warmup';

    protected static array $defaultWarmupSchedule = [
        // Week 1: 10 → 40 emails/day
        1 => 10, 2 => 12, 3 => 15, 4 => 18, 5 => 22, 6 => 27, 7 => 32,
        // Week 2: 40 → 100 emails/day
        8 => 38, 9 => 45, 10 => 54, 11 => 65, 12 => 78, 13 => 93, 14 => 100,
        // Week 3+: Hold at target
    ];

    protected $fillable = [
        'user_id',
        'name',
        'email_address',
        'status',
        'smtp_host',
        'smtp_port',
        'smtp_encryption',
        'smtp_username',
        'smtp_password',
        'imap_host',
        'imap_port',
        'imap_encryption',
        'imap_username',
        'imap_password',
        'uses_oauth',
        'oauth_provider',
        'oauth_access_token',
        'oauth_refresh_token',
        'oauth_expires_at',
        'daily_limit',
        'warmup_enabled',
        'warmup_started_at',
        'warmup_day',
        'send_window_start',
        'send_window_end',
        'skip_weekends',
        'timezone',
        'last_polled_at',
        'error_message',
        'last_error_at',
    ];

    protected function casts(): array
    {
        return [
            'smtp_port' => 'integer',
            'imap_port' => 'integer',
            'uses_oauth' => 'boolean',
            'daily_limit' => 'integer',
            'warmup_enabled' => 'boolean',
            'warmup_started_at' => 'date',
            'warmup_day' => 'integer',
            'skip_weekends' => 'boolean',
            'oauth_expires_at' => 'datetime',
            'last_polled_at' => 'datetime',
            'last_error_at' => 'datetime',
        ];
    }

    // Encrypted credential mutators
    public function setSmtpPasswordAttribute(string $value): void
    {
        $this->attributes['smtp_password'] = encrypt($value);
    }

    public function getSmtpPasswordAttribute(?string $value): ?string
    {
        return $value ? decrypt($value) : null;
    }

    public function setImapPasswordAttribute(string $value): void
    {
        $this->attributes['imap_password'] = encrypt($value);
    }

    public function getImapPasswordAttribute(?string $value): ?string
    {
        return $value ? decrypt($value) : null;
    }

    public function setOauthAccessTokenAttribute(?string $value): void
    {
        $this->attributes['oauth_access_token'] = $value ? encrypt($value) : null;
    }

    public function getOauthAccessTokenAttribute(?string $value): ?string
    {
        return $value ? decrypt($value) : null;
    }

    public function setOauthRefreshTokenAttribute(?string $value): void
    {
        $this->attributes['oauth_refresh_token'] = $value ? encrypt($value) : null;
    }

    public function getOauthRefreshTokenAttribute(?string $value): ?string
    {
        return $value ? decrypt($value) : null;
    }

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function campaigns(): HasMany
    {
        return $this->hasMany(Campaign::class);
    }

    public function sentEmails(): HasMany
    {
        return $this->hasMany(SentEmail::class);
    }

    public function sendingStats(): HasMany
    {
        return $this->hasMany(MailboxSendingStat::class);
    }

    // Business Logic
    public function getCurrentDailyLimit(): int
    {
        if (! $this->warmup_enabled) {
            return $this->daily_limit;
        }

        $day = $this->warmup_day;

        if ($day <= 0) {
            return self::$defaultWarmupSchedule[1];
        }

        return self::$defaultWarmupSchedule[$day] ?? $this->daily_limit;
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isPaused(): bool
    {
        return $this->status === self::STATUS_PAUSED;
    }

    public function hasError(): bool
    {
        return $this->status === self::STATUS_ERROR;
    }

    public function isInWarmup(): bool
    {
        return $this->status === self::STATUS_WARMUP || $this->warmup_enabled;
    }

    public function hasReachedDailyLimit(): bool
    {
        $stat = $this->sendingStats()
            ->where('date', now()->toDateString())
            ->first();

        $sentToday = $stat?->emails_sent ?? 0;

        return $sentToday >= $this->getCurrentDailyLimit();
    }

    public function getWarmupProgressPercentage(): int
    {
        if (! $this->warmup_enabled) {
            return 100;
        }

        $maxDays = 14; // 2 week warmup

        return min(100, (int) round(($this->warmup_day / $maxDays) * 100));
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeForUser($query, ?int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeNeedingPoll($query)
    {
        return $query->whereIn('status', [self::STATUS_ACTIVE, self::STATUS_WARMUP])
            ->where(function ($q) {
                $q->whereNull('last_polled_at')
                    ->orWhere('last_polled_at', '<', now()->subMinutes(5));
            });
    }
}
