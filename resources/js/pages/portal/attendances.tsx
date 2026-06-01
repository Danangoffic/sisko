import { Head } from '@inertiajs/react';
import { dashboard } from '@/routes';

interface Attendance {
    id: number;
    date: string;
    status: 'hadir' | 'izin' | 'sakit' | 'alpha';
    note: string | null;
    school_class: { name: string };
}

interface Student {
    name: string;
    nisn: string;
    school_class: { name: string } | null;
}

interface Props {
    attendances: { data: Attendance[]; current_page: number; last_page: number } | null;
    summary: Record<string, number>;
    student: Student | null;
}

const STATUS_LABEL: Record<string, string> = {
    hadir: 'Hadir',
    izin: 'Izin',
    sakit: 'Sakit',
    alpha: 'Alpha',
};

const STATUS_COLOR: Record<string, string> = {
    hadir: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
    izin: 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
    sakit: 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400',
    alpha: 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
};

export default function PortalAttendances({ attendances, summary, student }: Props) {
    return (
        <>
            <Head title="Absensi Saya" />
            <div className="flex h-full flex-1 flex-col gap-6 p-6">
                <div>
                    <h1 className="text-xl font-semibold">Rekap Absensi</h1>
                    {student && (
                        <p className="text-sm text-muted-foreground">
                            {student.name} · {student.school_class?.name ?? 'Belum ada kelas'}
                        </p>
                    )}
                </div>

                {/* Ringkasan */}
                <div className="grid grid-cols-2 gap-3 sm:grid-cols-4">
                    {(['hadir', 'izin', 'sakit', 'alpha'] as const).map((s) => (
                        <div key={s} className={`rounded-lg border p-3 text-center ${STATUS_COLOR[s]}`}>
                            <p className="text-2xl font-bold">{summary[s] ?? 0}</p>
                            <p className="text-sm">{STATUS_LABEL[s]}</p>
                        </div>
                    ))}
                </div>

                {/* Tabel */}
                {!attendances || attendances.data.length === 0 ? (
                    <p className="text-sm text-muted-foreground">Belum ada data absensi.</p>
                ) : (
                    <div className="overflow-x-auto">
                        <table className="w-full text-sm">
                            <thead>
                                <tr className="border-b text-left text-muted-foreground">
                                    <th className="pb-2 pr-4 font-medium">Tanggal</th>
                                    <th className="pb-2 pr-4 font-medium">Status</th>
                                    <th className="pb-2 font-medium">Keterangan</th>
                                </tr>
                            </thead>
                            <tbody>
                                {attendances.data.map((a) => (
                                    <tr key={a.id} className="border-b last:border-0">
                                        <td className="py-3 pr-4">
                                            {new Date(a.date).toLocaleDateString('id-ID', {
                                                weekday: 'short',
                                                day: 'numeric',
                                                month: 'short',
                                                year: 'numeric',
                                            })}
                                        </td>
                                        <td className="py-3 pr-4">
                                            <span className={`rounded px-2 py-0.5 text-xs font-medium ${STATUS_COLOR[a.status]}`}>
                                                {STATUS_LABEL[a.status]}
                                            </span>
                                        </td>
                                        <td className="py-3 text-muted-foreground">{a.note ?? '-'}</td>
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

PortalAttendances.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: dashboard() },
        { title: 'Absensi Saya', href: '/portal/attendances' },
    ],
};
