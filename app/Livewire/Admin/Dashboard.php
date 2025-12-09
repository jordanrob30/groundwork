<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Models\AdminAuditLog;
use App\Models\Campaign;
use App\Models\Response;
use App\Models\SentEmail;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Dashboard extends Component
{
    /**
     * Get total user count.
     */
    #[Computed]
    public function totalUsers(): int
    {
        return User::count();
    }

    /**
     * Get active users (logged in within 7 days).
     */
    #[Computed]
    public function activeUsers(): int
    {
        return User::where('updated_at', '>=', now()->subDays(7))->count();
    }

    /**
     * Get total campaign count.
     */
    #[Computed]
    public function totalCampaigns(): int
    {
        return Campaign::count();
    }

    /**
     * Get active campaign count.
     */
    #[Computed]
    public function activeCampaigns(): int
    {
        return Campaign::where('status', Campaign::STATUS_ACTIVE)->count();
    }

    /**
     * Get emails sent in the last 30 days.
     */
    #[Computed]
    public function emailsSentThisMonth(): int
    {
        return SentEmail::where('sent_at', '>=', now()->subDays(30))->count();
    }

    /**
     * Get responses received in the last 30 days.
     */
    #[Computed]
    public function responsesThisMonth(): int
    {
        return Response::where('received_at', '>=', now()->subDays(30))->count();
    }

    /**
     * Get recent admin activity.
     *
     * @return Collection<int, AdminAuditLog>
     */
    #[Computed]
    public function recentActivity(): Collection
    {
        return AdminAuditLog::with(['admin', 'targetUser'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('livewire.admin.dashboard');
    }
}
