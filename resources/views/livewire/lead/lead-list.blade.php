<div>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-text-primary">Leads</h2>
            <p class="mt-1 text-sm text-text-secondary">{{ $campaign->name }} &mdash; {{ $totalLeads }} total leads</p>
        </div>
        <div class="flex space-x-2">
            <a href="{{ route('campaigns.leads.import', $campaign) }}"
                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-brand hover:bg-brand-hover">
                Import CSV
            </a>
            <a href="{{ route('campaigns.leads.create', $campaign) }}"
                class="inline-flex items-center px-4 py-2 border border-border-default text-sm font-medium rounded-md text-text-primary bg-bg-elevated hover:bg-bg-surface">
                Add Lead
            </a>
            <button wire:click="export"
                class="inline-flex items-center px-4 py-2 border border-border-default text-sm font-medium rounded-md text-text-primary bg-bg-elevated hover:bg-bg-surface">
                Export
            </button>
        </div>
    </div>

    @if (session()->has('message'))
        <div class="mb-4 rounded-md bg-success-bg p-4">
            <p class="text-sm font-medium text-success">{{ session('message') }}</p>
        </div>
    @endif

    <!-- Filters -->
    <div class="mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex-1 max-w-md">
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search by email, name, or company..."
                class="block w-full rounded-md border-border-default shadow-sm focus:border-brand focus:ring-brand sm:text-sm">
        </div>

        <div class="flex flex-wrap gap-2">
            <button wire:click="setFilter('all')"
                class="px-3 py-1 text-sm rounded-full {{ $filterStatus === 'all' ? 'bg-brand text-white' : 'bg-bg-surface text-text-primary hover:bg-bg-surface' }}">
                All ({{ $totalLeads }})
            </button>
            @foreach ($statusCounts as $status => $count)
                <button wire:click="setFilter('{{ $status }}')"
                    class="px-3 py-1 text-sm rounded-full {{ $filterStatus === $status ? 'bg-brand text-white' : 'bg-bg-surface text-text-primary hover:bg-bg-surface' }}">
                    {{ ucfirst(str_replace('_', ' ', $status)) }} ({{ $count }})
                </button>
            @endforeach
        </div>
    </div>

    <!-- Bulk Actions -->
    @if (count($selectedLeadIds) > 0)
        <div class="mb-4 flex items-center gap-4 bg-bg-surface p-3 rounded-md">
            <span class="text-sm text-brand">{{ count($selectedLeadIds) }} selected</span>
            <button wire:click="deselectAll" class="text-sm text-brand hover:underline">Clear</button>
            <div class="flex gap-2">
                <select wire:change="bulkChangeStatus($event.target.value)" class="text-sm rounded-md border-border-default">
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
    <div class="bg-bg-elevated shadow rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-border-default">
            <thead class="bg-bg-base">
                <tr>
                    <th class="px-4 py-3 text-left">
                        <input type="checkbox" wire:click="selectAll"
                            {{ count($selectedLeadIds) === $totalLeads && $totalLeads > 0 ? 'checked' : '' }}
                            class="rounded border-border-default text-brand focus:ring-brand">
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-text-secondary uppercase">Contact</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-text-secondary uppercase">Company</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-text-secondary uppercase">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-text-secondary uppercase">Sequence</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-text-secondary uppercase">Last Contact</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-text-secondary uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-bg-elevated divide-y divide-border-default">
                @forelse ($leads as $lead)
                    <tr class="hover:bg-bg-surface">
                        <td class="px-4 py-3">
                            <input type="checkbox" wire:model.live="selectedLeadIds" value="{{ $lead->id }}"
                                class="rounded border-border-default text-brand focus:ring-brand">
                        </td>
                        <td class="px-4 py-3">
                            <div class="text-sm font-medium text-text-primary">{{ $lead->full_name }}</div>
                            <div class="text-sm text-text-secondary">{{ $lead->email }}</div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="text-sm text-text-primary">{{ $lead->company ?? '-' }}</div>
                            <div class="text-sm text-text-secondary">{{ $lead->role ?? '' }}</div>
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                @if ($lead->status === 'replied') bg-success-bg text-success
                                @elseif ($lead->status === 'contacted') bg-blue-100 text-blue-800
                                @elseif ($lead->status === 'bounced') bg-error-bg text-error
                                @elseif ($lead->status === 'unsubscribed') bg-bg-surface text-text-secondary
                                @else bg-warning-bg text-warning
                                @endif">
                                {{ ucfirst(str_replace('_', ' ', $lead->status)) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm text-text-secondary">
                            Step {{ $lead->current_sequence_step }}
                        </td>
                        <td class="px-4 py-3 text-sm text-text-secondary">
                            {{ $lead->last_contacted_at?->diffForHumans() ?? 'Never' }}
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('campaigns.leads.edit', [$campaign, $lead]) }}"
                                class="text-brand hover:text-brand-hover mr-3">Edit</a>
                            <button wire:click="delete({{ $lead->id }})" wire:confirm="Delete this lead?"
                                class="text-red-600 hover:text-red-900">Delete</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-text-secondary">
                            @if ($search || $filterStatus !== 'all')
                                No leads match your filters.
                            @else
                                No leads yet. <a href="{{ route('campaigns.leads.import', $campaign) }}" class="text-brand hover:underline">Import a CSV</a> to get started.
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
            class="inline-flex items-center px-4 py-2 border border-border-default shadow-sm text-sm font-medium rounded-md text-text-primary bg-bg-elevated hover:bg-bg-surface">
            &larr; Back to Campaign
        </a>
    </div>
</div>
