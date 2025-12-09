<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MessageReference extends Model
{
    use HasFactory;

    protected $fillable = [
        'sent_email_id',
        'reference_message_id',
        'position',
    ];

    protected $casts = [
        'position' => 'integer',
    ];

    public function sentEmail(): BelongsTo
    {
        return $this->belongsTo(SentEmail::class);
    }
}
