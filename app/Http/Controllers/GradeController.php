<?php

namespace App\Http\Controllers;

use App\Models\Grade;
use App\Models\GradeConfig;
use App\Models\SchoolClass;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class GradeController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $teacher = $this->teacherForUser($user);

        $gradesQuery = Grade::with(['student', 'subject', 'semester.academicYear', 'teacher.user'])->latest();

        // Guru hanya melihat nilai yang dia input
        if ($teacher) {
            $gradesQuery->where('teacher_id', $teacher->id);
        }

        $grades = $gradesQuery->paginate(30);

        // Scoping dropdown: guru hanya melihat mapel/kelas sesuai jadwalnya
        if ($teacher) {
            $subjectIds = $teacher->schedules()->pluck('subject_id')->unique();
            $subjects = Subject::whereIn('id', $subjectIds)->orderBy('name')->get(['id', 'name']);
        } else {
            $subjects = Subject::orderBy('name')->get(['id', 'name']);
        }

        return Inertia::render('grades/index', [
            'grades' => $grades,
            'classes' => SchoolClass::orderBy('name')->get(['id', 'name']),
            'subjects' => $subjects,
            'semesters' => Semester::with('academicYear:id,name')->get(['id', 'academic_year_id', 'name']),
            'teachers' => Teacher::with('user:id,name')->get(['id', 'user_id']),
            'myTeacherId' => $teacher?->id,
        ]);
    }

    /**
     * Store batch grades for students in a class.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        $teacher = $this->teacherForUser($user);

        $validated = $request->validate([
            'subject_id' => ['required', 'exists:subjects,id'],
            'semester_id' => ['required', 'exists:semesters,id'],
            'teacher_id' => ['required', 'exists:teachers,id'],
            'type' => ['required', Rule::in(['tugas', 'uts', 'uas', 'praktik'])],
            'records' => ['required', 'array', 'min:1'],
            'records.*.student_id' => ['required', 'exists:students,id'],
            'records.*.score' => ['nullable', 'numeric', 'min:0'],
            'records.*.letter_grade' => ['nullable', 'string', 'max:5'],
            'records.*.description' => ['nullable', 'string'],
        ]);

        // Guru hanya boleh input nilai untuk mapel yang dia ampu
        if ($teacher && (int) $validated['teacher_id'] !== $teacher->id) {
            throw ValidationException::withMessages([
                'teacher_id' => 'Anda hanya dapat menginput nilai sebagai guru yang bersangkutan.',
            ]);
        }

        // Ambil config penilaian aktif untuk validasi scale_max
        $semester = Semester::find($validated['semester_id']);
        $gradeConfig = $semester
            ? GradeConfig::where('academic_year_id', $semester->academic_year_id)->first()
            : null;

        $scaleMax = $gradeConfig ? (float) $gradeConfig->scale_max : 100;

        // Validasi score tidak melebihi scale_max
        foreach ($validated['records'] as $i => $record) {
            if (isset($record['score']) && $record['score'] !== null && (float) $record['score'] > $scaleMax) {
                throw ValidationException::withMessages([
                    "records.{$i}.score" => "Nilai tidak boleh melebihi {$scaleMax}.",
                ]);
            }
        }

        DB::transaction(function () use ($validated, $gradeConfig): void {
            foreach ($validated['records'] as $record) {
                $score = isset($record['score']) && $record['score'] !== '' ? (float) $record['score'] : null;
                $letterGrade = $this->resolveLetterGrade($score, $record['letter_grade'] ?? null, $gradeConfig);

                Grade::updateOrCreate(
                    [
                        'student_id' => $record['student_id'],
                        'subject_id' => $validated['subject_id'],
                        'semester_id' => $validated['semester_id'],
                        'type' => $validated['type'],
                    ],
                    [
                        'score' => $score,
                        'letter_grade' => $letterGrade,
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
        $user = $request->user();
        $teacher = $this->teacherForUser($user);

        // Guru hanya boleh edit nilai yang dia input
        if ($teacher && $grade->teacher_id !== $teacher->id) {
            throw ValidationException::withMessages([
                'grade' => 'Anda tidak memiliki akses untuk mengubah nilai ini.',
            ]);
        }

        // Ambil scale_max dari GradeConfig semester yang bersangkutan
        $gradeConfig = GradeConfig::whereHas('academicYear.semesters', function ($q) use ($grade): void {
            $q->where('id', $grade->semester_id);
        })->first();

        $scaleMax = $gradeConfig ? (float) $gradeConfig->scale_max : 100;

        $validated = $request->validate([
            'score' => ['nullable', 'numeric', 'min:0', "max:{$scaleMax}"],
            'letter_grade' => ['nullable', 'string', 'max:5'],
            'description' => ['nullable', 'string'],
        ]);

        $updates = $validated;

        if (array_key_exists('score', $validated) || array_key_exists('letter_grade', $validated)) {
            if (isset($validated['score']) && $validated['score'] !== null && $gradeConfig) {
                $updates['letter_grade'] = $gradeConfig->letterFor((float) $validated['score']);
            } elseif (array_key_exists('letter_grade', $validated)) {
                $updates['letter_grade'] = $validated['letter_grade'];
            }
        }

        $grade->update($updates);

        return back()->with('success', 'Nilai berhasil diperbarui.');
    }

    public function destroy(Grade $grade): RedirectResponse
    {
        $user = request()->user();
        $teacher = $this->teacherForUser($user);

        if ($teacher && $grade->teacher_id !== $teacher->id) {
            abort(403);
        }

        $grade->delete();

        return back()->with('success', 'Nilai berhasil dihapus.');
    }

    private function resolveLetterGrade(?float $score, ?string $letterGrade, ?GradeConfig $gradeConfig): ?string
    {
        if ($score !== null && $gradeConfig) {
            return $gradeConfig->letterFor($score);
        }

        return $letterGrade;
    }

    private function teacherForUser(User $user): ?Teacher
    {
        if (! $user->isGuru()) {
            return null;
        }

        abort_if($user->teacher === null, 403);

        return $user->teacher;
    }
}
