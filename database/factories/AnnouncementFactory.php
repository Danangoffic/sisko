<?php

namespace Database\Factories;

use App\Models\Announcement;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Announcement>
 */
class AnnouncementFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(),
            'content' => fake()->paragraphs(2, true),
            'author_id' => User::factory()->admin(),
            'target_role' => fake()->randomElement(['all', 'guru', 'siswa']),
            'is_pinned' => false,
            'published_at' => now(),
        ];
    }

    public function pinned(): static
    {
        return $this->state(['is_pinned' => true]);
    }
}
