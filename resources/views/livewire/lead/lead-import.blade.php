<div>
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-text-primary">Import Leads</h2>
        <p class="mt-1 text-sm text-text-secondary">{{ $campaign->name }}</p>
    </div>

    <!-- Progress Steps -->
    <div class="mb-8">
        <div class="flex items-center justify-center">
            @foreach ([1 => 'Upload', 2 => 'Map Columns', 3 => 'Preview', 4 => 'Import'] as $stepNum => $stepName)
                <div class="flex items-center">
                    <div class="flex items-center justify-center w-8 h-8 rounded-full {{ $step >= $stepNum ? 'bg-brand text-white' : 'bg-bg-surface text-text-secondary' }}">
                        {{ $stepNum }}
                    </div>
                    <span class="ml-2 text-sm {{ $step >= $stepNum ? 'text-brand font-medium' : 'text-text-secondary' }}">{{ $stepName }}</span>
                    @if ($stepNum < 4)
                        <div class="w-16 h-1 mx-4 {{ $step > $stepNum ? 'bg-brand' : 'bg-bg-surface' }}"></div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    <div class="bg-bg-elevated shadow rounded-lg p-6">
        @if ($step === 1)
            <!-- Step 1: Upload -->
            <div class="max-w-lg mx-auto">
                <div class="border-2 border-dashed border-border-default rounded-lg p-8 text-center">
                    <svg class="mx-auto h-12 w-12 text-text-muted" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    <div class="mt-4">
                        <label for="file" class="cursor-pointer">
                            <span class="text-brand hover:text-brand-hover font-medium">Upload a CSV file</span>
                            <span class="text-text-secondary"> or drag and drop</span>
                            <input type="file" wire:model="file" id="file" class="sr-only" accept=".csv,.txt">
                        </label>
                    </div>
                    <p class="mt-2 text-xs text-text-secondary">CSV up to 10MB</p>
                </div>

                <div wire:loading wire:target="file" class="mt-4 text-center">
                    <svg class="animate-spin h-5 w-5 text-brand mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <p class="mt-2 text-sm text-text-secondary">Analyzing file...</p>
                </div>

                @error('file') <p class="mt-2 text-sm text-red-600 text-center">{{ $message }}</p> @enderror
            </div>

        @elseif ($step === 2)
            <!-- Step 2: Map Columns -->
            <div>
                <p class="mb-4 text-sm text-text-secondary">Map your CSV columns to lead fields. Email is required.</p>
                <p class="mb-6 text-sm text-text-secondary">Found {{ $totalRows }} rows in your file.</p>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 max-w-2xl">
                    @foreach ($mappableFields as $field => $label)
                        <div>
                            <label class="block text-sm font-medium text-text-primary">
                                {{ $label }}
                                @if ($field === 'email') <span class="text-red-500">*</span> @endif
                            </label>
                            <select wire:model="columnMapping.{{ $field }}"
                                class="mt-1 block w-full rounded-md border-border-default shadow-sm focus:border-brand focus:ring-brand sm:text-sm">
                                <option value="">-- Select column --</option>
                                @foreach ($headers as $header)
                                    <option value="{{ $header }}">{{ $header }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endforeach
                </div>

                @error('columnMapping.email') <p class="mt-4 text-sm text-red-600">{{ $message }}</p> @enderror

                <div class="mt-8 flex justify-between">
                    <button wire:click="resetImport" class="px-4 py-2 border border-border-default rounded-md text-sm font-medium text-text-primary hover:bg-bg-surface">
                        Start Over
                    </button>
                    <button wire:click="mapColumns" class="px-4 py-2 bg-brand border border-transparent rounded-md text-sm font-medium text-white hover:bg-brand-hover">
                        Preview Import
                    </button>
                </div>
            </div>

        @elseif ($step === 3)
            <!-- Step 3: Preview -->
            <div>
                <p class="mb-4 text-sm text-text-secondary">Preview how your data will be imported. Review the first 10 rows below.</p>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-border-default">
                        <thead class="bg-bg-base">
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-medium text-text-secondary uppercase">Status</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-text-secondary uppercase">Email</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-text-secondary uppercase">Name</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-text-secondary uppercase">Company</th>
                            </tr>
                        </thead>
                        <tbody class="bg-bg-elevated divide-y divide-border-default">
                            @foreach ($previewData as $row)
                                <tr class="{{ $row['valid'] ? '' : 'bg-red-50' }}">
                                    <td class="px-3 py-2">
                                        @if ($row['valid'])
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-success-bg text-success">Valid</span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-error-bg text-error" title="{{ json_encode($row['errors']) }}">Invalid</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-2 text-sm text-text-primary">{{ $row['data']['email'] ?? '-' }}</td>
                                    <td class="px-3 py-2 text-sm text-text-secondary">{{ trim(($row['data']['first_name'] ?? '') . ' ' . ($row['data']['last_name'] ?? '')) ?: '-' }}</td>
                                    <td class="px-3 py-2 text-sm text-text-secondary">{{ $row['data']['company'] ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-4 p-4 bg-bg-base rounded-md">
                    <p class="text-sm text-text-secondary">
                        <strong>{{ $totalRows }}</strong> total rows will be processed.
                        Duplicate emails (same email in this campaign) will be updated instead of creating new leads.
                    </p>
                </div>

                <div class="mt-8 flex justify-between">
                    <button wire:click="goBack" class="px-4 py-2 border border-border-default rounded-md text-sm font-medium text-text-primary hover:bg-bg-surface">
                        Back
                    </button>
                    <button wire:click="startImport" class="px-4 py-2 bg-brand border border-transparent rounded-md text-sm font-medium text-white hover:bg-brand-hover">
                        Start Import
                    </button>
                </div>
            </div>

        @elseif ($step === 4)
            <!-- Step 4: Importing -->
            <div class="text-center py-8">
                @if ($importProgress < 100)
                    <svg class="animate-spin h-12 w-12 text-brand mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <p class="mt-4 text-lg font-medium text-text-primary">Importing leads...</p>
                    <div class="mt-4 max-w-xs mx-auto">
                        <div class="w-full bg-bg-surface rounded-full h-2">
                            <div class="bg-brand h-2 rounded-full transition-all duration-500" style="width: {{ $importProgress }}%"></div>
                        </div>
                        <p class="mt-2 text-sm text-text-secondary">{{ $importProgress }}% complete</p>
                    </div>
                    @if ($batchId)
                        <div wire:poll.2s="checkProgress"></div>
                    @endif
                @else
                    <svg class="h-12 w-12 text-green-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="mt-4 text-lg font-medium text-text-primary">Import Complete!</p>
                    @if ($importResult)
                        <p class="mt-2 text-sm text-text-secondary">
                            Successfully imported <strong>{{ $importResult['imported'] }}</strong> leads.
                            @if (($importResult['skipped'] ?? 0) > 0)
                                <br>{{ $importResult['skipped'] }} rows skipped due to validation errors.
                            @endif
                        </p>
                    @endif

                    @if (count($importErrors) > 0)
                        <div class="mt-4 text-left max-w-lg mx-auto">
                            <p class="text-sm font-medium text-red-600">Errors (showing first {{ count($importErrors) }}):</p>
                            <ul class="mt-2 text-sm text-red-500 list-disc list-inside">
                                @foreach (array_slice($importErrors, 0, 5) as $error)
                                    <li>Row {{ $error['row'] }}: {{ json_encode($error['errors']) }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="mt-8 flex justify-center gap-4">
                        <button wire:click="resetImport" class="px-4 py-2 border border-border-default rounded-md text-sm font-medium text-text-primary hover:bg-bg-surface">
                            Import More
                        </button>
                        <a href="{{ route('campaigns.leads.index', $campaign) }}" class="px-4 py-2 bg-brand border border-transparent rounded-md text-sm font-medium text-white hover:bg-brand-hover">
                            View Leads
                        </a>
                    </div>
                @endif
            </div>
        @endif
    </div>
</div>
