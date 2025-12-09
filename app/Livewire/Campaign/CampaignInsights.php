<?php

declare(strict_types=1);

namespace App\Livewire\Campaign;

use App\Models\Campaign;
use App\Services\CampaignService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Component;

class CampaignInsights extends Component
{
    public Campaign $campaign;

    public array $metrics = [];

    public array $decisionScore = [];

    public Collection $patterns;

    public Collection $pinnedQuotes;

    public function mount(Campaign $campaign): void
    {
        $this->campaign = $campaign;
        $this->patterns = collect();
        $this->pinnedQuotes = collect();
        $this->loadInsights();
    }

    public function loadInsights(): void
    {
        $service = app(CampaignService::class);
        $this->metrics = $service->getMetrics($this->campaign);
        $this->decisionScore = $service->getDecisionScore($this->campaign);
        $this->loadPatterns();
        $this->loadPinnedQuotes();
    }

    protected function loadPatterns(): void
    {
        $this->patterns = $this->campaign->insights()
            ->where('insight_type', 'pattern')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();
    }

    protected function loadPinnedQuotes(): void
    {
        $this->pinnedQuotes = $this->campaign->insights()
            ->where('insight_type', 'quote')
            ->where('is_pinned', true)
            ->orderByDesc('created_at')
            ->get();
    }

    public function refresh(): void
    {
        $this->loadInsights();
        $this->dispatch('insights-regenerated', campaign: $this->campaign);
    }

    public function pinQuote(int $insightId): void
    {
        $insight = $this->campaign->insights()->find($insightId);
        if ($insight) {
            $insight->update(['is_pinned' => true]);
            $this->loadPinnedQuotes();
            $this->dispatch('quote-pinned', insightId: $insightId);
        }
    }

    public function unpinQuote(int $insightId): void
    {
        $insight = $this->campaign->insights()->find($insightId);
        if ($insight) {
            $insight->update(['is_pinned' => false]);
            $this->loadPinnedQuotes();
            $this->dispatch('quote-unpinned', insightId: $insightId);
        }
    }

    public function triggerReanalysis(): void
    {
        // This will be implemented when AI analysis service is ready
        // For now, just refresh the insights
        $this->refresh();
    }

    public function render(): View
    {
        return view('livewire.campaign.campaign-insights');
    }
}
