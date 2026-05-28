<?php

namespace Database\Factories;

use App\Models\AcademicYear;
use App\Models\ClassPromotion;
use App\Models\SchoolClass;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ClassPromotion>
 */
class ClassPromotionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'student_id' => Student::factory(),
            'from_class_id' => SchoolClass::factory(),
            'to_class_id' => SchoolClass::factory(),
            'academic_year_id' => AcademicYear::factory(),
            'status' => fake()->randomElement(['naik', 'tinggal', 'lulus']),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
