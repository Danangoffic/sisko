# 06 — Integrasi GradeConfig

**Prioritas:** 🟡 Sedang · **Effort:** Sedang · **Status:** ⬜ Todo

## Objective

Memanfaatkan model `GradeConfig` agar penilaian konsisten: konversi nilai angka → huruf otomatis, penentuan lulus/tidak berdasarkan `passing_grade`, dan skala nilai yang dapat dikonfigurasi per tahun ajaran.

## Kondisi Saat Ini

- Model `App\Models\GradeConfig` ada (kolom: `academic_year_id`, `type` angka/huruf/deskripsi, `passing_grade`, `scale_max`) tapi **tidak dipakai controller mana pun** (model yatim).
- `GradeController` menerima `score`, `letter_grade`, `description` sebagai input manual tanpa aturan konversi atau validasi passing grade.
- Tidak ada UI untuk mengelola konfigurasi penilaian.

## Perubahan yang Dibutuhkan

1. Buat `GradeConfigController` (CRUD, admin only) + halaman React `pages/grade-configs/index.tsx`.
2. Saat menyimpan `Grade`, terapkan config aktif:
   - Konversi `score` → `letter_grade` otomatis bila `type` huruf (mis. A/B/C/D berdasarkan ambang).
   - Tandai lulus/tidak lulus berdasarkan `passing_grade`.
   - Validasi `score` tidak melebihi `scale_max`.
3. Tampilkan status lulus/tidak & huruf pada halaman nilai dan rapor.
4. Definisikan aturan konversi (mis. method `letterFor(float $score)` di `GradeConfig`).

## File Terdampak

- `app/Http/Controllers/GradeConfigController.php` (buat)
- `app/Models/GradeConfig.php` (tambah helper konversi/relasi)
- `app/Http/Controllers/GradeController.php` (terapkan config)
- `app/Http/Controllers/ReportCardController.php` (gunakan passing grade untuk status)
- `resources/js/pages/grade-configs/index.tsx` (buat)
- `routes/web.php` (resource route admin)
- `database/factories/GradeConfigFactory.php` (buat jika belum ada)

## Langkah Implementasi

1. `php artisan make:controller GradeConfigController --no-interaction`.
2. Tambah method konversi di `GradeConfig` + unit test untuk method tersebut.
3. Integrasikan ke `GradeController@store/update`.
4. Buat halaman pengelolaan config.
5. Tampilkan huruf/status di nilai & rapor.

## Testing

- Buat `tests/Unit/GradeConfigTest.php`:
  - [ ] Konversi score → letter sesuai ambang.
  - [ ] Penentuan lulus/tidak sesuai `passing_grade`.
- Buat/`update tests/Feature/GradeConfigTest.php`:
  - [ ] Admin bisa CRUD config; guru/siswa tidak (403).
  - [ ] Menyimpan grade menerapkan config aktif (letter terisi otomatis).
- Jalankan: `php artisan test --compact --filter=GradeConfig`

## Acceptance Criteria

- [ ] Config penilaian dapat dikelola admin.
- [ ] Nilai angka dikonversi ke huruf & status lulus otomatis sesuai config aktif.
- [ ] `scale_max` & `passing_grade` divalidasi.
- [ ] Test unit + feature hijau.
