<div>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <a href="{{ route('responses.index') }}" class="text-sm text-brand hover:text-brand-hover">&larr; Back to Inbox</a>
            <h2 class="mt-2 text-2xl font-bold text-text-primary">{{ $lead->full_name }}</h2>
            <p class="text-sm text-text-secondary">{{ $lead->company ?? $lead->email }} &mdash; {{ $response->campaign->name }}</p>
        </div>
        <div class="flex space-x-2">
            <button wire:click="reply"
                class="inline-flex items-center px-4 py-2 border border-border-default text-sm font-medium rounded-md text-text-primary bg-bg-elevated hover:bg-bg-surface">
                Reply
            </button>
            <button wire:click="bookCall"
                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-brand hover:bg-brand-hover">
                Book Call
            </button>
        </div>
    </div>

    @if (session()->has('message'))
        <div class="mb-4 rounded-md bg-success-bg p-4">
            <p class="text-sm font-medium text-success">{{ session('message') }}</p>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Conversation Thread -->
        <div class="lg:col-span-2 space-y-4">
            <div class="bg-bg-elevated shadow rounded-lg">
                <div class="px-4 py-3 border-b border-border-default">
                    <h3 class="text-lg font-medium text-text-primary">Conversation</h3>
                </div>
                <div class="divide-y divide-border-default">
                    @foreach ($conversationThread as $message)
                        <div class="p-4 {{ $message['type'] === 'received' ? 'bg-bg-surface' : '' }}">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-medium {{ $message['type'] === 'received' ? 'text-brand' : 'text-text-primary' }}">
                                    {{ $message['type'] === 'received' ? $lead->full_name : 'You' }}
                                    @if ($message['type'] === 'received' && ($message['is_auto_reply'] ?? false))
                                        <span class="ml-2 text-xs text-warning">(Auto-reply)</span>
                                    @endif
                                </span>
                                <span class="text-xs text-text-secondary">
                                    {{ \Carbon\Carbon::parse($message['date'])->format('M j, Y g:i A') }}
                                </span>
                            </div>
                            <p class="text-sm text-text-secondary mb-2">{{ $message['subject'] }}</p>
                            <div class="prose prose-sm max-w-none text-text-primary">
                                {!! nl2br(e(strip_tags($message['body']))) !!}
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- AI Analysis Panel -->
        <div class="space-y-4">
            <!-- Analysis Status -->
            <div class="bg-bg-elevated shadow rounded-lg p-4">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-text-primary">AI Analysis</h3>
                    @if ($response->analysis_status === 'completed')
                        <span class="text-xs text-success">Analyzed</span>
                    @elseif ($response->analysis_status === 'analyzing')
                        <span class="text-xs text-yellow-600">Analyzing...</span>
                    @elseif ($response->analysis_status === 'failed')
                        <span class="text-xs text-error">Failed</span>
                    @else
                        <span class="text-xs text-text-secondary">Pending</span>
                    @endif
                </div>

                <div class="space-y-4">
                    <!-- Interest Level -->
                    <div>
                        <label class="block text-sm font-medium text-text-primary">Interest Level</label>
                        <select wire:model="interest_level"
                            class="mt-1 block w-full rounded-md bg-bg-base border-border-default text-text-primary shadow-sm focus:border-brand focus:ring-brand sm:text-sm">
                            <option value="">Not analyzed</option>
                            <option value="hot">Hot - Ready to engage</option>
                            <option value="warm">Warm - Interested</option>
                            <option value="cold">Cold - Not interested</option>
                            <option value="negative">Negative - Explicit rejection</option>
                        </select>
                    </div>

                    <!-- Problem Confirmation -->
                    <div>
                        <label class="block text-sm font-medium text-text-primary">Problem Confirmation</label>
                        <select wire:model="problem_confirmation"
                            class="mt-1 block w-full rounded-md bg-bg-base border-border-default text-text-primary shadow-sm focus:border-brand focus:ring-brand sm:text-sm">
                            <option value="">Not analyzed</option>
                            <option value="yes">Yes - Confirms hypothesis</option>
                            <option value="no">No - Doesn't have problem</option>
                            <option value="different">Different problem</option>
                            <option value="unclear">Unclear</option>
                        </select>
                    </div>

                    <!-- Pain Severity -->
                    <div>
                        <label class="block text-sm font-medium text-text-primary">Pain Severity (1-5)</label>
                        <input type="range" wire:model="pain_severity" min="1" max="5" step="1"
                            class="mt-1 block w-full">
                        <div class="flex justify-between text-xs text-text-secondary">
                            <span>Mild</span>
                            <span>{{ $pain_severity ?? '-' }}</span>
                            <span>Critical</span>
                        </div>
                    </div>

                    <!-- Call Interest -->
                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" wire:model="call_interest"
                                class="rounded border-border-default text-brand focus:ring-brand">
                            <span class="ml-2 text-sm text-text-primary">Interested in a call</span>
                        </label>
                    </div>

                    <!-- Current Solution -->
                    <div>
                        <label class="block text-sm font-medium text-text-primary">Current Solution</label>
                        <textarea wire:model="current_solution" rows="2"
                            class="mt-1 block w-full rounded-md bg-bg-base border-border-default text-text-primary placeholder:text-text-muted shadow-sm focus:border-brand focus:ring-brand sm:text-sm"
                            placeholder="How they solve the problem now..."></textarea>
                    </div>

                    <!-- Summary -->
                    <div>
                        <label class="block text-sm font-medium text-text-primary">Summary</label>
                        <textarea wire:model="summary" rows="3"
                            class="mt-1 block w-full rounded-md bg-bg-base border-border-default text-text-primary placeholder:text-text-muted shadow-sm focus:border-brand focus:ring-brand sm:text-sm"
                            placeholder="Key takeaway from this response..."></textarea>
                    </div>

                    <!-- Actions -->
                    <div class="flex justify-between pt-2">
                        <button wire:click="reanalyze"
                            class="text-sm text-brand hover:text-brand-hover">
                            Re-analyze
                        </button>
                        <button wire:click="saveAnalysisOverride"
                            class="inline-flex items-center px-3 py-1.5 border border-transparent text-sm font-medium rounded-md text-white bg-brand hover:bg-brand-hover">
                            Save Changes
                        </button>
                    </div>
                </div>
            </div>

            <!-- Key Quotes -->
            @if (!empty($key_quotes))
                <div class="bg-bg-elevated shadow rounded-lg p-4">
                    <h3 class="text-lg font-medium text-text-primary mb-3">Key Quotes</h3>
                    <div class="space-y-2">
                        @foreach ($key_quotes as $quote)
                            <blockquote class="border-l-4 border-brand pl-3 py-1 text-sm text-text-secondary italic">
                                "{{ $quote }}"
                            </blockquote>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Lead Info -->
            <div class="bg-bg-elevated shadow rounded-lg p-4">
                <h3 class="text-lg font-medium text-text-primary mb-3">Lead Details</h3>
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-text-secondary">Email</dt>
                        <dd class="text-text-primary">{{ $lead->email }}</dd>
                    </div>
                    @if ($lead->company)
                        <div class="flex justify-between">
                            <dt class="text-text-secondary">Company</dt>
                            <dd class="text-text-primary">{{ $lead->company }}</dd>
                        </div>
                    @endif
                    @if ($lead->role)
                        <div class="flex justify-between">
                            <dt class="text-text-secondary">Role</dt>
                            <dd class="text-text-primary">{{ $lead->role }}</dd>
                        </div>
                    @endif
                    <div class="flex justify-between">
                        <dt class="text-text-secondary">Status</dt>
                        <dd class="text-text-primary capitalize">{{ str_replace('_', ' ', $lead->status) }}</dd>
                    </div>
                    @if ($lead->linkedin_url)
                        <div class="pt-2">
                            <a href="{{ $lead->linkedin_url }}" target="_blank" class="text-brand hover:text-brand-hover">
                                View LinkedIn Profile &rarr;
                            </a>
                        </div>
                    @endif
                </dl>
            </div>
        </div>
    </div>
</div>
