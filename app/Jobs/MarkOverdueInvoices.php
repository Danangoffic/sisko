<?php

namespace App\Jobs;

use App\Models\Invoice;
use App\Notifications\InvoiceDueReminder;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class MarkOverdueInvoices implements ShouldQueue
{
    use Queueable;

    public function handle(): void
    {
        // Ambil invoice yang akan di-flip ke overdue sebelum di-update,
        // supaya kita bisa kirim notifikasi untuk yang baru saja jatuh tempo.
        $newlyOverdue = Invoice::with(['student.user', 'paymentType'])
            ->where('status', 'pending')
            ->whereDate('due_date', '<', today())
            ->get();

        // Tandai invoice yang melewati due_date sebagai overdue
        Invoice::where('status', 'pending')
            ->whereDate('due_date', '<', today())
            ->update(['status' => 'overdue']);

        // Kirim reminder untuk invoice yang baru saja overdue
        foreach ($newlyOverdue as $invoice) {
            $user = $invoice->student?->user;
            if ($user) {
                $user->notify(new InvoiceDueReminder($invoice, 'overdue'));
            }
        }

        // Kirim reminder untuk invoice yang jatuh tempo dalam 3 hari
        $upcoming = Invoice::with(['student.user', 'paymentType'])
            ->where('status', 'pending')
            ->whereDate('due_date', today()->addDays(3))
            ->get();

        foreach ($upcoming as $invoice) {
            $user = $invoice->student?->user;
            if ($user) {
                $user->notify(new InvoiceDueReminder($invoice, 'upcoming'));
            }
        }
    }
}
