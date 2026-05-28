<?php

namespace Database\Factories;

use App\Models\Grade;
use App\Models\Semester;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Grade>
 */
class GradeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'student_id' => Student::factory(),
            'subject_id' => Subject::factory(),
            'semester_id' => Semester::factory(),
            'type' => fake()->randomElement(['tugas', 'uts', 'uas', 'praktik']),
            'score' => fake()->randomFloat(2, 0, 100),
            'letter_grade' => fake()->randomElement(['A', 'B', 'C', 'D', 'E']),
            'description' => fake()->optional()->sentence(),
            'teacher_id' => Teacher::factory(),
        ];
    }
}
