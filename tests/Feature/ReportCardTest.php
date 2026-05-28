<?php

namespace Tests\Feature;

use App\Models\Grade;
use App\Models\ReportCard;
use App\Models\Semester;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use App\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportCardTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Semester $semester;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->admin()->create();
        $this->semester = Semester::factory()->create();
    }

    public function test_admin_can_view_report_cards(): void
    {
        $response = $this->actingAs($this->admin)->get('/report-cards');

        $this->assertNotEquals(403, $response->getStatusCode());
    }

    public function test_can_generate_report_cards(): void
    {
        $subject = Subject::factory()->create();
        $teacher = Teacher::factory()->create();
        $students = Student::factory(3)->create();

        foreach ($students as $student) {
            Grade::factory()->create([
                'student_id' => $student->id,
                'subject_id' => $subject->id,
                'semester_id' => $this->semester->id,
                'teacher_id' => $teacher->id,
                'score' => fake()->numberBetween(60, 100),
            ]);
        }

        $this->actingAs($this->admin)
            ->post('/report-cards/generate', [
                'semester_id' => $this->semester->id,
                'teacher_notes' => 'Semester berjalan baik.',
            ])
            ->assertRedirect();

        $this->assertDatabaseCount('report_cards', 3);
        // Check ranking exists
        $this->assertDatabaseHas('report_cards', ['rank' => 1]);
        $this->assertDatabaseHas('report_cards', ['rank' => 3]);
    }

    public function test_generate_fails_without_grades(): void
    {
        $this->actingAs($this->admin)
            ->post('/report-cards/generate', ['semester_id' => $this->semester->id])
            ->assertRedirect();

        $this->assertDatabaseCount('report_cards', 0);
    }

    public function test_can_update_report_card_notes(): void
    {
        $rc = ReportCard::factory()->create(['semester_id' => $this->semester->id]);

        $this->actingAs($this->admin)
            ->put("/report-cards/{$rc->id}", ['teacher_notes' => 'Perlu perbaikan.'])
            ->assertRedirect();

        $this->assertDatabaseHas('report_cards', ['id' => $rc->id, 'teacher_notes' => 'Perlu perbaikan.']);
    }

    public function test_can_delete_report_card(): void
    {
        $rc = ReportCard::factory()->create(['semester_id' => $this->semester->id]);

        $this->actingAs($this->admin)->delete("/report-cards/{$rc->id}")->assertRedirect();

        $this->assertDatabaseMissing('report_cards', ['id' => $rc->id]);
    }

    public function test_siswa_cannot_access_report_cards(): void
    {
        $siswa = User::factory()->create(['role' => Role::Siswa]);

        $this->actingAs($siswa)->get('/report-cards')->assertStatus(403);
    }
}
