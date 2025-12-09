<div>
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Campaigns</h1>
                <p class="mt-1 text-sm text-gray-600">Manage your discovery campaigns</p>
            </div>
            <a href="{{ route('campaigns.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                New Campaign
            </a>
        </div>

        <!-- Filters -->
        <div class="mb-4 flex space-x-2">
            <button wire:click="setFilter('all')" class="px-3 py-1 text-sm rounded-full {{ $filterStatus === 'all' ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-700' }}">
                All
            </button>
            <button wire:click="setFilter('draft')" class="px-3 py-1 text-sm rounded-full {{ $filterStatus === 'draft' ? 'bg-gray-600 text-white' : 'bg-gray-200 text-gray-700' }}">
                Draft
            </button>
            <button wire:click="setFilter('active')" class="px-3 py-1 text-sm rounded-full {{ $filterStatus === 'active' ? 'bg-green-600 text-white' : 'bg-gray-200 text-gray-700' }}">
                Active
            </button>
            <button wire:click="setFilter('paused')" class="px-3 py-1 text-sm rounded-full {{ $filterStatus === 'paused' ? 'bg-yellow-600 text-white' : 'bg-gray-200 text-gray-700' }}">
                Paused
            </button>
            <button wire:click="setFilter('completed')" class="px-3 py-1 text-sm rounded-full {{ $filterStatus === 'completed' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700' }}">
                Completed
            </button>
            <button wire:click="setFilter('archived')" class="px-3 py-1 text-sm rounded-full {{ $filterStatus === 'archived' ? 'bg-red-600 text-white' : 'bg-gray-200 text-gray-700' }}">
                Archived
            </button>
        </div>

        <!-- Campaign List -->
        @if($campaigns->isEmpty())
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No campaigns</h3>
                    <p class="mt-1 text-sm text-gray-500">Get started by creating a new discovery campaign.</p>
                    <div class="mt-6">
                        <a href="{{ route('campaigns.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                            New Campaign
                        </a>
                    </div>
                </div>
            </div>
        @else
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <ul class="divide-y divide-gray-200">
                    @foreach($campaigns as $campaign)
                        <li class="p-4 hover:bg-gray-50">
                            <div class="flex items-center justify-between">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center">
                                        <a href="{{ route('campaigns.edit', $campaign) }}" class="text-lg font-medium text-indigo-600 hover:text-indigo-900 truncate">
                                            {{ $campaign->name }}
                                        </a>
                                        <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                            @switch($campaign->status)
                                                @case('draft') bg-gray-100 text-gray-800 @break
                                                @case('active') bg-green-100 text-green-800 @break
                                                @case('paused') bg-yellow-100 text-yellow-800 @break
                                                @case('completed') bg-blue-100 text-blue-800 @break
                                                @case('archived') bg-red-100 text-red-800 @break
                                            @endswitch">
                                            {{ ucfirst($campaign->status) }}
                                        </span>
                                    </div>
                                    <p class="mt-1 text-sm text-gray-500 truncate">
                                        {{ Str::limit($campaign->hypothesis, 100) }}
                                    </p>
                                    <div class="mt-2 flex items-center text-sm text-gray-500 space-x-4">
                                        <span>{{ $campaign->leads_count ?? $campaign->leads->count() }} leads</span>
                                        <span>{{ $campaign->responses->count() }} responses</span>
                                        <span>via {{ $campaign->mailbox?->email_address ?? 'No mailbox' }}</span>
                                    </div>
                                </div>

                                <div class="flex items-center space-x-2 ml-4">
                                    <!-- Quick Actions -->
                                    <a href="{{ route('campaigns.leads.index', $campaign) }}" class="text-gray-600 hover:text-gray-900" title="View Leads">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                        </svg>
                                    </a>

                                    <a href="{{ route('campaigns.insights', $campaign) }}" class="text-gray-600 hover:text-gray-900" title="View Insights">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                        </svg>
                                    </a>

                                    <button wire:click="duplicate({{ $campaign->id }})" class="text-gray-600 hover:text-gray-900" title="Duplicate">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                        </svg>
                                    </button>

                                    @if($campaign->status !== 'archived')
                                        <button wire:click="archive({{ $campaign->id }})" wire:confirm="Are you sure you want to archive this campaign?" class="text-yellow-600 hover:text-yellow-900" title="Archive">
                                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                                            </svg>
                                        </button>
                                    @endif

                                    <button wire:click="delete({{ $campaign->id }})" wire:confirm="Are you sure you want to delete this campaign? This action cannot be undone." class="text-red-600 hover:text-red-900" title="Delete">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
</div>
