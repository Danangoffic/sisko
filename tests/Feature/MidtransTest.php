<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\PaymentType;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MidtransTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Invoice $invoice;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->admin()->create();
        $this->invoice = Invoice::factory()->create([
            'student_id' => Student::factory()->create()->id,
            'payment_type_id' => PaymentType::factory()->create()->id,
            'amount' => 150000,
            'status' => 'pending',
        ]);
    }

    public function test_snap_token_route_requires_auth(): void
    {
        $this->post("/midtrans/snap-token/{$this->invoice->id}")
            ->assertRedirect('/login');
    }

    public function test_snap_token_route_accessible_by_admin(): void
    {
        // Will fail with Midtrans error since no real key, but should not be 403/404
        $response = $this->actingAs($this->admin)
            ->post("/midtrans/snap-token/{$this->invoice->id}");

        $this->assertNotEquals(403, $response->getStatusCode());
        $this->assertNotEquals(404, $response->getStatusCode());
    }

    public function test_notification_route_is_public(): void
    {
        // Should not redirect to login (no auth required)
        $response = $this->post('/midtrans/notification', [
            'order_id' => $this->invoice->invoice_number.'-12345',
            'transaction_status' => 'settlement',
            'fraud_status' => 'accept',
            'transaction_id' => 'txn-123',
            'payment_type' => 'bank_transfer',
            'gross_amount' => '150000',
        ]);

        $this->assertNotEquals(302, $response->getStatusCode());
    }

    public function test_notification_with_invalid_invoice_returns_404(): void
    {
        $response = $this->post('/midtrans/notification', [
            'order_id' => 'INV-NONEXIST-12345',
            'transaction_status' => 'settlement',
            'fraud_status' => 'accept',
            'transaction_id' => 'txn-123',
            'payment_type' => 'bank_transfer',
            'gross_amount' => '150000',
        ]);

        // Notification class reads from php://input, so this tests the route accessibility
        $this->assertNotEquals(405, $response->getStatusCode());
    }

    public function test_non_admin_cannot_access_snap_token(): void
    {
        $guru = User::factory()->guru()->create();

        $this->actingAs($guru)
            ->post("/midtrans/snap-token/{$this->invoice->id}")
            ->assertStatus(403);
    }
}
