<?php

namespace Database\Factories;

use App\Models\Attendance;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Attendance>
 */
class AttendanceFactory extends Factory
{
    public function definition(): array
    {
        $class = SchoolClass::factory()->create();

        return [
            'student_id' => Student::factory()->state(['school_class_id' => $class->id]),
            'school_class_id' => $class->id,
            'schedule_id' => null,
            'date' => fake()->date(),
            'status' => fake()->randomElement(['hadir', 'izin', 'sakit', 'alpha']),
            'note' => fake()->optional()->sentence(),
            'recorded_by' => User::factory()->admin(),
        ];
    }
}
