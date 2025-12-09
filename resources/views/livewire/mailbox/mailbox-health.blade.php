<div>
    <div class="max-w-4xl mx-auto py-6 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-text-primary">{{ $mailbox->name }}</h1>
                    <p class="mt-1 text-sm text-gray-600">{{ $mailbox->email_address }}</p>
                </div>
                <a href="{{ route('mailboxes.index') }}" class="text-sm text-indigo-600 hover:text-indigo-900">
                    &larr; Back to Mailboxes
                </a>
            </div>
        </div>

        <div class="space-y-6">
            <!-- Status Card -->
            <div class="bg-bg-elevated overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-text-primary mb-4">Status Overview</h3>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <!-- Status -->
                        <div class="bg-bg-base rounded-lg p-4">
                            <dt class="text-sm font-medium text-gray-500">Status</dt>
                            <dd class="mt-1 flex items-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    @switch($mailbox->status)
                                        @case('active') bg-green-100 text-success @break
                                        @case('warmup') bg-yellow-100 text-yellow-800 @break
                                        @case('paused') bg-gray-100 text-gray-800 @break
                                        @case('error') bg-red-100 text-red-800 @break
                                    @endswitch">
                                    {{ ucfirst($mailbox->status) }}
                                </span>
                            </dd>
                        </div>

                        <!-- Daily Limit -->
                        <div class="bg-bg-base rounded-lg p-4">
                            <dt class="text-sm font-medium text-gray-500">Daily Limit</dt>
                            <dd class="mt-1 text-2xl font-semibold text-text-primary">
                                {{ $mailbox->getCurrentDailyLimit() }}
                            </dd>
                        </div>

                        <!-- Warmup Progress -->
                        <div class="bg-bg-base rounded-lg p-4">
                            <dt class="text-sm font-medium text-gray-500">Warmup Progress</dt>
                            <dd class="mt-1">
                                @if($mailbox->warmup_enabled)
                                    <div class="flex items-center">
                                        <div class="flex-1 bg-gray-200 rounded-full h-2 mr-2">
                                            <div class="bg-indigo-600 h-2 rounded-full" style="width: {{ $warmupProgress }}%"></div>
                                        </div>
                                        <span class="text-sm text-text-primary">{{ $warmupProgress }}%</span>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1">Day {{ $mailbox->warmup_day }} of 14</p>
                                @else
                                    <span class="text-gray-500">Disabled</span>
                                @endif
                            </dd>
                        </div>

                        <!-- Last Polled -->
                        <div class="bg-bg-base rounded-lg p-4">
                            <dt class="text-sm font-medium text-gray-500">Last Polled</dt>
                            <dd class="mt-1 text-sm text-text-primary">
                                {{ $mailbox->last_polled_at ? $mailbox->last_polled_at->diffForHumans() : 'Never' }}
                            </dd>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Error Alert -->
            @if($lastError)
                <div class="bg-red-50 border-l-4 border-red-400 p-4 rounded-lg">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3 flex-1">
                            <h3 class="text-sm font-medium text-red-800">Error Detected</h3>
                            <p class="mt-1 text-sm text-red-700">{{ $lastError }}</p>
                            <p class="mt-1 text-xs text-red-600">
                                Occurred: {{ $mailbox->last_error_at?->diffForHumans() }}
                            </p>
                        </div>
                        <div class="ml-4">
                            <button wire:click="clearError" class="text-sm font-medium text-red-600 hover:text-red-500">
                                Dismiss
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Connection Test -->
            <div class="bg-bg-elevated overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-text-primary mb-4">Connection Test</h3>

                    <button wire:click="testConnection" wire:loading.attr="disabled" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-bg-elevated hover:bg-bg-base focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <span wire:loading.remove wire:target="testConnection">Test SMTP & IMAP Connection</span>
                        <span wire:loading wire:target="testConnection">Testing...</span>
                    </button>

                    @if($testResult)
                        <div class="mt-4 p-4 rounded-md {{ $testResult['success'] ? 'bg-success-bg' : 'bg-red-50' }}">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    @if($testResult['success'])
                                        <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                        </svg>
                                    @else
                                        <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                        </svg>
                                    @endif
                                </div>
                                <div class="ml-3">
                                    <h4 class="text-sm font-medium {{ $testResult['success'] ? 'text-success' : 'text-red-800' }}">
                                        {{ $testResult['success'] ? 'All connections successful!' : 'Connection failed' }}
                                    </h4>
                                    <div class="mt-2 text-sm {{ $testResult['success'] ? 'text-green-700' : 'text-red-700' }}">
                                        <p><strong>SMTP:</strong> {{ $testResult['smtp']['message'] }}</p>
                                        <p><strong>IMAP:</strong> {{ $testResult['imap']['message'] }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Sending Statistics -->
            <div class="bg-bg-elevated overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-text-primary mb-4">Last 7 Days Statistics</h3>

                    @if(count($recentStats) > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead>
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sent</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Delivered</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bounced</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Failed</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bounce Rate</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach($recentStats as $stat)
                                        <tr>
                                            <td class="px-4 py-3 text-sm text-text-primary">{{ \Carbon\Carbon::parse($stat['date'])->format('M j, Y') }}</td>
                                            <td class="px-4 py-3 text-sm text-text-primary">{{ $stat['emails_sent'] }}</td>
                                            <td class="px-4 py-3 text-sm text-green-600">{{ $stat['emails_delivered'] }}</td>
                                            <td class="px-4 py-3 text-sm text-red-600">{{ $stat['emails_bounced'] }}</td>
                                            <td class="px-4 py-3 text-sm text-orange-600">{{ $stat['emails_failed'] }}</td>
                                            <td class="px-4 py-3 text-sm {{ $stat['bounce_rate'] > 5 ? 'text-red-600' : 'text-text-primary' }}">{{ $stat['bounce_rate'] }}%</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-gray-500 text-sm">No sending statistics available yet.</p>
                    @endif
                </div>
            </div>

            <!-- Configuration Summary -->
            <div class="bg-bg-elevated overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-text-primary mb-4">Configuration</h3>

                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">SMTP Server</dt>
                            <dd class="mt-1 text-sm text-text-primary">{{ $mailbox->smtp_host }}:{{ $mailbox->smtp_port }} ({{ strtoupper($mailbox->smtp_encryption) }})</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">IMAP Server</dt>
                            <dd class="mt-1 text-sm text-text-primary">{{ $mailbox->imap_host }}:{{ $mailbox->imap_port }} ({{ strtoupper($mailbox->imap_encryption) }})</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Send Window</dt>
                            <dd class="mt-1 text-sm text-text-primary">{{ substr($mailbox->send_window_start, 0, 5) }} - {{ substr($mailbox->send_window_end, 0, 5) }} ({{ $mailbox->timezone }})</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Skip Weekends</dt>
                            <dd class="mt-1 text-sm text-text-primary">{{ $mailbox->skip_weekends ? 'Yes' : 'No' }}</dd>
                        </div>
                    </dl>

                    <div class="mt-6">
                        <a href="{{ route('mailboxes.edit', $mailbox) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">
                            Edit Configuration &rarr;
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
