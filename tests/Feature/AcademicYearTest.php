<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Semester;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AcademicYearTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->admin()->create();
    }

    public function test_admin_can_view_academic_years(): void
    {
        AcademicYear::factory(2)->create();

        $response = $this->actingAs($this->admin)->get('/academic-years');

        $this->assertNotEquals(403, $response->getStatusCode());
    }

    public function test_admin_can_create_academic_year(): void
    {
        $this->actingAs($this->admin)
            ->post('/academic-years', [
                'name' => '2025/2026',
                'start_date' => '2025-07-01',
                'end_date' => '2026-06-30',
                'is_active' => false,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('academic_years', ['name' => '2025/2026']);
    }

    public function test_setting_active_deactivates_others(): void
    {
        $existing = AcademicYear::factory()->active()->create();

        $this->actingAs($this->admin)
            ->post('/academic-years', [
                'name' => '2026/2027',
                'start_date' => '2026-07-01',
                'end_date' => '2027-06-30',
                'is_active' => true,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('academic_years', ['id' => $existing->id, 'is_active' => false]);
        $this->assertDatabaseHas('academic_years', ['name' => '2026/2027', 'is_active' => true]);
    }

    public function test_admin_can_update_academic_year(): void
    {
        $ay = AcademicYear::factory()->create();

        $this->actingAs($this->admin)
            ->put("/academic-years/{$ay->id}", [
                'name' => '2024/2025',
                'start_date' => '2024-07-01',
                'end_date' => '2025-06-30',
                'is_active' => false,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('academic_years', ['id' => $ay->id, 'name' => '2024/2025']);
    }

    public function test_admin_can_delete_academic_year(): void
    {
        $ay = AcademicYear::factory()->create();

        $this->actingAs($this->admin)->delete("/academic-years/{$ay->id}")->assertRedirect();

        $this->assertDatabaseMissing('academic_years', ['id' => $ay->id]);
    }

    public function test_deleting_academic_year_cascades_to_semesters(): void
    {
        $ay = AcademicYear::factory()->create();
        $semester = Semester::factory()->create(['academic_year_id' => $ay->id]);

        $this->actingAs($this->admin)->delete("/academic-years/{$ay->id}")->assertRedirect();

        $this->assertDatabaseMissing('semesters', ['id' => $semester->id]);
    }

    public function test_admin_can_add_semester(): void
    {
        $ay = AcademicYear::factory()->create();

        $this->actingAs($this->admin)
            ->post("/academic-years/{$ay->id}/semesters", [
                'name' => 'Ganjil',
                'start_date' => '2025-07-01',
                'end_date' => '2025-12-31',
                'is_active' => false,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('semesters', ['academic_year_id' => $ay->id, 'name' => 'Ganjil']);
    }

    public function test_setting_active_semester_deactivates_others(): void
    {
        $ay = AcademicYear::factory()->create();
        $existing = Semester::factory()->active()->create(['academic_year_id' => $ay->id]);

        $ay2 = AcademicYear::factory()->create();
        $this->actingAs($this->admin)
            ->post("/academic-years/{$ay2->id}/semesters", [
                'name' => 'Genap',
                'start_date' => '2026-01-01',
                'end_date' => '2026-06-30',
                'is_active' => true,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('semesters', ['id' => $existing->id, 'is_active' => false]);
    }

    public function test_admin_can_update_semester(): void
    {
        $semester = Semester::factory()->create();

        $this->actingAs($this->admin)
            ->put("/semesters/{$semester->id}", [
                'name' => 'Genap',
                'start_date' => '2026-01-01',
                'end_date' => '2026-06-30',
                'is_active' => false,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('semesters', ['id' => $semester->id, 'name' => 'Genap']);
    }

    public function test_admin_can_delete_semester(): void
    {
        $semester = Semester::factory()->create();

        $this->actingAs($this->admin)->delete("/semesters/{$semester->id}")->assertRedirect();

        $this->assertDatabaseMissing('semesters', ['id' => $semester->id]);
    }

    public function test_non_admin_cannot_access_academic_years(): void
    {
        $guru = User::factory()->guru()->create();

        $this->actingAs($guru)->get('/academic-years')->assertStatus(403);
    }
}
