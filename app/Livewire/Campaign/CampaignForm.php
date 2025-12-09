<?php

declare(strict_types=1);

namespace App\Livewire\Campaign;

use App\Models\Campaign;
use App\Models\Mailbox;
use App\Services\CampaignService;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class CampaignForm extends Component
{
    public ?Campaign $campaign = null;

    public string $name = '';

    public ?int $mailbox_id = null;

    public string $industry = '';

    public string $hypothesis = '';

    public string $target_persona = '';

    public string $success_criteria = '';

    public function mount(?Campaign $campaign = null): void
    {
        if ($campaign && $campaign->exists) {
            $this->campaign = $campaign;
            $this->name = $campaign->name;
            $this->mailbox_id = $campaign->mailbox_id;
            $this->industry = $campaign->industry ?? '';
            $this->hypothesis = $campaign->hypothesis;
            $this->target_persona = $campaign->target_persona ?? '';
            $this->success_criteria = $campaign->success_criteria ?? '';
        }
    }

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'mailbox_id' => ['required', 'exists:mailboxes,id'],
            'hypothesis' => ['required', 'string', 'min:20'],
            'industry' => ['nullable', 'string', 'max:255'],
            'target_persona' => ['nullable', 'string'],
            'success_criteria' => ['nullable', 'string'],
        ];
    }

    public function save(): void
    {
        $this->validate();

        $service = app(CampaignService::class);

        $data = [
            'name' => $this->name,
            'mailbox_id' => $this->mailbox_id,
            'industry' => $this->industry ?: null,
            'hypothesis' => $this->hypothesis,
            'target_persona' => $this->target_persona ?: null,
            'success_criteria' => $this->success_criteria ?: null,
        ];

        if ($this->campaign) {
            $service->update($this->campaign, $data);
            session()->flash('message', 'Campaign updated successfully.');
        } else {
            $campaign = $service->create(auth()->user(), $data);
            session()->flash('message', 'Campaign created successfully.');
        }

        $this->redirect(route('campaigns.index'));
    }

    public function activate(): void
    {
        if (! $this->campaign) {
            return;
        }

        try {
            app(CampaignService::class)->activate($this->campaign);
            $this->campaign->refresh();
            session()->flash('message', 'Campaign activated successfully.');
        } catch (\DomainException $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function pause(): void
    {
        if (! $this->campaign) {
            return;
        }

        app(CampaignService::class)->pause($this->campaign);
        $this->campaign->refresh();
        session()->flash('message', 'Campaign paused successfully.');
    }

    public function render(): View
    {
        $mailboxes = Mailbox::forUser(auth()->id())->get();

        return view('livewire.campaign.campaign-form', [
            'mailboxes' => $mailboxes,
        ]);
    }
}
