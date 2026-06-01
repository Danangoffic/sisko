<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\GradeConfig;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class GradeConfigController extends Controller
{
    public function index(): Response
    {
        $configs = GradeConfig::with('academicYear:id,name')
            ->latest()
            ->get();

        $academicYears = AcademicYear::orderByDesc('id')->get(['id', 'name']);

        return Inertia::render('grade-configs/index', [
            'configs' => $configs,
            'academicYears' => $academicYears,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'type' => ['required', Rule::in(['angka', 'huruf', 'deskripsi'])],
            'passing_grade' => ['required', 'numeric', 'min:0'],
            'scale_max' => ['required', 'numeric', 'min:1'],
        ]);

        // Satu config per tahun ajaran
        GradeConfig::updateOrCreate(
            ['academic_year_id' => $validated['academic_year_id']],
            $validated
        );

        return back()->with('success', 'Konfigurasi penilaian berhasil disimpan.');
    }

    public function update(Request $request, GradeConfig $gradeConfig): RedirectResponse
    {
        $validated = $request->validate([
            'type' => ['required', Rule::in(['angka', 'huruf', 'deskripsi'])],
            'passing_grade' => ['required', 'numeric', 'min:0'],
            'scale_max' => ['required', 'numeric', 'min:1'],
        ]);

        $gradeConfig->update($validated);

        return back()->with('success', 'Konfigurasi penilaian berhasil diperbarui.');
    }

    public function destroy(GradeConfig $gradeConfig): RedirectResponse
    {
        $gradeConfig->delete();

        return back()->with('success', 'Konfigurasi penilaian berhasil dihapus.');
    }
}
