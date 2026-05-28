<?php

namespace App\Http\Controllers;

use App\Models\Grade;
use App\Models\SchoolClass;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class GradeController extends Controller
{
    public function index(): Response
    {
        $grades = Grade::with(['student', 'subject', 'semester.academicYear', 'teacher.user'])
            ->latest()
            ->paginate(30);

        return Inertia::render('grades/index', [
            'grades' => $grades,
            'classes' => SchoolClass::orderBy('name')->get(['id', 'name']),
            'subjects' => Subject::orderBy('name')->get(['id', 'name']),
            'semesters' => Semester::with('academicYear:id,name')->get(['id', 'academic_year_id', 'name']),
            'teachers' => Teacher::with('user:id,name')->get(['id', 'user_id']),
        ]);
    }

    /**
     * Store batch grades for students in a class.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'subject_id' => ['required', 'exists:subjects,id'],
            'semester_id' => ['required', 'exists:semesters,id'],
            'teacher_id' => ['required', 'exists:teachers,id'],
            'type' => ['required', Rule::in(['tugas', 'uts', 'uas', 'praktik'])],
            'records' => ['required', 'array', 'min:1'],
            'records.*.student_id' => ['required', 'exists:students,id'],
            'records.*.score' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'records.*.letter_grade' => ['nullable', 'string', 'max:5'],
            'records.*.description' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($validated): void {
            foreach ($validated['records'] as $record) {
                Grade::updateOrCreate(
                    [
                        'student_id' => $record['student_id'],
                        'subject_id' => $validated['subject_id'],
                        'semester_id' => $validated['semester_id'],
                        'type' => $validated['type'],
                    ],
                    [
                        'score' => $record['score'] ?? null,
                        'letter_grade' => $record['letter_grade'] ?? null,
                        'description' => $record['description'] ?? null,
                        'teacher_id' => $validated['teacher_id'],
                    ]
                );
            }
        });

        return back()->with('success', 'Nilai berhasil disimpan.');
    }

    public function update(Request $request, Grade $grade): RedirectResponse
    {
        $validated = $request->validate([
            'score' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'letter_grade' => ['nullable', 'string', 'max:5'],
            'description' => ['nullable', 'string'],
        ]);

        $grade->update($validated);

        return back()->with('success', 'Nilai berhasil diperbarui.');
    }

    public function destroy(Grade $grade): RedirectResponse
    {
        $grade->delete();

        return back()->with('success', 'Nilai berhasil dihapus.');
    }
}
