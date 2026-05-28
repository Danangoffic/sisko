# Sisko - Sistem Informasi Sekolah

Aplikasi Sistem Manajemen Sekolah (SMS) berbasis web yang dibangun dengan Laravel dan React/Inertia.js.

## Tech Stack

- **Backend:** Laravel 13, PHP 8.5
- **Frontend:** React 19, Inertia.js v3, Tailwind CSS v4
- **Database:** SQLite (development)
- **Auth:** Laravel Fortify
- **Tooling:** Vite, TypeScript, ESLint, Prettier, PHPUnit

## Fitur

- Autentikasi (Login, Register, Forgot Password, Email Verification, 2FA)
- Manajemen Kelas (CRUD)
- Manajemen Siswa

## Instalasi

```bash
# Clone repository
git clone https://github.com/Danangoffic/sisko.git
cd sisko

# Install dependencies
composer install
npm install

# Setup environment
cp .env.example .env
php artisan key:generate

# Jalankan migrasi
php artisan migrate

# Build assets
npm run build
```

## Development

```bash
composer run dev
```

Atau jalankan secara terpisah:

```bash
php artisan serve
npm run dev
```

## Testing

```bash
php artisan test
```

## Lisensi

MIT
