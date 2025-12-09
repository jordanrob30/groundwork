<div>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-text-primary">User Management</h2>
    </x-slot>

    <!-- Flash Messages -->
    @if (session()->has('message'))
        <div class="mb-4 p-4 bg-success/10 border border-success rounded-lg text-success">
            {{ session('message') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="mb-4 p-4 bg-error/10 border border-error rounded-lg text-error">
            {{ session('error') }}
        </div>
    @endif

    <!-- Filters -->
    <div class="mb-6 flex flex-col sm:flex-row gap-4">
        <!-- Search -->
        <div class="flex-1">
            <input
                type="text"
                wire:model.live.debounce.300ms="search"
                placeholder="Search by name or email..."
                class="w-full px-4 py-2 bg-bg-surface border border-border-default rounded-lg text-text-primary placeholder-text-muted focus:outline-none focus:ring-2 focus:ring-brand focus:border-transparent"
            >
        </div>

        <!-- Role Filter -->
        <div class="sm:w-48">
            <select
                wire:model.live="roleFilter"
                class="w-full px-4 py-2 bg-bg-surface border border-border-default rounded-lg text-text-primary focus:outline-none focus:ring-2 focus:ring-brand focus:border-transparent"
            >
                <option value="">All Roles</option>
                <option value="admin">Admin</option>
                <option value="user">Standard User</option>
            </select>
        </div>
    </div>

    <!-- Users Table -->
    <div class="bg-bg-elevated rounded-lg border border-border-default overflow-hidden">
        <table class="min-w-full divide-y divide-border-default">
            <thead class="bg-bg-surface">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-text-secondary uppercase tracking-wider">
                        Name
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-text-secondary uppercase tracking-wider">
                        Email
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-text-secondary uppercase tracking-wider">
                        Role
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-text-secondary uppercase tracking-wider">
                        Created
                    </th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-text-secondary uppercase tracking-wider">
                        Actions
                    </th>
                </tr>
            </thead>
            <tbody class="bg-bg-elevated divide-y divide-border-default">
                @forelse ($this->users as $user)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-text-primary">{{ $user->name }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-text-secondary">{{ $user->email }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <select
                                wire:change="updateRole({{ $user->id }}, $event.target.value)"
                                class="text-sm bg-bg-surface border border-border-default rounded px-2 py-1 text-text-primary focus:outline-none focus:ring-2 focus:ring-brand"
                            >
                                <option value="user" @selected($user->role === 'user')>Standard User</option>
                                <option value="admin" @selected($user->role === 'admin')>Admin</option>
                            </select>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-text-secondary">
                            {{ $user->created_at->format('M j, Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                            @if ($user->id !== auth()->id())
                                <button
                                    wire:click="impersonate({{ $user->id }})"
                                    class="text-brand hover:text-brand-hover font-medium"
                                >
                                    Emulate
                                </button>
                            @else
                                <span class="text-text-muted">Current User</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-text-muted">
                            No users found matching your criteria.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if ($this->users->hasPages())
        <div class="mt-4">
            {{ $this->users->links() }}
        </div>
    @endif
</div>
