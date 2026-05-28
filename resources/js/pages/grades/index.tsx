import { Head, useForm } from '@inertiajs/react';
import { Pencil, Trash2 } from 'lucide-react';
import { useState } from 'react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import * as GradeController from '@/actions/App/Http/Controllers/GradeController';
import { dashboard } from '@/routes';

interface Subject { id: number; name: string }
interface Semester { id: number; name: string; academic_year: { id: number; name: string } }
interface Teacher { id: number; user: { name: string } }
interface SchoolClass { id: number; name: string }

interface Grade {
    id: number;
    type: string;
    score: number | null;
    letter_grade: string | null;
    description: string | null;
    student: { id: number; name: string; nisn: string };
    subject: Subject;
    semester: Semester;
    teacher: Teacher;
}

interface Props {
    grades: { data: Grade[]; current_page: number; last_page: number };
    classes: SchoolClass[];
    subjects: Subject[];
    semesters: Semester[];
    teachers: Teacher[];
}

const TYPES = ['tugas', 'uts', 'uas', 'praktik'] as const;

const emptyForm = {
    subject_id: '',
    semester_id: '',
    teacher_id: '',
    type: 'tugas' as typeof TYPES[number],
    records: [] as { student_id: number; score: string; letter_grade: string; description: string }[],
};

