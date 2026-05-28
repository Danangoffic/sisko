<?php

namespace Tests\Feature;

use App\Models\Teacher;
use App\Models\User;
use App\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeacherTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->admin()->create();
    }

    public function test_admin_can_view_teachers(): void
    {
        Teacher::factory(3)->create();

        $response = $this->actingAs($this->admin)->get('/teachers');

        $this->assertNotEquals(403, $response->getStatusCode());
    }

    public function test_admin_can_create_teacher(): void
    {
        $this->actingAs($this->admin)
            ->post('/teachers', [
                'name' => 'Budi Santoso',
                'email' => 'budi@sisko.test',
                'nip' => '12345678901234567890',
                'phone' => '081234567890',
                'address' => 'Jl. Merdeka No. 1',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('users', ['email' => 'budi@sisko.test', 'role' => Role::Guru->value]);
        $this->assertDatabaseHas('teachers', ['nip' => '12345678901234567890']);
    }

    public function test_admin_can_update_teacher(): void
    {
        $teacher = Teacher::factory()->create();

        $this->actingAs($this->admin)
            ->put("/teachers/{$teacher->id}", [
                'name' => 'Nama Baru',
                'email' => $teacher->user->email,
                'nip' => $teacher->nip,
                'phone' => '089999999999',
                'address' => $teacher->address,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('users', ['id' => $teacher->user_id, 'name' => 'Nama Baru']);
    }

    public function test_admin_can_delete_teacher(): void
    {
        $teacher = Teacher::factory()->create();
        $userId = $teacher->user_id;

        $this->actingAs($this->admin)
            ->delete("/teachers/{$teacher->id}")
            ->assertRedirect();

        $this->assertDatabaseMissing('users', ['id' => $userId]);
        $this->assertDatabaseMissing('teachers', ['id' => $teacher->id]);
    }

    public function test_non_admin_cannot_access_teachers(): void
    {
        $guru = User::factory()->guru()->create();

        $this->actingAs($guru)->get('/teachers')->assertStatus(403);
    }

    public function test_teacher_creation_requires_unique_email(): void
    {
        $existing = User::factory()->create(['email' => 'existing@sisko.test']);

        $this->actingAs($this->admin)
            ->post('/teachers', [
                'name' => 'Test',
                'email' => 'existing@sisko.test',
            ])
            ->assertSessionHasErrors('email');
    }
}
