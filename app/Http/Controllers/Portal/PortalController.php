<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Grade;
use App\Models\Invoice;
use App\Models\ReportCard;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PortalController extends Controller
{
    /**
     * Jadwal kelas siswa.
     */
    public function schedule(Request $request): Response
    {
        $student = $request->user()->student;

        $schedules = $student
            ? Schedule::with(['subject', 'teacher.user', 'academicYear'])
                ->where('school_class_id', $student->school_class_id)
                ->orderByRaw("CASE day WHEN 'Senin' THEN 1 WHEN 'Selasa' THEN 2 WHEN 'Rabu' THEN 3 WHEN 'Kamis' THEN 4 WHEN 'Jumat' THEN 5 WHEN 'Sabtu' THEN 6 END")
                ->orderBy('start_time')
                ->get()
            : collect();

        return Inertia::render('portal/schedule', [
            'schedules' => $schedules,
            'student' => $student?->load('schoolClass'),
        ]);
    }

    /**
     * Rekap absensi siswa.
     */
    public function attendances(Request $request): Response
    {
        $student = $request->user()->student;

        $attendances = $student
            ? Attendance::with('schoolClass')
                ->where('student_id', $student->id)
                ->latest('date')
                ->paginate(30)
            : null;

        $summary = $student
            ? Attendance::where('student_id', $student->id)
                ->selectRaw('status, count(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status')
            : collect();

        return Inertia::render('portal/attendances', [
            'attendances' => $attendances,
            'summary' => $summary,
            'student' => $student?->load('schoolClass'),
        ]);
    }

    /**
     * Nilai siswa.
     */
    public function grades(Request $request): Response
    {
        $student = $request->user()->student;

        $grades = $student
            ? Grade::with(['subject', 'semester.academicYear', 'teacher.user'])
                ->where('student_id', $student->id)
                ->latest()
                ->paginate(30)
            : null;

        return Inertia::render('portal/grades', [
            'grades' => $grades,
            'student' => $student?->load('schoolClass'),
        ]);
    }

    /**
     * Rapor siswa.
     */
    public function reportCards(Request $request): Response
    {
        $student = $request->user()->student;

        $reportCards = $student
            ? ReportCard::with(['semester.academicYear'])
                ->where('student_id', $student->id)
                ->latest()
                ->get()
            : collect();

        return Inertia::render('portal/report-cards', [
            'reportCards' => $reportCards,
            'student' => $student?->load('schoolClass'),
        ]);
    }

    /**
     * Tagihan siswa.
     */
    public function invoices(Request $request): Response
    {
        $student = $request->user()->student;

        $invoices = $student
            ? Invoice::with(['paymentType', 'payments'])
                ->where('student_id', $student->id)
                ->latest()
                ->paginate(20)
            : null;

        return Inertia::render('portal/invoices', [
            'invoices' => $invoices,
            'student' => $student?->load('schoolClass'),
            'midtransClientKey' => config('midtrans.client_key'),
        ]);
    }
}
