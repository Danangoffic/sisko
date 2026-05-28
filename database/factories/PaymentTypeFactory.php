<?php

namespace Database\Factories;

use App\Models\PaymentType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PaymentType>
 */
class PaymentTypeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['SPP', 'Uang Gedung', 'Seragam', 'Buku']),
            'amount' => fake()->randomElement([150000, 500000, 300000, 200000]),
            'is_recurring' => false,
            'recurring_period' => null,
        ];
    }

    public function recurring(string $period = 'bulanan'): static
    {
        return $this->state(['is_recurring' => true, 'recurring_period' => $period]);
    }
}
