<div>
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-900">
            {{ $campaign ? 'Edit Campaign' : 'Create Campaign' }}
        </h2>
        <p class="mt-1 text-sm text-gray-600">
            {{ $campaign ? 'Update your discovery campaign settings.' : 'Set up a new discovery campaign to validate your hypothesis.' }}
        </p>
    </div>

    @if (session()->has('message'))
        <div class="mb-4 rounded-md bg-green-50 p-4">
            <p class="text-sm font-medium text-green-800">{{ session('message') }}</p>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="mb-4 rounded-md bg-red-50 p-4">
            <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
        </div>
    @endif

    <form wire:submit="save" class="space-y-6">
        <div class="bg-white shadow rounded-lg p-6">
            <div class="grid grid-cols-1 gap-6">
                <!-- Campaign Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Campaign Name</label>
                    <input type="text" wire:model="name" id="name"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        placeholder="Q1 2025 SaaS Founders Discovery">
                    @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <!-- Mailbox Selection -->
                <div>
                    <label for="mailbox_id" class="block text-sm font-medium text-gray-700">Sending Mailbox</label>
                    <select wire:model="mailbox_id" id="mailbox_id"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="">Select a mailbox...</option>
                        @foreach ($mailboxes as $mailbox)
                            <option value="{{ $mailbox->id }}">{{ $mailbox->email }} ({{ $mailbox->name }})</option>
                        @endforeach
                    </select>
                    @error('mailbox_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    @if ($mailboxes->isEmpty())
                        <p class="mt-1 text-sm text-amber-600">
                            <a href="{{ route('mailboxes.create') }}" class="underline">Create a mailbox</a> first to send emails.
                        </p>
                    @endif
                </div>

                <!-- Industry -->
                <div>
                    <label for="industry" class="block text-sm font-medium text-gray-700">Target Industry</label>
                    <input type="text" wire:model="industry" id="industry"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        placeholder="e.g., B2B SaaS, Healthcare Tech, E-commerce">
                    @error('industry') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <!-- Hypothesis -->
                <div>
                    <label for="hypothesis" class="block text-sm font-medium text-gray-700">Hypothesis</label>
                    <textarea wire:model="hypothesis" id="hypothesis" rows="3"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        placeholder="We believe that [target persona] struggles with [problem] and would pay for [solution] because [reason]."></textarea>
                    <p class="mt-1 text-xs text-gray-500">Minimum 20 characters. Be specific about your assumption.</p>
                    @error('hypothesis') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <!-- Target Persona -->
                <div>
                    <label for="target_persona" class="block text-sm font-medium text-gray-700">Target Persona</label>
                    <textarea wire:model="target_persona" id="target_persona" rows="2"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        placeholder="e.g., Series A startup founders in fintech who have raised $2-10M"></textarea>
                    @error('target_persona') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <!-- Success Criteria -->
                <div>
                    <label for="success_criteria" class="block text-sm font-medium text-gray-700">Success Criteria</label>
                    <textarea wire:model="success_criteria" id="success_criteria" rows="2"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        placeholder="e.g., 10% reply rate with 5+ discovery calls booked"></textarea>
                    @error('success_criteria') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <!-- Campaign Setup (for existing campaigns) -->
        @if ($campaign)
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Campaign Setup</h3>
                <p class="text-sm text-gray-600 mb-4">Configure your email templates and import leads before activating the campaign.</p>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Templates Card -->
                    <a href="{{ route('campaigns.templates.index', $campaign) }}"
                        class="block p-4 border border-gray-200 rounded-lg hover:border-indigo-500 hover:bg-indigo-50 transition-colors">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10 bg-indigo-100 rounded-lg flex items-center justify-center">
                                    <svg class="h-6 w-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-gray-900">Email Templates</p>
                                    <p class="text-sm text-gray-500">{{ $campaign->templates()->count() }} template(s)</p>
                                </div>
                            </div>
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </div>
                    </a>

                    <!-- Leads Card -->
                    <a href="{{ route('campaigns.leads.index', $campaign) }}"
                        class="block p-4 border border-gray-200 rounded-lg hover:border-indigo-500 hover:bg-indigo-50 transition-colors">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10 bg-green-100 rounded-lg flex items-center justify-center">
                                    <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-gray-900">Leads</p>
                                    <p class="text-sm text-gray-500">{{ $campaign->leads()->count() }} lead(s)</p>
                                </div>
                            </div>
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Campaign Status -->
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Campaign Status</h3>
                <div class="flex items-center justify-between">
                    <div>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            @if ($campaign->status === 'active') bg-green-100 text-green-800
                            @elseif ($campaign->status === 'paused') bg-yellow-100 text-yellow-800
                            @elseif ($campaign->status === 'completed') bg-blue-100 text-blue-800
                            @else bg-gray-100 text-gray-800
                            @endif">
                            {{ ucfirst($campaign->status) }}
                        </span>
                        @if ($campaign->activated_at)
                            <span class="ml-2 text-sm text-gray-500">
                                Activated {{ $campaign->activated_at->diffForHumans() }}
                            </span>
                        @endif
                    </div>
                    <div class="flex space-x-2">
                        @if ($campaign->status === 'draft')
                            <button type="button" wire:click="activate"
                                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                                @if (!$campaign->canBeActivated()) disabled title="Add leads and templates first" @endif>
                                Activate Campaign
                            </button>
                        @elseif ($campaign->status === 'active')
                            <button type="button" wire:click="pause"
                                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-yellow-600 hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500">
                                Pause Campaign
                            </button>
                        @elseif ($campaign->status === 'paused')
                            <button type="button" wire:click="activate"
                                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                Resume Campaign
                            </button>
                        @endif
                    </div>
                </div>
                @if ($campaign->status === 'draft' && !$campaign->canBeActivated())
                    <p class="mt-2 text-sm text-amber-600">
                        To activate this campaign, you need at least one lead and one email template.
                    </p>
                @endif
            </div>
        @endif

        <!-- Form Actions -->
        <div class="flex justify-end space-x-3">
            <a href="{{ route('campaigns.index') }}"
                class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Cancel
            </a>
            <button type="submit"
                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                {{ $campaign ? 'Update Campaign' : 'Create Campaign' }}
            </button>
        </div>
    </form>
</div>
