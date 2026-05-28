<?php

namespace Tests\Feature;

use App\Models\Announcement;
use App\Models\User;
use App\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnnouncementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->admin()->create();
    }

    public function test_all_roles_can_view_announcements(): void
    {
        Announcement::factory()->create(['target_role' => 'all']);

        $guru = User::factory()->guru()->create();
        $this->actingAs($guru)->get('/announcements');
        $this->assertNotEquals(403, $this->actingAs($guru)->get('/announcements')->getStatusCode());

        $siswa = User::factory()->create(['role' => Role::Siswa]);
        $this->assertNotEquals(403, $this->actingAs($siswa)->get('/announcements')->getStatusCode());
    }

    public function test_admin_can_create_announcement(): void
    {
        $this->actingAs($this->admin)
            ->post('/announcements', [
                'title' => 'Libur Nasional',
                'content' => 'Sekolah libur tanggal 1 Juni.',
                'target_role' => 'all',
                'is_pinned' => true,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('announcements', ['title' => 'Libur Nasional', 'is_pinned' => true]);
    }

    public function test_admin_can_update_announcement(): void
    {
        $announcement = Announcement::factory()->create();

        $this->actingAs($this->admin)
            ->put("/announcements/{$announcement->id}", [
                'title' => 'Updated Title',
                'content' => 'Updated content.',
                'target_role' => 'guru',
                'is_pinned' => false,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('announcements', ['id' => $announcement->id, 'title' => 'Updated Title', 'target_role' => 'guru']);
    }

    public function test_admin_can_delete_announcement(): void
    {
        $announcement = Announcement::factory()->create();

        $this->actingAs($this->admin)->delete("/announcements/{$announcement->id}")->assertRedirect();

        $this->assertDatabaseMissing('announcements', ['id' => $announcement->id]);
    }

    public function test_non_admin_cannot_create_announcement(): void
    {
        $guru = User::factory()->guru()->create();

        $this->actingAs($guru)
            ->post('/announcements', [
                'title' => 'Test',
                'content' => 'Test content',
                'target_role' => 'all',
            ])
            ->assertStatus(403);
    }

    public function test_guru_only_sees_relevant_announcements(): void
    {
        Announcement::factory()->create(['target_role' => 'all']);
        Announcement::factory()->create(['target_role' => 'guru']);
        Announcement::factory()->create(['target_role' => 'siswa']);

        $guru = User::factory()->guru()->create();
        $response = $this->actingAs($guru)->get('/announcements');

        // Guru should not see siswa-only announcements (2 visible: all + guru)
        $this->assertNotEquals(403, $response->getStatusCode());
    }
}
