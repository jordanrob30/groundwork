<div>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <a href="{{ route('responses.index') }}" class="text-sm text-indigo-600 hover:text-indigo-900">&larr; Back to Inbox</a>
            <h2 class="mt-2 text-2xl font-bold text-gray-900">{{ $lead->full_name }}</h2>
            <p class="text-sm text-gray-600">{{ $lead->company ?? $lead->email }} &mdash; {{ $response->campaign->name }}</p>
        </div>
        <div class="flex space-x-2">
            <button wire:click="reply"
                class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                Reply
            </button>
            <button wire:click="bookCall"
                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                Book Call
            </button>
        </div>
    </div>

    @if (session()->has('message'))
        <div class="mb-4 rounded-md bg-green-50 p-4">
            <p class="text-sm font-medium text-green-800">{{ session('message') }}</p>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Conversation Thread -->
        <div class="lg:col-span-2 space-y-4">
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-3 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Conversation</h3>
                </div>
                <div class="divide-y divide-gray-200">
                    @foreach ($conversationThread as $message)
                        <div class="p-4 {{ $message['type'] === 'received' ? 'bg-blue-50' : '' }}">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-medium {{ $message['type'] === 'received' ? 'text-blue-900' : 'text-gray-900' }}">
                                    {{ $message['type'] === 'received' ? $lead->full_name : 'You' }}
                                    @if ($message['type'] === 'received' && ($message['is_auto_reply'] ?? false))
                                        <span class="ml-2 text-xs text-yellow-600">(Auto-reply)</span>
                                    @endif
                                </span>
                                <span class="text-xs text-gray-500">
                                    {{ \Carbon\Carbon::parse($message['date'])->format('M j, Y g:i A') }}
                                </span>
                            </div>
                            <p class="text-sm text-gray-600 mb-2">{{ $message['subject'] }}</p>
                            <div class="prose prose-sm max-w-none text-gray-700">
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
            <div class="bg-white shadow rounded-lg p-4">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">AI Analysis</h3>
                    @if ($response->analysis_status === 'completed')
                        <span class="text-xs text-green-600">Analyzed</span>
                    @elseif ($response->analysis_status === 'analyzing')
                        <span class="text-xs text-yellow-600">Analyzing...</span>
                    @elseif ($response->analysis_status === 'failed')
                        <span class="text-xs text-red-600">Failed</span>
                    @else
                        <span class="text-xs text-gray-500">Pending</span>
                    @endif
                </div>

                <div class="space-y-4">
                    <!-- Interest Level -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Interest Level</label>
                        <select wire:model="interest_level"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="">Not analyzed</option>
                            <option value="hot">Hot - Ready to engage</option>
                            <option value="warm">Warm - Interested</option>
                            <option value="cold">Cold - Not interested</option>
                            <option value="negative">Negative - Explicit rejection</option>
                        </select>
                    </div>

                    <!-- Problem Confirmation -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Problem Confirmation</label>
                        <select wire:model="problem_confirmation"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="">Not analyzed</option>
                            <option value="yes">Yes - Confirms hypothesis</option>
                            <option value="no">No - Doesn't have problem</option>
                            <option value="different">Different problem</option>
                            <option value="unclear">Unclear</option>
                        </select>
                    </div>

                    <!-- Pain Severity -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Pain Severity (1-5)</label>
                        <input type="range" wire:model="pain_severity" min="1" max="5" step="1"
                            class="mt-1 block w-full">
                        <div class="flex justify-between text-xs text-gray-500">
                            <span>Mild</span>
                            <span>{{ $pain_severity ?? '-' }}</span>
                            <span>Critical</span>
                        </div>
                    </div>

                    <!-- Call Interest -->
                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" wire:model="call_interest"
                                class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="ml-2 text-sm text-gray-700">Interested in a call</span>
                        </label>
                    </div>

                    <!-- Current Solution -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Current Solution</label>
                        <textarea wire:model="current_solution" rows="2"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            placeholder="How they solve the problem now..."></textarea>
                    </div>

                    <!-- Summary -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Summary</label>
                        <textarea wire:model="summary" rows="3"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            placeholder="Key takeaway from this response..."></textarea>
                    </div>

                    <!-- Actions -->
                    <div class="flex justify-between pt-2">
                        <button wire:click="reanalyze"
                            class="text-sm text-indigo-600 hover:text-indigo-900">
                            Re-analyze
                        </button>
                        <button wire:click="saveAnalysisOverride"
                            class="inline-flex items-center px-3 py-1.5 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                            Save Changes
                        </button>
                    </div>
                </div>
            </div>

            <!-- Key Quotes -->
            @if (!empty($key_quotes))
                <div class="bg-white shadow rounded-lg p-4">
                    <h3 class="text-lg font-medium text-gray-900 mb-3">Key Quotes</h3>
                    <div class="space-y-2">
                        @foreach ($key_quotes as $quote)
                            <blockquote class="border-l-4 border-indigo-300 pl-3 py-1 text-sm text-gray-600 italic">
                                "{{ $quote }}"
                            </blockquote>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Lead Info -->
            <div class="bg-white shadow rounded-lg p-4">
                <h3 class="text-lg font-medium text-gray-900 mb-3">Lead Details</h3>
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Email</dt>
                        <dd class="text-gray-900">{{ $lead->email }}</dd>
                    </div>
                    @if ($lead->company)
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Company</dt>
                            <dd class="text-gray-900">{{ $lead->company }}</dd>
                        </div>
                    @endif
                    @if ($lead->role)
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Role</dt>
                            <dd class="text-gray-900">{{ $lead->role }}</dd>
                        </div>
                    @endif
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Status</dt>
                        <dd class="text-gray-900 capitalize">{{ str_replace('_', ' ', $lead->status) }}</dd>
                    </div>
                    @if ($lead->linkedin_url)
                        <div class="pt-2">
                            <a href="{{ $lead->linkedin_url }}" target="_blank" class="text-indigo-600 hover:text-indigo-900">
                                View LinkedIn Profile &rarr;
                            </a>
                        </div>
                    @endif
                </dl>
            </div>
        </div>
    </div>
</div>
