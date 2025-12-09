<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Insight extends Model
{
    use HasFactory;

    public const TYPE_PATTERN = 'pattern';

    public const TYPE_THEME = 'theme';

    public const TYPE_OBJECTION = 'objection';

    public const TYPE_QUOTE = 'quote';

    protected $fillable = [
        'campaign_id',
        'response_id',
        'insight_type',
        'title',
        'content',
        'frequency',
        'response_ids',
        'metadata',
        'confidence_score',
        'is_pinned',
    ];

    protected $casts = [
        'response_ids' => 'array',
        'metadata' => 'array',
        'confidence_score' => 'decimal:2',
        'is_pinned' => 'boolean',
        'frequency' => 'integer',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function response(): BelongsTo
    {
        return $this->belongsTo(Response::class);
    }

    public function scopePinned($query)
    {
        return $query->where('is_pinned', true);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('insight_type', $type);
    }
}
