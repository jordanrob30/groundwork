<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CallBooking extends Model
{
    use HasFactory;

    public const OUTCOME_VALIDATED = 'validated';

    public const OUTCOME_INVALIDATED = 'invalidated';

    public const OUTCOME_NEED_MORE_INFO = 'need_more_info';

    public const OUTCOME_NO_SHOW = 'no_show';

    public const OUTCOME_RESCHEDULED = 'rescheduled';

    protected $fillable = [
        'lead_id',
        'campaign_id',
        'response_id',
        'scheduled_at',
        'completed_at',
        'duration_minutes',
        'outcome',
        'notes',
        'scheduling_link',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'completed_at' => 'datetime',
        'duration_minutes' => 'integer',
    ];

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function response(): BelongsTo
    {
        return $this->belongsTo(Response::class);
    }

    public function isCompleted(): bool
    {
        return $this->completed_at !== null;
    }

    public function isPending(): bool
    {
        return $this->scheduled_at !== null && $this->completed_at === null;
    }
}
