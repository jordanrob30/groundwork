<?php

declare(strict_types=1);

namespace App\Services;

use App\Events\ResponseAnalyzed;
use App\Exceptions\AiAnalysisException;
use App\Jobs\AnalyzeResponseJob;
use App\Models\Campaign;
use App\Models\Response;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;

class AiAnalysisService
{
    protected string $model;

    protected int $maxTokens;

    protected string $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.claude.api_key');
        $this->model = config('services.claude.model', 'claude-sonnet-4-20250514');
        $this->maxTokens = config('services.claude.max_tokens', 2048);
    }

    public function buildPrompt(Response $response): string
    {
        $campaign = $response->campaign;
        $lead = $response->lead;

        return <<<PROMPT
You are analyzing a response to an outreach email for customer discovery research.

## Campaign Context
- **Hypothesis being tested**: {$campaign->hypothesis}
- **Target Persona**: {$campaign->target_persona}
- **Industry**: {$campaign->industry}
- **Success Criteria**: {$campaign->success_criteria}

## Lead Information
- **Company**: {$lead->company}
- **Role**: {$lead->role}
- **Email**: {$lead->email}

## Original Outreach Email
Subject: {$response->sentEmail->subject}

{$response->sentEmail->body}

## Lead's Response
Subject: {$response->subject}

{$response->body_plain}

## Your Task
Analyze this response and extract structured insights to help validate or invalidate the hypothesis.
PROMPT;
    }

    public function getAnalysisSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'interest_level' => [
                    'type' => 'string',
                    'enum' => ['hot', 'warm', 'cold', 'negative'],
                    'description' => 'Overall interest level: hot=ready to buy/talk, warm=interested but not urgent, cold=polite but no interest, negative=explicit rejection',
                ],
                'problem_confirmation' => [
                    'type' => 'string',
                    'enum' => ['yes', 'no', 'different', 'unclear'],
                    'description' => 'Does the response confirm the hypothesized problem? yes=confirms it, no=denies having problem, different=has a different problem, unclear=not enough info',
                ],
                'pain_severity' => [
                    'type' => 'integer',
                    'minimum' => 1,
                    'maximum' => 5,
                    'description' => 'How severe is their pain point? 1=mild annoyance, 5=critical business issue',
                ],
                'current_solution' => [
                    'type' => 'string',
                    'description' => 'How do they currently solve this problem, if mentioned',
                ],
                'call_interest' => [
                    'type' => 'boolean',
                    'description' => 'Does the response indicate willingness to have a call or meeting?',
                ],
                'key_quotes' => [
                    'type' => 'array',
                    'items' => ['type' => 'string'],
                    'description' => 'Notable direct quotes from the response (max 3)',
                ],
                'summary' => [
                    'type' => 'string',
                    'description' => 'Brief 1-2 sentence summary of the response and its implications for the hypothesis',
                ],
                'confidence' => [
                    'type' => 'number',
                    'minimum' => 0,
                    'maximum' => 1,
                    'description' => 'Your confidence in this analysis (0-1)',
                ],
            ],
            'required' => ['interest_level', 'summary', 'confidence'],
        ];
    }

    public function analyze(Response $response): array
    {
        if (! $this->apiKey) {
            throw new AiAnalysisException('Claude API key not configured');
        }

        $response->markAsAnalyzing();

        try {
            $result = Http::withHeaders([
                'x-api-key' => $this->apiKey,
                'anthropic-version' => '2023-06-01',
                'content-type' => 'application/json',
            ])->post('https://api.anthropic.com/v1/messages', [
                'model' => $this->model,
                'max_tokens' => $this->maxTokens,
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $this->buildPrompt($response),
                    ],
                ],
                'tools' => [
                    [
                        'name' => 'analyze_response',
                        'description' => 'Analyze a customer discovery response and extract structured insights',
                        'input_schema' => $this->getAnalysisSchema(),
                    ],
                ],
                'tool_choice' => ['type' => 'tool', 'name' => 'analyze_response'],
            ]);

            if ($result->failed()) {
                throw new AiAnalysisException('Claude API request failed: '.$result->body());
            }

            $data = $result->json();
            $analysis = $this->extractAnalysis($data);

            $response->markAsAnalyzed([
                'interest_level' => $analysis['interest_level'] ?? null,
                'problem_confirmation' => $analysis['problem_confirmation'] ?? null,
                'pain_severity' => $analysis['pain_severity'] ?? null,
                'current_solution' => $analysis['current_solution'] ?? null,
                'call_interest' => $analysis['call_interest'] ?? null,
                'key_quotes' => $analysis['key_quotes'] ?? [],
                'summary' => $analysis['summary'] ?? null,
                'analysis_confidence' => $analysis['confidence'] ?? null,
            ]);

            event(new ResponseAnalyzed($response));

            return $analysis;
        } catch (\Exception $e) {
            $response->markAnalysisFailed();
            throw new AiAnalysisException('Analysis failed: '.$e->getMessage(), 0, $e);
        }
    }

    protected function extractAnalysis(array $apiResponse): array
    {
        foreach ($apiResponse['content'] ?? [] as $block) {
            if (($block['type'] ?? '') === 'tool_use' && ($block['name'] ?? '') === 'analyze_response') {
                return $block['input'] ?? [];
            }
        }

        return [];
    }

    public function queueAnalysis(Response $response): void
    {
        if (! $response->needsAnalysis()) {
            return;
        }

        AnalyzeResponseJob::dispatch($response);
    }

    public function batchReanalyze(Campaign $campaign): string
    {
        $responses = $campaign->responses()
            ->where('is_auto_reply', false)
            ->get();

        $jobs = $responses->map(fn ($response) => new AnalyzeResponseJob($response));

        $batch = Bus::batch($jobs)
            ->name("Reanalyze responses for campaign {$campaign->id}")
            ->allowFailures()
            ->dispatch();

        return $batch->id;
    }
}
