# 08 — Sinkronisasi Dokumentasi

**Prioritas:** 🟢 Rendah · **Effort:** Kecil · **Status:** ⬜ Todo

## Objective

Menyelaraskan dokumentasi project dengan kondisi codebase yang sebenarnya agar tidak menyesatkan kontributor.

## Kondisi Saat Ini

- `TASKS.md` menyatakan **Progress: 2/12** dan menandai 10 task sebagai "Todo", padahal semua modul tersebut **sudah terimplementasi** (model, controller, route, halaman, test).
- `README.md` di bagian Fitur hanya menyebut "Autentikasi, Manajemen Kelas, Manajemen Siswa" — jauh lebih sedikit dari yang sebenarnya ada.
- `README.md` menyebut "PHP 8.5" sementara `AGENTS.md` menyebut "php - 8.4". Perlu dikonfirmasi versi yang benar.

## Perubahan yang Dibutuhkan

1. Perbarui `TASKS.md`:
   - Update status task yang sudah selesai menjadi ✅.
   - Tambah catatan bahwa sisa pekerjaan adalah kelengkapan fungsional (lihat `docs/planning/`).
2. Perbarui `README.md`:
   - Lengkapi daftar fitur sesuai modul yang ada.
   - Samakan versi PHP dengan yang dipakai (`composer.json` / `AGENTS.md`).
3. Tautkan `docs/planning/README.md` dari README utama.

## File Terdampak

- `TASKS.md` (ubah)
- `README.md` (ubah)

## Langkah Implementasi

1. Audit status tiap modul vs `routes/web.php` dan folder `pages/`.
2. Update tabel `TASKS.md`.
3. Lengkapi bagian Fitur di `README.md` + tautan ke planning.
4. Konfirmasi versi PHP ke user lalu samakan.

## Testing

- Tidak ada test kode. Verifikasi manual: klaim dokumentasi cocok dengan route & halaman yang ada.

## Acceptance Criteria

- [ ] `TASKS.md` mencerminkan status implementasi yang sebenarnya.
- [ ] `README.md` mencantumkan seluruh fitur utama & versi PHP yang konsisten.
- [ ] Ada tautan ke `docs/planning/` dari dokumentasi utama.
