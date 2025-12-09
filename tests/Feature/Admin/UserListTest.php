<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class UserListTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_list_displays_all_users(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $users = User::factory()->count(5)->create(['role' => User::ROLE_USER]);

        $response = $this->actingAs($admin)->get('/admin/users');

        $response->assertStatus(200);
        foreach ($users as $user) {
            $response->assertSee($user->email);
        }
    }

    public function test_user_list_can_search_by_name(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $matchingUser = User::factory()->create([
            'name' => 'John Doe',
            'role' => User::ROLE_USER,
        ]);
        $nonMatchingUser = User::factory()->create([
            'name' => 'Jane Smith',
            'role' => User::ROLE_USER,
        ]);

        Livewire::actingAs($admin)
            ->test(\App\Livewire\Admin\UserList::class)
            ->set('search', 'John')
            ->assertSee('John Doe')
            ->assertDontSee('Jane Smith');
    }

    public function test_user_list_can_search_by_email(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $matchingUser = User::factory()->create([
            'email' => 'john@example.com',
            'role' => User::ROLE_USER,
        ]);
        $nonMatchingUser = User::factory()->create([
            'email' => 'jane@example.com',
            'role' => User::ROLE_USER,
        ]);

        Livewire::actingAs($admin)
            ->test(\App\Livewire\Admin\UserList::class)
            ->set('search', 'john@')
            ->assertSee('john@example.com')
            ->assertDontSee('jane@example.com');
    }

    public function test_user_list_can_filter_by_role(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'email' => 'admin@test.com',
        ]);
        $user = User::factory()->create([
            'role' => User::ROLE_USER,
            'email' => 'user@test.com',
        ]);

        Livewire::actingAs($admin)
            ->test(\App\Livewire\Admin\UserList::class)
            ->set('roleFilter', User::ROLE_USER)
            ->assertSee('user@test.com')
            ->assertDontSee('admin@test.com');
    }

    public function test_user_list_shows_user_role(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $user = User::factory()->create(['role' => User::ROLE_USER]);

        $response = $this->actingAs($admin)->get('/admin/users');

        $response->assertStatus(200);
        $response->assertSee('admin');
        $response->assertSee('user');
    }

    public function test_user_list_paginates_results(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        User::factory()->count(20)->create(['role' => User::ROLE_USER]);

        $component = Livewire::actingAs($admin)
            ->test(\App\Livewire\Admin\UserList::class);

        $users = $component->instance()->users;
        $this->assertEquals(15, $users->perPage());
    }
}
