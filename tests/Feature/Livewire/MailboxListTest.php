<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire;

use App\Livewire\Mailbox\MailboxList;
use App\Models\Mailbox;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MailboxListTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_component_renders(): void
    {
        Livewire::actingAs($this->user)
            ->test(MailboxList::class)
            ->assertStatus(200);
    }

    public function test_component_shows_user_mailboxes(): void
    {
        $mailbox = Mailbox::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'Test Mailbox',
        ]);

        Livewire::actingAs($this->user)
            ->test(MailboxList::class)
            ->assertSee('Test Mailbox');
    }

    public function test_component_does_not_show_other_users_mailboxes(): void
    {
        $otherUser = User::factory()->create();
        $otherMailbox = Mailbox::factory()->create([
            'user_id' => $otherUser->id,
            'name' => 'Other User Mailbox',
        ]);

        Livewire::actingAs($this->user)
            ->test(MailboxList::class)
            ->assertDontSee('Other User Mailbox');
    }

    public function test_component_can_pause_mailbox(): void
    {
        $mailbox = Mailbox::factory()->create([
            'user_id' => $this->user->id,
            'status' => Mailbox::STATUS_ACTIVE,
        ]);

        Livewire::actingAs($this->user)
            ->test(MailboxList::class)
            ->call('pause', $mailbox->id);

        $this->assertEquals(Mailbox::STATUS_PAUSED, $mailbox->fresh()->status);
    }

    public function test_component_can_resume_mailbox(): void
    {
        $mailbox = Mailbox::factory()->paused()->create([
            'user_id' => $this->user->id,
        ]);

        Livewire::actingAs($this->user)
            ->test(MailboxList::class)
            ->call('resume', $mailbox->id);

        $this->assertEquals(Mailbox::STATUS_ACTIVE, $mailbox->fresh()->status);
    }

    public function test_component_can_delete_mailbox(): void
    {
        $mailbox = Mailbox::factory()->create([
            'user_id' => $this->user->id,
        ]);

        Livewire::actingAs($this->user)
            ->test(MailboxList::class)
            ->call('delete', $mailbox->id);

        $this->assertNull(Mailbox::find($mailbox->id));
    }

    public function test_component_cannot_manage_other_users_mailbox(): void
    {
        $otherUser = User::factory()->create();
        $otherMailbox = Mailbox::factory()->create([
            'user_id' => $otherUser->id,
            'status' => Mailbox::STATUS_ACTIVE,
        ]);

        Livewire::actingAs($this->user)
            ->test(MailboxList::class)
            ->call('pause', $otherMailbox->id);

        // Should not change - authorization should prevent it
        $this->assertEquals(Mailbox::STATUS_ACTIVE, $otherMailbox->fresh()->status);
    }
}
