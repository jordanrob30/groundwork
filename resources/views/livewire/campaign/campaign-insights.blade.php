<div>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Campaign Insights</h2>
            <p class="mt-1 text-sm text-gray-600">{{ $campaign->name }}</p>
        </div>
        <div class="flex space-x-2">
            <button wire:click="refresh"
                class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                Refresh
            </button>
            <button wire:click="triggerReanalysis"
                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Re-analyze Responses
            </button>
        </div>
    </div>

    <!-- Decision Score -->
    <div class="bg-white shadow rounded-lg p-6 mb-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Decision Score</h3>
        <div class="flex items-center space-x-8">
            <div class="flex-shrink-0">
                <div class="relative w-32 h-32">
                    <svg class="w-32 h-32 transform -rotate-90" viewBox="0 0 36 36">
                        <path class="text-gray-200" stroke="currentColor" stroke-width="3" fill="none"
                            d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                        <path class="{{ $decisionScore['score'] >= 70 ? 'text-green-500' : ($decisionScore['score'] >= 40 ? 'text-yellow-500' : 'text-red-500') }}"
                            stroke="currentColor" stroke-width="3" fill="none" stroke-linecap="round"
                            stroke-dasharray="{{ $decisionScore['score'] }}, 100"
                            d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                    </svg>
                    <div class="absolute inset-0 flex items-center justify-center">
                        <span class="text-3xl font-bold text-gray-900">{{ $decisionScore['score'] }}</span>
                    </div>
                </div>
            </div>
            <div class="flex-1">
                <p class="text-lg font-medium {{ $decisionScore['score'] >= 70 ? 'text-green-600' : ($decisionScore['score'] >= 40 ? 'text-yellow-600' : 'text-red-600') }}">
                    {{ $decisionScore['recommendation'] }}
                </p>
                <div class="mt-4 space-y-2">
                    @foreach ($decisionScore['factors'] as $factor => $contribution)
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-600">{{ ucfirst(str_replace('_', ' ', $factor)) }}</span>
                            <span class="font-medium {{ $contribution > 0 ? 'text-green-600' : 'text-red-600' }}">
                                {{ $contribution > 0 ? '+' : '' }}{{ $contribution }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Key Metrics Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white shadow rounded-lg p-4">
            <dt class="text-sm font-medium text-gray-500 truncate">Response Rate</dt>
            <dd class="mt-1 text-3xl font-semibold text-gray-900">{{ number_format($metrics['response_rate'], 1) }}%</dd>
        </div>
        <div class="bg-white shadow rounded-lg p-4">
            <dt class="text-sm font-medium text-gray-500 truncate">Total Leads</dt>
            <dd class="mt-1 text-3xl font-semibold text-gray-900">{{ number_format($metrics['total_leads']) }}</dd>
        </div>
        <div class="bg-white shadow rounded-lg p-4">
            <dt class="text-sm font-medium text-gray-500 truncate">Emails Sent</dt>
            <dd class="mt-1 text-3xl font-semibold text-gray-900">{{ number_format($metrics['total_sent']) }}</dd>
        </div>
        <div class="bg-white shadow rounded-lg p-4">
            <dt class="text-sm font-medium text-gray-500 truncate">Total Responses</dt>
            <dd class="mt-1 text-3xl font-semibold text-gray-900">{{ number_format($metrics['total_responses']) }}</dd>
        </div>
    </div>

    <!-- Interest Breakdown -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Interest Breakdown</h3>
            <div class="space-y-3">
                @php
                    $interestColors = [
                        'hot' => 'bg-red-500',
                        'warm' => 'bg-orange-500',
                        'cold' => 'bg-blue-500',
                        'negative' => 'bg-gray-500',
                    ];
                    $totalInterest = array_sum($metrics['interest_breakdown'] ?? []);
                @endphp
                @foreach ($metrics['interest_breakdown'] ?? [] as $level => $count)
                    @php
                        $percentage = $totalInterest > 0 ? ($count / $totalInterest) * 100 : 0;
                    @endphp
                    <div>
                        <div class="flex items-center justify-between text-sm mb-1">
                            <span class="font-medium text-gray-700 capitalize">{{ $level }}</span>
                            <span class="text-gray-500">{{ $count }} ({{ number_format($percentage, 1) }}%)</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="{{ $interestColors[$level] ?? 'bg-gray-400' }} h-2 rounded-full" style="width: {{ $percentage }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Sequence Performance</h3>
            <div class="space-y-3">
                @foreach ($metrics['sequence_performance'] ?? [] as $position => $data)
                    <div class="flex items-center justify-between py-2 border-b border-gray-100 last:border-0">
                        <span class="text-sm font-medium text-gray-700">Email {{ $position }}</span>
                        <div class="text-right">
                            <span class="text-sm text-gray-500">{{ $data['sent'] }} sent</span>
                            <span class="mx-2 text-gray-300">|</span>
                            <span class="text-sm font-medium {{ $data['response_rate'] > 10 ? 'text-green-600' : 'text-gray-600' }}">
                                {{ number_format($data['response_rate'], 1) }}% response
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Detected Patterns -->
    @if ($patterns->isNotEmpty())
        <div class="bg-white shadow rounded-lg p-6 mb-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Detected Patterns</h3>
            <div class="space-y-4">
                @foreach ($patterns as $pattern)
                    <div class="border-l-4 border-indigo-400 pl-4 py-2">
                        <p class="text-gray-700">{{ $pattern->content }}</p>
                        <p class="text-xs text-gray-500 mt-1">
                            Based on {{ $pattern->metadata['sample_size'] ?? 'N/A' }} responses
                            <span class="ml-2">Confidence: {{ $pattern->confidence_score ?? 'N/A' }}%</span>
                        </p>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Pinned Quotes (Quote Board) -->
    <div class="bg-white shadow rounded-lg p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Quote Board</h3>
        @if ($pinnedQuotes->isEmpty())
            <p class="text-gray-500 text-center py-8">
                No quotes pinned yet. Pin notable quotes from responses to build your evidence board.
            </p>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach ($pinnedQuotes as $quote)
                    <div class="bg-gray-50 rounded-lg p-4 relative group">
                        <button wire:click="unpinQuote({{ $quote->id }})"
                            class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity text-gray-400 hover:text-red-500">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                        <blockquote class="text-gray-700 italic">"{{ $quote->content }}"</blockquote>
                        @if ($quote->response)
                            <p class="text-xs text-gray-500 mt-2">
                                — {{ $quote->response->lead->company ?? $quote->response->lead->email }}
                            </p>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
