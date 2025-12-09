<?php

declare(strict_types=1);

namespace App\Traits;

use App\Models\User;
use App\Services\AuditLogService;
use Carbon\Carbon;

trait HandlesImpersonation
{
    /**
     * Start impersonating a user.
     */
    public function startImpersonation(User $targetUser): void
    {
        // Store impersonation data in session
        session([
            'impersonating' => $targetUser->id,
            'impersonated_by' => auth()->id(),
            'impersonation_started_at' => now()->toIso8601String(),
        ]);

        // Log the impersonation start
        AuditLogService::logImpersonationStart($targetUser);
    }

    /**
     * Stop impersonating and return to admin session.
     */
    public function stopImpersonation(): void
    {
        $targetUserId = session('impersonating');
        $startedAt = session('impersonation_started_at');

        if ($targetUserId) {
            $targetUser = User::find($targetUserId);

            if ($targetUser && $startedAt) {
                $durationSeconds = (int) Carbon::parse($startedAt)->diffInSeconds(now());
                AuditLogService::logImpersonationStop($targetUser, $durationSeconds);
            }
        }

        // Clear session keys
        session()->forget(['impersonating', 'impersonated_by', 'impersonation_started_at']);
    }

    /**
     * Check if currently impersonating a user.
     */
    public function isImpersonating(): bool
    {
        return session()->has('impersonating');
    }

    /**
     * Get the ID of the user being impersonated.
     */
    public function getImpersonatedUserId(): ?int
    {
        return session('impersonating');
    }

    /**
     * Get the ID of the original admin who started impersonation.
     */
    public function getOriginalAdminId(): ?int
    {
        return session('impersonated_by');
    }

    /**
     * Get the effective user ID (impersonated user or current auth user).
     */
    public function getEffectiveUserId(): int
    {
        return session('impersonating', auth()->id());
    }
}
