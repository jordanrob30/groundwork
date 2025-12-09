<div>
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
        <button wire:click="refresh"
            class="inline-flex items-center px-3 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
            <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            Refresh
        </button>
    </div>

    <!-- Weekly Metrics -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white shadow rounded-lg p-4">
            <dt class="text-sm font-medium text-gray-500 truncate">Emails Sent (This Week)</dt>
            <dd class="mt-1 text-3xl font-semibold text-gray-900">{{ number_format($weeklyMetrics['emails_sent']) }}</dd>
        </div>
        <div class="bg-white shadow rounded-lg p-4">
            <dt class="text-sm font-medium text-gray-500 truncate">Responses</dt>
            <dd class="mt-1 text-3xl font-semibold text-gray-900">{{ number_format($weeklyMetrics['responses']) }}</dd>
        </div>
        <div class="bg-white shadow rounded-lg p-4">
            <dt class="text-sm font-medium text-gray-500 truncate">Response Rate</dt>
            <dd class="mt-1 text-3xl font-semibold text-gray-900">{{ $weeklyMetrics['response_rate'] }}%</dd>
        </div>
        <div class="bg-white shadow rounded-lg p-4">
            <dt class="text-sm font-medium text-gray-500 truncate">Hot Leads</dt>
            <dd class="mt-1 text-3xl font-semibold text-green-600">{{ number_format($weeklyMetrics['hot_leads']) }}</dd>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Active Campaigns -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-3 border-b border-gray-200 flex items-center justify-between">
                <h2 class="text-lg font-medium text-gray-900">Active Campaigns</h2>
                <a href="{{ route('campaigns.create') }}" class="text-sm text-indigo-600 hover:text-indigo-900">New Campaign</a>
            </div>
            <div class="divide-y divide-gray-200">
                @forelse ($activeCampaigns as $campaign)
                    <a href="{{ route('campaigns.insights', $campaign) }}" class="block px-4 py-3 hover:bg-gray-50">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $campaign->name }}</p>
                                <p class="text-xs text-gray-500">{{ $campaign->leads_count }} leads &middot; {{ $campaign->responses_count }} responses</p>
                            </div>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                Active
                            </span>
                        </div>
                    </a>
                @empty
                    <div class="px-4 py-6 text-center text-gray-500">
                        <p>No active campaigns.</p>
                        <a href="{{ route('campaigns.create') }}" class="text-indigo-600 hover:underline">Create your first campaign</a>
                    </div>
                @endforelse
            </div>
            @if ($activeCampaigns->count() > 0)
                <div class="px-4 py-3 border-t border-gray-200">
                    <a href="{{ route('campaigns.index') }}" class="text-sm text-indigo-600 hover:text-indigo-900">View all campaigns &rarr;</a>
                </div>
            @endif
        </div>

        <!-- Recent Responses -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-3 border-b border-gray-200 flex items-center justify-between">
                <h2 class="text-lg font-medium text-gray-900">Recent Responses</h2>
                <a href="{{ route('responses.index') }}" class="text-sm text-indigo-600 hover:text-indigo-900">View All</a>
            </div>
            <div class="divide-y divide-gray-200">
                @forelse ($recentResponses as $response)
                    <a href="{{ route('responses.show', $response) }}" class="block px-4 py-3 hover:bg-gray-50">
                        <div class="flex items-center justify-between">
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2">
                                    @if ($response->interest_level)
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium
                                            @if ($response->interest_level === 'hot') bg-red-100 text-red-800
                                            @elseif ($response->interest_level === 'warm') bg-orange-100 text-orange-800
                                            @else bg-gray-100 text-gray-800
                                            @endif">
                                            {{ ucfirst($response->interest_level) }}
                                        </span>
                                    @endif
                                    <span class="text-sm font-medium text-gray-900 truncate">{{ $response->lead->full_name }}</span>
                                </div>
                                <p class="text-xs text-gray-500 truncate">{{ $response->campaign->name }}</p>
                            </div>
                            <span class="text-xs text-gray-400">{{ $response->received_at->diffForHumans() }}</span>
                        </div>
                    </a>
                @empty
                    <div class="px-4 py-6 text-center text-gray-500">
                        No responses yet. Start campaigns to receive replies.
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Mailbox Health -->
    <div class="mt-6 bg-white shadow rounded-lg">
        <div class="px-4 py-3 border-b border-gray-200 flex items-center justify-between">
            <h2 class="text-lg font-medium text-gray-900">Mailbox Health</h2>
            <a href="{{ route('mailboxes.index') }}" class="text-sm text-indigo-600 hover:text-indigo-900">Manage Mailboxes</a>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Mailbox</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Warmup</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Daily Limit</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($mailboxHealth as $mailbox)
                        <tr class="{{ $mailbox['has_error'] ? 'bg-red-50' : '' }}">
                            <td class="px-4 py-3">
                                <p class="text-sm font-medium text-gray-900">{{ $mailbox['name'] }}</p>
                                <p class="text-xs text-gray-500">{{ $mailbox['email'] }}</p>
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                    @if ($mailbox['status'] === 'active') bg-green-100 text-green-800
                                    @elseif ($mailbox['status'] === 'warmup') bg-yellow-100 text-yellow-800
                                    @elseif ($mailbox['status'] === 'error') bg-red-100 text-red-800
                                    @else bg-gray-100 text-gray-800
                                    @endif">
                                    {{ ucfirst($mailbox['status']) }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="w-24 bg-gray-200 rounded-full h-2">
                                    <div class="bg-indigo-600 h-2 rounded-full" style="width: {{ $mailbox['warmup_progress'] }}%"></div>
                                </div>
                                <span class="text-xs text-gray-500">{{ $mailbox['warmup_progress'] }}%</span>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500">
                                {{ $mailbox['daily_limit'] }} emails/day
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-6 text-center text-gray-500">
                                No mailboxes configured. <a href="{{ route('mailboxes.create') }}" class="text-indigo-600 hover:underline">Add a mailbox</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
