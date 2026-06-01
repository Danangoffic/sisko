# 07 — Perbaikan Validasi & UX

**Prioritas:** 🟡 Sedang · **Effort:** Kecil · **Status:** ⬜ Todo

## Objective

Memperhalus penanganan error dan alur input agar pesan validasi muncul rapi di form Inertia dan kredensial akun dikelola dengan aman.

## Kondisi Saat Ini

### A. Schedule conflict pakai `abort(422)` mentah

`ScheduleController::checkConflict` memanggil `abort(422, '...')`. Pada Inertia, ini tidak ter-map ke `errors` bag sehingga pesan tidak muncul di field form secara natural.

### B. Password siswa hardcode `'password'`

`StudentController` membuat user siswa dengan `Hash::make('password')` dan `email_verified_at = now()`. Tidak ada mekanisme set/kirim/ubah kredensial — kredensial seragam dan tidak aman.

## Perubahan yang Dibutuhkan

### A. Validasi bentrok jadwal

1. Ganti `abort(422, ...)` dengan `ValidationException::withMessages([...])` agar masuk ke error bag field terkait (mis. `start_time`).
2. Pastikan frontend menampilkan error tersebut via `InputError`.

### B. Kredensial siswa

1. Generate password acak saat membuat akun siswa (bukan literal `'password'`).
2. Pilih salah satu strategi (diskusikan dengan user):
   - Kirim link set-password / reset-password ke email siswa, atau
   - Tampilkan password sementara sekali ke admin untuk diserahkan.
3. Pertimbangkan tidak langsung `email_verified_at = now()` jika ingin verifikasi email berjalan.

## File Terdampak

- `app/Http/Controllers/ScheduleController.php` (ubah)
- `app/Http/Controllers/StudentController.php` (ubah)
- `resources/js/pages/schedules/index.tsx` (pastikan error tampil)
- Mungkin `app/Notifications/` untuk kirim kredensial (opsional)

## Langkah Implementasi

1. Refactor `checkConflict` ke `ValidationException`.
2. Ganti generator password siswa + tentukan alur distribusi kredensial bersama user.
3. Verifikasi tampilan error di form jadwal.

## Testing

- Perbarui `tests/Feature/ScheduleTest.php`:
  - [ ] Membuat jadwal bentrok mengembalikan validation error pada field, bukan abort mentah.
- Perbarui `tests/Feature/StudentTest.php`:
  - [ ] Akun siswa dibuat dengan password acak (bukan `'password'`).
  - [ ] Alur kredensial sesuai keputusan (link reset / password sementara).
- Jalankan: `php artisan test --compact --filter=ScheduleTest` dan `--filter=StudentTest`

## Acceptance Criteria

- [ ] Bentrok jadwal tampil sebagai error field di form Inertia.
- [ ] Password siswa tidak lagi literal `'password'`.
- [ ] Ada mekanisme yang jelas untuk siswa mendapat akses akun.
- [ ] Test terkait hijau.
