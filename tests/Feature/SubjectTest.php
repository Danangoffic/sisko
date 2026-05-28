<?php

namespace Tests\Feature;

use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubjectTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->admin()->create();
    }

    public function test_admin_can_view_subjects(): void
    {
        $response = $this->actingAs($this->admin)->get('/subjects');

        $this->assertNotEquals(403, $response->getStatusCode());
    }

    public function test_admin_can_create_subject(): void
    {
        $this->actingAs($this->admin)
            ->post('/subjects', ['name' => 'Matematika', 'code' => 'MTK'])
            ->assertRedirect();

        $this->assertDatabaseHas('subjects', ['code' => 'MTK']);
    }

    public function test_admin_can_update_subject(): void
    {
        $subject = Subject::factory()->create();

        $this->actingAs($this->admin)
            ->put("/subjects/{$subject->id}", ['name' => 'Fisika', 'code' => 'FIS'])
            ->assertRedirect();

        $this->assertDatabaseHas('subjects', ['id' => $subject->id, 'name' => 'Fisika']);
    }

    public function test_admin_can_delete_subject(): void
    {
        $subject = Subject::factory()->create();

        $this->actingAs($this->admin)->delete("/subjects/{$subject->id}")->assertRedirect();

        $this->assertDatabaseMissing('subjects', ['id' => $subject->id]);
    }

    public function test_subject_code_must_be_unique(): void
    {
        Subject::factory()->create(['code' => 'MTK']);

        $this->actingAs($this->admin)
            ->post('/subjects', ['name' => 'Matematika 2', 'code' => 'MTK'])
            ->assertSessionHasErrors('code');
    }

    public function test_non_admin_cannot_access_subjects(): void
    {
        $guru = User::factory()->guru()->create();

        $this->actingAs($guru)->get('/subjects')->assertStatus(403);
    }
}
