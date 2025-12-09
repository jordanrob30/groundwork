<div>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-text-primary">Audit Log</h2>
    </x-slot>

    <!-- Filter -->
    <div class="mb-6">
        <select
            wire:model.live="actionFilter"
            class="px-4 py-2 bg-bg-surface border border-border-default rounded-lg text-text-primary focus:outline-none focus:ring-2 focus:ring-brand focus:border-transparent"
        >
            <option value="">All Actions</option>
            @foreach ($this->actionTypes as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
            @endforeach
        </select>
    </div>

    <!-- Audit Log Table -->
    <div class="bg-bg-elevated rounded-lg border border-border-default overflow-hidden">
        <table class="min-w-full divide-y divide-border-default">
            <thead class="bg-bg-surface">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-text-secondary uppercase tracking-wider">
                        Date/Time
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-text-secondary uppercase tracking-wider">
                        Admin
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-text-secondary uppercase tracking-wider">
                        Action
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-text-secondary uppercase tracking-wider">
                        Target User
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-text-secondary uppercase tracking-wider">
                        Details
                    </th>
                </tr>
            </thead>
            <tbody class="bg-bg-elevated divide-y divide-border-default">
                @forelse ($this->logs as $log)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-text-secondary">
                            {{ $log->created_at->format('M j, Y g:i A') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-text-primary">{{ $log->admin->name }}</div>
                            <div class="text-sm text-text-muted">{{ $log->admin->email }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs font-medium rounded-full
                                @if ($log->action === 'user.impersonate')
                                    bg-warning/10 text-warning
                                @elseif ($log->action === 'user.stop_impersonate')
                                    bg-success/10 text-success
                                @elseif ($log->action === 'user.role_change')
                                    bg-brand/10 text-brand
                                @else
                                    bg-bg-surface text-text-secondary
                                @endif
                            ">
                                {{ $log->getActionDescription() }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if ($log->targetUser)
                                <div class="text-sm font-medium text-text-primary">{{ $log->targetUser->name }}</div>
                                <div class="text-sm text-text-muted">{{ $log->targetUser->email }}</div>
                            @else
                                <span class="text-text-muted">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-text-secondary">
                            @if ($log->changes)
                                @if ($log->action === 'user.role_change')
                                    {{ $log->changes['from'] ?? 'unknown' }} → {{ $log->changes['to'] ?? 'unknown' }}
                                @elseif ($log->action === 'user.stop_impersonate' && isset($log->changes['duration_seconds']))
                                    Duration: {{ gmdate('H:i:s', $log->changes['duration_seconds']) }}
                                @else
                                    <span class="text-text-muted">-</span>
                                @endif
                            @else
                                <span class="text-text-muted">-</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-text-muted">
                            No audit logs found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if ($this->logs->hasPages())
        <div class="mt-4">
            {{ $this->logs->links() }}
        </div>
    @endif
</div>
