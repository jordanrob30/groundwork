<div>
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-900">
            {{ $lead ? 'Edit Lead' : 'Add Lead' }}
        </h2>
        <p class="mt-1 text-sm text-gray-600">{{ $campaign->name }}</p>
    </div>

    @if (session()->has('message'))
        <div class="mb-4 rounded-md bg-green-50 p-4">
            <p class="text-sm font-medium text-green-800">{{ session('message') }}</p>
        </div>
    @endif

    <form wire:submit="save" class="space-y-6">
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Contact Information</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Email -->
                <div class="md:col-span-2">
                    <label for="email" class="block text-sm font-medium text-gray-700">Email Address <span class="text-red-500">*</span></label>
                    <input type="email" wire:model="email" id="email"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        placeholder="contact@company.com">
                    @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <!-- First Name -->
                <div>
                    <label for="first_name" class="block text-sm font-medium text-gray-700">First Name</label>
                    <input type="text" wire:model="first_name" id="first_name"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        placeholder="John">
                    @error('first_name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <!-- Last Name -->
                <div>
                    <label for="last_name" class="block text-sm font-medium text-gray-700">Last Name</label>
                    <input type="text" wire:model="last_name" id="last_name"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        placeholder="Smith">
                    @error('last_name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <!-- Company -->
                <div>
                    <label for="company" class="block text-sm font-medium text-gray-700">Company</label>
                    <input type="text" wire:model="company" id="company"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        placeholder="Acme Inc.">
                    @error('company') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <!-- Role -->
                <div>
                    <label for="role" class="block text-sm font-medium text-gray-700">Role / Title</label>
                    <input type="text" wire:model="role" id="role"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        placeholder="CEO">
                    @error('role') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <!-- LinkedIn URL -->
                <div class="md:col-span-2">
                    <label for="linkedin_url" class="block text-sm font-medium text-gray-700">LinkedIn URL</label>
                    <input type="url" wire:model="linkedin_url" id="linkedin_url"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        placeholder="https://linkedin.com/in/johnsmith">
                    @error('linkedin_url') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <!-- Custom Fields -->
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Custom Fields</h3>
            <p class="text-sm text-gray-500 mb-4">Use these fields to store additional data for personalization.</p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @for ($i = 1; $i <= 5; $i++)
                    <div>
                        <label for="custom_field_{{ $i }}" class="block text-sm font-medium text-gray-700">Custom Field {{ $i }}</label>
                        <input type="text" wire:model="custom_field_{{ $i }}" id="custom_field_{{ $i }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        @error('custom_field_' . $i) <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                @endfor
            </div>
        </div>

        <!-- Lead Status (for existing leads) -->
        @if ($lead)
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Lead Status</h3>
                <div class="flex items-center space-x-4">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                        @if ($lead->status === 'replied') bg-green-100 text-green-800
                        @elseif ($lead->status === 'contacted') bg-blue-100 text-blue-800
                        @elseif ($lead->status === 'bounced') bg-red-100 text-red-800
                        @elseif ($lead->status === 'unsubscribed') bg-gray-100 text-gray-800
                        @else bg-yellow-100 text-yellow-800
                        @endif">
                        {{ ucfirst(str_replace('_', ' ', $lead->status)) }}
                    </span>
                    <span class="text-sm text-gray-500">
                        Sequence step: {{ $lead->current_sequence_step }}
                    </span>
                    @if ($lead->last_contacted_at)
                        <span class="text-sm text-gray-500">
                            Last contacted: {{ $lead->last_contacted_at->diffForHumans() }}
                        </span>
                    @endif
                </div>
            </div>
        @endif

        <!-- Form Actions -->
        <div class="flex justify-end space-x-3">
            <a href="{{ route('campaigns.leads.index', $campaign) }}"
                class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Cancel
            </a>
            <button type="submit"
                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                {{ $lead ? 'Update Lead' : 'Create Lead' }}
            </button>
        </div>
    </form>
</div>
