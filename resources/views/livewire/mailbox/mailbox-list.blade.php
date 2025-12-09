<div>
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-semibold text-text-primary">Mailboxes</h1>
                <p class="mt-1 text-sm text-text-secondary">Manage your connected email accounts</p>
            </div>
            <a href="{{ route('mailboxes.create') }}" class="inline-flex items-center px-4 py-2 bg-brand border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-brand-hover focus:bg-brand-hover focus:outline-none focus:ring-2 focus:ring-brand focus:ring-offset-2 transition ease-in-out duration-150">
                Add Mailbox
            </a>
        </div>

        <!-- Filters -->
        <div class="mb-4 flex space-x-2">
            <button wire:click="setFilter(null)" class="px-3 py-1 text-sm rounded-full {{ !$filterStatus ? 'bg-brand text-white' : 'bg-bg-surface text-text-primary' }}">
                All
            </button>
            <button wire:click="setFilter('active')" class="px-3 py-1 text-sm rounded-full {{ $filterStatus === 'active' ? 'bg-green-600 text-white' : 'bg-bg-surface text-text-primary' }}">
                Active
            </button>
            <button wire:click="setFilter('warmup')" class="px-3 py-1 text-sm rounded-full {{ $filterStatus === 'warmup' ? 'bg-yellow-600 text-white' : 'bg-bg-surface text-text-primary' }}">
                Warming Up
            </button>
            <button wire:click="setFilter('paused')" class="px-3 py-1 text-sm rounded-full {{ $filterStatus === 'paused' ? 'bg-gray-600 text-white' : 'bg-bg-surface text-text-primary' }}">
                Paused
            </button>
            <button wire:click="setFilter('error')" class="px-3 py-1 text-sm rounded-full {{ $filterStatus === 'error' ? 'bg-red-600 text-white' : 'bg-bg-surface text-text-primary' }}">
                Error
            </button>
        </div>

        <!-- Mailbox List -->
        @if($mailboxes->isEmpty())
            <div class="bg-bg-elevated overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-text-primary">No mailboxes</h3>
                    <p class="mt-1 text-sm text-text-secondary">Get started by connecting your email account.</p>
                    <div class="mt-6">
                        <a href="{{ route('mailboxes.create') }}" class="inline-flex items-center px-4 py-2 bg-brand border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-brand-hover">
                            Add Mailbox
                        </a>
                    </div>
                </div>
            </div>
        @else
            <div class="bg-bg-elevated overflow-hidden shadow-sm sm:rounded-lg">
                <ul class="divide-y divide-border-default">
                    @foreach($mailboxes as $mailbox)
                        <li class="p-4 hover:bg-bg-surface">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center min-w-0 flex-1">
                                    <div class="flex-shrink-0">
                                        @switch($mailbox->status)
                                            @case('active')
                                                <span class="inline-flex items-center justify-center h-10 w-10 rounded-full bg-green-100">
                                                    <svg class="h-5 w-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                    </svg>
                                                </span>
                                                @break
                                            @case('warmup')
                                                <span class="inline-flex items-center justify-center h-10 w-10 rounded-full bg-yellow-100">
                                                    <svg class="h-5 w-5 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                                                    </svg>
                                                </span>
                                                @break
                                            @case('paused')
                                                <span class="inline-flex items-center justify-center h-10 w-10 rounded-full bg-gray-100">
                                                    <svg class="h-5 w-5 text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM7 8a1 1 0 012 0v4a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v4a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                                    </svg>
                                                </span>
                                                @break
                                            @case('error')
                                                <span class="inline-flex items-center justify-center h-10 w-10 rounded-full bg-red-100">
                                                    <svg class="h-5 w-5 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                                    </svg>
                                                </span>
                                                @break
                                        @endswitch
                                    </div>
                                    <div class="ml-4 truncate">
                                        <p class="text-sm font-medium text-text-primary truncate">
                                            {{ $mailbox->name }}
                                        </p>
                                        <p class="text-sm text-text-secondary truncate">
                                            {{ $mailbox->email_address }}
                                        </p>
                                    </div>
                                </div>

                                <div class="flex items-center space-x-4 ml-4">
                                    <!-- Status Badge -->
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        @switch($mailbox->status)
                                            @case('active') bg-success-bg text-success @break
                                            @case('warmup') bg-warning-bg text-warning @break
                                            @case('paused') bg-bg-surface text-text-secondary @break
                                            @case('error') bg-error-bg text-error @break
                                        @endswitch">
                                        {{ ucfirst($mailbox->status) }}
                                        @if($mailbox->status === 'warmup')
                                            ({{ $mailbox->getWarmupProgressPercentage() }}%)
                                        @endif
                                    </span>

                                    <!-- Daily Limit -->
                                    <div class="text-sm text-text-secondary">
                                        {{ $mailbox->getCurrentDailyLimit() }}/day
                                    </div>

                                    <!-- Actions -->
                                    <div class="flex items-center space-x-2">
                                        @if($mailbox->status === 'paused' || $mailbox->status === 'error')
                                            <button wire:click="resume({{ $mailbox->id }})" wire:confirm="Are you sure you want to resume this mailbox?" class="text-green-600 hover:text-green-900">
                                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                            </button>
                                        @else
                                            <button wire:click="pause({{ $mailbox->id }})" wire:confirm="Are you sure you want to pause this mailbox?" class="text-yellow-600 hover:text-yellow-900">
                                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                            </button>
                                        @endif

                                        <a href="{{ route('mailboxes.edit', $mailbox) }}" class="text-brand hover:text-brand-hover">
                                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </a>

                                        <a href="{{ route('mailboxes.health', $mailbox) }}" class="text-text-secondary hover:text-text-primary">
                                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                            </svg>
                                        </a>

                                        <button wire:click="delete({{ $mailbox->id }})" wire:confirm="Are you sure you want to delete this mailbox? This action cannot be undone." class="text-red-600 hover:text-red-900">
                                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            @if($mailbox->status === 'error' && $mailbox->error_message)
                                <div class="mt-2 ml-14 text-sm text-red-600">
                                    {{ $mailbox->error_message }}
                                </div>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
</div>
