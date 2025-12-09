<div>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Response Inbox</h2>
            @if ($campaign)
                <p class="mt-1 text-sm text-gray-600">{{ $campaign->name }}</p>
            @else
                <p class="mt-1 text-sm text-gray-600">All campaigns</p>
            @endif
        </div>
        <div class="flex items-center space-x-2">
            <label class="flex items-center text-sm text-gray-600">
                <input type="checkbox" wire:click="toggleAutoReplies" {{ $showAutoReplies ? 'checked' : '' }}
                    class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 mr-2">
                Show auto-replies
            </label>
            <button wire:click="bulkMarkReviewed"
                class="inline-flex items-center px-3 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                Mark All Reviewed
            </button>
        </div>
    </div>

    @if (session()->has('message'))
        <div class="mb-4 rounded-md bg-green-50 p-4">
            <p class="text-sm font-medium text-green-800">{{ session('message') }}</p>
        </div>
    @endif

    <!-- Filters -->
    <div class="mb-6 flex flex-wrap gap-4">
        <!-- Interest Level Filter -->
        <div class="flex flex-wrap gap-2">
            <button wire:click="setInterestFilter('all')"
                class="px-3 py-1.5 text-sm rounded-full {{ $filterInterest === 'all' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                All
            </button>
            <button wire:click="setInterestFilter('hot')"
                class="px-3 py-1.5 text-sm rounded-full {{ $filterInterest === 'hot' ? 'bg-red-600 text-white' : 'bg-red-100 text-red-700 hover:bg-red-200' }}">
                Hot ({{ $interestCounts['hot'] ?? 0 }})
            </button>
            <button wire:click="setInterestFilter('warm')"
                class="px-3 py-1.5 text-sm rounded-full {{ $filterInterest === 'warm' ? 'bg-orange-600 text-white' : 'bg-orange-100 text-orange-700 hover:bg-orange-200' }}">
                Warm ({{ $interestCounts['warm'] ?? 0 }})
            </button>
            <button wire:click="setInterestFilter('cold')"
                class="px-3 py-1.5 text-sm rounded-full {{ $filterInterest === 'cold' ? 'bg-blue-600 text-white' : 'bg-blue-100 text-blue-700 hover:bg-blue-200' }}">
                Cold ({{ $interestCounts['cold'] ?? 0 }})
            </button>
            <button wire:click="setInterestFilter('negative')"
                class="px-3 py-1.5 text-sm rounded-full {{ $filterInterest === 'negative' ? 'bg-gray-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                Negative ({{ $interestCounts['negative'] ?? 0 }})
            </button>
        </div>

        <!-- Review Status Filter -->
        <div class="flex gap-2 ml-auto">
            <button wire:click="setStatusFilter('all')"
                class="px-3 py-1.5 text-sm rounded-md {{ $filterStatus === 'all' ? 'bg-indigo-100 text-indigo-700' : 'text-gray-600 hover:text-gray-900' }}">
                All
            </button>
            <button wire:click="setStatusFilter('unreviewed')"
                class="px-3 py-1.5 text-sm rounded-md {{ $filterStatus === 'unreviewed' ? 'bg-indigo-100 text-indigo-700' : 'text-gray-600 hover:text-gray-900' }}">
                Unreviewed
            </button>
            <button wire:click="setStatusFilter('reviewed')"
                class="px-3 py-1.5 text-sm rounded-md {{ $filterStatus === 'reviewed' ? 'bg-indigo-100 text-indigo-700' : 'text-gray-600 hover:text-gray-900' }}">
                Reviewed
            </button>
            <button wire:click="setStatusFilter('actioned')"
                class="px-3 py-1.5 text-sm rounded-md {{ $filterStatus === 'actioned' ? 'bg-indigo-100 text-indigo-700' : 'text-gray-600 hover:text-gray-900' }}">
                Actioned
            </button>
        </div>
    </div>

    <!-- Response List -->
    <div class="bg-white shadow rounded-lg divide-y divide-gray-200">
        @forelse ($responses as $response)
            <a href="{{ route('responses.show', $response) }}" class="block hover:bg-gray-50 transition-colors">
                <div class="px-4 py-4">
                    <div class="flex items-start justify-between">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-1">
                                @if ($response->interest_level)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                        @if ($response->interest_level === 'hot') bg-red-100 text-red-800
                                        @elseif ($response->interest_level === 'warm') bg-orange-100 text-orange-800
                                        @elseif ($response->interest_level === 'cold') bg-blue-100 text-blue-800
                                        @else bg-gray-100 text-gray-800
                                        @endif">
                                        {{ ucfirst($response->interest_level) }}
                                    </span>
                                @endif

                                @if ($response->is_auto_reply)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">
                                        Auto-reply
                                    </span>
                                @endif

                                @if ($response->call_interest)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                        Wants call
                                    </span>
                                @endif

                                @if ($response->review_status === 'unreviewed')
                                    <span class="w-2 h-2 bg-blue-500 rounded-full"></span>
                                @endif
                            </div>

                            <p class="text-sm font-medium text-gray-900 truncate">
                                {{ $response->lead->full_name }} &mdash; {{ $response->lead->company ?? $response->lead->email }}
                            </p>

                            <p class="text-sm text-gray-600 truncate">{{ $response->subject }}</p>

                            @if ($response->summary)
                                <p class="mt-1 text-sm text-gray-500 line-clamp-2">{{ $response->summary }}</p>
                            @else
                                <p class="mt-1 text-sm text-gray-400 line-clamp-2">{{ Str::limit(strip_tags($response->body_plain ?? $response->body), 150) }}</p>
                            @endif
                        </div>

                        <div class="ml-4 flex-shrink-0 text-right">
                            <p class="text-sm text-gray-500">{{ $response->received_at->diffForHumans() }}</p>
                            @if (!$campaign)
                                <p class="text-xs text-gray-400 mt-1">{{ $response->campaign->name }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            </a>
        @empty
            <div class="px-4 py-8 text-center text-gray-500">
                @if ($filterInterest !== 'all' || $filterStatus !== 'unreviewed')
                    No responses match your filters.
                @else
                    No responses yet. Responses will appear here when leads reply to your outreach.
                @endif
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="mt-4">
        {{ $responses->links() }}
    </div>
</div>
