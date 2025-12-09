<?php

declare(strict_types=1);

namespace App\Livewire\Lead;

use App\Models\Campaign;
use App\Models\Lead;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Component;

class LeadForm extends Component
{
    public Campaign $campaign;

    public ?Lead $lead = null;

    public string $email = '';

    public string $first_name = '';

    public string $last_name = '';

    public string $company = '';

    public string $role = '';

    public string $linkedin_url = '';

    public string $custom_field_1 = '';

    public string $custom_field_2 = '';

    public string $custom_field_3 = '';

    public string $custom_field_4 = '';

    public string $custom_field_5 = '';

    public function mount(Campaign $campaign, ?Lead $lead = null): void
    {
        $this->campaign = $campaign;

        if ($lead && $lead->exists) {
            $this->lead = $lead;
            $this->email = $lead->email;
            $this->first_name = $lead->first_name ?? '';
            $this->last_name = $lead->last_name ?? '';
            $this->company = $lead->company ?? '';
            $this->role = $lead->role ?? '';
            $this->linkedin_url = $lead->linkedin_url ?? '';
            $this->custom_field_1 = $lead->custom_field_1 ?? '';
            $this->custom_field_2 = $lead->custom_field_2 ?? '';
            $this->custom_field_3 = $lead->custom_field_3 ?? '';
            $this->custom_field_4 = $lead->custom_field_4 ?? '';
            $this->custom_field_5 = $lead->custom_field_5 ?? '';
        }
    }

    protected function rules(): array
    {
        $emailRules = ['required', 'email', 'max:255'];

        if ($this->lead) {
            $emailRules[] = Rule::unique('leads', 'email')
                ->where('campaign_id', $this->campaign->id)
                ->ignore($this->lead->id);
        } else {
            $emailRules[] = Rule::unique('leads', 'email')
                ->where('campaign_id', $this->campaign->id);
        }

        return [
            'email' => $emailRules,
            'first_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['nullable', 'string', 'max:100'],
            'company' => ['nullable', 'string', 'max:255'],
            'role' => ['nullable', 'string', 'max:255'],
            'linkedin_url' => ['nullable', 'url', 'max:500'],
            'custom_field_1' => ['nullable', 'string', 'max:255'],
            'custom_field_2' => ['nullable', 'string', 'max:255'],
            'custom_field_3' => ['nullable', 'string', 'max:255'],
            'custom_field_4' => ['nullable', 'string', 'max:255'],
            'custom_field_5' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'email' => $this->email,
            'first_name' => $this->first_name ?: null,
            'last_name' => $this->last_name ?: null,
            'company' => $this->company ?: null,
            'role' => $this->role ?: null,
            'linkedin_url' => $this->linkedin_url ?: null,
            'custom_field_1' => $this->custom_field_1 ?: null,
            'custom_field_2' => $this->custom_field_2 ?: null,
            'custom_field_3' => $this->custom_field_3 ?: null,
            'custom_field_4' => $this->custom_field_4 ?: null,
            'custom_field_5' => $this->custom_field_5 ?: null,
        ];

        if ($this->lead) {
            $this->lead->update($data);
            $this->dispatch('lead-updated', leadId: $this->lead->id);
            session()->flash('message', 'Lead updated successfully.');
        } else {
            $lead = $this->campaign->leads()->create(array_merge($data, [
                'status' => Lead::STATUS_PENDING,
            ]));
            $this->dispatch('lead-created', leadId: $lead->id);
            session()->flash('message', 'Lead created successfully.');
        }

        $this->redirect(route('campaigns.leads.index', $this->campaign));
    }

    public function render(): View
    {
        return view('livewire.lead.lead-form');
    }
}
