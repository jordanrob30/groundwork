<?php

declare(strict_types=1);

namespace App\Livewire\Dashboard;

use App\Models\Campaign;
use App\Models\Mailbox;
use App\Models\Response;
use App\Models\SentEmail;
use App\Traits\HandlesImpersonation;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Component;

class Dashboard extends Component
{
    use HandlesImpersonation;

    public Collection $activeCampaigns;

    public Collection $recentResponses;

    public array $weeklyMetrics;

    public Collection $mailboxHealth;

    public function mount(): void
    {
        $this->loadDashboardData();
    }

    public function refresh(): void
    {
        $this->loadDashboardData();
    }

    protected function loadDashboardData(): void
    {
        $userId = $this->getEffectiveUserId();

        $this->activeCampaigns = Campaign::forUser($userId)
            ->where('status', Campaign::STATUS_ACTIVE)
            ->withCount(['leads', 'responses'])
            ->get();

        $this->recentResponses = Response::whereHas('campaign', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })
            ->where('is_auto_reply', false)
            ->orderByDesc('received_at')
            ->limit(10)
            ->with(['lead', 'campaign'])
            ->get();

        $this->weeklyMetrics = $this->calculateWeeklyMetrics($userId);

        $this->mailboxHealth = Mailbox::forUser($userId)
            ->get()
            ->map(function ($mailbox) {
                return [
                    'id' => $mailbox->id,
                    'name' => $mailbox->name,
                    'email' => $mailbox->email_address,
                    'status' => $mailbox->status,
                    'warmup_progress' => $mailbox->getWarmupProgressPercentage(),
                    'daily_limit' => $mailbox->getCurrentDailyLimit(),
                    'has_error' => $mailbox->hasError(),
                    'last_error' => $mailbox->error_message,
                ];
            });
    }

    protected function calculateWeeklyMetrics(int $userId): array
    {
        $startOfWeek = now()->startOfWeek();
        $endOfWeek = now()->endOfWeek();

        $campaignIds = Campaign::forUser($userId)->pluck('id');

        $totalSent = SentEmail::whereIn('campaign_id', $campaignIds)
            ->whereBetween('sent_at', [$startOfWeek, $endOfWeek])
            ->count();

        $totalResponses = Response::whereIn('campaign_id', $campaignIds)
            ->whereBetween('received_at', [$startOfWeek, $endOfWeek])
            ->where('is_auto_reply', false)
            ->count();

        $hotResponses = Response::whereIn('campaign_id', $campaignIds)
            ->whereBetween('received_at', [$startOfWeek, $endOfWeek])
            ->where('interest_level', 'hot')
            ->count();

        return [
            'emails_sent' => $totalSent,
            'responses' => $totalResponses,
            'response_rate' => $totalSent > 0 ? round(($totalResponses / $totalSent) * 100, 1) : 0,
            'hot_leads' => $hotResponses,
        ];
    }

    public function render(): View
    {
        return view('livewire.dashboard.dashboard');
    }
}
