<div>
    <div class="max-w-4xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="md:grid md:grid-cols-3 md:gap-6">
            <div class="md:col-span-1">
                <div class="px-4 sm:px-0">
                    <h3 class="text-lg font-medium leading-6 text-text-primary">
                        {{ $mailbox ? 'Edit Mailbox' : 'Add New Mailbox' }}
                    </h3>
                    <p class="mt-1 text-sm text-text-secondary">
                        Configure your email account for sending and receiving emails.
                    </p>
                </div>
            </div>

            <div class="mt-5 md:mt-0 md:col-span-2">
                <form wire:submit="save">
                    @if (session()->has('message'))
                        <div class="mb-4 rounded-md bg-success-bg p-4">
                            <div class="text-sm font-medium text-success">
                                {{ session('message') }}
                            </div>
                        </div>
                    @endif

                    <div class="shadow sm:rounded-md sm:overflow-hidden">
                        <div class="px-4 py-5 bg-bg-elevated space-y-6 sm:p-6">
                            <!-- Basic Information -->
                            <div>
                                <h4 class="text-sm font-medium text-text-primary mb-4">Basic Information</h4>
                                <div class="grid grid-cols-6 gap-6">
                                    <div class="col-span-6 sm:col-span-3">
                                        <label for="name" class="block text-sm font-medium text-text-primary">Display Name</label>
                                        <input type="text" wire:model="name" id="name" class="mt-1 block w-full rounded-md border-border-default shadow-sm focus:border-brand focus:ring-brand sm:text-sm" placeholder="Work Gmail">
                                        @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>

                                    <div class="col-span-6 sm:col-span-3">
                                        <label for="email_address" class="block text-sm font-medium text-text-primary">Email Address</label>
                                        <input type="email" wire:model="email_address" id="email_address" class="mt-1 block w-full rounded-md border-border-default shadow-sm focus:border-brand focus:ring-brand sm:text-sm" placeholder="you@example.com">
                                        @error('email_address') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- SMTP Configuration -->
                            <div class="border-t border-border-default pt-6">
                                <h4 class="text-sm font-medium text-text-primary mb-4">SMTP Configuration (Outgoing)</h4>
                                <div class="grid grid-cols-6 gap-6">
                                    <div class="col-span-6 sm:col-span-3">
                                        <label for="smtp_host" class="block text-sm font-medium text-text-primary">SMTP Host</label>
                                        <input type="text" wire:model="smtp_host" id="smtp_host" class="mt-1 block w-full rounded-md border-border-default shadow-sm focus:border-brand focus:ring-brand sm:text-sm" placeholder="smtp.gmail.com">
                                        @error('smtp_host') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>

                                    <div class="col-span-3 sm:col-span-1">
                                        <label for="smtp_port" class="block text-sm font-medium text-text-primary">Port</label>
                                        <select wire:model="smtp_port" id="smtp_port" class="mt-1 block w-full rounded-md border-border-default shadow-sm focus:border-brand focus:ring-brand sm:text-sm">
                                            <option value="587">587</option>
                                            <option value="465">465</option>
                                            <option value="25">25</option>
                                            <option value="2525">2525</option>
                                        </select>
                                    </div>

                                    <div class="col-span-3 sm:col-span-2">
                                        <label for="smtp_encryption" class="block text-sm font-medium text-text-primary">Encryption</label>
                                        <select wire:model="smtp_encryption" id="smtp_encryption" class="mt-1 block w-full rounded-md border-border-default shadow-sm focus:border-brand focus:ring-brand sm:text-sm">
                                            <option value="tls">TLS</option>
                                            <option value="ssl">SSL</option>
                                        </select>
                                    </div>

                                    <div class="col-span-6 sm:col-span-3">
                                        <label for="smtp_username" class="block text-sm font-medium text-text-primary">Username</label>
                                        <input type="text" wire:model="smtp_username" id="smtp_username" class="mt-1 block w-full rounded-md border-border-default shadow-sm focus:border-brand focus:ring-brand sm:text-sm">
                                        @error('smtp_username') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>

                                    <div class="col-span-6 sm:col-span-3">
                                        <label for="smtp_password" class="block text-sm font-medium text-text-primary">Password {{ $mailbox ? '(leave blank to keep current)' : '' }}</label>
                                        <input type="password" wire:model="smtp_password" id="smtp_password" class="mt-1 block w-full rounded-md border-border-default shadow-sm focus:border-brand focus:ring-brand sm:text-sm">
                                        @error('smtp_password') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- IMAP Configuration -->
                            <div class="border-t border-border-default pt-6">
                                <h4 class="text-sm font-medium text-text-primary mb-4">IMAP Configuration (Incoming)</h4>
                                <div class="grid grid-cols-6 gap-6">
                                    <div class="col-span-6 sm:col-span-3">
                                        <label for="imap_host" class="block text-sm font-medium text-text-primary">IMAP Host</label>
                                        <input type="text" wire:model="imap_host" id="imap_host" class="mt-1 block w-full rounded-md border-border-default shadow-sm focus:border-brand focus:ring-brand sm:text-sm" placeholder="imap.gmail.com">
                                        @error('imap_host') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>

                                    <div class="col-span-3 sm:col-span-1">
                                        <label for="imap_port" class="block text-sm font-medium text-text-primary">Port</label>
                                        <select wire:model="imap_port" id="imap_port" class="mt-1 block w-full rounded-md border-border-default shadow-sm focus:border-brand focus:ring-brand sm:text-sm">
                                            <option value="993">993</option>
                                            <option value="143">143</option>
                                        </select>
                                    </div>

                                    <div class="col-span-3 sm:col-span-2">
                                        <label for="imap_encryption" class="block text-sm font-medium text-text-primary">Encryption</label>
                                        <select wire:model="imap_encryption" id="imap_encryption" class="mt-1 block w-full rounded-md border-border-default shadow-sm focus:border-brand focus:ring-brand sm:text-sm">
                                            <option value="ssl">SSL</option>
                                            <option value="tls">TLS</option>
                                        </select>
                                    </div>

                                    <div class="col-span-6 sm:col-span-3">
                                        <label for="imap_username" class="block text-sm font-medium text-text-primary">Username</label>
                                        <input type="text" wire:model="imap_username" id="imap_username" class="mt-1 block w-full rounded-md border-border-default shadow-sm focus:border-brand focus:ring-brand sm:text-sm">
                                        @error('imap_username') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>

                                    <div class="col-span-6 sm:col-span-3">
                                        <label for="imap_password" class="block text-sm font-medium text-text-primary">Password {{ $mailbox ? '(leave blank to keep current)' : '' }}</label>
                                        <input type="password" wire:model="imap_password" id="imap_password" class="mt-1 block w-full rounded-md border-border-default shadow-sm focus:border-brand focus:ring-brand sm:text-sm">
                                        @error('imap_password') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Test Connection -->
                            <div class="border-t border-border-default pt-6">
                                <button type="button" wire:click="validateCredentials" wire:loading.attr="disabled" class="inline-flex items-center px-4 py-2 border border-border-default rounded-md shadow-sm text-sm font-medium text-text-primary bg-bg-elevated hover:bg-bg-surface focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand">
                                    <span wire:loading.remove wire:target="validateCredentials">Test Connection</span>
                                    <span wire:loading wire:target="validateCredentials">Testing...</span>
                                </button>

                                @if($validationResult)
                                    <div class="mt-4 p-4 rounded-md {{ $validationResult['success'] ? 'bg-success-bg' : 'bg-red-50' }}">
                                        <div class="flex">
                                            <div class="flex-shrink-0">
                                                @if($validationResult['success'])
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
                                                <h3 class="text-sm font-medium {{ $validationResult['success'] ? 'text-green-800' : 'text-red-800' }}">
                                                    {{ $validationResult['success'] ? 'Connection successful!' : 'Connection failed' }}
                                                </h3>
                                                <div class="mt-2 text-sm {{ $validationResult['success'] ? 'text-green-700' : 'text-red-700' }}">
                                                    <p>SMTP: {{ $validationResult['smtp']['message'] ?? 'Not tested' }}</p>
                                                    @if($validationResult['imap'])
                                                        <p>IMAP: {{ $validationResult['imap']['message'] }}</p>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <!-- Sending Settings -->
                            <div class="border-t border-border-default pt-6">
                                <h4 class="text-sm font-medium text-text-primary mb-4">Sending Settings</h4>
                                <div class="grid grid-cols-6 gap-6">
                                    <div class="col-span-6 sm:col-span-2">
                                        <label for="daily_limit" class="block text-sm font-medium text-text-primary">Daily Limit</label>
                                        <input type="number" wire:model="daily_limit" id="daily_limit" min="1" max="500" class="mt-1 block w-full rounded-md border-border-default shadow-sm focus:border-brand focus:ring-brand sm:text-sm">
                                        @error('daily_limit') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>

                                    <div class="col-span-3 sm:col-span-2">
                                        <label for="send_window_start" class="block text-sm font-medium text-text-primary">Send Window Start</label>
                                        <input type="time" wire:model="send_window_start" id="send_window_start" class="mt-1 block w-full rounded-md border-border-default shadow-sm focus:border-brand focus:ring-brand sm:text-sm">
                                        @error('send_window_start') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>

                                    <div class="col-span-3 sm:col-span-2">
                                        <label for="send_window_end" class="block text-sm font-medium text-text-primary">Send Window End</label>
                                        <input type="time" wire:model="send_window_end" id="send_window_end" class="mt-1 block w-full rounded-md border-border-default shadow-sm focus:border-brand focus:ring-brand sm:text-sm">
                                        @error('send_window_end') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>

                                    <div class="col-span-6 sm:col-span-3">
                                        <label for="timezone" class="block text-sm font-medium text-text-primary">Timezone</label>
                                        <select wire:model="timezone" id="timezone" class="mt-1 block w-full rounded-md border-border-default shadow-sm focus:border-brand focus:ring-brand sm:text-sm">
                                            @foreach(timezone_identifiers_list() as $tz)
                                                <option value="{{ $tz }}">{{ $tz }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-span-6 sm:col-span-3">
                                        <div class="flex items-start">
                                            <div class="flex items-center h-5">
                                                <input type="checkbox" wire:model="skip_weekends" id="skip_weekends" class="focus:ring-brand h-4 w-4 text-brand border-border-default rounded">
                                            </div>
                                            <div class="ml-3 text-sm">
                                                <label for="skip_weekends" class="font-medium text-text-primary">Skip Weekends</label>
                                                <p class="text-text-secondary">Don't send emails on Saturday and Sunday</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-span-6">
                                        <div class="flex items-start">
                                            <div class="flex items-center h-5">
                                                <input type="checkbox" wire:model="warmup_enabled" id="warmup_enabled" class="focus:ring-brand h-4 w-4 text-brand border-border-default rounded">
                                            </div>
                                            <div class="ml-3 text-sm">
                                                <label for="warmup_enabled" class="font-medium text-text-primary">Enable Warm-up</label>
                                                <p class="text-text-secondary">Gradually increase sending volume over 2 weeks to build reputation</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="px-4 py-3 bg-bg-base text-right sm:px-6 space-x-3">
                            <a href="{{ route('mailboxes.index') }}" class="inline-flex justify-center py-2 px-4 border border-border-default shadow-sm text-sm font-medium rounded-md text-text-primary bg-bg-elevated hover:bg-bg-surface focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand">
                                Cancel
                            </a>
                            <button type="submit" wire:loading.attr="disabled" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-brand hover:bg-brand-hover focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand">
                                <span wire:loading.remove wire:target="save">{{ $mailbox ? 'Update Mailbox' : 'Create Mailbox' }}</span>
                                <span wire:loading wire:target="save">Saving...</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
