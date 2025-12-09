<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Models\AdminAuditLog;
use Illuminate\Contracts\View\View;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class AuditLog extends Component
{
    use WithPagination;

    /**
     * Filter by action type.
     */
    #[Url]
    public string $actionFilter = '';

    /**
     * Reset pagination when filter changes.
     */
    public function updatedActionFilter(): void
    {
        $this->resetPage();
    }

    /**
     * Get the filtered and paginated audit logs.
     *
     * @return LengthAwarePaginator<AdminAuditLog>
     */
    #[Computed]
    public function logs(): LengthAwarePaginator
    {
        $query = AdminAuditLog::with(['admin', 'targetUser'])
            ->orderBy('created_at', 'desc');

        if ($this->actionFilter !== '') {
            $query->where('action', $this->actionFilter);
        }

        return $query->paginate(20);
    }

    /**
     * Get available action types for the filter dropdown.
     *
     * @return array<string, string>
     */
    #[Computed]
    public function actionTypes(): array
    {
        return [
            AdminAuditLog::ACTION_IMPERSONATE => 'Started Impersonation',
            AdminAuditLog::ACTION_STOP_IMPERSONATE => 'Stopped Impersonation',
            AdminAuditLog::ACTION_ROLE_CHANGE => 'Role Change',
        ];
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('livewire.admin.audit-log');
    }
}
