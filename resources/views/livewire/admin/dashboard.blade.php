<div>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-text-primary">Admin Dashboard</h2>
    </x-slot>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
        <!-- Total Users -->
        <div class="bg-bg-elevated rounded-lg border border-border-default p-6">
            <div class="text-text-secondary text-sm font-medium">Total Users</div>
            <div class="text-3xl font-bold text-text-primary mt-2">{{ $this->totalUsers }}</div>
            <div class="text-text-muted text-sm mt-1">{{ $this->activeUsers }} active this week</div>
        </div>

        <!-- Total Campaigns -->
        <div class="bg-bg-elevated rounded-lg border border-border-default p-6">
            <div class="text-text-secondary text-sm font-medium">Total Campaigns</div>
            <div class="text-3xl font-bold text-text-primary mt-2">{{ $this->totalCampaigns }}</div>
            <div class="text-text-muted text-sm mt-1">{{ $this->activeCampaigns }} currently active</div>
        </div>

        <!-- Emails Sent -->
        <div class="bg-bg-elevated rounded-lg border border-border-default p-6">
            <div class="text-text-secondary text-sm font-medium">Emails Sent (30 days)</div>
            <div class="text-3xl font-bold text-text-primary mt-2">{{ number_format($this->emailsSentThisMonth) }}</div>
            <div class="text-text-muted text-sm mt-1">{{ number_format($this->responsesThisMonth) }} responses received</div>
        </div>
    </div>

    <!-- Quick Links -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <a href="{{ route('admin.users.index') }}" wire:navigate class="bg-bg-elevated rounded-lg border border-border-default p-6 hover:border-brand transition-colors">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-lg font-medium text-text-primary">User Management</div>
                    <div class="text-text-secondary text-sm mt-1">View and manage all users</div>
                </div>
                <svg class="w-6 h-6 text-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </div>
        </a>

        <a href="{{ route('admin.audit-log') }}" wire:navigate class="bg-bg-elevated rounded-lg border border-border-default p-6 hover:border-brand transition-colors">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-lg font-medium text-text-primary">Audit Log</div>
                    <div class="text-text-secondary text-sm mt-1">View admin action history</div>
                </div>
                <svg class="w-6 h-6 text-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </div>
        </a>
    </div>

    <!-- Recent Activity -->
    <div class="bg-bg-elevated rounded-lg border border-border-default">
        <div class="px-6 py-4 border-b border-border-default">
            <h3 class="text-lg font-medium text-text-primary">Recent Admin Activity</h3>
        </div>
        <div class="divide-y divide-border-default">
            @forelse ($this->recentActivity as $log)
                <div class="px-6 py-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="font-medium text-text-primary">{{ $log->admin->name }}</span>
                            <span class="text-text-secondary">{{ $log->getActionDescription() }}</span>
                            @if ($log->targetUser)
                                <span class="text-text-secondary">-</span>
                                <span class="text-text-primary">{{ $log->targetUser->name }}</span>
                            @endif
                        </div>
                        <div class="text-text-muted text-sm">
                            {{ $log->created_at->diffForHumans() }}
                        </div>
                    </div>
                </div>
            @empty
                <div class="px-6 py-8 text-center text-text-muted">
                    No admin activity recorded yet.
                </div>
            @endforelse
        </div>
    </div>
</div>
