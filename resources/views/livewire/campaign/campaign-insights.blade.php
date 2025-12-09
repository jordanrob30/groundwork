<div>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-text-primary">Campaign Insights</h2>
            <p class="mt-1 text-sm text-text-secondary">{{ $campaign->name }}</p>
        </div>
        <div class="flex space-x-2">
            <button wire:click="refresh"
                class="inline-flex items-center px-3 py-2 border border-border-default shadow-sm text-sm leading-4 font-medium rounded-md text-text-primary bg-bg-elevated hover:bg-bg-surface focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand">
                <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                Refresh
            </button>
            <button wire:click="triggerReanalysis"
                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-brand hover:bg-brand-hover focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand">
                Re-analyze Responses
            </button>
        </div>
    </div>

    <!-- Decision Score -->
    <div class="bg-bg-elevated shadow rounded-lg p-6 mb-6">
        <h3 class="text-lg font-medium text-text-primary mb-4">Decision Score</h3>
        <div class="flex items-center space-x-8">
            <div class="flex-shrink-0">
                <div class="relative w-32 h-32">
                    <svg class="w-32 h-32 transform -rotate-90" viewBox="0 0 36 36">
                        <path class="text-text-muted opacity-20" stroke="currentColor" stroke-width="3" fill="none"
                            d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                        <path class="{{ $decisionScore['score'] >= 70 ? 'text-success' : ($decisionScore['score'] >= 40 ? 'text-warning' : 'text-error') }}"
                            stroke="currentColor" stroke-width="3" fill="none" stroke-linecap="round"
                            stroke-dasharray="{{ $decisionScore['score'] }}, 100"
                            d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                    </svg>
                    <div class="absolute inset-0 flex items-center justify-center">
                        <span class="text-3xl font-bold text-text-primary">{{ $decisionScore['score'] }}</span>
                    </div>
                </div>
            </div>
            <div class="flex-1">
                <p class="text-lg font-medium {{ $decisionScore['score'] >= 70 ? 'text-success' : ($decisionScore['score'] >= 40 ? 'text-warning' : 'text-error') }}">
                    {{ $decisionScore['recommendation'] }}
                </p>
                <div class="mt-4 space-y-2">
                    @foreach ($decisionScore['factors'] as $factor => $contribution)
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-text-secondary">{{ ucfirst(str_replace('_', ' ', $factor)) }}</span>
                            <span class="font-medium {{ $contribution > 0 ? 'text-success' : 'text-error' }}">
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
        <div class="bg-bg-elevated shadow rounded-lg p-4">
            <dt class="text-sm font-medium text-text-secondary truncate">Response Rate</dt>
            <dd class="mt-1 text-3xl font-semibold text-text-primary">{{ number_format($metrics['response_rate'], 1) }}%</dd>
        </div>
        <div class="bg-bg-elevated shadow rounded-lg p-4">
            <dt class="text-sm font-medium text-text-secondary truncate">Total Leads</dt>
            <dd class="mt-1 text-3xl font-semibold text-text-primary">{{ number_format($metrics['total_leads']) }}</dd>
        </div>
        <div class="bg-bg-elevated shadow rounded-lg p-4">
            <dt class="text-sm font-medium text-text-secondary truncate">Emails Sent</dt>
            <dd class="mt-1 text-3xl font-semibold text-text-primary">{{ number_format($metrics['total_sent']) }}</dd>
        </div>
        <div class="bg-bg-elevated shadow rounded-lg p-4">
            <dt class="text-sm font-medium text-text-secondary truncate">Total Responses</dt>
            <dd class="mt-1 text-3xl font-semibold text-text-primary">{{ number_format($metrics['total_responses']) }}</dd>
        </div>
    </div>

    <!-- Interest Breakdown -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <div class="bg-bg-elevated shadow rounded-lg p-6">
            <h3 class="text-lg font-medium text-text-primary mb-4">Interest Breakdown</h3>
            <div class="space-y-3">
                @php
                    $interestColors = [
                        'hot' => 'bg-lead-hot',
                        'warm' => 'bg-lead-warm',
                        'cold' => 'bg-lead-cold',
                        'negative' => 'bg-lead-negative',
                    ];
                    $totalInterest = array_sum($metrics['interest_breakdown'] ?? []);
                @endphp
                @foreach ($metrics['interest_breakdown'] ?? [] as $level => $count)
                    @php
                        $percentage = $totalInterest > 0 ? ($count / $totalInterest) * 100 : 0;
                    @endphp
                    <div>
                        <div class="flex items-center justify-between text-sm mb-1">
                            <span class="font-medium text-text-primary capitalize">{{ $level }}</span>
                            <span class="text-text-secondary">{{ $count }} ({{ number_format($percentage, 1) }}%)</span>
                        </div>
                        <div class="w-full bg-bg-surface rounded-full h-2">
                            <div class="{{ $interestColors[$level] ?? 'bg-text-muted' }} h-2 rounded-full" style="width: {{ $percentage }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="bg-bg-elevated shadow rounded-lg p-6">
            <h3 class="text-lg font-medium text-text-primary mb-4">Sequence Performance</h3>
            <div class="space-y-3">
                @foreach ($metrics['sequence_performance'] ?? [] as $position => $data)
                    <div class="flex items-center justify-between py-2 border-b border-border-default last:border-0">
                        <span class="text-sm font-medium text-text-primary">Email {{ $position }}</span>
                        <div class="text-right">
                            <span class="text-sm text-text-secondary">{{ $data['sent'] }} sent</span>
                            <span class="mx-2 text-text-muted">|</span>
                            <span class="text-sm font-medium {{ $data['response_rate'] > 10 ? 'text-success' : 'text-text-secondary' }}">
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
        <div class="bg-bg-elevated shadow rounded-lg p-6 mb-6">
            <h3 class="text-lg font-medium text-text-primary mb-4">Detected Patterns</h3>
            <div class="space-y-4">
                @foreach ($patterns as $pattern)
                    <div class="border-l-4 border-brand pl-4 py-2">
                        <p class="text-text-primary">{{ $pattern->content }}</p>
                        <p class="text-xs text-text-secondary mt-1">
                            Based on {{ $pattern->metadata['sample_size'] ?? 'N/A' }} responses
                            <span class="ml-2">Confidence: {{ $pattern->confidence_score ?? 'N/A' }}%</span>
                        </p>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Pinned Quotes (Quote Board) -->
    <div class="bg-bg-elevated shadow rounded-lg p-6">
        <h3 class="text-lg font-medium text-text-primary mb-4">Quote Board</h3>
        @if ($pinnedQuotes->isEmpty())
            <p class="text-text-secondary text-center py-8">
                No quotes pinned yet. Pin notable quotes from responses to build your evidence board.
            </p>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach ($pinnedQuotes as $quote)
                    <div class="bg-bg-surface rounded-lg p-4 relative group">
                        <button wire:click="unpinQuote({{ $quote->id }})"
                            class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity text-text-muted hover:text-error">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                        <blockquote class="text-text-primary italic">"{{ $quote->content }}"</blockquote>
                        @if ($quote->response)
                            <p class="text-xs text-text-secondary mt-2">
                                — {{ $quote->response->lead->company ?? $quote->response->lead->email }}
                            </p>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
