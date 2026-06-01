<?php

use App\Jobs\GenerateMonthlyInvoices;
use App\Jobs\MarkOverdueInvoices;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Generate invoice SPP bulanan pada tanggal 1 setiap bulan
Schedule::job(new GenerateMonthlyInvoices(Carbon::now()->startOfMonth()))
    ->monthlyOn(1, '06:00')
    ->name('generate-monthly-invoices')
    ->withoutOverlapping();

// Tandai invoice overdue & kirim reminder setiap hari pukul 07:00
Schedule::job(new MarkOverdueInvoices)
    ->dailyAt('07:00')
    ->name('mark-overdue-invoices')
    ->withoutOverlapping();
