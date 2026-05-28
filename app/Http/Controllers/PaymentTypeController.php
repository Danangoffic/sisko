<?php

namespace App\Http\Controllers;

use App\Models\PaymentType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class PaymentTypeController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('payment-types/index', [
            'paymentTypes' => PaymentType::latest()->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'amount' => ['required', 'numeric', 'min:0'],
            'is_recurring' => ['boolean'],
            'recurring_period' => ['nullable', Rule::in(['bulanan', 'semester', 'tahunan'])],
        ]);

        PaymentType::create($validated);

        return back()->with('success', 'Jenis pembayaran berhasil ditambahkan.');
    }

    public function update(Request $request, PaymentType $paymentType): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'amount' => ['required', 'numeric', 'min:0'],
            'is_recurring' => ['boolean'],
            'recurring_period' => ['nullable', Rule::in(['bulanan', 'semester', 'tahunan'])],
        ]);

        $paymentType->update($validated);

        return back()->with('success', 'Jenis pembayaran berhasil diperbarui.');
    }

    public function destroy(PaymentType $paymentType): RedirectResponse
    {
        $paymentType->delete();

        return back()->with('success', 'Jenis pembayaran berhasil dihapus.');
    }
}
