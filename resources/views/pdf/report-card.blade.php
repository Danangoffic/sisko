<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rapor - {{ $reportCard->student->name }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 11px; color: #1a1a1a; }
        .page { padding: 24px 32px; }

        /* Header */
        .header { text-align: center; border-bottom: 2px solid #1a1a1a; padding-bottom: 12px; margin-bottom: 16px; }
        .header h1 { font-size: 16px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; }
        .header h2 { font-size: 13px; margin-top: 2px; }
        .header p { font-size: 10px; color: #555; margin-top: 2px; }

        /* Info siswa */
        .info-grid { display: table; width: 100%; margin-bottom: 16px; }
        .info-row { display: table-row; }
        .info-label { display: table-cell; width: 130px; padding: 2px 0; color: #555; }
        .info-value { display: table-cell; padding: 2px 0; font-weight: 500; }
        .info-value::before { content: ': '; }

        /* Tabel nilai */
        table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        th { background: #f0f0f0; padding: 6px 8px; text-align: left; font-weight: 600; border: 1px solid #ccc; }
        td { padding: 5px 8px; border: 1px solid #ccc; }
        tr:nth-child(even) td { background: #fafafa; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }

        /* Ringkasan */
        .summary { background: #f8f8f8; border: 1px solid #ddd; border-radius: 4px; padding: 12px; margin-bottom: 16px; }
        .summary-grid { display: table; width: 100%; }
        .summary-item { display: table-cell; text-align: center; }
        .summary-value { font-size: 20px; font-weight: bold; }
        .summary-label { font-size: 10px; color: #666; margin-top: 2px; }

        /* Catatan */
        .notes { border: 1px solid #ddd; border-radius: 4px; padding: 10px; margin-bottom: 24px; }
        .notes-title { font-weight: 600; margin-bottom: 4px; }
        .notes-content { color: #444; font-style: italic; }

        /* Tanda tangan */
        .signatures { display: table; width: 100%; margin-top: 32px; }
        .sig-cell { display: table-cell; text-align: center; width: 33%; }
        .sig-line { border-top: 1px solid #1a1a1a; margin: 48px 16px 4px; }
        .sig-name { font-weight: 600; }
        .sig-role { font-size: 10px; color: #666; }
    </style>
</head>
<body>
<div class="page">
    <!-- Header -->
    <div class="header">
        <h1>Laporan Hasil Belajar Siswa</h1>
        <h2>Sisko — Sistem Informasi Sekolah</h2>
        <p>{{ $reportCard->semester->academicYear->name }} · Semester {{ $reportCard->semester->name }}</p>
    </div>

    <!-- Info Siswa -->
    <div class="info-grid">
        <div class="info-row">
            <span class="info-label">Nama Siswa</span>
            <span class="info-value">{{ $reportCard->student->name }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">NISN</span>
            <span class="info-value">{{ $reportCard->student->nisn }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Kelas</span>
            <span class="info-value">{{ $reportCard->student->schoolClass?->name ?? '-' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Tahun Ajaran</span>
            <span class="info-value">{{ $reportCard->semester->academicYear->name }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Semester</span>
            <span class="info-value">{{ $reportCard->semester->name }}</span>
        </div>
    </div>

    <!-- Ringkasan -->
    <div class="summary">
        <div class="summary-grid">
            <div class="summary-item">
                <div class="summary-value">{{ number_format((float) $reportCard->average, 2) }}</div>
                <div class="summary-label">Rata-rata Nilai</div>
            </div>
            <div class="summary-item">
                <div class="summary-value">#{{ $reportCard->rank ?? '-' }}</div>
                <div class="summary-label">Peringkat Kelas</div>
            </div>
            <div class="summary-item">
                <div class="summary-value">{{ $grades->count() }}</div>
                <div class="summary-label">Mata Pelajaran</div>
            </div>
        </div>
    </div>

    <!-- Tabel Nilai per Mapel -->
    <table>
        <thead>
            <tr>
                <th style="width:30px">#</th>
                <th>Mata Pelajaran</th>
                <th class="text-center" style="width:60px">Tugas</th>
                <th class="text-center" style="width:60px">UTS</th>
                <th class="text-center" style="width:60px">UAS</th>
                <th class="text-center" style="width:60px">Praktik</th>
                <th class="text-center" style="width:60px">Rata-rata</th>
                <th class="text-center" style="width:40px">Huruf</th>
            </tr>
        </thead>
        <tbody>
            @forelse($grades->groupBy('subject_id') as $subjectId => $subjectGrades)
                @php
                    $subject = $subjectGrades->first()->subject;
                    $byType = $subjectGrades->keyBy('type');
                    $scores = $subjectGrades->whereNotNull('score')->pluck('score');
                    $avg = $scores->isNotEmpty() ? round($scores->avg(), 2) : null;
                    $letter = $subjectGrades->whereNotNull('letter_grade')->first()?->letter_grade;
                @endphp
                <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td>{{ $subject->name }}</td>
                    <td class="text-center">{{ $byType['tugas']?->score ?? '-' }}</td>
                    <td class="text-center">{{ $byType['uts']?->score ?? '-' }}</td>
                    <td class="text-center">{{ $byType['uas']?->score ?? '-' }}</td>
                    <td class="text-center">{{ $byType['praktik']?->score ?? '-' }}</td>
                    <td class="text-center font-bold">{{ $avg ?? '-' }}</td>
                    <td class="text-center font-bold">{{ $letter ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center" style="padding:12px;color:#888">Belum ada data nilai.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Catatan Guru -->
    @if($reportCard->teacher_notes)
    <div class="notes">
        <div class="notes-title">Catatan Wali Kelas</div>
        <div class="notes-content">{{ $reportCard->teacher_notes }}</div>
    </div>
    @endif

    <!-- Tanda Tangan -->
    <div class="signatures">
        <div class="sig-cell">
            <div class="sig-line"></div>
            <div class="sig-name">Orang Tua / Wali</div>
        </div>
        <div class="sig-cell">
            <div class="sig-line"></div>
            <div class="sig-name">Wali Kelas</div>
            <div class="sig-role">{{ $reportCard->student->schoolClass?->homeroom_teacher ?? '' }}</div>
        </div>
        <div class="sig-cell">
            <div class="sig-line"></div>
            <div class="sig-name">Kepala Sekolah</div>
        </div>
    </div>
</div>
</body>
</html>
