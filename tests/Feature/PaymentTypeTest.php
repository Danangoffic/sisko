<?php

namespace Tests\Feature;

use App\Models\PaymentType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentTypeTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->admin()->create();
    }

    public function test_admin_can_view_payment_types(): void
    {
        $response = $this->actingAs($this->admin)->get('/payment-types');

        $this->assertNotEquals(403, $response->getStatusCode());
    }

    public function test_admin_can_create_payment_type(): void
    {
        $this->actingAs($this->admin)
            ->post('/payment-types', [
                'name' => 'SPP',
                'amount' => 150000,
                'is_recurring' => true,
                'recurring_period' => 'bulanan',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('payment_types', ['name' => 'SPP', 'is_recurring' => true]);
    }

    public function test_admin_can_update_payment_type(): void
    {
        $pt = PaymentType::factory()->create();

        $this->actingAs($this->admin)
            ->put("/payment-types/{$pt->id}", [
                'name' => 'Uang Gedung',
                'amount' => 500000,
                'is_recurring' => false,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('payment_types', ['id' => $pt->id, 'name' => 'Uang Gedung']);
    }

    public function test_admin_can_delete_payment_type(): void
    {
        $pt = PaymentType::factory()->create();

        $this->actingAs($this->admin)->delete("/payment-types/{$pt->id}")->assertRedirect();

        $this->assertDatabaseMissing('payment_types', ['id' => $pt->id]);
    }

    public function test_non_admin_cannot_access_payment_types(): void
    {
        $guru = User::factory()->guru()->create();

        $this->actingAs($guru)->get('/payment-types')->assertStatus(403);
    }
}
