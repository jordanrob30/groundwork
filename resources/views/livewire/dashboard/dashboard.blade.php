<div>
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-text-primary">Dashboard</h1>
        <button wire:click="refresh"
            class="inline-flex items-center px-3 py-2 border border-border-default text-sm font-medium rounded-md text-text-secondary bg-bg-elevated hover:bg-bg-surface">
            <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            Refresh
        </button>
    </div>

    <!-- Weekly Metrics -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-bg-elevated shadow rounded-lg p-4">
            <dt class="text-sm font-medium text-text-secondary truncate">Emails Sent (This Week)</dt>
            <dd class="mt-1 text-3xl font-semibold text-text-primary">{{ number_format($weeklyMetrics['emails_sent']) }}</dd>
        </div>
        <div class="bg-bg-elevated shadow rounded-lg p-4">
            <dt class="text-sm font-medium text-text-secondary truncate">Responses</dt>
            <dd class="mt-1 text-3xl font-semibold text-text-primary">{{ number_format($weeklyMetrics['responses']) }}</dd>
        </div>
        <div class="bg-bg-elevated shadow rounded-lg p-4">
            <dt class="text-sm font-medium text-text-secondary truncate">Response Rate</dt>
            <dd class="mt-1 text-3xl font-semibold text-text-primary">{{ $weeklyMetrics['response_rate'] }}%</dd>
        </div>
        <div class="bg-bg-elevated shadow rounded-lg p-4">
            <dt class="text-sm font-medium text-text-secondary truncate">Hot Leads</dt>
            <dd class="mt-1 text-3xl font-semibold text-success">{{ number_format($weeklyMetrics['hot_leads']) }}</dd>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Active Campaigns -->
        <div class="bg-bg-elevated shadow rounded-lg">
            <div class="px-4 py-3 border-b border-border-default flex items-center justify-between">
                <h2 class="text-lg font-medium text-text-primary">Active Campaigns</h2>
                <a href="{{ route('campaigns.create') }}" class="text-sm text-brand hover:text-brand-hover">New Campaign</a>
            </div>
            <div class="divide-y divide-border-default">
                @forelse ($activeCampaigns as $campaign)
                    <a href="{{ route('campaigns.insights', $campaign) }}" class="block px-4 py-3 hover:bg-bg-surface">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-text-primary">{{ $campaign->name }}</p>
                                <p class="text-xs text-text-muted">{{ $campaign->leads_count }} leads &middot; {{ $campaign->responses_count }} responses</p>
                            </div>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-success-bg text-success">
                                Active
                            </span>
                        </div>
                    </a>
                @empty
                    <div class="px-4 py-6 text-center text-text-muted">
                        <p>No active campaigns.</p>
                        <a href="{{ route('campaigns.create') }}" class="text-brand hover:underline">Create your first campaign</a>
                    </div>
                @endforelse
            </div>
            @if ($activeCampaigns->count() > 0)
                <div class="px-4 py-3 border-t border-border-default">
                    <a href="{{ route('campaigns.index') }}" class="text-sm text-brand hover:text-brand-hover">View all campaigns &rarr;</a>
                </div>
            @endif
        </div>

        <!-- Recent Responses -->
        <div class="bg-bg-elevated shadow rounded-lg">
            <div class="px-4 py-3 border-b border-border-default flex items-center justify-between">
                <h2 class="text-lg font-medium text-text-primary">Recent Responses</h2>
                <a href="{{ route('responses.index') }}" class="text-sm text-brand hover:text-brand-hover">View All</a>
            </div>
            <div class="divide-y divide-border-default">
                @forelse ($recentResponses as $response)
                    <a href="{{ route('responses.show', $response) }}" class="block px-4 py-3 hover:bg-bg-surface">
                        <div class="flex items-center justify-between">
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2">
                                    @if ($response->interest_level)
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium border
                                            @if ($response->interest_level === 'hot') bg-lead-hot-bg text-lead-hot-text border-lead-hot-border
                                            @elseif ($response->interest_level === 'warm') bg-lead-warm-bg text-lead-warm-text border-lead-warm-border
                                            @else bg-lead-negative-bg text-lead-negative-text border-lead-negative-border
                                            @endif">
                                            {{ ucfirst($response->interest_level) }}
                                        </span>
                                    @endif
                                    <span class="text-sm font-medium text-text-primary truncate">{{ $response->lead->full_name }}</span>
                                </div>
                                <p class="text-xs text-text-muted truncate">{{ $response->campaign->name }}</p>
                            </div>
                            <span class="text-xs text-text-muted">{{ $response->received_at->diffForHumans() }}</span>
                        </div>
                    </a>
                @empty
                    <div class="px-4 py-6 text-center text-text-muted">
                        No responses yet. Start campaigns to receive replies.
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Mailbox Health -->
    <div class="mt-6 bg-bg-elevated shadow rounded-lg">
        <div class="px-4 py-3 border-b border-border-default flex items-center justify-between">
            <h2 class="text-lg font-medium text-text-primary">Mailbox Health</h2>
            <a href="{{ route('mailboxes.index') }}" class="text-sm text-brand hover:text-brand-hover">Manage Mailboxes</a>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-border-default">
                <thead class="bg-bg-surface">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-text-secondary uppercase">Mailbox</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-text-secondary uppercase">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-text-secondary uppercase">Warmup</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-text-secondary uppercase">Daily Limit</th>
                    </tr>
                </thead>
                <tbody class="bg-bg-elevated divide-y divide-border-default">
                    @forelse ($mailboxHealth as $mailbox)
                        <tr class="{{ $mailbox['has_error'] ? 'bg-error-bg' : '' }}">
                            <td class="px-4 py-3">
                                <p class="text-sm font-medium text-text-primary">{{ $mailbox['name'] }}</p>
                                <p class="text-xs text-text-muted">{{ $mailbox['email'] }}</p>
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                    @if ($mailbox['status'] === 'active') bg-success-bg text-success
                                    @elseif ($mailbox['status'] === 'warmup') bg-warning-bg text-warning
                                    @elseif ($mailbox['status'] === 'error') bg-error-bg text-error
                                    @else bg-lead-negative-bg text-lead-negative-text
                                    @endif">
                                    {{ ucfirst($mailbox['status']) }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="w-24 bg-bg-surface rounded-full h-2">
                                    <div class="bg-brand h-2 rounded-full" style="width: {{ $mailbox['warmup_progress'] }}%"></div>
                                </div>
                                <span class="text-xs text-text-muted">{{ $mailbox['warmup_progress'] }}%</span>
                            </td>
                            <td class="px-4 py-3 text-sm text-text-muted">
                                {{ $mailbox['daily_limit'] }} emails/day
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-6 text-center text-text-muted">
                                No mailboxes configured. <a href="{{ route('mailboxes.create') }}" class="text-brand hover:underline">Add a mailbox</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
