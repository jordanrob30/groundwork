<?php

declare(strict_types=1);

namespace App\Services;

use App\Events\CampaignActivated;
use App\Events\CampaignCompleted;
use App\Events\CampaignPaused;
use App\Models\Campaign;
use App\Models\User;

class CampaignService
{
    /**
     * Create a new campaign.
     */
    public function create(User $user, array $data): Campaign
    {
        $data['user_id'] = $user->id;
        $data['status'] = Campaign::STATUS_DRAFT;

        return Campaign::create($data);
    }

    /**
     * Update campaign configuration.
     */
    public function update(Campaign $campaign, array $data): Campaign
    {
        $campaign->update($data);

        return $campaign->fresh();
    }

    /**
     * Activate a draft or paused campaign.
     *
     * @throws \DomainException If campaign cannot be activated
     */
    public function activate(Campaign $campaign): Campaign
    {
        if (! $campaign->canBeActivated()) {
            $reasons = [];
            if ($campaign->leads()->count() === 0) {
                $reasons[] = 'no leads';
            }
            if ($campaign->emailTemplates()->count() === 0) {
                $reasons[] = 'no email templates';
            }
            throw new \DomainException('Campaign cannot be activated: '.implode(', ', $reasons));
        }

        $campaign->status = Campaign::STATUS_ACTIVE;
        $campaign->activated_at = now();
        $campaign->save();

        event(new CampaignActivated($campaign));

        return $campaign;
    }

    /**
     * Pause an active campaign.
     */
    public function pause(Campaign $campaign): Campaign
    {
        $campaign->status = Campaign::STATUS_PAUSED;
        $campaign->save();

        event(new CampaignPaused($campaign));

        return $campaign;
    }

    /**
     * Mark campaign as completed.
     */
    public function complete(Campaign $campaign): Campaign
    {
        $campaign->status = Campaign::STATUS_COMPLETED;
        $campaign->completed_at = now();
        $campaign->save();

        event(new CampaignCompleted($campaign));

        return $campaign;
    }

    /**
     * Archive a campaign (hides from active view).
     */
    public function archive(Campaign $campaign): Campaign
    {
        $campaign->status = Campaign::STATUS_ARCHIVED;
        $campaign->archived_at = now();
        $campaign->save();

        return $campaign;
    }

    /**
     * Duplicate a campaign with all settings.
     */
    public function duplicate(Campaign $campaign, string $newName): Campaign
    {
        $newCampaign = $campaign->replicate([
            'status',
            'activated_at',
            'completed_at',
            'archived_at',
        ]);

        $newCampaign->name = $newName;
        $newCampaign->status = Campaign::STATUS_DRAFT;
        $newCampaign->save();

        // Duplicate templates (but not leads)
        foreach ($campaign->emailTemplates as $template) {
            $newTemplate = $template->replicate();
            $newTemplate->campaign_id = $newCampaign->id;
            $newTemplate->save();
        }

        return $newCampaign;
    }

    /**
     * Calculate campaign metrics.
     *
     * @return array{
     *   total_leads: int,
     *   emails_sent: int,
     *   responses: int,
     *   response_rate: float,
     *   interest_breakdown: array,
     *   problem_validation_rate: float,
     *   avg_pain_severity: float,
     *   calls_booked: int
     * }
     */
    public function calculateMetrics(Campaign $campaign): array
    {
        $totalLeads = $campaign->leads()->count();
        $emailsSent = $campaign->sentEmails()->whereNotNull('sent_at')->count();
        $responses = $campaign->responses()->where('is_auto_reply', false)->count();
        $responseRate = $emailsSent > 0 ? round(($responses / $emailsSent) * 100, 2) : 0;

        // Interest breakdown
        $interestBreakdown = $campaign->responses()
            ->where('is_auto_reply', false)
            ->whereNotNull('interest_level')
            ->selectRaw('interest_level, COUNT(*) as count')
            ->groupBy('interest_level')
            ->pluck('count', 'interest_level')
            ->toArray();

        // Problem validation rate
        $analyzedResponses = $campaign->responses()
            ->whereNotNull('problem_confirmation')
            ->count();
        $confirmedProblem = $campaign->responses()
            ->where('problem_confirmation', 'yes')
            ->count();
        $problemValidationRate = $analyzedResponses > 0
            ? round(($confirmedProblem / $analyzedResponses) * 100, 2)
            : 0;

        // Average pain severity
        $avgPainSeverity = $campaign->responses()
            ->whereNotNull('pain_severity')
            ->avg('pain_severity') ?? 0;

        $callsBooked = $campaign->callBookings()->count();

        return [
            'total_leads' => $totalLeads,
            'emails_sent' => $emailsSent,
            'responses' => $responses,
            'response_rate' => $responseRate,
            'interest_breakdown' => $interestBreakdown,
            'problem_validation_rate' => $problemValidationRate,
            'avg_pain_severity' => round($avgPainSeverity, 1),
            'calls_booked' => $callsBooked,
        ];
    }

    /**
     * Calculate decision score based on campaign data.
     *
     * @return array{
     *   score: int,
     *   recommendation: string,
     *   factors: array
     * }
     */
    public function calculateDecisionScore(Campaign $campaign): array
    {
        $metrics = $this->calculateMetrics($campaign);

        // Scoring weights
        $factors = [];
        $score = 0;

        // Response rate (max 25 points)
        $responseRateScore = min(25, $metrics['response_rate'] * 2.5);
        $factors['response_rate'] = [
            'value' => $metrics['response_rate'],
            'score' => $responseRateScore,
            'max' => 25,
        ];
        $score += $responseRateScore;

        // Hot/Warm interest (max 30 points)
        $hotWarm = ($metrics['interest_breakdown']['hot'] ?? 0) + ($metrics['interest_breakdown']['warm'] ?? 0);
        $totalResponses = array_sum($metrics['interest_breakdown']);
        $interestRate = $totalResponses > 0 ? ($hotWarm / $totalResponses) * 100 : 0;
        $interestScore = min(30, $interestRate * 0.6);
        $factors['interest'] = [
            'value' => $interestRate,
            'score' => $interestScore,
            'max' => 30,
        ];
        $score += $interestScore;

        // Problem validation (max 25 points)
        $validationScore = min(25, $metrics['problem_validation_rate'] * 0.25);
        $factors['problem_validation'] = [
            'value' => $metrics['problem_validation_rate'],
            'score' => $validationScore,
            'max' => 25,
        ];
        $score += $validationScore;

        // Pain severity (max 10 points)
        $painScore = $metrics['avg_pain_severity'] * 2;
        $factors['pain_severity'] = [
            'value' => $metrics['avg_pain_severity'],
            'score' => $painScore,
            'max' => 10,
        ];
        $score += $painScore;

        // Calls booked (max 10 points)
        $callScore = min(10, $metrics['calls_booked'] * 2);
        $factors['calls_booked'] = [
            'value' => $metrics['calls_booked'],
            'score' => $callScore,
            'max' => 10,
        ];
        $score += $callScore;

        // Generate recommendation
        $recommendation = match (true) {
            $score >= 70 => 'Strong signal! Consider proceeding with development or seeking investment.',
            $score >= 50 => 'Promising signals. Continue validation with more targeted outreach.',
            $score >= 30 => 'Mixed signals. Refine your hypothesis and test with different personas.',
            default => 'Weak signal. Consider pivoting or exploring different problem spaces.',
        };

        return [
            'score' => (int) round($score),
            'recommendation' => $recommendation,
            'factors' => $factors,
        ];
    }
}
