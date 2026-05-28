<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\PaymentType;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Student $student;

    private PaymentType $paymentType;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->admin()->create();
        $this->student = Student::factory()->create();
        $this->paymentType = PaymentType::factory()->create(['amount' => 150000]);
    }

    public function test_admin_can_view_invoices(): void
    {
        $response = $this->actingAs($this->admin)->get('/invoices');

        $this->assertNotEquals(403, $response->getStatusCode());
    }

    public function test_admin_can_create_invoice(): void
    {
        $this->actingAs($this->admin)
            ->post('/invoices', [
                'student_id' => $this->student->id,
                'payment_type_id' => $this->paymentType->id,
                'amount' => 150000,
                'due_date' => '2026-06-15',
                'month' => '2026-06',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('invoices', ['student_id' => $this->student->id, 'status' => 'pending']);
    }

    public function test_admin_can_pay_invoice(): void
    {
        $invoice = Invoice::factory()->create([
            'student_id' => $this->student->id,
            'payment_type_id' => $this->paymentType->id,
            'amount' => 150000,
        ]);

        $this->actingAs($this->admin)
            ->post("/invoices/{$invoice->id}/pay", [
                'amount' => 150000,
                'payment_method' => 'cash',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('invoices', ['id' => $invoice->id, 'status' => 'paid']);
        $this->assertDatabaseHas('payments', ['invoice_id' => $invoice->id, 'amount' => 150000]);
    }

    public function test_partial_payment_keeps_pending(): void
    {
        $invoice = Invoice::factory()->create([
            'student_id' => $this->student->id,
            'payment_type_id' => $this->paymentType->id,
            'amount' => 150000,
        ]);

        $this->actingAs($this->admin)
            ->post("/invoices/{$invoice->id}/pay", [
                'amount' => 50000,
                'payment_method' => 'transfer',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('invoices', ['id' => $invoice->id, 'status' => 'pending']);
    }

    public function test_admin_can_delete_invoice(): void
    {
        $invoice = Invoice::factory()->create([
            'student_id' => $this->student->id,
            'payment_type_id' => $this->paymentType->id,
        ]);

        $this->actingAs($this->admin)->delete("/invoices/{$invoice->id}")->assertRedirect();

        $this->assertDatabaseMissing('invoices', ['id' => $invoice->id]);
    }

    public function test_non_admin_cannot_access_invoices(): void
    {
        $guru = User::factory()->guru()->create();

        $this->actingAs($guru)->get('/invoices')->assertStatus(403);
    }
}
