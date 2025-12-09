<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\Campaign;
use App\Models\Lead;
use App\Models\Mailbox;
use App\Models\Response;
use App\Models\SentEmail;
use App\Models\User;
use App\Services\AiAnalysisService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AiAnalysisServiceTest extends TestCase
{
    use RefreshDatabase;

    private AiAnalysisService $service;

    private User $user;

    private Campaign $campaign;

    private Response $response;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(AiAnalysisService::class);
        $this->user = User::factory()->create();

        $mailbox = Mailbox::factory()->create(['user_id' => $this->user->id]);
        $this->campaign = Campaign::factory()->create([
            'user_id' => $this->user->id,
            'mailbox_id' => $mailbox->id,
            'hypothesis' => 'Users need better customer discovery tools',
        ]);

        $lead = Lead::factory()->create(['campaign_id' => $this->campaign->id]);
        $sentEmail = SentEmail::factory()->create([
            'campaign_id' => $this->campaign->id,
            'lead_id' => $lead->id,
            'mailbox_id' => $mailbox->id,
        ]);

        $this->response = Response::factory()->create([
            'campaign_id' => $this->campaign->id,
            'lead_id' => $lead->id,
            'sent_email_id' => $sentEmail->id,
        ]);
    }

    public function test_build_prompt_includes_campaign_context(): void
    {
        $prompt = $this->service->buildPrompt($this->response);

        $this->assertStringContainsString('hypothesis', strtolower($prompt));
    }

    public function test_build_prompt_includes_response_content(): void
    {
        $this->response->update(['body_plain' => 'This is the response content']);

        $prompt = $this->service->buildPrompt($this->response);

        $this->assertStringContainsString('This is the response content', $prompt);
    }

    public function test_get_analysis_schema_returns_valid_json_schema(): void
    {
        $schema = $this->service->getAnalysisSchema();

        $this->assertIsArray($schema);
        $this->assertArrayHasKey('type', $schema);
        $this->assertEquals('object', $schema['type']);
        $this->assertArrayHasKey('properties', $schema);
    }

    public function test_analyze_throws_exception_without_api_key(): void
    {
        // Without setting the API key, analyze should throw an exception
        $this->expectException(\App\Exceptions\AiAnalysisException::class);
        $this->expectExceptionMessage('Claude API key not configured');

        $this->service->analyze($this->response);
    }

    public function test_analyze_handles_api_error(): void
    {
        // Set a fake API key for testing
        config(['services.claude.api_key' => 'test-api-key']);
        $this->service = new AiAnalysisService;

        Http::fake([
            'api.anthropic.com/*' => Http::response(['error' => 'Rate limited'], 429),
        ]);

        $this->expectException(\App\Exceptions\AiAnalysisException::class);

        $this->service->analyze($this->response);
    }
}
