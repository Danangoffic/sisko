<?php

namespace Database\Factories;

use App\Models\Invoice;
use App\Models\PaymentType;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Invoice>
 */
class InvoiceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'student_id' => Student::factory(),
            'payment_type_id' => PaymentType::factory(),
            'invoice_number' => 'INV-'.fake()->unique()->numerify('########'),
            'amount' => fake()->randomElement([150000, 500000]),
            'due_date' => fake()->dateTimeBetween('now', '+30 days')->format('Y-m-d'),
            'status' => 'pending',
            'paid_at' => null,
            'month' => now()->format('Y-m'),
        ];
    }

    public function paid(): static
    {
        return $this->state(['status' => 'paid', 'paid_at' => now()]);
    }

    public function overdue(): static
    {
        return $this->state([
            'status' => 'overdue',
            'due_date' => now()->subDays(7)->format('Y-m-d'),
        ]);
    }
}
