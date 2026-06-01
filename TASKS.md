# Sisko — Implementation Tasks

> **Catatan:** Semua 12 modul inti sudah terimplementasi (model, controller, route, halaman React, test).
> Sisa pekerjaan adalah kelengkapan fungsional — lihat [`docs/planning/`](./docs/planning/README.md).

## Progress: 12/12 modul inti selesai

| # | Task | Status |
|---|------|--------|
| 1 | Role & Permission System | ✅ Done |
| 2 | Manajemen Data Guru | ✅ Done |
| 3 | Enhancement Data Siswa | ✅ Done |
| 4 | Tahun Ajaran & Semester | ✅ Done |
| 5 | Kenaikan Kelas | ✅ Done |
| 6 | Mata Pelajaran & Jadwal | ✅ Done |
| 7 | Absensi | ✅ Done |
| 8 | Penilaian & Rapor | ✅ Done |
| 9 | Keuangan Dasar | ✅ Done |
| 10 | Integrasi Midtrans | ✅ Done |
| 11 | Pengumuman & Berita | ✅ Done |
| 12 | Perpustakaan | ✅ Done |

## Kelengkapan Fungsional (dari analisa gap)

| # | Fitur | Status |
|---|-------|--------|
| F1 | Navigasi & Sidebar Lengkap | ✅ Done |
| F2 | Dashboard Statistik per Role | ✅ Done |
| F3 | Portal Siswa + Scoping Guru | ✅ Done |
| F4 | Otomatisasi Keuangan (Jobs & Scheduler) | ✅ Done |
| F5 | Export PDF Rapor | ✅ Done |
| F6 | Integrasi GradeConfig | ✅ Done |
| F7 | Perbaikan Validasi & UX | ✅ Done |
| F8 | Sinkronisasi Dokumentasi | ✅ Done |

---

## Task 1: Role & Permission System ✅

**Files:**
- `app/Role.php`
- `app/Models/User.php`
- `app/Http/Middleware/EnsureUserHasRole.php`
- `database/migrations/2026_05_28_150757_add_role_to_users_table.php`
- `database/seeders/AdminSeeder.php`
- `database/factories/UserFactory.php`
- `bootstrap/app.php`
- `routes/web.php`
- `resources/js/types/auth.ts`
- `resources/js/components/app-sidebar.tsx`
- `tests/Feature/RolePermissionTest.php`

---

## Task 2: Manajemen Data Guru ✅

**Files:**
- `app/Models/Teacher.php`
- `app/Http/Controllers/TeacherController.php`
- `database/migrations/2026_05_28_151118_create_teachers_table.php`
- `database/factories/TeacherFactory.php`
- `resources/js/pages/teachers/index.tsx`
- `tests/Feature/TeacherTest.php`
- `tests/TestCase.php`

---

## Notes

- PHP 8.4 path: `export PATH="/opt/homebrew/opt/php@8.4/bin:$PATH"` (sudah ditambahkan ke `~/.zshrc`)
- Admin default: `admin@sisko.test` / `password`
- Run tests: `php artisan test --compact`
- Run migration: `php artisan migrate`
- Run seeder: `php artisan db:seed`
- Setelah `composer install`, jalankan `composer require barryvdh/laravel-dompdf:^3.1` untuk fitur PDF rapor
