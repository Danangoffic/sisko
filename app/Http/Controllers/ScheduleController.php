<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Schedule;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ScheduleController extends Controller
{
    public function index(): Response
    {
        $schedules = Schedule::with(['schoolClass', 'subject', 'teacher.user', 'academicYear'])
            ->orderByRaw("CASE day WHEN 'Senin' THEN 1 WHEN 'Selasa' THEN 2 WHEN 'Rabu' THEN 3 WHEN 'Kamis' THEN 4 WHEN 'Jumat' THEN 5 WHEN 'Sabtu' THEN 6 END")
            ->orderBy('start_time')
            ->get();

        return Inertia::render('schedules/index', [
            'schedules' => $schedules,
            'classes' => SchoolClass::orderBy('name')->get(['id', 'name']),
            'subjects' => Subject::orderBy('name')->get(['id', 'name']),
            'teachers' => Teacher::with('user:id,name')->get(['id', 'user_id']),
            'academicYears' => AcademicYear::orderByDesc('id')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'school_class_id' => ['required', 'exists:school_classes,id'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'teacher_id' => ['required', 'exists:teachers,id'],
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'day' => ['required', Rule::in(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'])],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
        ]);

        $this->checkConflict($validated);

        Schedule::create($validated);

        return back()->with('success', 'Jadwal berhasil ditambahkan.');
    }

    public function update(Request $request, Schedule $schedule): RedirectResponse
    {
        $validated = $request->validate([
            'school_class_id' => ['required', 'exists:school_classes,id'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'teacher_id' => ['required', 'exists:teachers,id'],
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'day' => ['required', Rule::in(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'])],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
        ]);

        $this->checkConflict($validated, $schedule->id);

        $schedule->update($validated);

        return back()->with('success', 'Jadwal berhasil diperbarui.');
    }

    public function destroy(Schedule $schedule): RedirectResponse
    {
        $schedule->delete();

        return back()->with('success', 'Jadwal berhasil dihapus.');
    }

    /**
     * Check for time conflicts: same class OR same teacher on same day overlapping time.
     *
     * @param  array<string, mixed>  $data
     */
    private function checkConflict(array $data, ?int $excludeId = null): void
    {
        $query = Schedule::where('academic_year_id', $data['academic_year_id'])
            ->where('day', $data['day'])
            ->where(function ($q) use ($data): void {
                $q->where('school_class_id', $data['school_class_id'])
                    ->orWhere('teacher_id', $data['teacher_id']);
            })
            ->where('start_time', '<', $data['end_time'])
            ->where('end_time', '>', $data['start_time']);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        if ($query->exists()) {
            abort(422, 'Jadwal bentrok dengan jadwal yang sudah ada.');
        }
    }
}
