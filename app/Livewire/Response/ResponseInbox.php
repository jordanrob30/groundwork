<?php

declare(strict_types=1);

namespace App\Livewire\Response;

use App\Models\Campaign;
use App\Models\Response;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Component;
use Livewire\WithPagination;

class ResponseInbox extends Component
{
    use WithPagination;

    public ?Campaign $campaign = null;

    public string $filterInterest = 'all';

    public string $filterStatus = 'unreviewed';

    public bool $showAutoReplies = false;

    public int $perPage = 20;

    protected $queryString = [
        'filterInterest' => ['except' => 'all'],
        'filterStatus' => ['except' => 'unreviewed'],
        'showAutoReplies' => ['except' => false],
    ];

    public function mount(?Campaign $campaign = null): void
    {
        $this->campaign = $campaign;
    }

    public function setInterestFilter(string $interest): void
    {
        $this->filterInterest = $interest;
        $this->resetPage();
    }

    public function setStatusFilter(string $status): void
    {
        $this->filterStatus = $status;
        $this->resetPage();
    }

    public function toggleAutoReplies(): void
    {
        $this->showAutoReplies = ! $this->showAutoReplies;
        $this->resetPage();
    }

    public function markReviewed(int $responseId): void
    {
        $response = $this->getResponseById($responseId);
        if ($response) {
            $response->markAsReviewed();
            $this->dispatch('response-reviewed', responseId: $responseId);
        }
    }

    public function markActioned(int $responseId): void
    {
        $response = $this->getResponseById($responseId);
        if ($response) {
            $response->markAsActioned();
            $this->dispatch('response-actioned', responseId: $responseId);
        }
    }

    public function bulkMarkReviewed(): void
    {
        $this->getFilteredQuery()
            ->where('review_status', Response::REVIEW_UNREVIEWED)
            ->update([
                'review_status' => Response::REVIEW_REVIEWED,
                'reviewed_at' => now(),
            ]);

        session()->flash('message', 'All visible responses marked as reviewed.');
    }

    protected function getResponseById(int $id): ?Response
    {
        $query = Response::where('id', $id);

        if ($this->campaign) {
            $query->where('campaign_id', $this->campaign->id);
        }

        return $query->first();
    }

    protected function getFilteredQuery()
    {
        $query = Response::with(['lead', 'campaign', 'sentEmail']);

        if ($this->campaign) {
            $query->where('campaign_id', $this->campaign->id);
        }

        if ($this->filterInterest !== 'all') {
            $query->where('interest_level', $this->filterInterest);
        }

        if ($this->filterStatus !== 'all') {
            $query->where('review_status', $this->filterStatus);
        }

        if (! $this->showAutoReplies) {
            $query->where('is_auto_reply', false);
        }

        return $query->orderByDesc('received_at');
    }

    public function getInterestCounts(): Collection
    {
        $query = Response::selectRaw('interest_level, count(*) as count');

        if ($this->campaign) {
            $query->where('campaign_id', $this->campaign->id);
        }

        if (! $this->showAutoReplies) {
            $query->where('is_auto_reply', false);
        }

        return $query->groupBy('interest_level')
            ->pluck('count', 'interest_level');
    }

    public function render(): View
    {
        return view('livewire.response.response-inbox', [
            'responses' => $this->getFilteredQuery()->paginate($this->perPage),
            'interestCounts' => $this->getInterestCounts(),
        ]);
    }
}
