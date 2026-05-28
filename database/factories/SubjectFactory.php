<?php

namespace Database\Factories;

use App\Models\Subject;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Subject>
 */
class SubjectFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'name' => ucwords($name),
            'code' => strtoupper(substr(str_replace(' ', '', $name), 0, 6)).fake()->unique()->numerify('##'),
            'description' => fake()->optional()->sentence(),
        ];
    }
}
