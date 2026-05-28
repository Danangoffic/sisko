<?php

namespace Database\Factories;

use App\Models\ReportCard;
use App\Models\Semester;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ReportCard>
 */
class ReportCardFactory extends Factory
{
    public function definition(): array
    {
        return [
            'student_id' => Student::factory(),
            'semester_id' => Semester::factory(),
            'average' => fake()->randomFloat(2, 50, 100),
            'rank' => fake()->numberBetween(1, 30),
            'teacher_notes' => fake()->optional()->sentence(),
        ];
    }
}
