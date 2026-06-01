<?php

namespace App\Notifications;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InvoiceDueReminder extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Invoice $invoice,
        public readonly string $type // 'upcoming' | 'overdue'
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(mixed $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(mixed $notifiable): MailMessage
    {
        $amount = number_format((float) $this->invoice->amount, 0, ',', '.');
        $dueDate = $this->invoice->due_date->format('d/m/Y');
        $paymentName = $this->invoice->paymentType->name;

        if ($this->type === 'upcoming') {
            return (new MailMessage)
                ->subject("Pengingat: Tagihan {$paymentName} Jatuh Tempo 3 Hari Lagi")
                ->greeting("Halo, {$notifiable->name}!")
                ->line("Tagihan **{$paymentName}** sebesar **Rp {$amount}** akan jatuh tempo pada **{$dueDate}**.")
                ->line('Segera lakukan pembayaran untuk menghindari keterlambatan.')
                ->action('Lihat Tagihan', url('/portal/invoices'))
                ->line('Terima kasih.');
        }

        return (new MailMessage)
            ->subject("Tagihan {$paymentName} Telah Jatuh Tempo")
            ->greeting("Halo, {$notifiable->name}!")
            ->line("Tagihan **{$paymentName}** sebesar **Rp {$amount}** telah melewati jatuh tempo ({$dueDate}).")
            ->line('Harap segera melakukan pembayaran.')
            ->action('Lihat Tagihan', url('/portal/invoices'))
            ->line('Terima kasih.');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(mixed $notifiable): array
    {
        return [
            'type' => $this->type,
            'invoice_id' => $this->invoice->id,
            'invoice_number' => $this->invoice->invoice_number,
            'payment_type' => $this->invoice->paymentType->name,
            'amount' => $this->invoice->amount,
            'due_date' => $this->invoice->due_date->toDateString(),
        ];
    }
}
