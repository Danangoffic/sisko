# TASK: Implementasi Autentikasi & Manajemen Kelas (Laravel + React/Inertia)

## CONTEXT & OBJECTIVE

Kita sedang membangun MVP untuk Sistem Manajemen Sekolah (SMS) bernama "Sisko". Aplikasi ini menggunakan arsitektur Laravel Monolith dengan React/Inertia.js sebagai frontend layer.

Tugas: mengamankan modul "Manajemen Kelas" agar hanya bisa diakses oleh pengguna yang sudah login (Authenticated Users). Autentikasi sudah tersedia via Laravel starter kit (React stack).

## TECH STACK SPECIFICATION

- Framework: Laravel 13 dengan React/Inertia.js starter kit
- PHP Version: PHP 8.5
- Database: SQLite (dev) / PostgreSQL / MySQL (Eloquent ORM)
- Frontend: React 19 + Inertia v3 + Tailwind CSS v4
- Security: Standard Laravel Auth Middleware, CSRF Protection

## ARCHITECTURE & IMPLEMENTATION REQUIREMENTS

### 1. Route Protection (routes/web.php)

Bungkus semua route "Manajemen Kelas" ke dalam group middleware `auth` dan `verified`:

```php
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/kelas', [SchoolClassController::class, 'index'])->name('class.index');
    Route::post('/kelas', [SchoolClassController::class, 'store'])->name('class.store');
    Route::delete('/kelas/{school_class}', [SchoolClassController::class, 'destroy'])->name('class.destroy');
});
```

### 2. Database Layer (Migrations & Models)

- Gunakan tabel `users` bawaan untuk autentikasi.

- Migration `school_classes`:
  - `id` (Primary Key)
  - `name` (string, max 50, unique)
  - `homeroom_teacher` (string, max 100)
  - `timestamps`

- Migration `students`:
  - `id` (Primary Key)
  - `school_class_id` (Foreign Key → school_classes, cascade on delete)
  - `nisn` (string, max 10, unique)
  - `name` (string, max 100)
  - `gender` (enum: ['L', 'P'])
  - `timestamps`

- Relasi Eloquent: `SchoolClass` hasMany `Student`, `Student` belongsTo `SchoolClass`.

### 3. Controller & Presentation Layer

- **SchoolClassController**: `index()`, `store()`, `destroy()` dengan query `withCount('students')`.
- **React Page (SchoolClass/Index.tsx)**: Gunakan layout `AuthenticatedLayout` bawaan starter kit. Form input di kiri, tabel daftar kelas di kanan.

## OUTPUT FILES

1. Migration: `xxxx_create_school_classes_table.php`
2. Migration: `xxxx_create_students_table.php`
3. Model: `SchoolClass.php`
4. Model: `Student.php`
5. Controller: `SchoolClassController.php`
6. Routes: `routes/web.php`
7. React Page: `resources/js/pages/SchoolClass/Index.tsx`
