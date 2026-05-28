<?php

namespace Database\Factories;

use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'invoice_id' => Invoice::factory(),
            'amount' => fake()->randomElement([150000, 500000]),
            'payment_method' => fake()->randomElement(['cash', 'transfer', 'midtrans']),
            'transaction_id' => fake()->optional()->uuid(),
            'paid_at' => now(),
        ];
    }
}
