# 03 — Portal Siswa & Scoping Guru

**Prioritas:** 🔴 Kritis · **Effort:** Besar · **Status:** ⬜ Todo

## Objective

1. Memberi role `siswa` halaman yang berguna (lihat nilai, rapor, jadwal, absensi, tagihan sendiri).
2. Membatasi guru hanya bisa mengelola data sesuai penugasannya (mapel/kelas yang diampu), menutup celah otorisasi.

## Kondisi Saat Ini

- Role `siswa` ada di enum `App\Role` dan dibuat otomatis di `StudentController`, tapi **tidak ada satu route pun** untuk siswa. Siswa login → hanya dashboard kosong.
- `Student` punya relasi `belongsTo(User::class)`, jadi mapping siswa↔akun sudah ada.
- `GradeController` dan `AttendanceController` **tidak memfilter** berdasarkan guru — guru mana pun bisa input nilai untuk mapel/kelas mana pun.
- `User` model belum punya relasi `teacher()` / `student()`.

## Perubahan yang Dibutuhkan

### A. Portal Siswa (read-only)

1. Tambah relasi `User::student()` (hasOne) dan `User::teacher()` (hasOne).
2. Buat route group `role:siswa` dengan halaman:
   - Nilai saya (`/portal/grades`)
   - Rapor saya (`/portal/report-cards`)
   - Jadwal kelas saya (`/portal/schedule`)
   - Absensi saya (`/portal/attendances`)
   - Tagihan saya (`/portal/invoices`) + tombol bayar Midtrans
3. Controller portal memfilter data berdasarkan `auth()->user()->student`.

### B. Scoping Guru

1. Pada `GradeController` & `AttendanceController`, jika user adalah guru (bukan admin), batasi:
   - Daftar mapel/kelas hanya yang ada di `schedules` milik guru tersebut.
   - Validasi saat store: pastikan `teacher_id`/`subject_id`/`school_class_id` sesuai penugasan guru.
2. Pertimbangkan Policy (`GradePolicy`, `AttendancePolicy`) agar otorisasi terpusat.

## File Terdampak

- `app/Models/User.php` (tambah relasi)
- `app/Http/Controllers/Portal/` (buat controller portal siswa)
- `app/Http/Controllers/GradeController.php`, `AttendanceController.php` (scoping)
- `app/Policies/` (opsional, buat policy)
- `routes/web.php` (group `role:siswa` + portal)
- `resources/js/pages/portal/` (halaman React baru)
- `resources/js/components/app-sidebar.tsx` (menu siswa — sinkron dengan planning #01)

## Langkah Implementasi

1. Tambah relasi di `User`.
2. `php artisan make:controller Portal/GradeController` dst (atau satu `Portal/DashboardController` + method).
3. Buat halaman React read-only di `pages/portal/`.
4. Tambah scoping/policy untuk guru.
5. Daftarkan route dan menu sidebar.

## Testing

- Buat `tests/Feature/Portal/StudentPortalTest.php`:
  - [ ] Siswa hanya melihat nilai/rapor/tagihan miliknya.
  - [ ] Siswa tidak bisa akses route admin/guru (403).
- Perbarui `GradeTest` / `AttendanceTest`:
  - [ ] Guru tidak bisa input nilai untuk mapel/kelas di luar penugasannya (403/422).
  - [ ] Admin tetap bisa input semua.
- Jalankan: `php artisan test --compact --filter=Portal` dan `--filter=GradeTest`.

## Acceptance Criteria

- [ ] Siswa punya minimal 1 halaman portal yang menampilkan datanya sendiri.
- [ ] Siswa tidak dapat melihat data siswa lain.
- [ ] Guru hanya dapat mengelola nilai/absensi untuk penugasannya.
- [ ] Semua jalur ditutup test (happy path + akses ditolak).
