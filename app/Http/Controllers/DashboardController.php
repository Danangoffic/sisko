<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Attendance;
use App\Models\Grade;
use App\Models\Invoice;
use App\Models\ReportCard;
use App\Models\Schedule;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        $announcements = Announcement::with('author:id,name')
            ->whereNotNull('published_at')
            ->where(function ($q) use ($user): void {
                if (! $user->isAdmin()) {
                    $q->where('target_role', 'all')
                        ->orWhere('target_role', $user->role->value);
                }
            })
            ->orderByDesc('is_pinned')
            ->latest('published_at')
            ->limit(5)
            ->get();

        $stats = match (true) {
            $user->isAdmin() => $this->adminStats(),
            $user->isGuru() => $this->guruStats($user),
            default => $this->siswaStats($user),
        };

        return Inertia::render('dashboard', [
            'announcements' => $announcements,
            'stats' => $stats,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function adminStats(): array
    {
        return [
            'total_siswa' => Student::count(),
            'total_guru' => Teacher::count(),
            'total_kelas' => SchoolClass::count(),
            'invoice_pending' => Invoice::where('status', 'pending')->count(),
            'invoice_overdue' => Invoice::where('status', 'overdue')->count(),
            'absensi_hari_ini' => Attendance::whereDate('date', today())->count(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function guruStats(mixed $user): array
    {
        $teacher = $user->teacher;

        if (! $teacher) {
            return ['jadwal_hari_ini' => 0, 'absensi_hari_ini' => 0, 'total_nilai_diinput' => 0];
        }

        $hariIni = now()->locale('id')->isoFormat('dddd');
        $hariMap = [
            'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu',
        ];
        $hariIndo = $hariMap[now()->format('l')] ?? now()->format('l');

        return [
            'jadwal_hari_ini' => Schedule::where('teacher_id', $teacher->id)
                ->where('day', $hariIndo)
                ->count(),
            'absensi_hari_ini' => Attendance::where('recorded_by', $user->id)
                ->whereDate('date', today())
                ->count(),
            'total_nilai_diinput' => Grade::where('teacher_id', $teacher->id)->count(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function siswaStats(mixed $user): array
    {
        $student = $user->student;

        if (! $student) {
            return ['tagihan_pending' => 0, 'rata_rata_nilai' => null];
        }

        $latestReportCard = ReportCard::where('student_id', $student->id)
            ->with('semester.academicYear')
            ->latest()
            ->first();

        return [
            'tagihan_pending' => Invoice::where('student_id', $student->id)
                ->whereIn('status', ['pending', 'overdue'])
                ->count(),
            'tagihan_overdue' => Invoice::where('student_id', $student->id)
                ->where('status', 'overdue')
                ->count(),
            'rata_rata_nilai' => $latestReportCard?->average,
            'ranking' => $latestReportCard?->rank,
            'kelas' => $student->schoolClass?->name,
        ];
    }
}
