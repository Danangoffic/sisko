# 05 — Export PDF Rapor

**Prioritas:** 🟠 Tinggi · **Effort:** Sedang · **Status:** ⬜ Todo

## Objective

Memungkinkan rapor siswa dicetak/diunduh sebagai PDF dengan layout yang rapi (identitas siswa, daftar nilai per mapel, rata-rata, ranking, catatan wali kelas).

## Kondisi Saat Ini

- `ReportCardController` hanya menghitung `average` & `rank` lalu menyimpan; **tidak ada export**.
- **Tidak ada** package PDF (`dompdf`/`snappy`) di `composer.json`.
- Detail nilai per mapel tidak ditarik saat menampilkan rapor (hanya agregat tersimpan).

## Perubahan yang Dibutuhkan

1. Tambah package PDF — disarankan `barryvdh/laravel-dompdf` (perlu **persetujuan** karena menambah dependency).
2. Buat endpoint `ReportCardController@download($reportCard)` yang merender Blade view ke PDF.
3. Buat Blade template rapor (`resources/views/pdf/report-card.blade.php`) berisi:
   - Header sekolah, identitas siswa & kelas, semester/tahun ajaran.
   - Tabel nilai per mapel (tarik dari `grades` sesuai `student_id` + `semester_id`).
   - Rata-rata, ranking, catatan wali kelas, kolom tanda tangan.
4. Tambah tombol "Unduh PDF" di `resources/js/pages/report-cards/index.tsx` (dan portal siswa #03).

## File Terdampak

- `composer.json` (tambah dependency — butuh approval)
- `app/Http/Controllers/ReportCardController.php` (tambah `download`)
- `resources/views/pdf/report-card.blade.php` (buat)
- `routes/web.php` (route download)
- `resources/js/pages/report-cards/index.tsx` (tombol unduh)

## Langkah Implementasi

1. Minta persetujuan user untuk menambah `barryvdh/laravel-dompdf`.
2. `composer require barryvdh/laravel-dompdf` setelah disetujui.
3. Buat Blade template & method `download` yang mengumpulkan nilai per mapel.
4. Tambah route (admin/guru, dan siswa untuk rapornya sendiri).
5. Tambah tombol unduh di frontend.

## Testing

- Perbarui `tests/Feature/ReportCardTest.php`:
  - [ ] Endpoint download mengembalikan response PDF (`Content-Type: application/pdf`, status 200).
  - [ ] Siswa hanya bisa mengunduh rapornya sendiri (jika diekspos ke portal).
  - [ ] Rapor yang tidak ada → 404.
- Jalankan: `php artisan test --compact --filter=ReportCardTest`

## Acceptance Criteria

- [ ] Rapor dapat diunduh sebagai PDF dengan layout lengkap.
- [ ] PDF memuat nilai per mapel, rata-rata, ranking, catatan.
- [ ] Otorisasi akses unduh sesuai role.
- [ ] Test endpoint download hijau.

## Catatan

Menambah dependency melanggar aturan "Do not change dependencies without approval" pada `AGENTS.md` — **wajib konfirmasi user** sebelum `composer require`.
