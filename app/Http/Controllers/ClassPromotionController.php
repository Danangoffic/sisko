<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\ClassPromotion;
use App\Models\SchoolClass;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ClassPromotionController extends Controller
{
    public function index(): Response
    {
        $academicYears = AcademicYear::orderByDesc('id')->get(['id', 'name']);
        $classes = SchoolClass::orderBy('name')->get(['id', 'name']);

        $promotions = ClassPromotion::with(['student', 'fromClass', 'toClass', 'academicYear'])
            ->latest()
            ->paginate(20);

        return Inertia::render('promotions/index', [
            'promotions' => $promotions,
            'academicYears' => $academicYears,
            'classes' => $classes,
        ]);
    }

    /**
     * Promote all students in a class (batch).
     * Creates a ClassPromotion record for each student not yet processed.
     */
    public function promoteBatch(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'from_class_id' => ['required', 'exists:school_classes,id'],
            'to_class_id' => ['nullable', 'exists:school_classes,id'],
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'status' => ['required', Rule::in(['naik', 'tinggal', 'lulus'])],
            'notes' => ['nullable', 'string'],
        ]);

        $students = Student::where('school_class_id', $validated['from_class_id'])->get();

        if ($students->isEmpty()) {
            return back()->with('error', 'Tidak ada siswa di kelas ini.');
        }

        DB::transaction(function () use ($validated, $students): void {
            foreach ($students as $student) {
                ClassPromotion::updateOrCreate(
                    ['student_id' => $student->id, 'academic_year_id' => $validated['academic_year_id']],
                    [
                        'from_class_id' => $validated['from_class_id'],
                        'to_class_id' => $validated['to_class_id'] ?? null,
                        'status' => $validated['status'],
                        'notes' => $validated['notes'] ?? null,
                    ]
                );
            }
        });

        return back()->with('success', "Kenaikan kelas batch berhasil diproses untuk {$students->count()} siswa.");
    }

    /**
     * Store or update a single student's promotion (individual override).
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'from_class_id' => ['required', 'exists:school_classes,id'],
            'to_class_id' => ['nullable', 'exists:school_classes,id'],
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'status' => ['required', Rule::in(['naik', 'tinggal', 'lulus'])],
            'notes' => ['nullable', 'string'],
        ]);

        ClassPromotion::updateOrCreate(
            ['student_id' => $validated['student_id'], 'academic_year_id' => $validated['academic_year_id']],
            $validated
        );

        return back()->with('success', 'Data kenaikan kelas berhasil disimpan.');
    }

    public function update(Request $request, ClassPromotion $promotion): RedirectResponse
    {
        $validated = $request->validate([
            'to_class_id' => ['nullable', 'exists:school_classes,id'],
            'status' => ['required', Rule::in(['naik', 'tinggal', 'lulus'])],
            'notes' => ['nullable', 'string'],
        ]);

        $promotion->update($validated);

        return back()->with('success', 'Data kenaikan kelas berhasil diperbarui.');
    }

    public function destroy(ClassPromotion $promotion): RedirectResponse
    {
        $promotion->delete();

        return back()->with('success', 'Data kenaikan kelas berhasil dihapus.');
    }
}
