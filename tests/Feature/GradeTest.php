<?php

namespace Tests\Feature;

use App\Models\Grade;
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
}
