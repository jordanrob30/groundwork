<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminAuditLog extends Model
{
    /**
     * Action type constants.
     */
    public const ACTION_IMPERSONATE = 'user.impersonate';

    public const ACTION_STOP_IMPERSONATE = 'user.stop_impersonate';

    public const ACTION_ROLE_CHANGE = 'user.role_change';

    /**
     * Indicates if the model should be timestamped.
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'admin_id',
        'target_user_id',
        'action',
        'changes',
        'ip_address',
        'user_agent',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'changes' => 'json',
            'created_at' => 'datetime',
        ];
    }

    /**
     * Get the admin who performed the action.
     *
     * @return BelongsTo<User, $this>
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    /**
     * Get the target user affected by the action.
     *
     * @return BelongsTo<User, $this>
     */
    public function targetUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'target_user_id');
    }

    /**
     * Get a human-readable description of the action.
     */
    public function getActionDescription(): string
    {
        return match ($this->action) {
            self::ACTION_IMPERSONATE => 'Started impersonating user',
            self::ACTION_STOP_IMPERSONATE => 'Stopped impersonating user',
            self::ACTION_ROLE_CHANGE => 'Changed user role',
            default => $this->action,
        };
    }
}
