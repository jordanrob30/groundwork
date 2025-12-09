<div>
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-900">
            {{ $template ? 'Edit Template' : 'Create Template' }}
        </h2>
        <p class="mt-1 text-sm text-gray-600">{{ $campaign->name }}</p>
    </div>

    @if (session()->has('message'))
        <div class="mb-4 rounded-md bg-green-50 p-4">
            <p class="text-sm font-medium text-green-800">{{ session('message') }}</p>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Editor -->
        <div class="space-y-6">
            <form wire:submit="save" class="space-y-6">
                <div class="bg-white shadow rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Template Details</h3>

                    <!-- Name -->
                    <div class="mb-4">
                        <label for="name" class="block text-sm font-medium text-gray-700">Template Name</label>
                        <input type="text" wire:model="name" id="name"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            placeholder="Initial Outreach">
                        @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <!-- Sequence Settings -->
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label for="sequence_order" class="block text-sm font-medium text-gray-700">Sequence Position</label>
                            <input type="number" wire:model="sequence_order" id="sequence_order" min="1"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            @error('sequence_order') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="delay_days" class="block text-sm font-medium text-gray-700">Days After Previous</label>
                            <input type="number" wire:model="delay_days" id="delay_days" min="0" max="30"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            @error('delay_days') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="delay_type" class="block text-sm font-medium text-gray-700">Delay Type</label>
                        <select wire:model="delay_type" id="delay_type"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="business">Business Days</option>
                            <option value="calendar">Calendar Days</option>
                        </select>
                    </div>
                </div>

                <div class="bg-white shadow rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Email Content</h3>

                    <!-- Subject -->
                    <div class="mb-4">
                        <label for="subject" class="block text-sm font-medium text-gray-700">Subject Line</label>
                        <input type="text" wire:model.live.debounce.500ms="subject" id="subject"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            placeholder="Quick question about @{{company}}">
                        @error('subject') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <!-- Body -->
                    <div class="mb-4">
                        <label for="body" class="block text-sm font-medium text-gray-700">Email Body</label>
                        <textarea wire:model.live.debounce.500ms="body" id="body" rows="12"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm font-mono"
                            placeholder="Hi @{{first_name}},

I noticed @{{company}} is in the [industry] space..."></textarea>
                        @error('body') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <!-- Variable Buttons -->
                    <div class="border-t pt-4">
                        <p class="text-xs font-medium text-gray-500 uppercase mb-2">Insert Variable</p>
                        <div class="flex flex-wrap gap-2">
                            @foreach ($supportedVariables as $variable => $label)
                                <button type="button" wire:click="insertVariable('{{ $variable }}')"
                                    class="inline-flex items-center px-2.5 py-1.5 border border-gray-300 text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50">
                                    {{ $label }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex justify-between">
                    <a href="{{ route('campaigns.templates.index', $campaign) }}"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        Cancel
                    </a>
                    <div class="flex space-x-3">
                        <button type="button" wire:click="saveToLibrary"
                            class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            Save to Library
                        </button>
                        <button type="submit"
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700">
                            {{ $template ? 'Update Template' : 'Create Template' }}
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Preview -->
        <div class="lg:sticky lg:top-4 self-start">
            <div class="bg-white shadow rounded-lg p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Preview</h3>
                    @if ($previewLead)
                        <span class="text-xs text-gray-500">
                            Using: {{ $previewLead->full_name }} @ {{ $previewLead->company }}
                        </span>
                    @endif
                </div>

                <div class="border rounded-lg overflow-hidden">
                    <!-- Email Header -->
                    <div class="bg-gray-50 px-4 py-3 border-b">
                        <div class="text-sm">
                            <span class="font-medium text-gray-500">To:</span>
                            <span class="text-gray-900 ml-2">{{ $previewLead?->email ?? 'recipient@example.com' }}</span>
                        </div>
                        <div class="text-sm mt-1">
                            <span class="font-medium text-gray-500">Subject:</span>
                            <span class="text-gray-900 ml-2">{{ $previewSubject ?: '(No subject)' }}</span>
                        </div>
                    </div>

                    <!-- Email Body -->
                    <div class="px-4 py-4 bg-white">
                        <div class="prose prose-sm max-w-none text-gray-700 whitespace-pre-wrap">{{ $previewBody ?: '(No content)' }}</div>
                    </div>
                </div>

                <!-- Variable Highlight -->
                @php
                    $detected = (new \App\Models\EmailTemplate(['subject' => $subject, 'body' => $body]))->detected_variables;
                @endphp
                @if (count($detected) > 0)
                    <div class="mt-4 p-3 bg-blue-50 rounded-md">
                        <p class="text-xs font-medium text-blue-700 mb-1">Variables used in this template:</p>
                        <div class="flex flex-wrap gap-1">
                            @foreach ($detected as $var => $label)
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                    {{ $var }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@script
<script>
    $wire.on('insert-variable', ({ variable }) => {
        const textarea = document.getElementById('body');
        if (textarea) {
            const start = textarea.selectionStart;
            const end = textarea.selectionEnd;
            const text = textarea.value;
            textarea.value = text.substring(0, start) + variable + text.substring(end);
            textarea.selectionStart = textarea.selectionEnd = start + variable.length;
            textarea.focus();
            $wire.set('body', textarea.value);
        }
    });
</script>
@endscript
