<?php

declare(strict_types=1);

namespace App\Livewire\Template;

use App\Models\Campaign;
use App\Models\EmailTemplate;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Component;

class SequenceBuilder extends Component
{
    public Campaign $campaign;

    public Collection $templates;

    public ?int $editingTemplateId = null;

    public function mount(Campaign $campaign): void
    {
        $this->campaign = $campaign;
        $this->loadTemplates();
    }

    protected function loadTemplates(): void
    {
        $this->templates = $this->campaign->templates()
            ->orderBy('sequence_order')
            ->get();
    }

    public function reorder(array $order): void
    {
        foreach ($order as $index => $templateId) {
            EmailTemplate::where('id', $templateId)
                ->where('campaign_id', $this->campaign->id)
                ->update(['sequence_order' => $index + 1]);
        }

        $this->loadTemplates();
        $this->dispatch('sequence-updated');
    }

    public function addFromLibrary(int $templateId): void
    {
        $libraryTemplate = EmailTemplate::where('id', $templateId)
            ->where('user_id', auth()->id())
            ->where('is_library_template', true)
            ->first();

        if (! $libraryTemplate) {
            session()->flash('error', 'Library template not found.');

            return;
        }

        $newOrder = $this->templates->max('sequence_order') + 1 ?? 1;

        $newTemplate = $libraryTemplate->replicate();
        $newTemplate->campaign_id = $this->campaign->id;
        $newTemplate->sequence_order = $newOrder;
        $newTemplate->is_library_template = false;
        $newTemplate->save();

        $this->loadTemplates();
        $this->dispatch('template-added', templateId: $newTemplate->id);
        session()->flash('message', 'Template added to sequence.');
    }

    public function remove(int $templateId): void
    {
        $template = EmailTemplate::where('id', $templateId)
            ->where('campaign_id', $this->campaign->id)
            ->first();

        if ($template) {
            $template->delete();
            $this->resequence();
            $this->loadTemplates();
            $this->dispatch('template-removed', templateId: $templateId);
            session()->flash('message', 'Template removed from sequence.');
        }
    }

    public function updateDelay(int $templateId, int $days): void
    {
        EmailTemplate::where('id', $templateId)
            ->where('campaign_id', $this->campaign->id)
            ->update(['delay_days' => max(0, min(30, $days))]);

        $this->loadTemplates();
    }

    protected function resequence(): void
    {
        $templates = $this->campaign->templates()
            ->orderBy('sequence_order')
            ->get();

        foreach ($templates as $index => $template) {
            $template->update(['sequence_order' => $index + 1]);
        }
    }

    public function duplicate(int $templateId): void
    {
        $template = EmailTemplate::where('id', $templateId)
            ->where('campaign_id', $this->campaign->id)
            ->first();

        if ($template) {
            $newTemplate = $template->duplicate();
            $newTemplate->name = $template->name.' (Copy)';
            $newTemplate->sequence_order = $this->templates->max('sequence_order') + 1;
            $newTemplate->save();

            $this->loadTemplates();
            session()->flash('message', 'Template duplicated.');
        }
    }

    public function render(): View
    {
        $libraryTemplates = EmailTemplate::libraryTemplates(auth()->id())->get();

        return view('livewire.template.sequence-builder', [
            'libraryTemplates' => $libraryTemplates,
        ]);
    }
}