export default function GradesIndex({ grades, subjects, semesters, teachers }: Props) {
    const [editingId, setEditingId] = useState<number | null>(null);
    const createForm = useForm({ ...emptyForm });
    const editForm = useForm({ score: '', letter_grade: '', description: '' });
    const deleteForm = useForm({});

    function handleCreate(e: React.FormEvent) {
        e.preventDefault();
        createForm.post(GradeController.store().url, { onSuccess: () => createForm.reset() });
    }

    function startEdit(g: Grade) {
        setEditingId(g.id);
        editForm.setData({ score: String(g.score ?? ''), letter_grade: g.letter_grade ?? '', description: g.description ?? '' });
    }

    function handleUpdate(e: React.FormEvent, id: number) {
        e.preventDefault();
        editForm.put(GradeController.update(id).url, { onSuccess: () => setEditingId(null) });
    }

    function handleDelete(id: number) {
        if (!confirm('Hapus nilai ini?')) return;
        deleteForm.delete(GradeController.destroy(id).url);
    }

    function addRecord() {
        createForm.setData('records', [...createForm.data.records, { student_id: 0, score: '', letter_grade: '', description: '' }]);
    }

    return (
        <>
            <Head title="Penilaian" />
            <div className="flex h-full flex-1 flex-col gap-6 p-6 md:flex-row">
                {/* Form Input Nilai Batch */}
                <div className="w-full shrink-0 md:w-80">
                    <h2 className="mb-4 text-lg font-semibold">Input Nilai</h2>
                    <form onSubmit={handleCreate} className="space-y-3">
                        <SelectField label="Semester" value={createForm.data.semester_id} onChange={(v) => createForm.setData('semester_id', v)} error={createForm.errors.semester_id}>
                            <option value="">Pilih Semester</option>
                            {semesters.map((s) => <option key={s.id} value={s.id}>{s.academic_year.name} - {s.name}</option>)}
                        </SelectField>
                        <SelectField label="Mata Pelajaran" value={createForm.data.subject_id} onChange={(v) => createForm.setData('subject_id', v)} error={createForm.errors.subject_id}>
                            <option value="">Pilih Mapel</option>
                            {subjects.map((s) => <option key={s.id} value={s.id}>{s.name}</option>)}
                        </SelectField>
                        <SelectField label="Guru" value={createForm.data.teacher_id} onChange={(v) => createForm.setData('teacher_id', v)} error={createForm.errors.teacher_id}>
                            <option value="">Pilih Guru</option>
                            {teachers.map((t) => <option key={t.id} value={t.id}>{t.user.name}</option>)}
                        </SelectField>
                        <SelectField label="Tipe" value={createForm.data.type} onChange={(v) => createForm.setData('type', v as typeof TYPES[number])} error={createForm.errors.type}>
                            {TYPES.map((t) => <option key={t} value={t}>{t.toUpperCase()}</option>)}
                        </SelectField>

                        <div className="space-y-2">
                            <div className="flex items-center justify-between">
                                <Label>Siswa & Nilai</Label>
                                <Button type="button" size="sm" variant="outline" onClick={addRecord}>+ Tambah</Button>
                            </div>
                            {createForm.data.records.map((r, i) => (
                                <div key={i} className="flex gap-1">
                                    <Input className="w-20" type="number" placeholder="ID" value={r.student_id || ''} onChange={(e) => {
                                        const recs = [...createForm.data.records];
                                        recs[i] = { ...recs[i], student_id: Number(e.target.value) };
                                        createForm.setData('records', recs);
                                    }} />
                                    <Input className="w-16" type="number" placeholder="Nilai" value={r.score} onChange={(e) => {
                                        const recs = [...createForm.data.records];
                                        recs[i] = { ...recs[i], score: e.target.value };
                                        createForm.setData('records', recs);
                                    }} />
                                    <Input className="w-12" placeholder="A-E" value={r.letter_grade} onChange={(e) => {
                                        const recs = [...createForm.data.records];
                                        recs[i] = { ...recs[i], letter_grade: e.target.value };
                                        createForm.setData('records', recs);
                                    }} />
                                </div>
                            ))}
                            <InputError message={createForm.errors.records} />
                        </div>

                        <Button type="submit" disabled={createForm.processing} className="w-full">Simpan Nilai</Button>
                    </form>
                </div>

                {/* Tabel Nilai */}
                <div className="flex-1 overflow-x-auto">
                    <h2 className="mb-4 text-lg font-semibold">Daftar Nilai</h2>
                    <table className="w-full text-sm">
                        <thead>
                            <tr className="border-b text-left text-muted-foreground">
                                <th className="pb-2 pr-4 font-medium">Siswa</th>
                                <th className="pb-2 pr-4 font-medium">Mapel</th>
                                <th className="pb-2 pr-4 font-medium">Semester</th>
                                <th className="pb-2 pr-4 font-medium">Tipe</th>
                                <th className="pb-2 pr-4 font-medium">Nilai</th>
                                <th className="pb-2 pr-4 font-medium">Huruf</th>
                                <th className="pb-2" />
                            </tr>
                        </thead>
                        <tbody>
                            {grades.data.length === 0 ? (
                                <tr><td colSpan={7} className="py-6 text-center text-muted-foreground">Belum ada data nilai.</td></tr>
                            ) : grades.data.map((g) =>
                                editingId === g.id ? (
                                    <tr key={g.id} className="border-b">
                                        <td colSpan={7} className="py-3">
                                            <form onSubmit={(e) => handleUpdate(e, g.id)} className="flex flex-wrap items-center gap-2">
                                                <Input className="w-20" type="number" value={editForm.data.score} onChange={(e) => editForm.setData('score', e.target.value)} placeholder="Nilai" />
                                                <Input className="w-16" value={editForm.data.letter_grade} onChange={(e) => editForm.setData('letter_grade', e.target.value)} placeholder="Huruf" />
                                                <Input className="w-40" value={editForm.data.description} onChange={(e) => editForm.setData('description', e.target.value)} placeholder="Deskripsi" />
                                                <Button type="submit" size="sm" disabled={editForm.processing}>Simpan</Button>
                                                <Button type="button" size="sm" variant="ghost" onClick={() => setEditingId(null)}>Batal</Button>
                                            </form>
                                        </td>
                                    </tr>
                                ) : (
                                    <tr key={g.id} className="border-b last:border-0">
                                        <td className="py-3 pr-4 font-medium">{g.student.name}</td>
                                        <td className="py-3 pr-4">{g.subject.name}</td>
                                        <td className="py-3 pr-4 text-xs">{g.semester.academic_year.name} - {g.semester.name}</td>
                                        <td className="py-3 pr-4 uppercase">{g.type}</td>
                                        <td className="py-3 pr-4 font-mono">{g.score ?? '-'}</td>
                                        <td className="py-3 pr-4">{g.letter_grade ?? '-'}</td>
                                        <td className="flex gap-1 py-3">
                                            <Button variant="ghost" size="icon" onClick={() => startEdit(g)}><Pencil className="h-4 w-4" /></Button>
                                            <Button variant="ghost" size="icon" onClick={() => handleDelete(g.id)} disabled={deleteForm.processing}><Trash2 className="h-4 w-4 text-destructive" /></Button>
                                        </td>
                                    </tr>
                                )
                            )}
                        </tbody>
                    </table>
                </div>
            </div>
        </>
    );
}

function SelectField({ label, value, onChange, error, children }: { label: string; value: string; onChange: (v: string) => void; error?: string; children: React.ReactNode }) {
    return (
        <div className="space-y-1">
            <Label>{label}</Label>
            <select className="w-full rounded-md border px-3 py-2 text-sm" value={value} onChange={(e) => onChange(e.target.value)}>{children}</select>
            {error && <p className="text-sm text-destructive">{error}</p>}
        </div>
    );
}

GradesIndex.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: dashboard() },
        { title: 'Penilaian', href: GradeController.index().url },
    ],
};
