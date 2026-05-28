<?php

namespace Database\Factories;

use App\Models\SchoolClass;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SchoolClass>
 */
class SchoolClassFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['VII', 'VIII', 'IX']).' '.fake()->randomLetter(),
            'homeroom_teacher' => fake()->name(),
        ];
    }
}
