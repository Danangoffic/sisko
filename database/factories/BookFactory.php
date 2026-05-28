<?php

namespace Database\Factories;

use App\Models\Book;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Book>
 */
class BookFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3),
            'author' => fake()->name(),
            'isbn' => fake()->unique()->isbn13(),
            'publisher' => fake()->company(),
            'year' => fake()->year(),
            'category' => fake()->randomElement(['Fiksi', 'Non-Fiksi', 'Sains', 'Sejarah', 'Agama']),
            'stock' => fake()->numberBetween(1, 20),
        ];
    }
}
