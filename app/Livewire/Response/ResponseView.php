<?php

declare(strict_types=1);

namespace App\Livewire\Response;

use App\Models\Lead;
use App\Models\Response;
use App\Models\SentEmail;
use App\Services\AiAnalysisService;
use App\Services\ReplyDetectionService;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class ResponseView extends Component
{
    public Response $response;

    public Lead $lead;

    public SentEmail $originalEmail;

    public array $conversationThread = [];

    public ?string $interest_level;

    public ?string $problem_confirmation;

    public ?int $pain_severity;

    public ?string $current_solution;

    public ?bool $call_interest;

    public array $key_quotes;

    public ?string $summary;

    public function mount(Response $response): void
    {
        $this->response = $response;
        $this->lead = $response->lead;
        $this->originalEmail = $response->sentEmail;

        $this->interest_level = $response->interest_level;
        $this->problem_confirmation = $response->problem_confirmation;
        $this->pain_severity = $response->pain_severity;
        $this->current_solution = $response->current_solution;
        $this->call_interest = $response->call_interest;
        $this->key_quotes = $response->key_quotes ?? [];
        $this->summary = $response->summary;

        $this->loadConversationThread();

        if ($response->review_status === Response::REVIEW_UNREVIEWED) {
            $response->markAsReviewed();
        }
    }

    protected function loadConversationThread(): void
    {
        $service = app(ReplyDetectionService::class);
        $this->conversationThread = $service->getConversationThread($this->response);
    }

    public function saveAnalysisOverride(): void
    {
        $this->response->update([
            'interest_level' => $this->interest_level,
            'problem_confirmation' => $this->problem_confirmation,
            'pain_severity' => $this->pain_severity,
            'current_solution' => $this->current_solution,
            'call_interest' => $this->call_interest,
            'key_quotes' => $this->key_quotes,
            'summary' => $this->summary,
        ]);

        $this->dispatch('analysis-overridden', responseId: $this->response->id);
        session()->flash('message', 'Analysis updated successfully.');
    }

    public function reanalyze(): void
    {
        $service = app(AiAnalysisService::class);
        $service->queueAnalysis($this->response);

        $this->dispatch('analysis-requested', responseId: $this->response->id);
        session()->flash('message', 'Re-analysis queued. Results will appear shortly.');
    }

    public function bookCall(): void
    {
        $this->dispatch('open-call-booking', responseId: $this->response->id, leadId: $this->lead->id);
    }

    public function reply(): void
    {
        $this->dispatch('open-reply-composer', responseId: $this->response->id);
    }

    public function render(): View
    {
        return view('livewire.response.response-view');
    }
}
