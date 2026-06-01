import { Head } from '@inertiajs/react';
import { dashboard } from '@/routes';

interface Grade {
    id: number;
    type: string;
    score: number | null;
    letter_grade: string | null;
    description: string | null;
    subject: { name: string };
    semester: { name: string; academic_year: { name: string } };
    teacher: { user: { name: string } };
}

interface Student {
    name: string;
    nisn: string;
    school_class: { name: string } | null;
}

interface Props {
    grades: { data: Grade[]; current_page: number; last_page: number } | null;
    student: Student | null;
}

export default function PortalGrades({ grades, student }: Props) {
    return (
        <>
            <Head title="Nilai Saya" />
            <div className="flex h-full flex-1 flex-col gap-6 p-6">
                <div>
                    <h1 className="text-xl font-semibold">Nilai Saya</h1>
                    {student && (
                        <p className="text-sm text-muted-foreground">
                            {student.name} · {student.school_class?.name ?? 'Belum ada kelas'}
                        </p>
                    )}
                </div>

                {!grades || grades.data.length === 0 ? (
                    <p className="text-sm text-muted-foreground">Belum ada data nilai.</p>
                ) : (
                    <div className="overflow-x-auto">
                        <table className="w-full text-sm">
                            <thead>
                                <tr className="border-b text-left text-muted-foreground">
                                    <th className="pb-2 pr-4 font-medium">Mata Pelajaran</th>
                                    <th className="pb-2 pr-4 font-medium">Semester</th>
                                    <th className="pb-2 pr-4 font-medium">Tipe</th>
                                    <th className="pb-2 pr-4 font-medium">Nilai</th>
                                    <th className="pb-2 pr-4 font-medium">Huruf</th>
                                    <th className="pb-2 font-medium">Guru</th>
                                </tr>
                            </thead>
                            <tbody>
                                {grades.data.map((g) => (
                                    <tr key={g.id} className="border-b last:border-0">
                                        <td className="py-3 pr-4 font-medium">{g.subject.name}</td>
                                        <td className="py-3 pr-4 text-xs">
                                            {g.semester.academic_year.name} – {g.semester.name}
                                        </td>
                                        <td className="py-3 pr-4 uppercase">{g.type}</td>
                                        <td className="py-3 pr-4 font-mono">{g.score ?? '-'}</td>
                                        <td className="py-3 pr-4">
                                            {g.letter_grade ? (
                                                <span className="rounded bg-primary/10 px-2 py-0.5 text-xs font-bold text-primary">
                                                    {g.letter_grade}
                                                </span>
                                            ) : '-'}
                                        </td>
                                        <td className="py-3 text-muted-foreground">{g.teacher.user.name}</td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                )}
            </div>
        </>
    );
}

PortalGrades.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: dashboard() },
        { title: 'Nilai Saya', href: '/portal/grades' },
    ],
};
