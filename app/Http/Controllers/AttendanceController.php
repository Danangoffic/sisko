<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\SchoolClass;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class AttendanceController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $teacher = $user->isGuru() ? $user->teacher : null;

        $classes = SchoolClass::orderBy('name')->get(['id', 'name']);

        $query = Attendance::with(['student', 'schoolClass'])->latest('date');

        // Guru hanya melihat absensi yang dia catat
        if ($teacher) {
            $query->where('recorded_by', $user->id);
        }

        if ($request->filled('school_class_id')) {
            $query->where('school_class_id', $request->input('school_class_id'));
        }

        if ($request->filled('date')) {
            $query->whereDate('date', $request->input('date'));
        }

        return Inertia::render('attendances/index', [
            'attendances' => $query->paginate(30),
            'classes' => $classes,
            'students' => Inertia::optional(fn () => $request->filled('school_class_id')
                ? Student::where('school_class_id', $request->input('school_class_id'))->orderBy('name')->get(['id', 'name', 'nisn'])
                : []
            ),
            'filters' => [
                'school_class_id' => $request->input('school_class_id', ''),
                'date' => $request->input('date', ''),
            ],
        ]);
    }

    /**
     * Store batch attendance for a class on a date.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        $teacher = $user->isGuru() ? $user->teacher : null;

        $validated = $request->validate([
            'school_class_id' => ['required', 'exists:school_classes,id'],
            'date' => ['required', 'date'],
            'schedule_id' => ['nullable', 'exists:schedules,id'],
            'records' => ['required', 'array', 'min:1'],
            'records.*.student_id' => ['required', 'exists:students,id'],
            'records.*.status' => ['required', Rule::in(['hadir', 'izin', 'sakit', 'alpha'])],
            'records.*.note' => ['nullable', 'string'],
        ]);

        // Guru hanya boleh absen kelas yang ada di jadwalnya
        if ($teacher) {
            $hasSchedule = $teacher->schedules()
                ->where('school_class_id', $validated['school_class_id'])
                ->exists();

            if (! $hasSchedule) {
                throw ValidationException::withMessages([
                    'school_class_id' => 'Anda tidak memiliki jadwal mengajar di kelas ini.',
                ]);
            }
        }

        DB::transaction(function () use ($validated, $request): void {
            foreach ($validated['records'] as $record) {
                $query = Attendance::where('student_id', $record['student_id'])
                    ->whereDate('date', $validated['date'])
                    ->where('school_class_id', $validated['school_class_id']);

                if (! empty($validated['schedule_id'])) {
                    $query->where('schedule_id', $validated['schedule_id']);
                } else {
                    $query->whereNull('schedule_id');
                }

                $attendance = $query->first();

                $data = [
                    'student_id' => $record['student_id'],
                    'school_class_id' => $validated['school_class_id'],
                    'schedule_id' => $validated['schedule_id'] ?? null,
                    'date' => $validated['date'],
                    'status' => $record['status'],
                    'note' => $record['note'] ?? null,
                    'recorded_by' => $request->user()->id,
                ];

                if ($attendance) {
                    $attendance->update($data);
                } else {
                    Attendance::create($data);
                }
            }
        });

        return back()->with('success', 'Absensi berhasil disimpan.');
    }

    public function destroy(Attendance $attendance): RedirectResponse
    {
        $user = request()->user();

        // Guru hanya boleh hapus absensi yang dia catat
        if ($user->isGuru() && $attendance->recorded_by !== $user->id) {
            abort(403);
        }

        $attendance->delete();

        return back()->with('success', 'Data absensi berhasil dihapus.');
    }
}
