<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\Models\Campaign;
use App\Models\EmailTemplate;
use App\Models\Lead;
use App\Models\Mailbox;
use App\Models\SentEmail;
use App\Models\User;
use App\Services\SendEngineService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\GreenmailClient;
use Tests\TestCase;

/**
 * Integration tests for campaign email sending using Greenmail.
 *
 * These tests verify that emails are actually sent through SMTP
 * and can be verified via Greenmail's API.
 */
class CampaignSendingTest extends TestCase
{
    use RefreshDatabase;

    protected GreenmailClient $greenmail;

    protected SendEngineService $sendEngine;

    protected User $user;

    protected Mailbox $mailbox;

    protected function setUp(): void
    {
        parent::setUp();

        $this->greenmail = new GreenmailClient;
        $this->sendEngine = app(SendEngineService::class);

        // Clear any existing messages
        $this->greenmail->purgeAllMessages();

        // Create user with Greenmail-configured mailbox
        $this->user = User::factory()->create();
        $this->mailbox = Mailbox::factory()
            ->greenmail()
            ->create(['user_id' => $this->user->id]);
    }

    public function test_can_send_single_email_to_lead(): void
    {
        // Arrange
        $campaign = Campaign::factory()->create([
            'user_id' => $this->user->id,
            'mailbox_id' => $this->mailbox->id,
            'status' => Campaign::STATUS_ACTIVE,
        ]);

        $template = EmailTemplate::factory()->create([
            'user_id' => $this->user->id,
            'campaign_id' => $campaign->id,
            'subject' => 'Hello {{first_name}}',
            'body' => '<p>Hi {{first_name}}, this is a test from {{company}}.</p>',
            'sequence_order' => 1,
        ]);

        $lead = Lead::factory()->create([
            'campaign_id' => $campaign->id,
            'email' => 'recipient@localhost',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'company' => 'Acme Inc',
        ]);

        $rendered = $this->sendEngine->renderTemplate($template, $lead);

        $sentEmail = SentEmail::create([
            'mailbox_id' => $this->mailbox->id,
            'campaign_id' => $campaign->id,
            'lead_id' => $lead->id,
            'template_id' => $template->id,
            'message_id' => '<test-'.uniqid().'@localhost>',
            'subject' => $rendered['subject'],
            'body' => $rendered['body'],
            'status' => SentEmail::STATUS_QUEUED,
            'sequence_step' => 1,
        ]);

        // Act
        $result = $this->sendEngine->send($sentEmail);

        // Assert
        $this->assertTrue($result, 'Email should be sent successfully');
        $this->assertEquals(SentEmail::STATUS_SENT, $sentEmail->fresh()->status);

        // Verify email arrived in Greenmail
        $this->greenmail->waitForMessages('recipient@localhost', 1);
        $messages = $this->greenmail->getMessages('recipient@localhost');

        $this->assertCount(1, $messages, 'Should have 1 message in mailbox');
        $this->assertStringContainsString('Hello John', $messages[0]['subject']);
    }

    public function test_template_variables_are_rendered_correctly(): void
    {
        // Arrange
        $campaign = Campaign::factory()->create([
            'user_id' => $this->user->id,
            'mailbox_id' => $this->mailbox->id,
            'status' => Campaign::STATUS_ACTIVE,
        ]);

        $template = EmailTemplate::factory()->create([
            'user_id' => $this->user->id,
            'campaign_id' => $campaign->id,
            'subject' => 'Question about {{company}}',
            'body' => '<p>Hi {{first_name}} {{last_name}},</p><p>I noticed you work as {{role}} at {{company}}.</p>',
            'sequence_order' => 1,
        ]);

        $lead = Lead::factory()->create([
            'campaign_id' => $campaign->id,
            'email' => 'jane@localhost',
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'company' => 'TechCorp',
            'role' => 'CTO',
        ]);

        $rendered = $this->sendEngine->renderTemplate($template, $lead);

        $sentEmail = SentEmail::create([
            'mailbox_id' => $this->mailbox->id,
            'campaign_id' => $campaign->id,
            'lead_id' => $lead->id,
            'template_id' => $template->id,
            'message_id' => '<test-'.uniqid().'@localhost>',
            'subject' => $rendered['subject'],
            'body' => $rendered['body'],
            'status' => SentEmail::STATUS_QUEUED,
            'sequence_step' => 1,
        ]);

        // Act
        $this->sendEngine->send($sentEmail);

        // Assert
        $this->greenmail->waitForMessages('jane@localhost', 1);
        $messages = $this->greenmail->getMessages('jane@localhost');

        $this->assertCount(1, $messages);
        $this->assertEquals('Question about TechCorp', $messages[0]['subject']);
    }

