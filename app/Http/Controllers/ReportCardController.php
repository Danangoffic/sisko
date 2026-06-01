<?php

namespace App\Http\Controllers;

use App\Models\Grade;
use App\Models\ReportCard;
use App\Models\Semester;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ReportCardController extends Controller
{
    public function index(): Response
    {
        $reportCards = ReportCard::with(['student.schoolClass', 'semester.academicYear'])
            ->latest()
            ->paginate(20);

        $semesters = Semester::with('academicYear:id,name')->get(['id', 'academic_year_id', 'name']);

        return Inertia::render('report-cards/index', [
            'reportCards' => $reportCards,
            'semesters' => $semesters,
        ]);
    }

    /**
     * Generate report cards for all students in a semester.
     */
    public function generate(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'semester_id' => ['required', 'exists:semesters,id'],
            'teacher_notes' => ['nullable', 'string'],
        ]);

        $semesterId = $validated['semester_id'];

        // Get all students who have grades in this semester
        $studentIds = Grade::where('semester_id', $semesterId)
            ->distinct()
            ->pluck('student_id');

        if ($studentIds->isEmpty()) {
            return back()->with('error', 'Tidak ada nilai untuk semester ini.');
        }

        DB::transaction(function () use ($studentIds, $semesterId, $validated): void {
            $averages = [];

            foreach ($studentIds as $studentId) {
                $avg = Grade::where('student_id', $studentId)
                    ->where('semester_id', $semesterId)
                    ->avg('score');

                $averages[] = ['student_id' => $studentId, 'average' => round($avg, 2)];
            }

            // Sort by average descending for ranking
            usort($averages, fn ($a, $b) => $b['average'] <=> $a['average']);

            foreach ($averages as $rank => $item) {
                ReportCard::updateOrCreate(
                    ['student_id' => $item['student_id'], 'semester_id' => $semesterId],
                    [
                        'average' => $item['average'],
                        'rank' => $rank + 1,
                        'teacher_notes' => $validated['teacher_notes'] ?? null,
                    ]
                );
            }
        });

        return back()->with('success', 'Rapor berhasil di-generate untuk '.$studentIds->count().' siswa.');
    }

    /**
     * Download rapor sebagai PDF.
     */
    public function download(ReportCard $reportCard): HttpResponse
    {
        $reportCard->load([
            'student.schoolClass',
            'semester.academicYear',
        ]);

        $grades = Grade::with('subject')
            ->where('student_id', $reportCard->student_id)
            ->where('semester_id', $reportCard->semester_id)
            ->get();

        $pdf = Pdf::loadView('pdf.report-card', [
            'reportCard' => $reportCard,
            'grades' => $grades,
        ])->setPaper('a4', 'portrait');

        $filename = 'rapor-'
            .str_replace(' ', '-', strtolower($reportCard->student->name))
            .'-'.$reportCard->semester->academicYear->name
            .'-'.$reportCard->semester->name
            .'.pdf';

        return $pdf->download($filename);
    }

    public function update(Request $request, ReportCard $reportCard): RedirectResponse
    {
        $validated = $request->validate([
            'teacher_notes' => ['nullable', 'string'],
        ]);

        $reportCard->update($validated);

        return back()->with('success', 'Catatan rapor berhasil diperbarui.');
    }

    public function destroy(ReportCard $reportCard): RedirectResponse
    {
        $reportCard->delete();

        return back()->with('success', 'Rapor berhasil dihapus.');
    }
}
