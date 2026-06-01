<?php

namespace Tests\Feature;

use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use App\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page(): void
    {
        $response = $this->get(route('dashboard'));
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_visit_the_dashboard(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('dashboard'));
        $response->assertOk();
    }

    public function test_admin_dashboard_receives_stats(): void
    {
        $admin = User::factory()->admin()->create();
        Student::factory(3)->create();
        Teacher::factory(2)->create();
        $classCount = SchoolClass::count();

        $response = $this->actingAs($admin)->get(route('dashboard'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('dashboard')
            ->has('stats')
            ->where('stats.total_siswa', 3)
            ->where('stats.total_guru', 2)
            ->where('stats.total_kelas', $classCount)
        );
    }

    public function test_guru_dashboard_receives_stats(): void
    {
        $guru = User::factory()->guru()->create();

        $response = $this->actingAs($guru)->get(route('dashboard'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('dashboard')
            ->has('stats')
        );
    }

    public function test_siswa_dashboard_receives_stats(): void
    {
        $siswaUser = User::factory()->create(['role' => Role::Siswa]);
        $class = SchoolClass::factory()->create();
        Student::factory()->create(['user_id' => $siswaUser->id, 'school_class_id' => $class->id]);

        $response = $this->actingAs($siswaUser)->get(route('dashboard'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('dashboard')
            ->has('stats')
            ->where('stats.tagihan_pending', 0)
        );
    }

    public function test_dashboard_includes_announcements(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get(route('dashboard'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('dashboard')
            ->has('announcements')
        );
    }
}
