<div class="py-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Dev Mail Tool</h1>
            <p class="text-sm text-gray-500">Greenmail test server interface</p>
        </div>
        <div class="flex items-center gap-3">
            @if($this->isAvailable)
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                    <span class="w-2 h-2 mr-1.5 bg-green-400 rounded-full"></span>
                    Connected
                </span>
            @else
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                    <span class="w-2 h-2 mr-1.5 bg-red-400 rounded-full"></span>
                    Disconnected
                </span>
            @endif
            <button wire:click="refresh" class="inline-flex items-center px-3 py-1.5 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                Refresh
            </button>
            <button wire:click="openCompose" class="inline-flex items-center px-3 py-1.5 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Compose
            </button>
            <button wire:click="purgeAll" wire:confirm="Are you sure you want to delete ALL messages from ALL mailboxes?" class="inline-flex items-center px-3 py-1.5 border border-red-300 text-sm font-medium rounded-md text-red-700 bg-white hover:bg-red-50">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
                Purge All
            </button>
        </div>
    </div>

    {{-- Flash Messages --}}
    @if (session()->has('success'))
        <div class="mb-4 rounded-md bg-green-50 p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                </div>
            </div>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="mb-4 rounded-md bg-red-50 p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                </div>
            </div>
        </div>
    @endif

    @if(!$this->isAvailable)
        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-yellow-700">
                        Greenmail server is not available. Make sure Docker containers are running with <code class="bg-yellow-100 px-1 rounded">docker compose up -d</code>
                    </p>
                </div>
            </div>
        </div>
    @else
        <div class="flex gap-4 h-[calc(100vh-220px)]">
            {{-- Mailbox List --}}
            <div class="w-64 flex-shrink-0 bg-white rounded-lg shadow overflow-hidden flex flex-col">
                <div class="px-4 py-3 bg-gray-50 border-b border-gray-200">
                    <h2 class="text-sm font-medium text-gray-700">Mailboxes</h2>
                </div>
                <div class="flex-1 overflow-y-auto">
                    @forelse($this->users as $user)
                        <button
                            wire:click="selectUser('{{ $user['email'] }}')"
                            class="w-full px-4 py-3 text-left hover:bg-gray-50 border-b border-gray-100 {{ $selectedUser === $user['email'] ? 'bg-indigo-50 border-l-2 border-l-indigo-500' : '' }}"
                        >
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-900 truncate">{{ $user['email'] }}</span>
                            </div>
                        </button>
                    @empty
                        <div class="px-4 py-8 text-center text-gray-500 text-sm">
                            No mailboxes yet.<br>
                            <span class="text-xs">Send an email to create one.</span>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Message List --}}
            <div class="w-80 flex-shrink-0 bg-white rounded-lg shadow overflow-hidden flex flex-col">
                <div class="px-4 py-3 bg-gray-50 border-b border-gray-200 flex items-center justify-between">
                    <h2 class="text-sm font-medium text-gray-700">
                        @if($selectedUser)
                            Messages
                        @else
                            Select a mailbox
                        @endif
                    </h2>
                    @if($selectedUser && count($this->messages) > 0)
                        <button
                            wire:click="deleteUserMessages"
                            wire:confirm="Delete all messages in this mailbox?"
                            class="text-xs text-red-600 hover:text-red-800"
                        >
                            Clear all
                        </button>
                    @endif
                </div>
                <div class="flex-1 overflow-y-auto">
                    @if($selectedUser)
                        @forelse($this->messages as $message)
                            <button
                                wire:click="selectMessage('{{ $message['id'] }}')"
                                class="w-full px-4 py-3 text-left hover:bg-gray-50 border-b border-gray-100 {{ $selectedMessageId === $message['id'] ? 'bg-indigo-50' : '' }}"
                            >
                                <div class="text-sm font-medium text-gray-900 truncate">{{ $message['from'] ?: 'Unknown' }}</div>
                                <div class="text-sm text-gray-600 truncate">{{ $message['subject'] ?: '(No Subject)' }}</div>
                                <div class="text-xs text-gray-400 mt-1">{{ $message['receivedDate'] ?: '' }}</div>
                            </button>
                        @empty
                            <div class="px-4 py-8 text-center text-gray-500 text-sm">
                                No messages in this mailbox.
                            </div>
                        @endforelse
                    @else
                        <div class="px-4 py-8 text-center text-gray-500 text-sm">
                            Select a mailbox to view messages.
                        </div>
                    @endif
                </div>
            </div>

            {{-- Message View / Compose --}}
            <div class="flex-1 bg-white rounded-lg shadow overflow-hidden flex flex-col">
                @if($showCompose)
                    {{-- Compose Form --}}
                    <div class="px-4 py-3 bg-gray-50 border-b border-gray-200 flex items-center justify-between">
                        <h2 class="text-sm font-medium text-gray-700">New Message</h2>
                        <button wire:click="closeCompose" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <form wire:submit="sendEmail" class="flex-1 flex flex-col p-4">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">From</label>
                                <input type="email" wire:model="composeFrom" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="sender@localhost">
                                @error('composeFrom') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">To</label>
                                <input type="email" wire:model="composeTo" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="recipient@localhost">
                                @error('composeTo') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Subject</label>
                                <input type="text" wire:model="composeSubject" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Email subject">
                                @error('composeSubject') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div class="flex-1">
                                <label class="block text-sm font-medium text-gray-700">Message</label>
                                <textarea wire:model="composeBody" rows="10" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Your message..."></textarea>
                                @error('composeBody') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="mt-4 flex justify-end">
                            <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                                </svg>
                                Send
                            </button>
                        </div>
                    </form>
                @elseif($this->currentMessage)
                    @php $msg = $this->currentMessage; @endphp
                    {{-- Message View --}}
                    <div class="px-4 py-3 bg-gray-50 border-b border-gray-200 flex items-center justify-between">
                        <div>
                            <h2 class="text-sm font-medium text-gray-900">{{ $msg['subject'] ?: '(No Subject)' }}</h2>
                            <p class="text-xs text-gray-500">From: {{ $msg['from'] ?: 'Unknown' }}</p>
                        </div>
                        <button wire:click="openReply" class="inline-flex items-center px-3 py-1.5 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                            </svg>
                            Reply
                        </button>
                    </div>

                    @if($showReply)
                        {{-- Reply Form --}}
                        <div class="p-4 border-b border-gray-200 bg-gray-50">
                            <form wire:submit="sendReply">
                                <div class="mb-2">
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Reply as: {{ $selectedUser }}</label>
                                    <textarea wire:model="replyBody" rows="4" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Type your reply..."></textarea>
                                    @error('replyBody') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                <div class="flex justify-end gap-2">
                                    <button type="button" wire:click="closeReply" class="px-3 py-1.5 text-sm text-gray-600 hover:text-gray-800">Cancel</button>
                                    <button type="submit" class="inline-flex items-center px-3 py-1.5 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                                        Send Reply
                                    </button>
                                </div>
                            </form>
                        </div>
                    @endif

                    <div class="flex-1 overflow-y-auto p-4">
                        <div class="prose prose-sm max-w-none">
                            <div class="mb-4 text-xs text-gray-500 space-y-1">
                                <div><strong>To:</strong> {{ $msg['to'] ?: $selectedUser }}</div>
                                <div><strong>Date:</strong> {{ $msg['receivedDate'] ?: 'Unknown' }}</div>
                                @if($msg['messageId'])
                                    <div><strong>Message-ID:</strong> <code class="text-xs bg-gray-100 px-1 rounded">{{ $msg['messageId'] }}</code></div>
                                @endif
                            </div>
                            <hr class="my-4">
                            @if($msg['htmlContent'])
                                <div class="email-content">
                                    {!! $msg['htmlContent'] !!}
                                </div>
                            @elseif($msg['textContent'])
                                <pre class="whitespace-pre-wrap text-sm text-gray-800">{{ $msg['textContent'] }}</pre>
                            @else
                                <p class="text-gray-500 italic">No content available.</p>
                            @endif
                        </div>
                    </div>
                @else
                    {{-- No message selected --}}
                    <div class="flex-1 flex items-center justify-center text-gray-500">
                        <div class="text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            <p class="mt-2 text-sm">Select a message to view</p>
                            <p class="mt-1 text-xs text-gray-400">or compose a new one</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>
