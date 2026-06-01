<?php

namespace Tests\Feature;

use App\Jobs\GenerateMonthlyInvoices;
use App\Jobs\MarkOverdueInvoices;
use App\Models\Invoice;
use App\Models\PaymentType;
use App\Models\Student;
use App\Models\User;
use App\Notifications\InvoiceDueReminder;
use App\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class InvoiceAutomationTest extends TestCase
{
    use RefreshDatabase;

    public function test_generate_monthly_invoices_creates_invoices_for_all_students(): void
    {
        $paymentType = PaymentType::factory()->create(['is_recurring' => true, 'amount' => 500000]);
        $students = Student::factory(3)->create();

        $month = Carbon::create(2026, 6, 1);
        (new GenerateMonthlyInvoices($month))->handle();

        $this->assertDatabaseCount('invoices', 3);
        $this->assertDatabaseHas('invoices', [
            'payment_type_id' => $paymentType->id,
            'month' => '2026-06',
            'status' => 'pending',
        ]);
    }

    public function test_generate_monthly_invoices_is_idempotent(): void
    {
        PaymentType::factory()->create(['is_recurring' => true]);
        Student::factory(2)->create();

        $month = Carbon::create(2026, 6, 1);

        (new GenerateMonthlyInvoices($month))->handle();
        (new GenerateMonthlyInvoices($month))->handle(); // jalankan dua kali

        // Tetap hanya 2 invoice, tidak dobel
        $this->assertDatabaseCount('invoices', 2);
    }

    public function test_generate_skips_non_recurring_payment_types(): void
    {
        PaymentType::factory()->create(['is_recurring' => false]);
        Student::factory(2)->create();

        (new GenerateMonthlyInvoices(Carbon::now()))->handle();

        $this->assertDatabaseCount('invoices', 0);
    }

    public function test_mark_overdue_updates_past_due_invoices(): void
    {
        $overdueInvoice = Invoice::factory()->create([
            'status' => 'pending',
            'due_date' => now()->subDay(),
        ]);

        $pendingInvoice = Invoice::factory()->create([
            'status' => 'pending',
            'due_date' => now()->addDays(5),
        ]);

        $paidInvoice = Invoice::factory()->create([
            'status' => 'paid',
            'due_date' => now()->subDay(),
        ]);

        (new MarkOverdueInvoices)->handle();

        $this->assertDatabaseHas('invoices', ['id' => $overdueInvoice->id, 'status' => 'overdue']);
        $this->assertDatabaseHas('invoices', ['id' => $pendingInvoice->id, 'status' => 'pending']);
        $this->assertDatabaseHas('invoices', ['id' => $paidInvoice->id, 'status' => 'paid']);
    }

    public function test_reminder_notification_sent_for_upcoming_invoices(): void
    {
        Notification::fake();

        $student = Student::factory()->create();
        $user = User::factory()->create(['role' => Role::Siswa]);
        $student->update(['user_id' => $user->id]);

        Invoice::factory()->create([
            'student_id' => $student->id,
            'status' => 'pending',
            'due_date' => now()->addDays(3),
        ]);

        (new MarkOverdueInvoices)->handle();

        Notification::assertSentTo($user, InvoiceDueReminder::class, function ($notification) {
            return $notification->type === 'upcoming';
        });
    }

    public function test_no_invoices_generated_when_no_students(): void
    {
        PaymentType::factory()->create(['is_recurring' => true]);

        (new GenerateMonthlyInvoices(Carbon::now()))->handle();

        $this->assertDatabaseCount('invoices', 0);
    }

    public function test_reminder_notification_sent_for_newly_overdue_invoices(): void
    {
        Notification::fake();

        $student = Student::factory()->create();
        $user = User::factory()->create(['role' => Role::Siswa]);
        $student->update(['user_id' => $user->id]);

        // Invoice yang sudah lewat due_date (kemarin) → akan di-flip ke overdue
        Invoice::factory()->create([
            'student_id' => $student->id,
            'status' => 'pending',
            'due_date' => now()->subDay(),
        ]);

        (new MarkOverdueInvoices)->handle();

        // Status harus berubah ke overdue
        $this->assertDatabaseHas('invoices', ['student_id' => $student->id, 'status' => 'overdue']);

        // Notifikasi overdue harus terkirim
        Notification::assertSentTo($user, InvoiceDueReminder::class, function ($notification) {
            return $notification->type === 'overdue';
        });
    }

    public function test_overdue_reminder_not_sent_for_already_overdue_invoices(): void
    {
        Notification::fake();

        $student = Student::factory()->create();
        $user = User::factory()->create(['role' => Role::Siswa]);
        $student->update(['user_id' => $user->id]);

        // Invoice yang sudah berstatus overdue sebelumnya (bukan baru di-flip)
        Invoice::factory()->create([
            'student_id' => $student->id,
            'status' => 'overdue',
            'due_date' => now()->subDays(5),
        ]);

        (new MarkOverdueInvoices)->handle();

        // Tidak ada notifikasi baru untuk invoice yang sudah overdue sebelumnya
        Notification::assertNothingSent();
    }
}
