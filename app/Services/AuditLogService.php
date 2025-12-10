<?php

declare(strict_types=1);

namespace App\Services;

use App\Metrics\Collectors\AdminActivityCollector;
use App\Models\AdminAuditLog;
use App\Models\User;
use Illuminate\Http\Request;

class AuditLogService
{
    /**
     * Log an administrative action.
     *
     * @param  array<string, mixed>|null  $changes
     */
    public static function log(
        string $action,
        ?User $targetUser = null,
        ?array $changes = null,
        ?Request $request = null
    ): AdminAuditLog {
        $request = $request ?? request();

        // Record metric for admin action
        try {
            $collector = app(AdminActivityCollector::class);
            $collector->incrementAdminAction($action, auth()->id());
        } catch (\Throwable $e) {
            // Silently fail to avoid impacting audit logging
        }

        return AdminAuditLog::create([
            'admin_id' => auth()->id(),
            'target_user_id' => $targetUser?->id,
            'action' => $action,
            'changes' => $changes,
            'ip_address' => $request->ip(),
            'user_agent' => substr($request->userAgent() ?? '', 0, 500),
        ]);
    }

    /**
     * Log the start of user impersonation.
     */
    public static function logImpersonationStart(User $targetUser): AdminAuditLog
    {
        return self::log(
            AdminAuditLog::ACTION_IMPERSONATE,
            $targetUser,
            ['started_at' => now()->toIso8601String()]
        );
    }

    /**
     * Log the end of user impersonation.
     */
    public static function logImpersonationStop(User $targetUser, int $durationSeconds): AdminAuditLog
    {
        return self::log(
            AdminAuditLog::ACTION_STOP_IMPERSONATE,
            $targetUser,
            ['duration_seconds' => $durationSeconds]
        );
    }

    /**
     * Log a role change for a user.
     */
    public static function logRoleChange(User $targetUser, string $fromRole, string $toRole): AdminAuditLog
    {
        // Record role change metric
        try {
            $collector = app(AdminActivityCollector::class);
            $collector->incrementRoleChange($fromRole, $toRole);
        } catch (\Throwable $e) {
            // Silently fail to avoid impacting audit logging
        }

        return self::log(
            AdminAuditLog::ACTION_ROLE_CHANGE,
            $targetUser,
            ['from' => $fromRole, 'to' => $toRole]
        );
    }
}
