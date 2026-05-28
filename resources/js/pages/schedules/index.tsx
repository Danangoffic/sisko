import { Head, useForm } from '@inertiajs/react';
import { Trash2 } from 'lucide-react';
import { useState } from 'react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import * as ScheduleController from '@/actions/App/Http/Controllers/ScheduleController';
import { dashboard } from '@/routes';

const DAYS = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'] as const;

interface SchoolClass { id: number; name: string }
interface Subject { id: number; name: string }
interface Teacher { id: number; user: { name: string } }
interface AcademicYear { id: number; name: string }

interface Schedule {
    id: number;
    day: typeof DAYS[number];
    start_time: string;
    end_time: string;
    school_class: SchoolClass;
    subject: Subject;
    teacher: Teacher;
    academic_year: AcademicYear;
}

interface Props {
    schedules: Schedule[];
    classes: SchoolClass[];
    subjects: Subject[];
    teachers: Teacher[];
    academicYears: AcademicYear[];
}

const emptyForm = {
    school_class_id: '',
    subject_id: '',
    teacher_id: '',
    academic_year_id: '',
    day: 'Senin' as typeof DAYS[number],
    start_time: '',
    end_time: '',
};

export default function SchedulesIndex({ schedules, classes, subjects, teachers, academicYears }: Props) {
    const [filterClass, setFilterClass] = useState('');
    const [filterYear, setFilterYear] = useState('');
    const createForm = useForm({ ...emptyForm });
    const deleteForm = useForm({});

    function handleCreate(e: React.FormEvent) {
        e.preventDefault();
        createForm.post(ScheduleController.store().url, { onSuccess: () => createForm.reset() });
    }

    function handleDelete(id: number) {
        if (!confirm('Hapus jadwal ini?')) return;
        deleteForm.delete(ScheduleController.destroy(id).url);
    }

    const filtered = schedules.filter((s) => {
        if (filterClass && String(s.school_class.id) !== filterClass) return false;
        if (filterYear && String(s.academic_year.id) !== filterYear) return false;
        return true;
    });

    // Group by day for grid view
    const byDay = DAYS.reduce<Record<string, Schedule[]>>((acc, day) => {
        acc[day] = filtered.filter((s) => s.day === day);
        return acc;
    }, {} as Record<string, Schedule[]>);

    return (
        <>
            <Head title="Jadwal Pelajaran" />
            <div className="flex h-full flex-1 flex-col gap-6 p-6 md:flex-row">
                {/* Form Tambah */}
                <div className="w-full shrink-0 md:w-72">
                    <h2 className="mb-4 text-lg font-semibold">Tambah Jadwal</h2>
                    <form onSubmit={handleCreate} className="space-y-3">
                        <SelectField label="Tahun Ajaran" value={createForm.data.academic_year_id} onChange={(v) => createForm.setData('academic_year_id', v)} error={createForm.errors.academic_year_id}>
                            <option value="">Pilih Tahun Ajaran</option>
                            {academicYears.map((ay) => <option key={ay.id} value={ay.id}>{ay.name}</option>)}
                        </SelectField>
                        <SelectField label="Kelas" value={createForm.data.school_class_id} onChange={(v) => createForm.setData('school_class_id', v)} error={createForm.errors.school_class_id}>
                            <option value="">Pilih Kelas</option>
                            {classes.map((c) => <option key={c.id} value={c.id}>{c.name}</option>)}
                        </SelectField>
                        <SelectField label="Mata Pelajaran" value={createForm.data.subject_id} onChange={(v) => createForm.setData('subject_id', v)} error={createForm.errors.subject_id}>
                            <option value="">Pilih Mapel</option>
                            {subjects.map((s) => <option key={s.id} value={s.id}>{s.name}</option>)}
                        </SelectField>
                        <SelectField label="Guru" value={createForm.data.teacher_id} onChange={(v) => createForm.setData('teacher_id', v)} error={createForm.errors.teacher_id}>
                            <option value="">Pilih Guru</option>
                            {teachers.map((t) => <option key={t.id} value={t.id}>{t.user.name}</option>)}
                        </SelectField>
                        <SelectField label="Hari" value={createForm.data.day} onChange={(v) => createForm.setData('day', v as typeof DAYS[number])} error={createForm.errors.day}>
                            {DAYS.map((d) => <option key={d} value={d}>{d}</option>)}
                        </SelectField>
                        <div className="grid grid-cols-2 gap-2">
                            <div className="space-y-1">
                                <Label>Mulai</Label>
                                <Input type="time" value={createForm.data.start_time} onChange={(e) => createForm.setData('start_time', e.target.value)} />
                                <InputError message={createForm.errors.start_time} />
                            </div>
                            <div className="space-y-1">
                                <Label>Selesai</Label>
                                <Input type="time" value={createForm.data.end_time} onChange={(e) => createForm.setData('end_time', e.target.value)} />
                                <InputError message={createForm.errors.end_time} />
                            </div>
                        </div>
                        <Button type="submit" disabled={createForm.processing} className="w-full">Simpan</Button>
                    </form>
                </div>

                {/* Grid Jadwal */}
                <div className="flex-1 overflow-x-auto">
                    <div className="mb-4 flex flex-wrap items-center gap-3">
                        <h2 className="text-lg font-semibold">Jadwal Pelajaran</h2>
                        <select className="rounded-md border px-3 py-1.5 text-sm" value={filterYear} onChange={(e) => setFilterYear(e.target.value)}>
                            <option value="">Semua Tahun Ajaran</option>
                            {academicYears.map((ay) => <option key={ay.id} value={ay.id}>{ay.name}</option>)}
                        </select>
                        <select className="rounded-md border px-3 py-1.5 text-sm" value={filterClass} onChange={(e) => setFilterClass(e.target.value)}>
                            <option value="">Semua Kelas</option>
                            {classes.map((c) => <option key={c.id} value={c.id}>{c.name}</option>)}
                        </select>
                    </div>

                    <div className="space-y-4">
                        {DAYS.map((day) => (
                            <div key={day}>
                                <h3 className="mb-2 font-medium text-muted-foreground">{day}</h3>
                                {byDay[day].length === 0 ? (
                                    <p className="text-sm text-muted-foreground/60 pl-2">Tidak ada jadwal.</p>
                                ) : (
                                    <div className="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                                        {byDay[day].map((s) => (
                                            <div key={s.id} className="flex items-start justify-between rounded-lg border p-3">
                                                <div>
                                                    <div className="font-medium">{s.subject.name}</div>
                                                    <div className="text-xs text-muted-foreground">{s.school_class.name} · {s.teacher.user.name}</div>
                                                    <div className="mt-1 text-xs">{s.start_time} – {s.end_time}</div>
                                                </div>
                                                <Button variant="ghost" size="icon" className="h-7 w-7 shrink-0" onClick={() => handleDelete(s.id)} disabled={deleteForm.processing}>
                                                    <Trash2 className="h-3.5 w-3.5 text-destructive" />
                                                </Button>
                                            </div>
                                        ))}
                                    </div>
                                )}
                            </div>
                        ))}
                    </div>
                </div>
            </div>
        </>
    );
}

function SelectField({ label, value, onChange, error, children }: {
    label: string;
    value: string;
    onChange: (v: string) => void;
    error?: string;
    children: React.ReactNode;
}) {
    return (
        <div className="space-y-1">
            <Label>{label}</Label>
            <select className="w-full rounded-md border px-3 py-2 text-sm" value={value} onChange={(e) => onChange(e.target.value)}>
                {children}
            </select>
            {error && <p className="text-sm text-destructive">{error}</p>}
        </div>
    );
}

SchedulesIndex.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: dashboard() },
        { title: 'Jadwal Pelajaran', href: ScheduleController.index().url },
    ],
};