    public function test_can_queue_campaign_emails_for_multiple_leads(): void
    {
        // Arrange
        $campaign = Campaign::factory()->create([
            'user_id' => $this->user->id,
            'mailbox_id' => $this->mailbox->id,
            'status' => Campaign::STATUS_ACTIVE,
        ]);

        EmailTemplate::factory()->create([
            'user_id' => $this->user->id,
            'campaign_id' => $campaign->id,
            'subject' => 'Outreach to {{first_name}}',
            'body' => '<p>Hello {{first_name}}</p>',
            'sequence_order' => 1,
        ]);

        // Create multiple leads
        Lead::factory()->count(3)->create([
            'campaign_id' => $campaign->id,
            'status' => Lead::STATUS_PENDING,
        ]);

        // Act
        $queuedCount = $this->sendEngine->queueCampaignEmails($campaign);

        // Assert
        $this->assertEquals(3, $queuedCount);
        $this->assertEquals(3, SentEmail::where('campaign_id', $campaign->id)->count());

        // All leads should be marked as queued
        $this->assertEquals(3, Lead::where('campaign_id', $campaign->id)
            ->where('status', Lead::STATUS_QUEUED)
            ->count());
    }

    public function test_daily_limit_is_respected_when_queuing(): void
    {
        // Arrange - set low daily limit
        $this->mailbox->update(['daily_limit' => 2]);

        $campaign = Campaign::factory()->create([
            'user_id' => $this->user->id,
            'mailbox_id' => $this->mailbox->id,
            'status' => Campaign::STATUS_ACTIVE,
        ]);

        EmailTemplate::factory()->create([
            'user_id' => $this->user->id,
            'campaign_id' => $campaign->id,
            'subject' => 'Test',
            'body' => '<p>Test</p>',
            'sequence_order' => 1,
        ]);

        // Create more leads than daily limit
        Lead::factory()->count(5)->create([
            'campaign_id' => $campaign->id,
            'status' => Lead::STATUS_PENDING,
        ]);

        // Act
        $queuedCount = $this->sendEngine->queueCampaignEmails($campaign);

        // Assert - should only queue up to daily limit
        $this->assertEquals(2, $queuedCount);
        $this->assertEquals(2, SentEmail::where('campaign_id', $campaign->id)->count());
    }

    public function test_does_not_queue_for_inactive_campaign(): void
    {
        // Arrange
        $campaign = Campaign::factory()->create([
            'user_id' => $this->user->id,
            'mailbox_id' => $this->mailbox->id,
            'status' => Campaign::STATUS_DRAFT, // Not active
        ]);

        EmailTemplate::factory()->create([
            'user_id' => $this->user->id,
            'campaign_id' => $campaign->id,
            'sequence_order' => 1,
        ]);

        Lead::factory()->count(3)->create([
            'campaign_id' => $campaign->id,
            'status' => Lead::STATUS_PENDING,
        ]);

        // Act
        $queuedCount = $this->sendEngine->queueCampaignEmails($campaign);

        // Assert
        $this->assertEquals(0, $queuedCount);
        $this->assertEquals(0, SentEmail::where('campaign_id', $campaign->id)->count());
    }

    public function test_sent_email_is_recorded_in_database(): void
    {
        // Arrange
        $campaign = Campaign::factory()->create([
            'user_id' => $this->user->id,
            'mailbox_id' => $this->mailbox->id,
            'status' => Campaign::STATUS_ACTIVE,
        ]);

        $template = EmailTemplate::factory()->create([
            'user_id' => $this->user->id,
            'campaign_id' => $campaign->id,
            'subject' => 'Database Test',
            'body' => '<p>Testing database recording</p>',
            'sequence_order' => 1,
        ]);

        $lead = Lead::factory()->create([
            'campaign_id' => $campaign->id,
            'email' => 'dbtest@localhost',
        ]);

        $rendered = $this->sendEngine->renderTemplate($template, $lead);

        $sentEmail = SentEmail::create([
            'mailbox_id' => $this->mailbox->id,
            'campaign_id' => $campaign->id,
            'lead_id' => $lead->id,
            'template_id' => $template->id,
            'message_id' => '<test-'.uniqid().'@localhost>',
            'subject' => $rendered['subject'],
            'body' => $rendered['body'],
            'status' => SentEmail::STATUS_QUEUED,
            'sequence_step' => 1,
        ]);

        // Act
        $this->sendEngine->send($sentEmail);

        // Assert database state
        $sentEmail->refresh();
        $this->assertEquals(SentEmail::STATUS_SENT, $sentEmail->status);
        $this->assertNotNull($sentEmail->sent_at);

        // Lead should be marked as contacted
        $lead->refresh();
        $this->assertNotNull($lead->last_contacted_at);
    }

