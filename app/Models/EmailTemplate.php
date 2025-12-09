<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailTemplate extends Model
{
    use HasFactory;

    public const DELAY_TYPE_BUSINESS = 'business';

    public const DELAY_TYPE_CALENDAR = 'calendar';

    public const SUPPORTED_VARIABLES = [
        'first_name' => 'Lead first name',
        'last_name' => 'Lead last name',
        'company' => 'Lead company',
        'role' => 'Lead role/title',
        'custom_field_1' => 'Custom Field 1',
        'custom_field_2' => 'Custom Field 2',
        'custom_field_3' => 'Custom Field 3',
        'custom_field_4' => 'Custom Field 4',
        'custom_field_5' => 'Custom Field 5',
    ];

    protected $fillable = [
        'campaign_id',
        'user_id',
        'name',
        'subject',
        'body',
        'sequence_order',
        'delay_days',
        'delay_type',
        'is_library_template',
    ];

    protected $casts = [
        'sequence_order' => 'integer',
        'delay_days' => 'integer',
        'is_library_template' => 'boolean',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getDetectedVariablesAttribute(): array
    {
        $pattern = '/\{\{(\w+)\}\}/';
        $variables = [];

        preg_match_all($pattern, $this->subject, $subjectMatches);
        preg_match_all($pattern, $this->body, $bodyMatches);

        $allMatches = array_unique(array_merge(
            $subjectMatches[1] ?? [],
            $bodyMatches[1] ?? []
        ));

        foreach ($allMatches as $variable) {
            if (array_key_exists($variable, self::SUPPORTED_VARIABLES)) {
                $variables[$variable] = self::SUPPORTED_VARIABLES[$variable];
            }
        }

        return $variables;
    }

    public function renderSubject(Lead $lead): string
    {
        return $this->replaceVariables($this->subject, $lead);
    }

    public function renderBody(Lead $lead): string
    {
        return $this->replaceVariables($this->body, $lead);
    }

    protected function replaceVariables(string $content, Lead $lead): string
    {
        $replacements = [
            '{{first_name}}' => $lead->first_name ?? '',
            '{{last_name}}' => $lead->last_name ?? '',
            '{{company}}' => $lead->company ?? '',
            '{{role}}' => $lead->role ?? '',
            '{{custom_field_1}}' => $lead->custom_field_1 ?? '',
            '{{custom_field_2}}' => $lead->custom_field_2 ?? '',
            '{{custom_field_3}}' => $lead->custom_field_3 ?? '',
            '{{custom_field_4}}' => $lead->custom_field_4 ?? '',
            '{{custom_field_5}}' => $lead->custom_field_5 ?? '',
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $content);
    }

    public function scopeForCampaign($query, int $campaignId)
    {
        return $query->where('campaign_id', $campaignId);
    }

    public function scopeLibraryTemplates($query, int $userId)
    {
        return $query->where('user_id', $userId)
            ->where('is_library_template', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sequence_order');
    }

    public function duplicate(?int $newCampaignId = null): self
    {
        $new = $this->replicate();
        $new->campaign_id = $newCampaignId ?? $this->campaign_id;
        $new->is_library_template = false;
        $new->save();

        return $new;
    }

    public function saveToLibrary(): self
    {
        $library = $this->replicate();
        $library->campaign_id = null;
        $library->sequence_order = null;
        $library->is_library_template = true;
        $library->save();

        return $library;
    }
}
