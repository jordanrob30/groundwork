<?php

declare(strict_types=1);

namespace Tests\Feature\Mailbox;

use App\Models\Mailbox;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MailboxCrudTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_user_can_view_mailbox_list(): void
    {
        $mailboxes = Mailbox::factory()->count(3)->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->get(route('mailboxes.index'));

        $response->assertStatus(200);
    }

    public function test_user_can_view_create_mailbox_form(): void
    {
        $response = $this->actingAs($this->user)->get(route('mailboxes.create'));

        $response->assertStatus(200);
    }

    public function test_user_can_view_edit_mailbox_form(): void
    {
        $mailbox = Mailbox::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->get(route('mailboxes.edit', $mailbox));

        $response->assertStatus(200);
    }

    public function test_user_cannot_access_other_users_mailbox(): void
    {
        $otherUser = User::factory()->create();
        $mailbox = Mailbox::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($this->user)->get(route('mailboxes.edit', $mailbox));

        $response->assertStatus(403);
    }

    public function test_mailbox_model_encrypts_smtp_password(): void
    {
        $mailbox = Mailbox::factory()->create([
            'user_id' => $this->user->id,
            'smtp_password' => 'secret-password',
        ]);

        // Verify the raw database value is encrypted (not plain text)
        $rawValue = \DB::table('mailboxes')
            ->where('id', $mailbox->id)
            ->value('smtp_password');

        $this->assertNotEquals('secret-password', $rawValue);

        // Verify the accessor decrypts correctly
        $this->assertEquals('secret-password', $mailbox->smtp_password);
    }

    public function test_mailbox_model_encrypts_imap_password(): void
    {
        $mailbox = Mailbox::factory()->create([
            'user_id' => $this->user->id,
            'imap_password' => 'secret-password',
        ]);

        $rawValue = \DB::table('mailboxes')
            ->where('id', $mailbox->id)
            ->value('imap_password');

        $this->assertNotEquals('secret-password', $rawValue);
        $this->assertEquals('secret-password', $mailbox->imap_password);
    }

    public function test_mailbox_warmup_progress_calculation(): void
    {
        $mailbox = Mailbox::factory()->create([
            'user_id' => $this->user->id,
            'warmup_enabled' => true,
            'warmup_day' => 7,
        ]);

        // 7 days out of 14 = 50%
        $this->assertEquals(50, $mailbox->getWarmupProgressPercentage());
    }

    public function test_mailbox_warmup_progress_returns_100_when_disabled(): void
    {
        $mailbox = Mailbox::factory()->create([
            'user_id' => $this->user->id,
            'warmup_enabled' => false,
        ]);

        $this->assertEquals(100, $mailbox->getWarmupProgressPercentage());
    }

    public function test_mailbox_status_helper_methods(): void
    {
        $activeMailbox = Mailbox::factory()->create([
            'user_id' => $this->user->id,
            'status' => Mailbox::STATUS_ACTIVE,
        ]);

        $pausedMailbox = Mailbox::factory()->paused()->create([
            'user_id' => $this->user->id,
        ]);

        $errorMailbox = Mailbox::factory()->withError()->create([
            'user_id' => $this->user->id,
        ]);

        $this->assertTrue($activeMailbox->isActive());
        $this->assertFalse($activeMailbox->isPaused());

        $this->assertTrue($pausedMailbox->isPaused());
        $this->assertFalse($pausedMailbox->isActive());

        $this->assertTrue($errorMailbox->hasError());
    }

    public function test_mailbox_scopes(): void
    {
        Mailbox::factory()->create([
            'user_id' => $this->user->id,
            'status' => Mailbox::STATUS_ACTIVE,
        ]);
        Mailbox::factory()->paused()->create(['user_id' => $this->user->id]);

        $activeMailboxes = Mailbox::active()->forUser($this->user->id)->get();

        $this->assertCount(1, $activeMailboxes);
    }
}
