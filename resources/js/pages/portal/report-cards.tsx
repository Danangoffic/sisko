import { Head } from '@inertiajs/react';
import { dashboard } from '@/routes';

interface ReportCard {
    id: number;
    average: number | null;
    rank: number | null;
    teacher_notes: string | null;
    semester: { name: string; academic_year: { name: string } };
}

interface Student {
    name: string;
    nisn: string;
    school_class: { name: string } | null;
}

interface Props {
    reportCards: ReportCard[];
    student: Student | null;
}

export default function PortalReportCards({ reportCards, student }: Props) {
    return (
        <>
            <Head title="Rapor Saya" />
            <div className="flex h-full flex-1 flex-col gap-6 p-6">
                <div>
                    <h1 className="text-xl font-semibold">Rapor Saya</h1>
                    {student && (
                        <p className="text-sm text-muted-foreground">
                            {student.name} · {student.school_class?.name ?? 'Belum ada kelas'}
                        </p>
                    )}
                </div>

                {reportCards.length === 0 ? (
                    <p className="text-sm text-muted-foreground">Belum ada rapor.</p>
                ) : (
                    <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        {reportCards.map((rc) => (
                            <div key={rc.id} className="rounded-xl border bg-card p-5">
                                <p className="text-sm font-medium text-muted-foreground">
                                    {rc.semester.academic_year.name} – {rc.semester.name}
                                </p>
                                <div className="mt-3 flex items-end gap-4">
                                    <div>
                                        <p className="text-3xl font-bold">{rc.average ?? '-'}</p>
                                        <p className="text-xs text-muted-foreground">Rata-rata</p>
                                    </div>
                                    <div>
                                        <p className="text-3xl font-bold">#{rc.rank ?? '-'}</p>
                                        <p className="text-xs text-muted-foreground">Ranking</p>
                                    </div>
                                </div>
                                {rc.teacher_notes && (
                                    <p className="mt-3 text-sm text-muted-foreground italic">
                                        "{rc.teacher_notes}"
                                    </p>
                                )}
                            </div>
                        ))}
                    </div>
                )}
            </div>
        </>
    );
}

PortalReportCards.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: dashboard() },
        { title: 'Rapor Saya', href: '/portal/report-cards' },
    ],
};
