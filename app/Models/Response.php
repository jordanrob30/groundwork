<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Response extends Model
{
    use HasFactory;

    public const ANALYSIS_STATUS_PENDING = 'pending';

    public const ANALYSIS_STATUS_ANALYZING = 'analyzing';

    public const ANALYSIS_STATUS_COMPLETED = 'completed';

    public const ANALYSIS_STATUS_FAILED = 'failed';

    public const INTEREST_HOT = 'hot';

    public const INTEREST_WARM = 'warm';

    public const INTEREST_COLD = 'cold';

    public const INTEREST_NEGATIVE = 'negative';

    public const PROBLEM_YES = 'yes';

    public const PROBLEM_NO = 'no';

    public const PROBLEM_DIFFERENT = 'different';

    public const PROBLEM_UNCLEAR = 'unclear';

    public const REVIEW_UNREVIEWED = 'unreviewed';

    public const REVIEW_REVIEWED = 'reviewed';

    public const REVIEW_ACTIONED = 'actioned';

    protected $fillable = [
        'sent_email_id',
        'lead_id',
        'campaign_id',
        'message_id',
        'in_reply_to',
        'subject',
        'body',
        'body_plain',
        'is_auto_reply',
        'received_at',
        'analyzed_at',
        'analysis_status',
        'interest_level',
        'problem_confirmation',
        'pain_severity',
        'current_solution',
        'call_interest',
        'key_quotes',
        'summary',
        'analysis_confidence',
        'review_status',
        'reviewed_at',
    ];

    protected $casts = [
        'is_auto_reply' => 'boolean',
        'received_at' => 'datetime',
        'analyzed_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'call_interest' => 'boolean',
        'key_quotes' => 'array',
        'pain_severity' => 'integer',
        'analysis_confidence' => 'decimal:2',
    ];

    public function sentEmail(): BelongsTo
    {
        return $this->belongsTo(SentEmail::class);
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function isAnalyzed(): bool
    {
        return $this->analysis_status === self::ANALYSIS_STATUS_COMPLETED;
    }

    public function needsAnalysis(): bool
    {
        return $this->analysis_status === self::ANALYSIS_STATUS_PENDING
            && ! $this->is_auto_reply;
    }

    public function markAsAnalyzing(): void
    {
        $this->update(['analysis_status' => self::ANALYSIS_STATUS_ANALYZING]);
    }

    public function markAsAnalyzed(array $analysis): void
    {
        $this->update(array_merge($analysis, [
            'analysis_status' => self::ANALYSIS_STATUS_COMPLETED,
            'analyzed_at' => now(),
        ]));
    }

    public function markAnalysisFailed(): void
    {
        $this->update(['analysis_status' => self::ANALYSIS_STATUS_FAILED]);
    }

    public function markAsReviewed(): void
    {
        $this->update([
            'review_status' => self::REVIEW_REVIEWED,
            'reviewed_at' => now(),
        ]);
    }

    public function markAsActioned(): void
    {
        $this->update([
            'review_status' => self::REVIEW_ACTIONED,
            'reviewed_at' => now(),
        ]);
    }

    public function scopeUnreviewed($query)
    {
        return $query->where('review_status', self::REVIEW_UNREVIEWED);
    }

    public function scopeNeedsAnalysis($query)
    {
        return $query->where('analysis_status', self::ANALYSIS_STATUS_PENDING)
            ->where('is_auto_reply', false);
    }

    public function scopeWithInterest($query, string $level)
    {
        return $query->where('interest_level', $level);
    }

    public function scopeHot($query)
    {
        return $query->where('interest_level', self::INTEREST_HOT);
    }

    public function scopeWarm($query)
    {
        return $query->where('interest_level', self::INTEREST_WARM);
    }

    public function scopeNotAutoReply($query)
    {
        return $query->where('is_auto_reply', false);
    }
}
