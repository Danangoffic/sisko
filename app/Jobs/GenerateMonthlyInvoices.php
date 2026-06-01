<?php

namespace App\Jobs;

use App\Models\Invoice;
use App\Models\PaymentType;
use App\Models\Student;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Carbon;

class GenerateMonthlyInvoices implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Carbon $forMonth
    ) {}

    public function handle(): void
    {
        $monthKey = $this->forMonth->format('Y-m');
        $dueDate = $this->forMonth->copy()->endOfMonth();

        $recurringTypes = PaymentType::where('is_recurring', true)->get();

        if ($recurringTypes->isEmpty()) {
            return;
        }

        $students = Student::all(['id', 'name']);

        foreach ($recurringTypes as $paymentType) {
            foreach ($students as $student) {
                // Idempoten: skip jika sudah ada invoice bulan ini
                $exists = Invoice::where('student_id', $student->id)
                    ->where('payment_type_id', $paymentType->id)
                    ->where('month', $monthKey)
                    ->exists();

                if ($exists) {
                    continue;
                }

                $invoiceNumber = 'INV-'.$this->forMonth->format('Ym').'-'
                    .str_pad((string) (Invoice::count() + 1), 4, '0', STR_PAD_LEFT);

                Invoice::create([
                    'student_id' => $student->id,
                    'payment_type_id' => $paymentType->id,
                    'invoice_number' => $invoiceNumber,
                    'amount' => $paymentType->amount,
                    'due_date' => $dueDate,
                    'status' => 'pending',
                    'month' => $monthKey,
                ]);
            }
        }
    }
}
