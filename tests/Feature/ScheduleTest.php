<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Schedule;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScheduleTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    /** @var array<string, mixed> */
    private array $base;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->admin()->create();
        $this->base = [
            'school_class_id' => SchoolClass::factory()->create()->id,
            'subject_id' => Subject::factory()->create()->id,
            'teacher_id' => Teacher::factory()->create()->id,
            'academic_year_id' => AcademicYear::factory()->create()->id,
            'day' => 'Senin',
            'start_time' => '07:00',
            'end_time' => '08:30',
        ];
    }

    public function test_admin_can_view_schedules(): void
    {
        $response = $this->actingAs($this->admin)->get('/schedules');

        $this->assertNotEquals(403, $response->getStatusCode());
    }

    public function test_admin_can_create_schedule(): void
    {
        $this->actingAs($this->admin)->post('/schedules', $this->base)->assertRedirect();

        $this->assertDatabaseHas('schedules', ['day' => 'Senin', 'start_time' => '07:00']);
    }

    public function test_admin_can_delete_schedule(): void
    {
        $schedule = Schedule::factory()->create($this->base);

        $this->actingAs($this->admin)->delete("/schedules/{$schedule->id}")->assertRedirect();

        $this->assertDatabaseMissing('schedules', ['id' => $schedule->id]);
    }

    public function test_class_conflict_is_rejected(): void
    {
        // Same class, same day, overlapping time
        Schedule::factory()->create($this->base);

        $response = $this->actingAs($this->admin)
            ->post('/schedules', array_merge($this->base, [
                'subject_id' => Subject::factory()->create()->id,
                'teacher_id' => Teacher::factory()->create()->id,
                'start_time' => '08:00',
                'end_time' => '09:30',
            ]));

        // ValidationException dari Inertia dikembalikan sebagai redirect dengan session errors
        $response->assertRedirect();
        $response->assertSessionHasErrors(['start_time']);
    }

    public function test_teacher_conflict_is_rejected(): void
    {
        // Same teacher, same day, overlapping time
        Schedule::factory()->create($this->base);

        $response = $this->actingAs($this->admin)
            ->post('/schedules', array_merge($this->base, [
                'school_class_id' => SchoolClass::factory()->create()->id,
                'subject_id' => Subject::factory()->create()->id,
                'start_time' => '07:30',
                'end_time' => '09:00',
            ]));

        $response->assertRedirect();
        $response->assertSessionHasErrors(['start_time']);
    }

    public function test_non_overlapping_schedule_is_allowed(): void
    {
        Schedule::factory()->create($this->base);

        $this->actingAs($this->admin)
            ->post('/schedules', array_merge($this->base, [
                'subject_id' => Subject::factory()->create()->id,
                'teacher_id' => Teacher::factory()->create()->id,
                'school_class_id' => SchoolClass::factory()->create()->id,
                'start_time' => '09:00',
                'end_time' => '10:30',
            ]))
            ->assertRedirect();

        $this->assertDatabaseCount('schedules', 2);
    }

    public function test_non_admin_cannot_access_schedules(): void
    {
        $guru = User::factory()->guru()->create();

        $this->actingAs($guru)->get('/schedules')->assertStatus(403);
    }
}
