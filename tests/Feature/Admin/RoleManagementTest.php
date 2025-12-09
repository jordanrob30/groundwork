<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\AdminAuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class RoleManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_change_user_role_to_admin(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $user = User::factory()->create(['role' => User::ROLE_USER]);

        Livewire::actingAs($admin)
            ->test(\App\Livewire\Admin\UserList::class)
            ->call('updateRole', $user->id, User::ROLE_ADMIN);

        $user->refresh();
        $this->assertEquals(User::ROLE_ADMIN, $user->role);
    }

    public function test_admin_can_change_admin_role_to_user(): void
    {
        $admin1 = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $admin2 = User::factory()->create(['role' => User::ROLE_ADMIN]);

        Livewire::actingAs($admin1)
            ->test(\App\Livewire\Admin\UserList::class)
            ->call('updateRole', $admin2->id, User::ROLE_USER);

        $admin2->refresh();
        $this->assertEquals(User::ROLE_USER, $admin2->role);
    }

    public function test_cannot_remove_last_admin(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        Livewire::actingAs($admin)
            ->test(\App\Livewire\Admin\UserList::class)
            ->call('updateRole', $admin->id, User::ROLE_USER);

        $admin->refresh();
        $this->assertEquals(User::ROLE_ADMIN, $admin->role);
    }

    public function test_role_change_is_logged(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $user = User::factory()->create(['role' => User::ROLE_USER]);

        Livewire::actingAs($admin)
            ->test(\App\Livewire\Admin\UserList::class)
            ->call('updateRole', $user->id, User::ROLE_ADMIN);

        $this->assertDatabaseHas('admin_audit_logs', [
            'admin_id' => $admin->id,
            'target_user_id' => $user->id,
            'action' => AdminAuditLog::ACTION_ROLE_CHANGE,
        ]);

        $log = AdminAuditLog::where('action', AdminAuditLog::ACTION_ROLE_CHANGE)->first();
        $this->assertEquals('user', $log->changes['from']);
        $this->assertEquals('admin', $log->changes['to']);
    }

    public function test_invalid_role_rejected(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $user = User::factory()->create(['role' => User::ROLE_USER]);

        Livewire::actingAs($admin)
            ->test(\App\Livewire\Admin\UserList::class)
            ->call('updateRole', $user->id, 'invalid_role');

        $user->refresh();
        $this->assertEquals(User::ROLE_USER, $user->role);
    }
}