    public function test_failed_email_records_error(): void
    {
        // Arrange - Create mailbox with invalid SMTP settings
        $badMailbox = Mailbox::factory()->create([
            'user_id' => $this->user->id,
            'smtp_host' => 'nonexistent.invalid.host',
            'smtp_port' => 9999,
        ]);

        $campaign = Campaign::factory()->create([
            'user_id' => $this->user->id,
            'mailbox_id' => $badMailbox->id,
            'status' => Campaign::STATUS_ACTIVE,
        ]);

        $lead = Lead::factory()->create([
            'campaign_id' => $campaign->id,
            'email' => 'fail@localhost',
        ]);

        $sentEmail = SentEmail::create([
            'mailbox_id' => $badMailbox->id,
            'campaign_id' => $campaign->id,
            'lead_id' => $lead->id,
            'message_id' => '<test-'.uniqid().'@localhost>',
            'subject' => 'This will fail',
            'body' => '<p>Test</p>',
            'status' => SentEmail::STATUS_QUEUED,
            'sequence_step' => 1,
        ]);

        // Act
        $result = $this->sendEngine->send($sentEmail);

        // Assert
        $this->assertFalse($result);

        $sentEmail->refresh();
        $this->assertEquals(SentEmail::STATUS_FAILED, $sentEmail->status);
        $this->assertNotNull($sentEmail->error_message);
    }

    public function test_multiple_emails_to_same_lead_include_threading_headers(): void
    {
        // Arrange
        $campaign = Campaign::factory()->create([
            'user_id' => $this->user->id,
            'mailbox_id' => $this->mailbox->id,
            'status' => Campaign::STATUS_ACTIVE,
        ]);

        $template1 = EmailTemplate::factory()->create([
            'user_id' => $this->user->id,
            'campaign_id' => $campaign->id,
            'subject' => 'First email',
            'body' => '<p>Initial outreach</p>',
            'sequence_order' => 1,
        ]);

        $template2 = EmailTemplate::factory()->create([
            'user_id' => $this->user->id,
            'campaign_id' => $campaign->id,
            'subject' => 'Follow up',
            'body' => '<p>Following up</p>',
            'sequence_order' => 2,
            'delay_days' => 3,
        ]);

        $lead = Lead::factory()->create([
            'campaign_id' => $campaign->id,
            'email' => 'threading@localhost',
        ]);

        // Send first email
        $rendered1 = $this->sendEngine->renderTemplate($template1, $lead);
        $sentEmail1 = SentEmail::create([
            'mailbox_id' => $this->mailbox->id,
            'campaign_id' => $campaign->id,
            'lead_id' => $lead->id,
            'template_id' => $template1->id,
            'message_id' => '<first-'.uniqid().'@localhost>',
            'subject' => $rendered1['subject'],
            'body' => $rendered1['body'],
            'status' => SentEmail::STATUS_QUEUED,
            'sequence_step' => 1,
        ]);
        $this->sendEngine->send($sentEmail1);

        // Send second email (should include In-Reply-To header)
        $rendered2 = $this->sendEngine->renderTemplate($template2, $lead);
        $sentEmail2 = SentEmail::create([
            'mailbox_id' => $this->mailbox->id,
            'campaign_id' => $campaign->id,
            'lead_id' => $lead->id,
            'template_id' => $template2->id,
            'message_id' => '<second-'.uniqid().'@localhost>',
            'subject' => $rendered2['subject'],
            'body' => $rendered2['body'],
            'status' => SentEmail::STATUS_QUEUED,
            'sequence_step' => 2,
        ]);
        $this->sendEngine->send($sentEmail2);

        // Assert both emails arrived
        $this->greenmail->waitForMessages('threading@localhost', 2);
        $messages = $this->greenmail->getMessages('threading@localhost');
        $this->assertCount(2, $messages);

        // Verify message references were created for threading
        $this->assertDatabaseHas('message_references', [
            'sent_email_id' => $sentEmail2->id,
            'reference_message_id' => $sentEmail1->message_id,
        ]);
    }

    protected function tearDown(): void
    {
        // Clean up Greenmail messages after each test
        $this->greenmail->purgeAllMessages();

        parent::tearDown();
    }
}
