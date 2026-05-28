<?php

namespace Tests\Feature;

use App\Models\User;
use App\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RolePermissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_has_siswa_role_by_default(): void
    {
        $user = User::factory()->create();

        $this->assertEquals(Role::Siswa, $user->role);
    }

    public function test_admin_factory_state_creates_admin(): void
    {
        $admin = User::factory()->admin()->create();

        $this->assertEquals(Role::Admin, $admin->role);
        $this->assertTrue($admin->isAdmin());
        $this->assertFalse($admin->isGuru());
        $this->assertFalse($admin->isSiswa());
    }

    public function test_guru_factory_state_creates_guru(): void
    {
        $guru = User::factory()->guru()->create();

        $this->assertEquals(Role::Guru, $guru->role);
        $this->assertTrue($guru->isGuru());
    }

    public function test_role_middleware_allows_correct_role(): void
    {
        $admin = User::factory()->admin()->create();

        // Middleware allows admin through (403 would mean blocked, 200/302 means allowed)
        $response = $this->actingAs($admin)->get('/users');
        $this->assertNotEquals(403, $response->getStatusCode());
    }

    public function test_role_middleware_blocks_wrong_role(): void
    {
        $siswa = User::factory()->create(); // default: siswa

        $this->actingAs($siswa)
            ->get('/users')
            ->assertStatus(403);
    }

    public function test_role_middleware_blocks_unauthenticated(): void
    {
        $this->get('/users')->assertRedirect('/login');
    }
}
