<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Midtrans\Config;
use Midtrans\Notification;
use Midtrans\Snap;

class MidtransController extends Controller
{
    public function __construct()
    {
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = config('midtrans.is_sanitized');
        Config::$is3ds = config('midtrans.is_3ds');
    }

    /**
     * Create Snap token for an invoice.
     */
    public function createSnapToken(Invoice $invoice): JsonResponse
    {
        $invoice->load(['student', 'paymentType']);

        $params = [
            'transaction_details' => [
                'order_id' => $invoice->invoice_number.'-'.time(),
                'gross_amount' => (int) $invoice->amount,
            ],
            'customer_details' => [
                'first_name' => $invoice->student->name,
                'email' => $invoice->student->user?->email ?? 'noemail@sisko.test',
            ],
            'item_details' => [
                [
                    'id' => $invoice->payment_type_id,
                    'price' => (int) $invoice->amount,
                    'quantity' => 1,
                    'name' => $invoice->paymentType->name.' - '.($invoice->month ?? 'One-time'),
                ],
            ],
        ];

        $snapToken = Snap::getSnapToken($params);

        return response()->json(['snap_token' => $snapToken]);
    }

    /**
     * Handle Midtrans notification callback.
     */
    public function notification(Request $request): JsonResponse
    {
        $notification = new Notification;

        $orderId = $notification->order_id;
        $transactionStatus = $notification->transaction_status;
        $fraudStatus = $notification->fraud_status;
        $transactionId = $notification->transaction_id;
        $paymentType = $notification->payment_type;
        $grossAmount = $notification->gross_amount;

        // Extract invoice_number from order_id (format: INV-XXXXXXXX-XXXX-timestamp)
        $invoiceNumber = preg_replace('/-\d+$/', '', $orderId);

        $invoice = Invoice::where('invoice_number', $invoiceNumber)->first();

        if (! $invoice) {
            return response()->json(['message' => 'Invoice not found'], 404);
        }

        if ($transactionStatus === 'capture' || $transactionStatus === 'settlement') {
            if ($fraudStatus === 'accept' || $transactionStatus === 'settlement') {
                Payment::create([
                    'invoice_id' => $invoice->id,
                    'amount' => (float) $grossAmount,
                    'payment_method' => 'midtrans_'.$paymentType,
                    'transaction_id' => $transactionId,
                    'paid_at' => now(),
                ]);

                $invoice->update(['status' => 'paid', 'paid_at' => now()]);
            }
        } elseif (in_array($transactionStatus, ['cancel', 'deny', 'expire'])) {
            if ($invoice->status !== 'paid') {
                $invoice->update(['status' => 'overdue']);
            }
        }

        return response()->json(['message' => 'OK']);
    }
}
