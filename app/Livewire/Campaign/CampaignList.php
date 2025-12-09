<?php

declare(strict_types=1);

namespace App\Livewire\Campaign;

use App\Models\Campaign;
use App\Services\CampaignService;
use App\Traits\HandlesImpersonation;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Component;

class CampaignList extends Component
{
    use HandlesImpersonation;

    public Collection $campaigns;

    public string $filterStatus = 'all';

    public string $sortBy = 'updated_at';

    public string $sortDirection = 'desc';

    public function mount(): void
    {
        $this->loadCampaigns();
    }

    public function loadCampaigns(): void
    {
        $query = Campaign::forUser($this->getEffectiveUserId())
            ->with(['mailbox', 'leads', 'responses']);

        if ($this->filterStatus !== 'all') {
            $query->withStatus($this->filterStatus);
        } else {
            $query->notArchived();
        }

        $this->campaigns = $query
            ->orderBy($this->sortBy, $this->sortDirection)
            ->get();
    }

    public function setFilter(string $status): void
    {
        $this->filterStatus = $status;
        $this->loadCampaigns();
    }

    public function sort(string $column): void
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'desc';
        }
        $this->loadCampaigns();
    }

    public function duplicate(int $campaignId): void
    {
        $campaign = Campaign::findOrFail($campaignId);

        if ($campaign->user_id !== $this->getEffectiveUserId()) {
            return;
        }

        $newName = $campaign->name.' (Copy)';
        app(CampaignService::class)->duplicate($campaign, $newName);

        $this->dispatch('campaign-duplicated');
        $this->loadCampaigns();
    }

    public function archive(int $campaignId): void
    {
        $campaign = Campaign::findOrFail($campaignId);

        if ($campaign->user_id !== $this->getEffectiveUserId()) {
            return;
        }

        app(CampaignService::class)->archive($campaign);

        $this->dispatch('campaign-archived');
        $this->loadCampaigns();
    }

    public function delete(int $campaignId): void
    {
        $campaign = Campaign::findOrFail($campaignId);

        if ($campaign->user_id !== $this->getEffectiveUserId()) {
            return;
        }

        $campaign->delete();

        $this->dispatch('campaign-deleted');
        $this->loadCampaigns();
    }

    public function render(): View
    {
        return view('livewire.campaign.campaign-list');
    }
}
