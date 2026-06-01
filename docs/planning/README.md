# Planning Sisko — Fitur yang Dibutuhkan

Dokumen ini merangkum gap fungsional hasil analisa codebase per **31 Mei 2026**. Seluruh modul inti (12 modul) sudah memiliki model, controller, route, halaman React, dan test. Yang kurang bukan modul baru, melainkan **kelengkapan fungsional** agar aplikasi benar-benar utuh dan bisa dipakai end-to-end.

## Daftar Planning

| # | Fitur | Prioritas | Effort | Status |
|---|-------|-----------|--------|--------|
| 01 | [Navigasi & Sidebar Lengkap](./01-navigation-sidebar.md) | 🔴 Kritis | Kecil | ✅ Done |
| 02 | [Dashboard Statistik](./02-dashboard.md) | 🔴 Kritis | Sedang | ✅ Done |
| 03 | [Portal Siswa & Scoping Guru](./03-student-teacher-portal.md) | 🔴 Kritis | Besar | ✅ Done |
| 04 | [Otomatisasi Keuangan (Jobs & Scheduler)](./04-finance-automation.md) | 🟠 Tinggi | Besar | ✅ Done |
| 05 | [Export PDF Rapor](./05-report-card-pdf.md) | 🟠 Tinggi | Sedang | ✅ Done |
| 06 | [Integrasi GradeConfig](./06-grade-config.md) | 🟡 Sedang | Sedang | ✅ Done |
| 07 | [Perbaikan Validasi & UX](./07-validation-ux.md) | 🟡 Sedang | Kecil | ✅ Done |
| 08 | [Sinkronisasi Dokumentasi](./08-docs-sync.md) | 🟢 Rendah | Kecil | ✅ Done |

## Urutan Pengerjaan yang Disarankan

1. **#01 Sidebar** — quick win, langsung membuka 9 modul yang sudah jadi tapi tidak terjangkau navigasi.
2. **#02 Dashboard** — halaman pertama yang dilihat user, sekarang masih placeholder kosong.
3. **#03 Portal Siswa & Scoping Guru** — menutup celah otorisasi dan memberi nilai bagi role `siswa`.
4. **#04 Otomatisasi Keuangan** — invoice SPP otomatis + reminder.
5. **#05 Export PDF Rapor** & **#06 GradeConfig** — melengkapi modul akademik.
6. **#07 Validasi/UX** & **#08 Dokumentasi** — polish.

## Konvensi Setiap Dokumen

Setiap file planning memuat:

- **Objective** — tujuan fitur.
- **Kondisi Saat Ini** — apa yang sudah/belum ada di codebase.
- **Perubahan yang Dibutuhkan** — daftar pekerjaan konkret.
- **File Terdampak** — file yang dibuat/diubah.
- **Langkah Implementasi** — urutan teknis.
- **Testing** — test yang harus ditulis/diperbarui.
- **Acceptance Criteria** — definisi selesai.
