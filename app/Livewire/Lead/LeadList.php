<?php

declare(strict_types=1);

namespace App\Livewire\Lead;

use App\Models\Campaign;
use App\Models\Lead;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Component;
use Livewire\WithPagination;

class LeadList extends Component
{
    use WithPagination;

    public Campaign $campaign;

    public string $search = '';

    public string $filterStatus = 'all';

    public array $selectedLeadIds = [];

    public int $perPage = 25;

    protected $queryString = [
        'search' => ['except' => ''],
        'filterStatus' => ['except' => 'all'],
    ];

    public function mount(Campaign $campaign): void
    {
        $this->campaign = $campaign;
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterStatus(): void
    {
        $this->resetPage();
    }

    public function setFilter(string $status): void
    {
        $this->filterStatus = $status;
        $this->resetPage();
    }

    public function selectAll(): void
    {
        $this->selectedLeadIds = $this->getFilteredLeadsQuery()
            ->pluck('id')
            ->map(fn ($id) => (string) $id)
            ->toArray();
    }

    public function deselectAll(): void
    {
        $this->selectedLeadIds = [];
    }

    public function bulkDelete(): void
    {
        if (empty($this->selectedLeadIds)) {
            return;
        }

        Lead::whereIn('id', $this->selectedLeadIds)
            ->where('campaign_id', $this->campaign->id)
            ->delete();

        $count = count($this->selectedLeadIds);
        $this->selectedLeadIds = [];
        $this->dispatch('leads-deleted', count: $count);
        session()->flash('message', "{$count} leads deleted successfully.");
    }

    public function bulkChangeStatus(string $status): void
    {
        if (empty($this->selectedLeadIds) || ! in_array($status, Lead::STATUSES)) {
            return;
        }

        Lead::whereIn('id', $this->selectedLeadIds)
            ->where('campaign_id', $this->campaign->id)
            ->update(['status' => $status]);

        $count = count($this->selectedLeadIds);
        $this->selectedLeadIds = [];
        $this->dispatch('leads-status-changed', count: $count, status: $status);
        session()->flash('message', "{$count} leads updated to {$status}.");
    }

    public function delete(int $leadId): void
    {
        $lead = Lead::where('id', $leadId)
            ->where('campaign_id', $this->campaign->id)
            ->first();

        if ($lead) {
            $lead->delete();
            session()->flash('message', 'Lead deleted successfully.');
        }
    }

    public function export()
    {
        $leads = $this->getFilteredLeadsQuery()->get();

        $filename = "leads-{$this->campaign->id}-".now()->format('Y-m-d-His').'.csv';

        return response()->streamDownload(function () use ($leads) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'Email', 'First Name', 'Last Name', 'Company', 'Role',
                'LinkedIn URL', 'Status', 'Sequence Step', 'Last Contacted', 'Replied At',
            ]);

            foreach ($leads as $lead) {
                fputcsv($handle, [
                    $lead->email,
                    $lead->first_name,
                    $lead->last_name,
                    $lead->company,
                    $lead->role,
                    $lead->linkedin_url,
                    $lead->status,
                    $lead->current_sequence_step,
                    $lead->last_contacted_at?->toDateTimeString(),
                    $lead->replied_at?->toDateTimeString(),
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    protected function getFilteredLeadsQuery()
    {
        $query = Lead::where('campaign_id', $this->campaign->id);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('email', 'like', "%{$this->search}%")
                    ->orWhere('first_name', 'like', "%{$this->search}%")
                    ->orWhere('last_name', 'like', "%{$this->search}%")
                    ->orWhere('company', 'like', "%{$this->search}%");
            });
        }

        if ($this->filterStatus !== 'all') {
            $query->where('status', $this->filterStatus);
        }

        return $query->orderBy('created_at', 'desc');
    }

    public function getStatusCounts(): Collection
    {
        return Lead::where('campaign_id', $this->campaign->id)
            ->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');
    }

    public function render(): View
    {
        return view('livewire.lead.lead-list', [
            'leads' => $this->getFilteredLeadsQuery()->paginate($this->perPage),
            'statusCounts' => $this->getStatusCounts(),
            'totalLeads' => Lead::where('campaign_id', $this->campaign->id)->count(),
        ]);
    }
}
