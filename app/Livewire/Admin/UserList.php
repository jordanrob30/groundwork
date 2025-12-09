<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Models\User;
use App\Services\AuditLogService;
use App\Traits\HandlesImpersonation;
use Illuminate\Contracts\View\View;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class UserList extends Component
{
    use HandlesImpersonation, WithPagination;

    /**
     * Search query for filtering users.
     */
    #[Url(as: 'q')]
    public string $search = '';

    /**
     * Role filter.
     */
    #[Url]
    public string $roleFilter = '';

    /**
     * Reset pagination when search changes.
     */
    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    /**
     * Reset pagination when role filter changes.
     */
    public function updatedRoleFilter(): void
    {
        $this->resetPage();
    }

    /**
     * Get the filtered and paginated users.
     *
     * @return LengthAwarePaginator<User>
     */
    #[Computed]
    public function users(): LengthAwarePaginator
    {
        $query = User::query()
            ->orderBy('created_at', 'desc');

        if ($this->search !== '') {
            $query->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('email', 'like', "%{$this->search}%");
            });
        }

        if ($this->roleFilter !== '') {
            $query->where('role', $this->roleFilter);
        }

        return $query->paginate(15);
    }

    /**
     * Update a user's role.
     */
    public function updateRole(int $userId, string $newRole): void
    {
        $user = User::findOrFail($userId);
        $oldRole = $user->role;

        // Validate role value
        if (! in_array($newRole, [User::ROLE_USER, User::ROLE_ADMIN], true)) {
            session()->flash('error', 'Invalid role specified.');

            return;
        }

        // Prevent removing the last admin
        if ($user->isAdmin() && $newRole === User::ROLE_USER) {
            $adminCount = User::where('role', User::ROLE_ADMIN)->count();
            if ($adminCount <= 1) {
                session()->flash('error', 'Cannot remove the last administrator.');

                return;
            }
        }

        // Update the role
        $user->update(['role' => $newRole]);

        // Log the change
        AuditLogService::logRoleChange($user, $oldRole, $newRole);

        session()->flash('message', "User role updated to {$newRole}.");
    }

    /**
     * Start impersonating a user.
     */
    public function impersonate(int $userId): void
    {
        $user = User::findOrFail($userId);

        // Cannot impersonate yourself
        if ($user->id === auth()->id()) {
            session()->flash('error', 'Cannot emulate yourself.');

            return;
        }

        $this->startImpersonation($user);

        $this->redirect(route('dashboard'), navigate: true);
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('livewire.admin.user-list');
    }
}
