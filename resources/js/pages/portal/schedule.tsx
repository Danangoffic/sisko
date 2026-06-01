import { Head } from '@inertiajs/react';
import { dashboard } from '@/routes';

interface Schedule {
    id: number;
    day: string;
    start_time: string;
    end_time: string;
    subject: { name: string };
    teacher: { user: { name: string } };
    academic_year: { name: string };
}

interface Student {
    name: string;
    nisn: string;
    school_class: { name: string } | null;
}

interface Props {
    schedules: Schedule[];
    student: Student | null;
}

const DAYS = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

export default function PortalSchedule({ schedules, student }: Props) {
    const byDay = DAYS.reduce<Record<string, Schedule[]>>((acc, day) => {
        acc[day] = schedules.filter((s) => s.day === day);
        return acc;
    }, {});

    return (
        <>
            <Head title="Jadwal Saya" />
            <div className="flex h-full flex-1 flex-col gap-6 p-6">
                <div>
                    <h1 className="text-xl font-semibold">Jadwal Pelajaran</h1>
                    {student && (
                        <p className="text-sm text-muted-foreground">
                            {student.name} · {student.school_class?.name ?? 'Belum ada kelas'}
                        </p>
                    )}
                </div>

                {schedules.length === 0 ? (
                    <p className="text-sm text-muted-foreground">Belum ada jadwal untuk kelas Anda.</p>
                ) : (
                    <div className="space-y-6">
                        {DAYS.filter((day) => byDay[day].length > 0).map((day) => (
                            <div key={day}>
                                <h2 className="mb-2 font-medium">{day}</h2>
                                <div className="space-y-2">
                                    {byDay[day].map((s) => (
                                        <div
                                            key={s.id}
                                            className="flex items-center gap-4 rounded-lg border bg-card p-3"
                                        >
                                            <div className="w-24 shrink-0 text-sm font-mono text-muted-foreground">
                                                {s.start_time} – {s.end_time}
                                            </div>
                                            <div>
                                                <p className="font-medium">{s.subject.name}</p>
                                                <p className="text-sm text-muted-foreground">{s.teacher.user.name}</p>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            </div>
                        ))}
                    </div>
                )}
            </div>
        </>
    );
}

PortalSchedule.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: dashboard() },
        { title: 'Jadwal Saya', href: '/portal/schedule' },
    ],
};
