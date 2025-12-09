<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\AdminAuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class UserImpersonationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_start_impersonation(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $user = User::factory()->create(['role' => User::ROLE_USER]);

        Livewire::actingAs($admin)
            ->test(\App\Livewire\Admin\UserList::class)
            ->call('impersonate', $user->id)
            ->assertRedirect('/dashboard');

        $this->assertEquals($user->id, session('impersonating'));
        $this->assertEquals($admin->id, session('impersonated_by'));
    }

    public function test_admin_cannot_impersonate_themselves(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        Livewire::actingAs($admin)
            ->test(\App\Livewire\Admin\UserList::class)
            ->call('impersonate', $admin->id)
            ->assertNoRedirect();

        $this->assertNull(session('impersonating'));
    }

    public function test_impersonation_is_logged(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $user = User::factory()->create(['role' => User::ROLE_USER]);

        Livewire::actingAs($admin)
            ->test(\App\Livewire\Admin\UserList::class)
            ->call('impersonate', $user->id);

        $this->assertDatabaseHas('admin_audit_logs', [
            'admin_id' => $admin->id,
            'target_user_id' => $user->id,
            'action' => AdminAuditLog::ACTION_IMPERSONATE,
        ]);
    }

    public function test_stop_impersonation_clears_session(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $user = User::factory()->create(['role' => User::ROLE_USER]);

        // Start impersonation
        session([
            'impersonating' => $user->id,
            'impersonated_by' => $admin->id,
            'impersonation_started_at' => now()->toIso8601String(),
        ]);

        $response = $this->actingAs($admin)->post('/admin/stop-impersonate');

        $response->assertRedirect('/admin/users');
        $this->assertNull(session('impersonating'));
        $this->assertNull(session('impersonated_by'));
    }

    public function test_stop_impersonation_is_logged(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $user = User::factory()->create(['role' => User::ROLE_USER]);

        // Start impersonation
        session([
            'impersonating' => $user->id,
            'impersonated_by' => $admin->id,
            'impersonation_started_at' => now()->toIso8601String(),
        ]);

        $this->actingAs($admin)->post('/admin/stop-impersonate');

        $this->assertDatabaseHas('admin_audit_logs', [
            'admin_id' => $admin->id,
            'target_user_id' => $user->id,
            'action' => AdminAuditLog::ACTION_STOP_IMPERSONATE,
        ]);
    }

    public function test_emulation_banner_shows_during_impersonation(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $user = User::factory()->create(['role' => User::ROLE_USER]);

        // Start impersonation
        session([
            'impersonating' => $user->id,
            'impersonated_by' => $admin->id,
            'impersonation_started_at' => now()->toIso8601String(),
        ]);

        $response = $this->actingAs($admin)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('You are viewing as');
        $response->assertSee($user->name);
    }

    public function test_profile_blocked_during_impersonation(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $user = User::factory()->create(['role' => User::ROLE_USER]);

        // Start impersonation
        session([
            'impersonating' => $user->id,
            'impersonated_by' => $admin->id,
            'impersonation_started_at' => now()->toIso8601String(),
        ]);

        $response = $this->actingAs($admin)->get('/profile');

        $response->assertStatus(403);
    }
}
