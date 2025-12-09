<?php

declare(strict_types=1);

namespace App\Livewire\Template;

use App\Models\Campaign;
use App\Models\EmailTemplate;
use App\Models\Lead;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class TemplateEditor extends Component
{
    public Campaign $campaign;

    public ?EmailTemplate $template = null;

    public string $name = '';

    public string $subject = '';

    public string $body = '';

    public int $sequence_order = 1;

    public int $delay_days = 3;

    public string $delay_type = 'business';

    public bool $is_library_template = false;

    public ?Lead $previewLead = null;

    public string $previewSubject = '';

    public string $previewBody = '';

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'subject' => ['required', 'string', 'max:500'],
            'body' => ['required', 'string'],
            'sequence_order' => ['required', 'integer', 'min:1'],
            'delay_days' => ['required', 'integer', 'min:0', 'max:30'],
            'delay_type' => ['required', 'in:business,calendar'],
        ];
    }

    public function mount(Campaign $campaign, ?EmailTemplate $template = null): void
    {
        $this->campaign = $campaign;

        if ($template && $template->exists) {
            $this->template = $template;
            $this->name = $template->name;
            $this->subject = $template->subject;
            $this->body = $template->body;
            $this->sequence_order = $template->sequence_order ?? 1;
            $this->delay_days = $template->delay_days;
            $this->delay_type = $template->delay_type;
            $this->is_library_template = $template->is_library_template;
        } else {
            $this->sequence_order = $campaign->templates()->max('sequence_order') + 1 ?? 1;
        }

        $this->loadPreviewLead();
    }

    protected function loadPreviewLead(): void
    {
        $this->previewLead = $this->campaign->leads()
            ->whereNotNull('first_name')
            ->whereNotNull('company')
            ->first();

        if (! $this->previewLead) {
            $this->previewLead = new Lead([
                'first_name' => 'John',
                'last_name' => 'Smith',
                'email' => 'john@example.com',
                'company' => 'Acme Inc.',
                'role' => 'CEO',
            ]);
        }

        $this->updatePreview();
    }

    public function updatedSubject(): void
    {
        $this->updatePreview();
    }

    public function updatedBody(): void
    {
        $this->updatePreview();
    }

    protected function updatePreview(): void
    {
        if (! $this->previewLead) {
            return;
        }

        $template = new EmailTemplate([
            'subject' => $this->subject,
            'body' => $this->body,
        ]);

        $this->previewSubject = $template->renderSubject($this->previewLead);
        $this->previewBody = $template->renderBody($this->previewLead);
    }

    public function insertVariable(string $variable): void
    {
        $this->dispatch('insert-variable', variable: '{{'.$variable.'}}');
    }

    public function preview(?Lead $lead = null): void
    {
        if ($lead) {
            $this->previewLead = $lead;
        }
        $this->updatePreview();
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'subject' => $this->subject,
            'body' => $this->body,
            'sequence_order' => $this->sequence_order,
            'delay_days' => $this->delay_days,
            'delay_type' => $this->delay_type,
        ];

        if ($this->template) {
            $this->template->update($data);
            $this->dispatch('template-updated', templateId: $this->template->id);
            session()->flash('message', 'Template updated successfully.');
        } else {
            $template = $this->campaign->templates()->create(array_merge($data, [
                'user_id' => auth()->id(),
            ]));
            $this->dispatch('template-created', templateId: $template->id);
            session()->flash('message', 'Template created successfully.');
        }

        $this->redirect(route('campaigns.templates.index', $this->campaign));
    }

    public function saveToLibrary(): void
    {
        $this->validate();

        if (! $this->template) {
            $this->save();
            $this->template = EmailTemplate::where('campaign_id', $this->campaign->id)
                ->where('user_id', auth()->id())
                ->orderByDesc('id')
                ->first();
        }

        if ($this->template) {
            $library = $this->template->saveToLibrary();
            $this->dispatch('template-saved-to-library', templateId: $library->id);
            session()->flash('message', 'Template saved to library.');
        }
    }

    public function render(): View
    {
        return view('livewire.template.template-editor', [
            'supportedVariables' => EmailTemplate::SUPPORTED_VARIABLES,
        ]);
    }
}
