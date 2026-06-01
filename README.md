# Sisko — Sistem Informasi Sekolah

Aplikasi Sistem Manajemen Sekolah (SMS) berbasis web yang dibangun dengan Laravel dan React/Inertia.js.

## Tech Stack

- **Backend:** Laravel 13, PHP 8.4
- **Frontend:** React 19, Inertia.js v3, Tailwind CSS v4
- **Database:** SQLite (development) / PostgreSQL / MySQL
- **Auth:** Laravel Fortify (login, register, 2FA, passkey, email verification)
- **Pembayaran:** Midtrans
- **Tooling:** Vite, TypeScript, ESLint, Prettier, PHPUnit

## Fitur

### Autentikasi & Otorisasi
- Login, Register, Forgot Password, Email Verification, 2FA, Passkey
- Role-based access: Admin, Guru, Siswa
- Middleware `EnsureUserHasRole`

### Akademik
- Manajemen Kelas (CRUD)
- Manajemen Siswa (CRUD + akun user otomatis)
- Manajemen Guru (CRUD + akun user)
- Mata Pelajaran & Jadwal (dengan validasi bentrok)
- Tahun Ajaran & Semester
- Kenaikan Kelas (batch + individual override)
- Absensi harian per kelas (admin & guru, scoped per penugasan)
- Penilaian (batch input, konversi huruf otomatis via GradeConfig)
- Rapor (generate otomatis, export PDF)
- Konfigurasi Penilaian (KKM, skala, tipe)

### Keuangan
- Jenis Pembayaran (recurring & one-time)
- Invoice & Pembayaran manual
- Integrasi Midtrans (Snap)
- Generate invoice SPP otomatis bulanan (scheduler)
- Reminder & tandai overdue otomatis (scheduler)

### Perpustakaan
- Katalog Buku (CRUD)
- Peminjaman & Pengembalian Buku

### Pengumuman
- CRUD pengumuman (admin), target per role
- Tampil di dashboard & halaman pengumuman

### Portal Siswa
- Jadwal kelas, rekap absensi, nilai, rapor, tagihan

## Instalasi

```bash
git clone https://github.com/Danangoffic/sisko.git
cd sisko

composer install
npm install

cp .env.example .env
php artisan key:generate
php artisan migrate --seed

npm run build
```

## Development

```bash
composer run dev
```

## Testing

```bash
php artisan test --compact
```

## Akun Default

- **Admin:** `admin@sisko.test` / `password`

## Planning & Roadmap

Lihat [`docs/planning/`](./docs/planning/README.md) untuk detail rencana pengembangan.

## Lisensi

MIT
