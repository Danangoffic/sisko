<?php

namespace Database\Seeders;

use App\Models\Grade;
use App\Models\Semester;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Database\Seeder;

class GradeSeeder extends Seeder
{
    public function run(): void
    {
        $student = Student::first();
        $subject = Subject::first();
        $semester = Semester::first();
        $teacher = Teacher::first();

        if (! $student || ! $subject || ! $semester || ! $teacher) {
            $this->command?->info('Skipping GradeSeeder: ensure students, subjects, semesters and teachers exist');

            return;
        }

        $samples = [
            ['type' => 'tugas', 'score' => 82.5, 'letter_grade' => 'B', 'description' => 'Tugas harian'],
            ['type' => 'uts', 'score' => 78.0, 'letter_grade' => 'C+', 'description' => 'Ujian tengah semester'],
            ['type' => 'uas', 'score' => 88.0, 'letter_grade' => 'B+', 'description' => 'Ujian akhir semester'],
        ];

        foreach ($samples as $s) {
            Grade::firstOrCreate(
                [
                    'student_id' => $student->id,
                    'subject_id' => $subject->id,
                    'semester_id' => $semester->id,
                    'type' => $s['type'],
                ],
                [
                    'score' => $s['score'],
                    'letter_grade' => $s['letter_grade'],
                    'description' => $s['description'],
                    'teacher_id' => $teacher->id,
                ]
            );
        }
    }
}
