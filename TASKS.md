# Sisko - Implementation Tasks

## Progress: 2/12 tasks completed

| # | Task | Status |
|---|------|--------|
| 1 | Role & Permission System | ✅ Done |
| 2 | Manajemen Data Guru | ✅ Done |
| 3 | Enhancement Data Siswa | ⬜ Todo |
| 4 | Tahun Ajaran & Semester | ⬜ Todo |
| 5 | Kenaikan Kelas | ⬜ Todo |
| 6 | Mata Pelajaran & Jadwal | ⬜ Todo |
| 7 | Absensi | ⬜ Todo |
| 8 | Penilaian & Rapor | ⬜ Todo |
| 9 | Keuangan Dasar | ⬜ Todo |
| 10 | Integrasi Midtrans | ⬜ Todo |
| 11 | Pengumuman & Berita | ⬜ Todo |
| 12 | Perpustakaan | ⬜ Todo |

---

## Task 1: Role & Permission System ✅

**Objective:** Implementasi sistem role (Admin, Guru, Siswa) dengan middleware authorization.

**Completed:**
- `Role` enum (admin/guru/siswa) + kolom `role` di tabel `users`
- Middleware `EnsureUserHasRole` dengan alias `role`
- `AdminSeeder` → `admin@sisko.test` / password: `password`
- `UserFactory` states: `admin()`, `guru()`
- Sidebar dinamis berdasarkan role
- Route `/users` hanya bisa diakses admin

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

**Objective:** CRUD data guru yang terhubung dengan user account.

**Completed:**
- Migration `teachers`: user_id (FK), nip, phone, address
- Model `Teacher` belongsTo `User`
- `TeacherController` CRUD (store buat user+teacher dalam DB transaction, destroy cascade)
- React page `teachers/index.tsx` — form tambah + tabel + edit inline
- `TestCase` base class disable `PreventRequestForgery` (CSRF Laravel 13)
- Wayfinder generated untuk `TeacherController`

**Files:**
- `app/Models/Teacher.php`
- `app/Http/Controllers/TeacherController.php`
- `database/migrations/2026_05_28_151118_create_teachers_table.php`
- `database/factories/TeacherFactory.php`
- `resources/js/pages/teachers/index.tsx`
- `tests/Feature/TeacherTest.php`
- `tests/TestCase.php`

---

## Task 3: Enhancement Data Siswa ⬜

**Objective:** Tambah detail lengkap pada data siswa & hubungkan dengan user account.

**Plan:**
- Tambah kolom pada `students`: user_id (FK nullable), tempat_lahir, tanggal_lahir, alamat, no_telp_ortu, nama_ortu
- Update model `Student` — belongsTo User, belongsTo SchoolClass
- `StudentController` CRUD lengkap (admin only)
- React page `students/index.tsx` — tabel dengan filter per kelas

---

## Task 4: Tahun Ajaran & Semester ⬜

**Objective:** Manajemen tahun ajaran dan semester sebagai konteks untuk semua data akademik.

**Plan:**
- Migration `academic_years`: name, start_date, end_date, is_active
- Migration `semesters`: academic_year_id (FK), name (Ganjil/Genap), start_date, end_date, is_active
- Hanya satu tahun ajaran & semester aktif pada satu waktu
- Model + Controller + React page

---

## Task 5: Kenaikan Kelas ⬜

**Objective:** Sistem kenaikan kelas otomatis dengan opsi override manual.

**Plan:**
- Migration `class_promotions`: student_id, from_class_id, to_class_id, academic_year_id, status (naik/tinggal/lulus)
- `ClassPromotionController` — promote batch (otomatis) + individual override
- React page `promotions/index.tsx`

---

## Task 6: Mata Pelajaran & Jadwal ⬜

**Objective:** Kelola mata pelajaran dan jadwal pelajaran per kelas.

**Plan:**
- Migration `subjects`: name, code, description
- Migration `schedules`: school_class_id, subject_id, teacher_id, academic_year_id, day, start_time, end_time
- Validasi jadwal tidak bentrok
- React page jadwal: grid hari x jam

---

## Task 7: Absensi ⬜

**Objective:** Sistem absensi harian dan per mata pelajaran.

**Plan:**
- Migration `attendances`: student_id, school_class_id, schedule_id (nullable), date, status (hadir/izin/sakit/alpha), note, recorded_by
- `AttendanceController` — form absensi per kelas, rekap bulanan
- React page: checklist siswa per kelas

---

## Task 8: Penilaian & Rapor ⬜

**Objective:** Sistem penilaian fleksibel (angka/huruf/deskripsi) dan cetak rapor.

**Plan:**
- Migration `grade_configs`: academic_year_id, type (angka/huruf/deskripsi), passing_grade, scale_max
- Migration `grades`: student_id, subject_id, semester_id, type (tugas/uts/uas/praktik), score, letter_grade, description, teacher_id
- Migration `report_cards`: student_id, semester_id, average, rank, teacher_notes
- React pages: input nilai (grid), view rapor

---

## Task 9: Keuangan Dasar ⬜

**Objective:** Manajemen pembayaran SPP dan jenis pembayaran lain dengan sistem invoice.

**Plan:**
- Migration `payment_types`: name, amount, is_recurring, recurring_period
- Migration `invoices`: student_id, payment_type_id, invoice_number, amount, due_date, status, paid_at, month
- Migration `payments`: invoice_id, amount, payment_method, transaction_id, paid_at
- Job: generate invoice SPP otomatis bulanan, tandai overdue

---

## Task 10: Integrasi Midtrans ⬜

**Objective:** Pembayaran online via Midtrans + reminder otomatis.

**Plan:**
- Install `midtrans/midtrans-php`
- `MidtransController` — create snap token, handle notification callback
- Tombol "Bayar Online" pada halaman invoice siswa
- Notification/reminder untuk invoice mendekati due date & overdue

---

## Task 11: Pengumuman & Berita ⬜

**Objective:** Sistem pengumuman sekolah yang bisa ditargetkan per role.

**Plan:**
- Migration `announcements`: title, content, author_id, target_role (all/guru/siswa), is_pinned, published_at
- `AnnouncementController` CRUD (admin), list (semua role)
- Tampilkan pengumuman terbaru di dashboard

---

## Task 12: Perpustakaan ⬜

**Objective:** Katalog buku dan sistem peminjaman.

**Plan:**
- Migration `books`: title, author, isbn, publisher, year, category, stock
- Migration `book_loans`: book_id, student_id, borrowed_at, due_date, returned_at, status, fine
- `BookController` CRUD (admin)
- `BookLoanController` — pinjam, kembalikan, kalkulasi denda

---

## Notes

- PHP 8.4 path: `export PATH="/opt/homebrew/opt/php@8.4/bin:$PATH"` (sudah ditambahkan ke `~/.zshrc`)
- Admin default: `admin@sisko.test` / `password`
- Run tests: `php artisan test --compact`
- Run migration: `php artisan migrate`
- Run seeder: `php artisan db:seed`
