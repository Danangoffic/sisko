<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Grade;
use App\Models\GradeConfig;
use App\Models\Semester;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use App\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GradeTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private array $base;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->admin()->create();
        $this->base = [
            'subject_id' => Subject::factory()->create()->id,
            'semester_id' => Semester::factory()->create()->id,
            'teacher_id' => Teacher::factory()->create()->id,
            'type' => 'uts',
        ];
    }

    public function test_admin_can_view_grades(): void
    {
        $response = $this->actingAs($this->admin)->get('/grades');

        $this->assertNotEquals(403, $response->getStatusCode());
    }

    public function test_can_store_batch_grades(): void
    {
        $students = Student::factory(2)->create();

        $this->actingAs($this->admin)
            ->post('/grades', array_merge($this->base, [
                'records' => $students->map(fn ($s) => [
                    'student_id' => $s->id,
                    'score' => 85,
                    'letter_grade' => 'A',
                    'description' => '',
                ])->toArray(),
            ]))
            ->assertRedirect();

        $this->assertDatabaseCount('grades', 2);
    }

    public function test_store_upserts_existing_grade(): void
    {
        $student = Student::factory()->create();

        $payload = array_merge($this->base, [
            'records' => [['student_id' => $student->id, 'score' => 70, 'letter_grade' => 'B', 'description' => '']],
        ]);

        $this->actingAs($this->admin)->post('/grades', $payload);
        $this->actingAs($this->admin)->post('/grades', array_merge($this->base, [
            'records' => [['student_id' => $student->id, 'score' => 90, 'letter_grade' => 'A', 'description' => '']],
        ]))->assertRedirect();

        $this->assertDatabaseCount('grades', 1);
        $this->assertDatabaseHas('grades', ['student_id' => $student->id, 'score' => 90]);
    }

    public function test_can_update_grade(): void
    {
        $grade = Grade::factory()->create();

        $this->actingAs($this->admin)
            ->put("/grades/{$grade->id}", ['score' => 95, 'letter_grade' => 'A', 'description' => 'Excellent'])
            ->assertRedirect();

        $this->assertDatabaseHas('grades', ['id' => $grade->id, 'score' => 95]);
    }

    public function test_can_delete_grade(): void
    {
        $grade = Grade::factory()->create();

        $this->actingAs($this->admin)->delete("/grades/{$grade->id}")->assertRedirect();

        $this->assertDatabaseMissing('grades', ['id' => $grade->id]);
    }

    public function test_siswa_cannot_access_grades(): void
    {
        $siswa = User::factory()->create(['role' => Role::Siswa]);

        $this->actingAs($siswa)->get('/grades')->assertStatus(403);
    }

    public function test_guru_can_only_input_grades_as_themselves(): void
    {
        $guruUser = User::factory()->guru()->create();
        $teacher = Teacher::factory()->create(['user_id' => $guruUser->id]);
        $otherTeacher = Teacher::factory()->create();
        $student = Student::factory()->create();

        // Guru mencoba input nilai dengan teacher_id orang lain → redirect dengan session error
        $this->actingAs($guruUser)
            ->post('/grades', array_merge($this->base, [
                'teacher_id' => $otherTeacher->id,
                'records' => [['student_id' => $student->id, 'score' => 80, 'letter_grade' => '', 'description' => '']],
            ]))
            ->assertRedirect()
            ->assertSessionHasErrors(['teacher_id']);
    }

    public function test_guru_without_teacher_profile_is_denied_grade_access_and_mutation(): void
    {
        $guruUser = User::factory()->guru()->create();
        $grade = Grade::factory()->create();
        $student = Student::factory()->create();

        $this->actingAs($guruUser)->get('/grades')->assertStatus(403);

        $this->actingAs($guruUser)
            ->post('/grades', array_merge($this->base, [
                'records' => [[
                    'student_id' => $student->id,
                    'score' => 80,
                    'letter_grade' => '',
                    'description' => '',
                ]],
            ]))
            ->assertStatus(403);

        $this->actingAs($guruUser)
            ->put("/grades/{$grade->id}", [
                'score' => 90,
                'letter_grade' => 'A',
                'description' => 'Updated',
            ])
            ->assertStatus(403);

        $this->actingAs($guruUser)
            ->delete("/grades/{$grade->id}")
            ->assertStatus(403);
    }

    public function test_guru_can_input_grades_as_themselves(): void
    {
        $guruUser = User::factory()->guru()->create();
        $teacher = Teacher::factory()->create(['user_id' => $guruUser->id]);
        $student = Student::factory()->create();

        $this->actingAs($guruUser)
            ->post('/grades', array_merge($this->base, [
                'teacher_id' => $teacher->id,
                'records' => [['student_id' => $student->id, 'score' => 80, 'letter_grade' => '', 'description' => '']],
            ]))
            ->assertRedirect();

        $this->assertDatabaseHas('grades', ['student_id' => $student->id, 'score' => 80]);
    }

    public function test_update_respects_grade_config_scale_max(): void
    {
        $academicYear = AcademicYear::factory()->create();
        $semester = Semester::factory()->create(['academic_year_id' => $academicYear->id]);
        GradeConfig::factory()->create(['academic_year_id' => $academicYear->id, 'scale_max' => 10]);

        $grade = Grade::factory()->create(['semester_id' => $semester->id, 'score' => 8]);

        // Score melebihi scale_max=10 → harus ditolak
        $this->actingAs($this->admin)
            ->put("/grades/{$grade->id}", ['score' => 11, 'letter_grade' => '', 'description' => ''])
            ->assertSessionHasErrors(['score']);

        // Score valid dalam scale_max=10 → harus diterima
        $this->actingAs($this->admin)
            ->put("/grades/{$grade->id}", ['score' => 9, 'letter_grade' => '', 'description' => ''])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('grades', ['id' => $grade->id, 'score' => 9]);
    }

    public function test_update_overwrites_stale_letter_grade_from_grade_config(): void
    {
        $academicYear = AcademicYear::factory()->create();
        $semester = Semester::factory()->create(['academic_year_id' => $academicYear->id]);
        GradeConfig::factory()->create(['academic_year_id' => $academicYear->id, 'scale_max' => 100]);

        $grade = Grade::factory()->create(['semester_id' => $semester->id, 'score' => 70, 'letter_grade' => 'B']);

        // Letter lama ikut terkirim dari form edit, tetapi score berubah ke 90 → harus tetap jadi A
        $this->actingAs($this->admin)
            ->put("/grades/{$grade->id}", ['score' => 90, 'letter_grade' => 'B', 'description' => ''])
            ->assertRedirect();

        $this->assertDatabaseHas('grades', ['id' => $grade->id, 'score' => 90, 'letter_grade' => 'A']);
    }

    public function test_update_preserves_letter_grade_when_score_is_not_provided(): void
    {
        $academicYear = AcademicYear::factory()->create();
        $semester = Semester::factory()->create(['academic_year_id' => $academicYear->id]);
        GradeConfig::factory()->create(['academic_year_id' => $academicYear->id, 'scale_max' => 100]);

        $grade = Grade::factory()->create(['semester_id' => $semester->id, 'score' => 70, 'letter_grade' => 'B']);

        $this->actingAs($this->admin)
            ->put("/grades/{$grade->id}", ['description' => 'Updated note'])
            ->assertRedirect();

        $this->assertDatabaseHas('grades', [
            'id' => $grade->id,
            'score' => 70,
            'letter_grade' => 'B',
            'description' => 'Updated note',
        ]);
    }

    public function test_guru_cannot_update_other_teachers_grade(): void
    {
        $guruUser = User::factory()->guru()->create();
        Teacher::factory()->create(['user_id' => $guruUser->id]);
        $otherTeacher = Teacher::factory()->create();

        $grade = Grade::factory()->create(['teacher_id' => $otherTeacher->id]);

        $this->actingAs($guruUser)
            ->put("/grades/{$grade->id}", ['score' => 50, 'letter_grade' => '', 'description' => ''])
            ->assertSessionHasErrors(['grade']);
    }
}
