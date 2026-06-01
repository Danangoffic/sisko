# 02 — Dashboard Statistik

**Prioritas:** 🔴 Kritis · **Effort:** Sedang · **Status:** ⬜ Todo

## Objective

Mengubah dashboard dari placeholder kosong menjadi ringkasan informasi yang relevan per role: statistik utama, pengumuman terbaru, dan info actionable (tagihan jatuh tempo, rekap absensi).

## Kondisi Saat Ini

- `resources/js/pages/dashboard.tsx` hanya menampilkan kotak `PlaceholderPattern`.
- Route `dashboard` memakai `Route::inertia('dashboard', 'dashboard')` — **tidak ada controller**, jadi tidak ada data yang dikirim.

## Perubahan yang Dibutuhkan

1. Buat `DashboardController@index` yang mengirim data sesuai role.
2. Ganti `Route::inertia('dashboard', ...)` dengan route ke controller.
3. Data yang dikirim:
   - **admin**: jumlah siswa, guru, kelas; total tagihan pending & overdue; pengumuman terbaru; ringkasan absensi hari ini.
   - **guru**: kelas/jadwal yang diampu; absensi yang perlu diisi hari ini; pengumuman.
   - **siswa**: ringkasan nilai/rapor terakhir; tagihan belum lunas; pengumuman.
4. Bangun komponen kartu statistik (`StatCard`) dan daftar pengumuman ringkas di frontend.
5. Tangani empty state untuk tiap widget.

## File Terdampak

- `app/Http/Controllers/DashboardController.php` (buat — `php artisan make:controller DashboardController`)
- `routes/web.php` (ubah route dashboard)
- `resources/js/pages/dashboard.tsx` (ubah)
- `resources/js/components/stat-card.tsx` (buat, opsional)

## Langkah Implementasi

1. `php artisan make:controller DashboardController --no-interaction`.
2. Implementasikan `index(Request $request)` dengan query agregat (`count`, `sum`) yang efisien; gunakan `when($user->isAdmin(), ...)` untuk percabangan data.
3. Pertimbangkan `Inertia::optional()` untuk widget berat agar tidak memblok render awal.
4. Update route dan halaman React.
5. Pastikan query menghindari N+1 (eager load bila perlu).

## Testing

- `tests/Feature/DashboardTest.php` sudah ada — perbarui:
  - [ ] Admin menerima props statistik (siswa/guru/kelas count, tagihan).
  - [ ] Guru menerima props jadwal/absensi miliknya.
  - [ ] Siswa menerima props nilai/tagihan miliknya.
  - [ ] User belum login diarahkan ke login.
- Jalankan: `php artisan test --compact --filter=DashboardTest`

## Acceptance Criteria

- [ ] Dashboard menampilkan minimal 3 kartu statistik sesuai role.
- [ ] Pengumuman terbaru tampil di dashboard.
- [ ] Tidak ada query N+1; halaman load tanpa error.
- [ ] Placeholder kosong dihapus.
