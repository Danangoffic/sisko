# 01 — Navigasi & Sidebar Lengkap

**Prioritas:** 🔴 Kritis · **Effort:** Kecil · **Status:** ⬜ Todo

## Objective

Menampilkan semua modul yang sudah dibangun di sidebar, difilter berdasarkan role, sehingga fitur yang ada benar-benar bisa diakses tanpa mengetik URL manual.

## Kondisi Saat Ini

`resources/js/components/app-sidebar.tsx` hanya menampilkan 4 menu:

- Dashboard
- Manajemen Kelas (admin, guru)
- Manajemen Guru (admin)
- Manajemen Pengguna (admin)

Modul berikut **sudah punya halaman + route** tapi tidak ada link-nya:

- Manajemen Siswa (`students.index`)
- Tahun Ajaran & Semester (`academic-years.index`)
- Kenaikan Kelas (`promotions.index`)
- Mata Pelajaran (`subjects.index`)
- Jadwal (`schedules.index`)
- Absensi (`attendances.index`)
- Nilai (`grades.index`)
- Rapor (`report-cards.index`)
- Jenis Pembayaran (`payment-types.index`)
- Invoice/Tagihan (`invoices.index`)
- Buku (`books.index`)
- Peminjaman Buku (`book-loans.index`)
- Pengumuman (`announcements.index`)

## Perubahan yang Dibutuhkan

1. Susun ulang menu menjadi grup logis (Akademik, Keuangan, Perpustakaan, Administrasi) memakai komponen grup sidebar yang ada (`NavMain` / `SidebarGroup`).
2. Filter tiap item berdasarkan role:
   - **admin**: semua menu.
   - **guru**: Dashboard, Kelas, Absensi, Nilai, Rapor, Jadwal, Pengumuman.
   - **siswa**: Dashboard, Pengumuman (dan menu portal siswa — lihat planning #03).
3. Gunakan import Wayfinder (`@/actions/...` atau `@/routes/...`) untuk URL, konsisten dengan pola `SchoolClassController.index().url` yang sudah dipakai.
4. Tambahkan ikon `lucide-react` yang sesuai untuk tiap menu.

## File Terdampak

- `resources/js/components/app-sidebar.tsx` (ubah)
- Mungkin perlu helper kecil untuk grouping menu by role (opsional)

## Langkah Implementasi

1. Definisikan struktur menu sebagai array of group `{ label, items, roles }`.
2. Buat fungsi filter `visibleFor(role)` yang mengembalikan grup + item yang boleh tampil.
3. Render grup memakai komponen sidebar yang tersedia.
4. Pastikan generated Wayfinder action tersedia untuk semua controller (jalankan build bila perlu).

## Testing

- Sidebar adalah komponen presentasi; verifikasi via:
  - Manual: login sebagai admin/guru/siswa, pastikan menu sesuai role.
  - Opsional: test render React (jika ada setup Vitest/RTL) untuk memastikan item muncul/tidak sesuai role.
- Pastikan tidak ada link ke route yang tidak diizinkan role tersebut (cross-check dengan `routes/web.php`).

## Acceptance Criteria

- [ ] Semua modul yang memiliki route punya entri menu yang sesuai role.
- [ ] Menu admin menampilkan seluruh modul; guru dan siswa hanya menu yang relevan.
- [ ] Tidak ada link menuju route yang akan menghasilkan 403 untuk role tersebut.
- [ ] URL dihasilkan via Wayfinder, bukan string hardcode (kecuali `/users` jika memang konvensi eksisting).
