<div>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Email Sequence</h2>
            <p class="mt-1 text-sm text-gray-600">{{ $campaign->name }}</p>
        </div>
        <div class="flex space-x-2">
            @if ($libraryTemplates->isNotEmpty())
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" type="button"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        Add from Library
                        <svg class="ml-2 -mr-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="open" @click.outside="open = false" x-cloak
                        class="absolute right-0 mt-2 w-64 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-10">
                        <div class="py-1" role="menu">
                            @foreach ($libraryTemplates as $libTemplate)
                                <button wire:click="addFromLibrary({{ $libTemplate->id }})" @click="open = false"
                                    class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    {{ $libTemplate->name }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
            <a href="{{ route('campaigns.templates.create', $campaign) }}"
                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700">
                Create Template
            </a>
        </div>
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

    <!-- Sequence Timeline -->
    @if ($templates->isEmpty())
        <div class="bg-white shadow rounded-lg p-8 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
            </svg>
            <h3 class="mt-4 text-lg font-medium text-gray-900">No email templates yet</h3>
            <p class="mt-2 text-sm text-gray-500">Create your first email template to build your outreach sequence.</p>
            <a href="{{ route('campaigns.templates.create', $campaign) }}"
                class="mt-4 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700">
                Create First Template
            </a>
        </div>
    @else
        <div class="space-y-4" wire:sortable="reorder">
            @foreach ($templates as $template)
                <div wire:sortable.item="{{ $template->id }}" wire:key="template-{{ $template->id }}"
                    class="bg-white shadow rounded-lg overflow-hidden">
                    <div class="flex items-stretch">
                        <!-- Drag Handle & Order -->
                        <div wire:sortable.handle class="flex-shrink-0 w-16 bg-gray-50 flex flex-col items-center justify-center cursor-move border-r">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16" />
                            </svg>
                            <span class="mt-1 text-lg font-bold text-gray-600">{{ $template->sequence_order }}</span>
                        </div>

                        <!-- Template Details -->
                        <div class="flex-1 p-4">
                            <div class="flex items-start justify-between">
                                <div>
                                    <h4 class="text-lg font-medium text-gray-900">{{ $template->name }}</h4>
                                    <p class="mt-1 text-sm text-gray-600">{{ Str::limit($template->subject, 60) }}</p>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <a href="{{ route('campaigns.templates.edit', [$campaign, $template]) }}"
                                        class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">Edit</a>
                                    <button wire:click="duplicate({{ $template->id }})"
                                        class="text-gray-600 hover:text-gray-900 text-sm font-medium">Duplicate</button>
                                    <button wire:click="remove({{ $template->id }})" wire:confirm="Remove this template from the sequence?"
                                        class="text-red-600 hover:text-red-900 text-sm font-medium">Remove</button>
                                </div>
                            </div>

                            <div class="mt-3 flex items-center text-sm text-gray-500">
                                @if ($template->sequence_order > 1)
                                    <div class="flex items-center">
                                        <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <span>
                                            Send after
                                            <input type="number" wire:change="updateDelay({{ $template->id }}, $event.target.value)"
                                                value="{{ $template->delay_days }}"
                                                min="0" max="30"
                                                class="w-12 mx-1 px-1 py-0.5 text-center border-gray-300 rounded text-sm">
                                            {{ $template->delay_type === 'business' ? 'business' : 'calendar' }} days
                                        </span>
                                    </div>
                                @else
                                    <span class="text-green-600 font-medium">Initial email (sent immediately)</span>
                                @endif

                                @php
                                    $variables = $template->detected_variables;
                                @endphp
                                @if (count($variables) > 0)
                                    <span class="mx-3 text-gray-300">|</span>
                                    <span>Variables: {{ implode(', ', array_keys($variables)) }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Sequence Summary -->
        <div class="mt-6 bg-blue-50 rounded-lg p-4">
            <h4 class="text-sm font-medium text-blue-900">Sequence Summary</h4>
            <div class="mt-2 text-sm text-blue-700">
                <p>{{ $templates->count() }} email{{ $templates->count() > 1 ? 's' : '' }} in sequence</p>
                @if ($templates->count() > 1)
                    @php
                        $totalDays = $templates->skip(1)->sum('delay_days');
                        $allBusiness = $templates->every(fn($t) => $t->delay_type === 'business');
                    @endphp
                    <p class="mt-1">
                        Full sequence takes approximately {{ $totalDays }} {{ $allBusiness ? 'business' : '' }} days to complete
                    </p>
                @endif
            </div>
        </div>
    @endif

    <div class="mt-6 flex justify-start">
        <a href="{{ route('campaigns.edit', $campaign) }}"
            class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
            &larr; Back to Campaign
        </a>
    </div>
</div>
