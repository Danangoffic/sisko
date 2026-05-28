<?php

namespace Database\Factories;

use App\Models\AcademicYear;
use App\Models\GradeConfig;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GradeConfig>
 */
class GradeConfigFactory extends Factory
{
    public function definition(): array
    {
        return [
            'academic_year_id' => AcademicYear::factory(),
            'type' => fake()->randomElement(['angka', 'huruf', 'deskripsi']),
            'passing_grade' => 70,
            'scale_max' => 100,
        ];
    }
}
