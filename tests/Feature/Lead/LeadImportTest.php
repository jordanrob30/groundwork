<?php

declare(strict_types=1);

namespace Tests\Feature\Lead;

use App\Models\Campaign;
use App\Models\Lead;
use App\Models\Mailbox;
use App\Models\User;
use App\Services\LeadImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LeadImportTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Mailbox $mailbox;

    private Campaign $campaign;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->mailbox = Mailbox::factory()->create(['user_id' => $this->user->id]);
        $this->campaign = Campaign::factory()->create([
            'user_id' => $this->user->id,
            'mailbox_id' => $this->mailbox->id,
        ]);

        Storage::fake('local');
    }

    public function test_user_can_view_lead_list(): void
    {
        Lead::factory()->count(5)->create(['campaign_id' => $this->campaign->id]);

        $response = $this->actingAs($this->user)->get(route('campaigns.leads.index', $this->campaign));

        $response->assertStatus(200);
    }

    public function test_user_can_view_import_form(): void
    {
        $response = $this->actingAs($this->user)->get(route('campaigns.leads.import', $this->campaign));

        $response->assertStatus(200);
    }

    public function test_lead_import_service_analyzes_csv_file(): void
    {
        $csvContent = "email,first_name,last_name,company\njohn@example.com,John,Doe,Acme Inc\njane@example.com,Jane,Smith,Tech Corp";
        $path = Storage::put('imports/test.csv', $csvContent);
        $fullPath = Storage::path('imports/test.csv');

        $service = app(LeadImportService::class);
        $analysis = $service->analyzeFile($fullPath);

        $this->assertArrayHasKey('headers', $analysis);
        $this->assertArrayHasKey('row_count', $analysis);
        $this->assertArrayHasKey('sample_rows', $analysis);
        $this->assertContains('email', $analysis['headers']);
        $this->assertContains('first_name', $analysis['headers']);
        $this->assertEquals(2, $analysis['row_count']);
    }

    public function test_lead_import_service_generates_preview(): void
    {
        $csvContent = "email,first_name,last_name,company\njohn@example.com,John,Doe,Acme Inc\njane@example.com,Jane,Smith,Tech Corp";
        Storage::put('imports/test.csv', $csvContent);
        $fullPath = Storage::path('imports/test.csv');

        $columnMapping = [
            'email' => 'email',
            'first_name' => 'first_name',
            'last_name' => 'last_name',
            'company' => 'company',
        ];

        $service = app(LeadImportService::class);
        $preview = $service->generatePreview($fullPath, $columnMapping, 10);

        $this->assertCount(2, $preview);
        $this->assertEquals('john@example.com', $preview[0]['email']);
        $this->assertEquals('John', $preview[0]['first_name']);
    }

    public function test_lead_import_service_imports_leads(): void
    {
        $csvContent = "email,first_name,last_name,company\njohn@example.com,John,Doe,Acme Inc\njane@example.com,Jane,Smith,Tech Corp";
        Storage::put('imports/test.csv', $csvContent);
        $fullPath = Storage::path('imports/test.csv');

        $columnMapping = [
            'email' => 'email',
            'first_name' => 'first_name',
            'last_name' => 'last_name',
            'company' => 'company',
        ];

        $service = app(LeadImportService::class);
        $result = $service->import($this->campaign, $fullPath, $columnMapping);

        $this->assertArrayHasKey('imported', $result);
        $this->assertArrayHasKey('skipped', $result);
        $this->assertEquals(2, $result['imported']);
        $this->assertEquals(0, $result['skipped']);

        $this->assertEquals(2, Lead::where('campaign_id', $this->campaign->id)->count());
    }

    public function test_lead_import_service_deduplicates_by_email(): void
    {
        // Create an existing lead
        Lead::factory()->create([
            'campaign_id' => $this->campaign->id,
            'email' => 'john@example.com',
            'first_name' => 'Old',
            'last_name' => 'Name',
        ]);

        $csvContent = "email,first_name,last_name,company\njohn@example.com,John,Doe,Acme Inc\njane@example.com,Jane,Smith,Tech Corp";
        Storage::put('imports/test.csv', $csvContent);
        $fullPath = Storage::path('imports/test.csv');

        $columnMapping = [
            'email' => 'email',
            'first_name' => 'first_name',
            'last_name' => 'last_name',
            'company' => 'company',
        ];

        $service = app(LeadImportService::class);
        $result = $service->import($this->campaign, $fullPath, $columnMapping);

        // Should have updated the existing one and added the new one
        $this->assertEquals(2, Lead::where('campaign_id', $this->campaign->id)->count());

        // The existing lead should be updated
        $existingLead = Lead::where('email', 'john@example.com')
            ->where('campaign_id', $this->campaign->id)
            ->first();
        $this->assertEquals('John', $existingLead->first_name);
    }

    public function test_lead_import_validates_email(): void
    {
        $csvContent = "email,first_name,last_name,company\ninvalid-email,John,Doe,Acme Inc\njane@example.com,Jane,Smith,Tech Corp";
        Storage::put('imports/test.csv', $csvContent);
        $fullPath = Storage::path('imports/test.csv');

        $columnMapping = [
            'email' => 'email',
            'first_name' => 'first_name',
            'last_name' => 'last_name',
            'company' => 'company',
        ];

        $service = app(LeadImportService::class);
        $result = $service->import($this->campaign, $fullPath, $columnMapping);

        // Invalid email should be skipped
        $this->assertEquals(1, $result['imported']);
        $this->assertEquals(1, $result['skipped']);
    }

    public function test_lead_model_full_name_attribute(): void
    {
        $lead = Lead::factory()->create([
            'campaign_id' => $this->campaign->id,
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);

        $this->assertEquals('John Doe', $lead->full_name);
    }

    public function test_lead_status_scopes(): void
    {
        Lead::factory()->create([
            'campaign_id' => $this->campaign->id,
            'status' => Lead::STATUS_PENDING,
        ]);
        Lead::factory()->contacted()->create(['campaign_id' => $this->campaign->id]);
        Lead::factory()->responded()->create(['campaign_id' => $this->campaign->id]);
        Lead::factory()->bounced()->create(['campaign_id' => $this->campaign->id]);

        $this->assertCount(1, Lead::pending()->get());
        $this->assertCount(2, Lead::contactable()->get()); // pending + contacted
        $this->assertCount(1, Lead::replied()->get());
    }
}
