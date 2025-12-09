<div>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Leads</h2>
            <p class="mt-1 text-sm text-gray-600">{{ $campaign->name }} &mdash; {{ $totalLeads }} total leads</p>
        </div>
        <div class="flex space-x-2">
            <a href="{{ route('campaigns.leads.import', $campaign) }}"
                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700">
                Import CSV
            </a>
            <a href="{{ route('campaigns.leads.create', $campaign) }}"
                class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                Add Lead
            </a>
            <button wire:click="export"
                class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                Export
            </button>
        </div>
    </div>

    @if (session()->has('message'))
        <div class="mb-4 rounded-md bg-green-50 p-4">
            <p class="text-sm font-medium text-green-800">{{ session('message') }}</p>
        </div>
    @endif

    <!-- Filters -->
    <div class="mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex-1 max-w-md">
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search by email, name, or company..."
                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
        </div>

        <div class="flex flex-wrap gap-2">
            <button wire:click="setFilter('all')"
                class="px-3 py-1 text-sm rounded-full {{ $filterStatus === 'all' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                All ({{ $totalLeads }})
            </button>
            @foreach ($statusCounts as $status => $count)
                <button wire:click="setFilter('{{ $status }}')"
                    class="px-3 py-1 text-sm rounded-full {{ $filterStatus === $status ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                    {{ ucfirst(str_replace('_', ' ', $status)) }} ({{ $count }})
                </button>
            @endforeach
        </div>
    </div>

    <!-- Bulk Actions -->
    @if (count($selectedLeadIds) > 0)
        <div class="mb-4 flex items-center gap-4 bg-indigo-50 p-3 rounded-md">
            <span class="text-sm text-indigo-700">{{ count($selectedLeadIds) }} selected</span>
            <button wire:click="deselectAll" class="text-sm text-indigo-600 hover:underline">Clear</button>
            <div class="flex gap-2">
                <select wire:change="bulkChangeStatus($event.target.value)" class="text-sm rounded-md border-gray-300">
                    <option value="">Change status...</option>
                    @foreach (\App\Models\Lead::STATUSES as $status)
                        <option value="{{ $status }}">{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                    @endforeach
                </select>
                <button wire:click="bulkDelete" wire:confirm="Are you sure you want to delete the selected leads?"
                    class="px-3 py-1 text-sm text-red-600 hover:text-red-800">
                    Delete Selected
                </button>
            </div>
        </div>
    @endif

    <!-- Leads Table -->
    <div class="bg-white shadow rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left">
                        <input type="checkbox" wire:click="selectAll"
                            {{ count($selectedLeadIds) === $totalLeads && $totalLeads > 0 ? 'checked' : '' }}
                            class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Contact</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Company</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Sequence</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Last Contact</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse ($leads as $lead)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <input type="checkbox" wire:model.live="selectedLeadIds" value="{{ $lead->id }}"
                                class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        </td>
                        <td class="px-4 py-3">
                            <div class="text-sm font-medium text-gray-900">{{ $lead->full_name }}</div>
                            <div class="text-sm text-gray-500">{{ $lead->email }}</div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="text-sm text-gray-900">{{ $lead->company ?? '-' }}</div>
                            <div class="text-sm text-gray-500">{{ $lead->role ?? '' }}</div>
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                @if ($lead->status === 'replied') bg-green-100 text-green-800
                                @elseif ($lead->status === 'contacted') bg-blue-100 text-blue-800
                                @elseif ($lead->status === 'bounced') bg-red-100 text-red-800
                                @elseif ($lead->status === 'unsubscribed') bg-gray-100 text-gray-800
                                @else bg-yellow-100 text-yellow-800
                                @endif">
                                {{ ucfirst(str_replace('_', ' ', $lead->status)) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-500">
                            Step {{ $lead->current_sequence_step }}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-500">
                            {{ $lead->last_contacted_at?->diffForHumans() ?? 'Never' }}
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('campaigns.leads.edit', [$campaign, $lead]) }}"
                                class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</a>
                            <button wire:click="delete({{ $lead->id }})" wire:confirm="Delete this lead?"
                                class="text-red-600 hover:text-red-900">Delete</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                            @if ($search || $filterStatus !== 'all')
                                No leads match your filters.
                            @else
                                No leads yet. <a href="{{ route('campaigns.leads.import', $campaign) }}" class="text-indigo-600 hover:underline">Import a CSV</a> to get started.
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-4">
        {{ $leads->links() }}
    </div>

    <div class="mt-6 flex justify-start">
        <a href="{{ route('campaigns.edit', $campaign) }}"
            class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
            &larr; Back to Campaign
        </a>
    </div>
</div>
