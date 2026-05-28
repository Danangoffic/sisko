import { Head, useForm } from '@inertiajs/react';
import { Pencil, Trash2 } from 'lucide-react';
import { useState } from 'react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import * as ReportCardController from '@/actions/App/Http/Controllers/ReportCardController';
import { dashboard } from '@/routes';

interface Semester { id: number; name: string; academic_year: { id: number; name: string } }

interface ReportCard {
    id: number;
    average: number | null;
    rank: number | null;
    teacher_notes: string | null;
    student: { id: number; name: string; nisn: string; school_class: { name: string } };
    semester: Semester;
}

interface Props {
    reportCards: { data: ReportCard[]; current_page: number; last_page: number };
    semesters: Semester[];
}

export default function ReportCardsIndex({ reportCards, semesters }: Props) {
    const [editingId, setEditingId] = useState<number | null>(null);
    const generateForm = useForm({ semester_id: '', teacher_notes: '' });
    const editForm = useForm({ teacher_notes: '' });
    const deleteForm = useForm({});

    function handleGenerate(e: React.FormEvent) {
        e.preventDefault();
        generateForm.post(ReportCardController.generate().url, { onSuccess: () => generateForm.reset() });
    }

    function startEdit(rc: ReportCard) {
        setEditingId(rc.id);
        editForm.setData({ teacher_notes: rc.teacher_notes ?? '' });
    }

    function handleUpdate(e: React.FormEvent, id: number) {
        e.preventDefault();
        editForm.put(ReportCardController.update(id).url, { onSuccess: () => setEditingId(null) });
    }

    function handleDelete(id: number) {
        if (!confirm('Hapus rapor ini?')) return;
        deleteForm.delete(ReportCardController.destroy(id).url);
    }

    return (
        <>
            <Head title="Rapor" />
            <div className="flex h-full flex-1 flex-col gap-6 p-6 md:flex-row">
                {/* Form Generate */}
                <div className="w-full shrink-0 md:w-72">
                    <h2 className="mb-4 text-lg font-semibold">Generate Rapor</h2>
                    <form onSubmit={handleGenerate} className="space-y-3">
                        <div className="space-y-1">
                            <Label>Semester</Label>
                            <select className="w-full rounded-md border px-3 py-2 text-sm" value={generateForm.data.semester_id} onChange={(e) => generateForm.setData('semester_id', e.target.value)}>
                                <option value="">Pilih Semester</option>
                                {semesters.map((s) => <option key={s.id} value={s.id}>{s.academic_year.name} - {s.name}</option>)}
                            </select>
                            <InputError message={generateForm.errors.semester_id} />
                        </div>
                        <div className="space-y-1">
                            <Label>Catatan Guru (opsional)</Label>
                            <textarea className="w-full rounded-md border px-3 py-2 text-sm" rows={3} value={generateForm.data.teacher_notes} onChange={(e) => generateForm.setData('teacher_notes', e.target.value)} />
                        </div>
                        <Button type="submit" disabled={generateForm.processing} className="w-full">Generate Rapor</Button>
                    </form>
                </div>

                {/* Tabel Rapor */}
                <div className="flex-1 overflow-x-auto">
                    <h2 className="mb-4 text-lg font-semibold">Daftar Rapor</h2>
                    <table className="w-full text-sm">
                        <thead>
                            <tr className="border-b text-left text-muted-foreground">
                                <th className="pb-2 pr-4 font-medium">Siswa</th>
                                <th className="pb-2 pr-4 font-medium">Kelas</th>
                                <th className="pb-2 pr-4 font-medium">Semester</th>
                                <th className="pb-2 pr-4 font-medium">Rata-rata</th>
                                <th className="pb-2 pr-4 font-medium">Ranking</th>
                                <th className="pb-2 pr-4 font-medium">Catatan</th>
                                <th className="pb-2" />
                            </tr>
                        </thead>
                        <tbody>
                            {reportCards.data.length === 0 ? (
                                <tr><td colSpan={7} className="py-6 text-center text-muted-foreground">Belum ada rapor.</td></tr>
                            ) : reportCards.data.map((rc) =>
                                editingId === rc.id ? (
                                    <tr key={rc.id} className="border-b">
                                        <td colSpan={7} className="py-3">
                                            <form onSubmit={(e) => handleUpdate(e, rc.id)} className="flex items-center gap-2">
                                                <span className="font-medium">{rc.student.name}</span>
                                                <textarea className="flex-1 rounded-md border px-2 py-1 text-sm" rows={1} value={editForm.data.teacher_notes} onChange={(e) => editForm.setData('teacher_notes', e.target.value)} placeholder="Catatan guru" />
                                                <Button type="submit" size="sm" disabled={editForm.processing}>Simpan</Button>
                                                <Button type="button" size="sm" variant="ghost" onClick={() => setEditingId(null)}>Batal</Button>
                                            </form>
                                        </td>
                                    </tr>
                                ) : (
                                    <tr key={rc.id} className="border-b last:border-0">
                                        <td className="py-3 pr-4">
                                            <div className="font-medium">{rc.student.name}</div>
                                            <div className="text-xs text-muted-foreground">{rc.student.nisn}</div>
                                        </td>
                                        <td className="py-3 pr-4">{rc.student.school_class.name}</td>
                                        <td className="py-3 pr-4 text-xs">{rc.semester.academic_year.name} - {rc.semester.name}</td>
                                        <td className="py-3 pr-4 font-mono">{rc.average ?? '-'}</td>
                                        <td className="py-3 pr-4 font-mono">{rc.rank ?? '-'}</td>
                                        <td className="py-3 pr-4 text-muted-foreground">{rc.teacher_notes ?? '-'}</td>
                                        <td className="flex gap-1 py-3">
                                            <Button variant="ghost" size="icon" onClick={() => startEdit(rc)}><Pencil className="h-4 w-4" /></Button>
                                            <Button variant="ghost" size="icon" onClick={() => handleDelete(rc.id)} disabled={deleteForm.processing}><Trash2 className="h-4 w-4 text-destructive" /></Button>
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

ReportCardsIndex.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: dashboard() },
        { title: 'Rapor', href: ReportCardController.index().url },
    ],
};
