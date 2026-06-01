# 04 — Otomatisasi Keuangan (Jobs & Scheduler)

**Prioritas:** 🟠 Tinggi · **Effort:** Besar · **Status:** ⬜ Todo

## Objective

Mengotomatiskan siklus keuangan: generate invoice SPP bulanan otomatis, menandai invoice `overdue`, dan mengirim reminder menjelang/melewati jatuh tempo.

## Kondisi Saat Ini

- Invoice dibuat **manual satu per satu** via `InvoiceController@store`.
- Status `overdue` **tidak pernah di-set otomatis** (hanya berubah lewat callback Midtrans saat cancel/deny/expire).
- **Tidak ada** folder `app/Jobs`, `app/Notifications`, `app/Console`.
- `routes/console.php` masih default (`inspire`), tidak ada scheduler terdaftar.
- `PaymentType` punya kolom `is_recurring` & `recurring_period` tapi belum dimanfaatkan.

## Perubahan yang Dibutuhkan

1. **Job generate invoice bulanan**: untuk setiap `PaymentType` `is_recurring`, buat invoice untuk semua siswa aktif pada periode berjalan (hindari duplikat per `month`).
2. **Job tandai overdue**: ubah invoice `pending` yang lewat `due_date` menjadi `overdue`.
3. **Notification reminder**: kirim ke siswa/ortu untuk invoice mendekati jatuh tempo (mis. H-3) dan yang sudah overdue. Channel: database + mail (atau sesuai konfigurasi).
4. **Scheduler**: daftarkan job harian/bulanan di `routes/console.php` (atau `bootstrap/app.php` `->withSchedule`).

## File Terdampak

- `app/Jobs/GenerateMonthlyInvoices.php` (buat)
- `app/Jobs/MarkOverdueInvoices.php` (buat)
- `app/Notifications/InvoiceDueReminder.php` (buat)
- `app/Console/Commands/` atau `routes/console.php` (scheduler)
- `bootstrap/app.php` (jika scheduling didefinisikan di sini)
- `app/Models/PaymentType.php`, `Invoice.php` (helper/scopes bila perlu)

## Langkah Implementasi

1. `php artisan make:job GenerateMonthlyInvoices --no-interaction`.
2. `php artisan make:job MarkOverdueInvoices --no-interaction`.
3. `php artisan make:notification InvoiceDueReminder --no-interaction`.
4. Implementasikan logika idempoten (cek `month` + `student_id` + `payment_type_id` agar tidak dobel).
5. Daftarkan schedule:
   - `GenerateMonthlyInvoices` → bulanan (mis. tanggal 1).
   - `MarkOverdueInvoices` → harian.
   - reminder → harian.
6. Verifikasi via `php artisan schedule:list`.

## Testing

- Buat `tests/Feature/InvoiceAutomationTest.php`:
  - [ ] Generate bulanan membuat invoice untuk semua siswa aktif, tanpa duplikat saat dijalankan dua kali.
  - [ ] Invoice lewat due date berubah jadi `overdue`; yang `paid` tidak terpengaruh.
  - [ ] Notifikasi reminder ter-`fake` dan terkirim ke target yang benar (`Notification::fake()`).
- Jalankan: `php artisan test --compact --filter=InvoiceAutomation`

## Acceptance Criteria

- [ ] Invoice SPP bisa di-generate otomatis & idempoten.
- [ ] Status `overdue` ter-update otomatis harian.
- [ ] Reminder terkirim untuk invoice mendekati/lewat jatuh tempo.
- [ ] Job terdaftar dan muncul di `schedule:list`.
- [ ] Semua jalur ditutup test.
