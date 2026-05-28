<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Semester;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class AcademicYearController extends Controller
{
    public function index(): Response
    {
        $academicYears = AcademicYear::with('semesters')->latest()->get();

        return Inertia::render('academic-years/index', [
            'academicYears' => $academicYears,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:20', 'unique:academic_years,name'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'is_active' => ['boolean'],
        ]);

        DB::transaction(function () use ($validated): void {
            if (! empty($validated['is_active'])) {
                AcademicYear::where('is_active', true)->update(['is_active' => false]);
            }

            AcademicYear::create($validated);
        });

        return back()->with('success', 'Tahun ajaran berhasil ditambahkan.');
    }

    public function update(Request $request, AcademicYear $academicYear): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:20', Rule::unique('academic_years', 'name')->ignore($academicYear->id)],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'is_active' => ['boolean'],
        ]);

        DB::transaction(function () use ($validated, $academicYear): void {
            if (! empty($validated['is_active'])) {
                AcademicYear::where('is_active', true)->where('id', '!=', $academicYear->id)->update(['is_active' => false]);
            }

            $academicYear->update($validated);
        });

        return back()->with('success', 'Tahun ajaran berhasil diperbarui.');
    }

    public function destroy(AcademicYear $academicYear): RedirectResponse
    {
        $academicYear->delete(); // cascades to semesters

        return back()->with('success', 'Tahun ajaran berhasil dihapus.');
    }

    public function storeSemester(Request $request, AcademicYear $academicYear): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', Rule::in(['Ganjil', 'Genap'])],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'is_active' => ['boolean'],
        ]);

        DB::transaction(function () use ($validated, $academicYear): void {
            if (! empty($validated['is_active'])) {
                Semester::where('is_active', true)->update(['is_active' => false]);
            }

            $academicYear->semesters()->create($validated);
        });

        return back()->with('success', 'Semester berhasil ditambahkan.');
    }

    public function updateSemester(Request $request, Semester $semester): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', Rule::in(['Ganjil', 'Genap'])],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'is_active' => ['boolean'],
        ]);

        DB::transaction(function () use ($validated, $semester): void {
            if (! empty($validated['is_active'])) {
                Semester::where('is_active', true)->where('id', '!=', $semester->id)->update(['is_active' => false]);
            }

            $semester->update($validated);
        });

        return back()->with('success', 'Semester berhasil diperbarui.');
    }

    public function destroySemester(Semester $semester): RedirectResponse
    {
        $semester->delete();

        return back()->with('success', 'Semester berhasil dihapus.');
    }
}
