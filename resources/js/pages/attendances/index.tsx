import { Head, router, useForm } from '@inertiajs/react';
import { Trash2 } from 'lucide-react';
import { useState } from 'react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import * as AttendanceController from '@/actions/App/Http/Controllers/AttendanceController';
import { dashboard } from '@/routes';

const STATUS_OPTIONS = ['hadir', 'izin', 'sakit', 'alpha'] as const;
type Status = typeof STATUS_OPTIONS[number];

const statusColor: Record<Status, string> = {
    hadir: 'bg-green-100 text-green-700',
    izin: 'bg-blue-100 text-blue-700',
    sakit: 'bg-yellow-100 text-yellow-700',
    alpha: 'bg-red-100 text-red-700',
};

interface SchoolClass { id: number; name: string }
interface StudentItem { id: number; name: string; nisn: string }
interface Attendance {
    id: number;
    date: string;
    status: Status;
    note: string | null;
    student: { id: number; name: string; nisn: string };
    school_class: SchoolClass;
}

interface Props {
    attendances: { data: Attendance[]; current_page: number; last_page: number };
    classes: SchoolClass[];
    students?: StudentItem[];
    filters: { school_class_id: string; date: string };
}

export default function AttendancesIndex({ attendances, classes, students, filters }: Props) {
    const [records, setRecords] = useState<Record<number, { status: Status; note: string }>>({});
    const deleteForm = useForm({});

    const form = useForm({
        school_class_id: filters.school_class_id,
        date: filters.date || new Date().toISOString().slice(0, 10),
        schedule_id: null as number | null,
        records: [] as { student_id: number; status: Status; note: string }[],
    });

    function handleFilter() {
        router.get(AttendanceController.index().url, {
            school_class_id: form.data.school_class_id,
            date: form.data.date,
        }, { preserveState: true, only: ['attendances', 'students', 'filters'] });
    }

    function initRecords() {
        if (!students?.length) return;
        const init: Record<number, { status: Status; note: string }> = {};
        students.forEach((s) => { init[s.id] = { status: 'hadir', note: '' }; });
        setRecords(init);
    }

    function handleSubmit(e: React.FormEvent) {
        e.preventDefault();
        const recs = Object.entries(records).map(([id, r]) => ({
            student_id: Number(id),
            status: r.status,
            note: r.note || '',
        }));
        form.transform((data) => ({ ...data, records: recs }));
        form.post(AttendanceController.store().url);
    }

    function handleDelete(id: number) {
        if (!confirm('Hapus data absensi ini?')) return;
        deleteForm.delete(AttendanceController.destroy(id).url);
    }

    return (
        <>
            <Head title="Absensi" />
            <div className="flex h-full flex-1 flex-col gap-6 p-6 md:flex-row">
                {/* Panel Kiri: Filter + Form Input */}
                <div className="w-full shrink-0 space-y-4 md:w-80">
                    <h2 className="text-lg font-semibold">Input Absensi</h2>

                    <div className="space-y-3">
                        <div className="space-y-1">
                            <Label>Kelas</Label>
                            <select className="w-full rounded-md border px-3 py-2 text-sm" value={form.data.school_class_id} onChange={(e) => form.setData('school_class_id', e.target.value)}>
                                <option value="">Pilih Kelas</option>
                                {classes.map((c) => <option key={c.id} value={c.id}>{c.name}</option>)}
                            </select>
                        </div>
                        <div className="space-y-1">
                            <Label>Tanggal</Label>
                            <Input type="date" value={form.data.date} onChange={(e) => form.setData('date', e.target.value)} />
                        </div>
                        <Button type="button" variant="outline" className="w-full" onClick={() => { handleFilter(); initRecords(); }}>
                            Tampilkan Siswa
                        </Button>
                    </div>

                    {students && students.length > 0 && Object.keys(records).length > 0 && (
                        <form onSubmit={handleSubmit} className="space-y-2">
                            <div className="max-h-96 space-y-2 overflow-y-auto rounded-md border p-2">
                                {students.map((s) => (
                                    <div key={s.id} className="flex items-center gap-2 rounded-md p-1.5 text-sm hover:bg-muted/40">
                                        <span className="min-w-0 flex-1 truncate">{s.name}</span>
                                        <select
                                            className="rounded border px-1.5 py-1 text-xs"
                                            value={records[s.id]?.status ?? 'hadir'}
                                            onChange={(e) => setRecords((prev) => ({ ...prev, [s.id]: { ...prev[s.id], status: e.target.value as Status } }))}
                                        >
                                            {STATUS_OPTIONS.map((st) => <option key={st} value={st}>{st}</option>)}
                                        </select>
                                    </div>
                                ))}
                            </div>
                            <Button type="submit" disabled={form.processing} className="w-full">Simpan Absensi</Button>
                        </form>
                    )}
                </div>

                {/* Tabel Riwayat */}
                <div className="flex-1 overflow-x-auto">
                    <h2 className="mb-4 text-lg font-semibold">Riwayat Absensi</h2>
                    <table className="w-full text-sm">
                        <thead>
                            <tr className="border-b text-left text-muted-foreground">
                                <th className="pb-2 pr-4 font-medium">Tanggal</th>
                                <th className="pb-2 pr-4 font-medium">Siswa</th>
                                <th className="pb-2 pr-4 font-medium">Kelas</th>
                                <th className="pb-2 pr-4 font-medium">Status</th>
                                <th className="pb-2 pr-4 font-medium">Catatan</th>
                                <th className="pb-2" />
                            </tr>
                        </thead>
                        <tbody>
                            {attendances.data.length === 0 ? (
                                <tr><td colSpan={6} className="py-6 text-center text-muted-foreground">Belum ada data absensi.</td></tr>
                            ) : attendances.data.map((a) => (
                                <tr key={a.id} className="border-b last:border-0">
                                    <td className="py-3 pr-4">{a.date}</td>
                                    <td className="py-3 pr-4">
                                        <div className="font-medium">{a.student.name}</div>
                                        <div className="text-xs text-muted-foreground">{a.student.nisn}</div>
                                    </td>
                                    <td className="py-3 pr-4">{a.school_class.name}</td>
                                    <td className="py-3 pr-4">
                                        <span className={`rounded-full px-2 py-0.5 text-xs capitalize ${statusColor[a.status]}`}>{a.status}</span>
                                    </td>
                                    <td className="py-3 pr-4 text-muted-foreground">{a.note ?? '-'}</td>
                                    <td className="py-3">
                                        <Button variant="ghost" size="icon" onClick={() => handleDelete(a.id)} disabled={deleteForm.processing}>
                                            <Trash2 className="h-4 w-4 text-destructive" />
                                        </Button>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            </div>
        </>
    );
}

AttendancesIndex.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: dashboard() },
        { title: 'Absensi', href: AttendanceController.index().url },
    ],
};
