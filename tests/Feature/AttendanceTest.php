<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;
use App\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private SchoolClass $class;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->admin()->create();
        $this->class = SchoolClass::factory()->create();
    }

    public function test_admin_can_view_attendances(): void
    {
        $response = $this->actingAs($this->admin)->get('/attendances');

        $this->assertNotEquals(403, $response->getStatusCode());
    }

    public function test_guru_can_view_attendances(): void
    {
        $guru = User::factory()->guru()->create();

        $response = $this->actingAs($guru)->get('/attendances');

        $this->assertNotEquals(403, $response->getStatusCode());
    }

    public function test_admin_can_store_batch_attendance(): void
    {
        $students = Student::factory(3)->create(['school_class_id' => $this->class->id]);

        $records = $students->map(fn ($s) => [
            'student_id' => $s->id,
            'status' => 'hadir',
            'note' => '',
        ])->toArray();

        $this->actingAs($this->admin)
            ->post('/attendances', [
                'school_class_id' => $this->class->id,
                'date' => '2026-05-28',
                'records' => $records,
            ])
            ->assertRedirect();

        $this->assertDatabaseCount('attendances', 3);
    }

    public function test_store_upserts_existing_attendance(): void
    {
        $student = Student::factory()->create(['school_class_id' => $this->class->id]);

        $this->actingAs($this->admin)->post('/attendances', [
            'school_class_id' => $this->class->id,
            'date' => '2026-05-28',
            'records' => [['student_id' => $student->id, 'status' => 'hadir', 'note' => '']],
        ]);

        $this->actingAs($this->admin)->post('/attendances', [
            'school_class_id' => $this->class->id,
            'date' => '2026-05-28',
            'records' => [['student_id' => $student->id, 'status' => 'alpha', 'note' => 'Tanpa keterangan']],
        ])->assertRedirect();

        $this->assertDatabaseCount('attendances', 1);
        $this->assertDatabaseHas('attendances', ['student_id' => $student->id, 'status' => 'alpha']);
    }

    public function test_admin_can_delete_attendance(): void
    {
        $student = Student::factory()->create(['school_class_id' => $this->class->id]);
        $attendance = Attendance::create([
            'student_id' => $student->id,
            'school_class_id' => $this->class->id,
            'date' => '2026-05-28',
            'status' => 'hadir',
            'recorded_by' => $this->admin->id,
        ]);

        $this->actingAs($this->admin)->delete("/attendances/{$attendance->id}")->assertRedirect();

        $this->assertDatabaseMissing('attendances', ['id' => $attendance->id]);
    }

    public function test_siswa_cannot_access_attendances(): void
    {
        $siswa = User::factory()->create(['role' => Role::Siswa]);

        $this->actingAs($siswa)->get('/attendances')->assertStatus(403);
    }

    public function test_store_validates_records(): void
    {
        $this->actingAs($this->admin)
            ->post('/attendances', [
                'school_class_id' => $this->class->id,
                'date' => '2026-05-28',
                'records' => [],
            ])
            ->assertSessionHasErrors('records');
    }
}
