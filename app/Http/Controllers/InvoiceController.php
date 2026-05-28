<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PaymentType;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class InvoiceController extends Controller
{
    public function index(): Response
    {
        $invoices = Invoice::with(['student', 'paymentType', 'payments'])
            ->latest()
            ->paginate(20);

        return Inertia::render('invoices/index', [
            'invoices' => $invoices,
            'students' => Student::orderBy('name')->get(['id', 'name', 'nisn']),
            'paymentTypes' => PaymentType::orderBy('name')->get(['id', 'name', 'amount']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'payment_type_id' => ['required', 'exists:payment_types,id'],
            'amount' => ['required', 'numeric', 'min:0'],
            'due_date' => ['required', 'date'],
            'month' => ['nullable', 'string', 'max:7'],
        ]);

        $validated['invoice_number'] = 'INV-'.now()->format('Ymd').'-'.str_pad((string) (Invoice::count() + 1), 4, '0', STR_PAD_LEFT);
        $validated['status'] = 'pending';

        Invoice::create($validated);

        return back()->with('success', 'Invoice berhasil dibuat.');
    }

    /**
     * Record a payment for an invoice.
     */
    public function pay(Request $request, Invoice $invoice): RedirectResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:1'],
            'payment_method' => ['required', 'string', 'max:50'],
            'transaction_id' => ['nullable', 'string', 'max:100'],
        ]);

        DB::transaction(function () use ($validated, $invoice): void {
            Payment::create([
                'invoice_id' => $invoice->id,
                'amount' => $validated['amount'],
                'payment_method' => $validated['payment_method'],
                'transaction_id' => $validated['transaction_id'] ?? null,
                'paid_at' => now(),
            ]);

            $totalPaid = $invoice->payments()->sum('amount') + $validated['amount'];

            if ($totalPaid >= $invoice->amount) {
                $invoice->update(['status' => 'paid', 'paid_at' => now()]);
            }
        });

        return back()->with('success', 'Pembayaran berhasil dicatat.');
    }

    public function destroy(Invoice $invoice): RedirectResponse
    {
        $invoice->delete();

        return back()->with('success', 'Invoice berhasil dihapus.');
    }
}
