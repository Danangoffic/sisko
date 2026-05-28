import { Head, useForm } from '@inertiajs/react';
import { Pencil, Trash2 } from 'lucide-react';
import { useState } from 'react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import * as ClassPromotionController from '@/actions/App/Http/Controllers/ClassPromotionController';
import { dashboard } from '@/routes';

interface SchoolClass {
    id: number;
    name: string;
}

interface AcademicYear {
    id: number;
    name: string;
}

interface Promotion {
    id: number;
    status: 'naik' | 'tinggal' | 'lulus';
    notes: string | null;
    student: { id: number; name: string; nisn: string };
    from_class: SchoolClass;
    to_class: SchoolClass | null;
    academic_year: AcademicYear;
}

interface Props {
    promotions: { data: Promotion[]; current_page: number; last_page: number };
    academicYears: AcademicYear[];
    classes: SchoolClass[];
}

const statusLabel: Record<string, string> = { naik: 'Naik Kelas', tinggal: 'Tinggal Kelas', lulus: 'Lulus' };
const statusColor: Record<string, string> = {
    naik: 'bg-green-100 text-green-700',
    tinggal: 'bg-yellow-100 text-yellow-700',
    lulus: 'bg-blue-100 text-blue-700',
};

const emptyBatch = { from_class_id: '', to_class_id: '', academic_year_id: '', status: 'naik' as const, notes: '' };
const emptyIndividual = { student_id: '', from_class_id: '', to_class_id: '', academic_year_id: '', status: 'naik' as const, notes: '' };

export default function PromotionsIndex({ promotions, academicYears, classes }: Props) {
    const [tab, setTab] = useState<'batch' | 'individual'>('batch');
    const [editingId, setEditingId] = useState<number | null>(null);

    const batchForm = useForm({ ...emptyBatch });
    const individualForm = useForm({ ...emptyIndividual });
    const editForm = useForm({ to_class_id: '', status: 'naik' as 'naik' | 'tinggal' | 'lulus', notes: '' });
    const deleteForm = useForm({});

    function handleBatch(e: React.FormEvent) {
        e.preventDefault();
        batchForm.post(ClassPromotionController.promoteBatch().url, { onSuccess: () => batchForm.reset() });
    }

    function handleIndividual(e: React.FormEvent) {
        e.preventDefault();
        individualForm.post(ClassPromotionController.store().url, { onSuccess: () => individualForm.reset() });
    }

    function startEdit(p: Promotion) {
        setEditingId(p.id);
        editForm.setData({ to_class_id: String(p.to_class?.id ?? ''), status: p.status, notes: p.notes ?? '' });
    }

    function handleUpdate(e: React.FormEvent, id: number) {
        e.preventDefault();
        editForm.put(ClassPromotionController.update(id).url, { onSuccess: () => setEditingId(null) });
    }

    function handleDelete(id: number) {
        if (!confirm('Hapus data kenaikan kelas ini?')) return;
        deleteForm.delete(ClassPromotionController.destroy(id).url);
    }

    return (
        <>
            <Head title="Kenaikan Kelas" />
            <div className="flex h-full flex-1 flex-col gap-6 p-6 md:flex-row">
                {/* Panel Kiri: Form */}
                <div className="w-full shrink-0 md:w-80">
                    <div className="mb-4 flex gap-2">
                        <Button size="sm" variant={tab === 'batch' ? 'default' : 'outline'} onClick={() => setTab('batch')}>Batch</Button>
                        <Button size="sm" variant={tab === 'individual' ? 'default' : 'outline'} onClick={() => setTab('individual')}>Individual</Button>
                    </div>

                    {tab === 'batch' ? (
                        <>
                            <h2 className="mb-3 font-semibold">Proses Batch per Kelas</h2>
                            <form onSubmit={handleBatch} className="space-y-3">
                                <SelectField label="Tahun Ajaran" value={batchForm.data.academic_year_id} onChange={(v) => batchForm.setData('academic_year_id', v)} error={batchForm.errors.academic_year_id}>
                                    <option value="">Pilih Tahun Ajaran</option>
                                    {academicYears.map((ay) => <option key={ay.id} value={ay.id}>{ay.name}</option>)}
                                </SelectField>
                                <SelectField label="Dari Kelas" value={batchForm.data.from_class_id} onChange={(v) => batchForm.setData('from_class_id', v)} error={batchForm.errors.from_class_id}>
                                    <option value="">Pilih Kelas</option>
                                    {classes.map((c) => <option key={c.id} value={c.id}>{c.name}</option>)}
                                </SelectField>
                                <SelectField label="Ke Kelas (opsional)" value={batchForm.data.to_class_id} onChange={(v) => batchForm.setData('to_class_id', v)} error={batchForm.errors.to_class_id}>
                                    <option value="">-</option>
                                    {classes.map((c) => <option key={c.id} value={c.id}>{c.name}</option>)}
                                </SelectField>
                                <SelectField label="Status" value={batchForm.data.status} onChange={(v) => batchForm.setData('status', v as typeof batchForm.data.status)} error={batchForm.errors.status}>
                                    <option value="naik">Naik Kelas</option>
                                    <option value="tinggal">Tinggal Kelas</option>
                                    <option value="lulus">Lulus</option>
                                </SelectField>
                                <div className="space-y-1">
                                    <Label>Catatan</Label>
                                    <textarea className="w-full rounded-md border px-3 py-2 text-sm" rows={2} value={batchForm.data.notes} onChange={(e) => batchForm.setData('notes', e.target.value)} />
                                </div>
                                <Button type="submit" disabled={batchForm.processing} className="w-full">Proses Batch</Button>
                            </form>
                        </>
                    ) : (
                        <>
                            <h2 className="mb-3 font-semibold">Override Individual</h2>
                            <form onSubmit={handleIndividual} className="space-y-3">
                                <div className="space-y-1">
                                    <Label>ID Siswa</Label>
                                    <Input type="number" value={individualForm.data.student_id} onChange={(e) => individualForm.setData('student_id', e.target.value)} placeholder="ID siswa" />
                                    <InputError message={individualForm.errors.student_id} />
                                </div>
                                <SelectField label="Tahun Ajaran" value={individualForm.data.academic_year_id} onChange={(v) => individualForm.setData('academic_year_id', v)} error={individualForm.errors.academic_year_id}>
                                    <option value="">Pilih Tahun Ajaran</option>
                                    {academicYears.map((ay) => <option key={ay.id} value={ay.id}>{ay.name}</option>)}
                                </SelectField>
                                <SelectField label="Dari Kelas" value={individualForm.data.from_class_id} onChange={(v) => individualForm.setData('from_class_id', v)} error={individualForm.errors.from_class_id}>
                                    <option value="">Pilih Kelas</option>
                                    {classes.map((c) => <option key={c.id} value={c.id}>{c.name}</option>)}
                                </SelectField>
                                <SelectField label="Ke Kelas (opsional)" value={individualForm.data.to_class_id} onChange={(v) => individualForm.setData('to_class_id', v)} error={individualForm.errors.to_class_id}>
                                    <option value="">-</option>
                                    {classes.map((c) => <option key={c.id} value={c.id}>{c.name}</option>)}
                                </SelectField>
                                <SelectField label="Status" value={individualForm.data.status} onChange={(v) => individualForm.setData('status', v as typeof individualForm.data.status)} error={individualForm.errors.status}>
                                    <option value="naik">Naik Kelas</option>
                                    <option value="tinggal">Tinggal Kelas</option>
                                    <option value="lulus">Lulus</option>
                                </SelectField>
                                <div className="space-y-1">
                                    <Label>Catatan</Label>
                                    <textarea className="w-full rounded-md border px-3 py-2 text-sm" rows={2} value={individualForm.data.notes} onChange={(e) => individualForm.setData('notes', e.target.value)} />
                                </div>
                                <Button type="submit" disabled={individualForm.processing} className="w-full">Simpan</Button>
                            </form>
                        </>
                    )}
                </div>

                {/* Tabel */}
                <div className="flex-1 overflow-x-auto">
                    <h2 className="mb-4 text-lg font-semibold">Riwayat Kenaikan Kelas</h2>
                    <table className="w-full text-sm">
                        <thead>
                            <tr className="border-b text-left text-muted-foreground">
                                <th className="pb-2 pr-4 font-medium">Siswa</th>
                                <th className="pb-2 pr-4 font-medium">Tahun Ajaran</th>
                                <th className="pb-2 pr-4 font-medium">Dari</th>
                                <th className="pb-2 pr-4 font-medium">Ke</th>
                                <th className="pb-2 pr-4 font-medium">Status</th>
                                <th className="pb-2 pr-4 font-medium">Catatan</th>
                                <th className="pb-2" />
                            </tr>
                        </thead>
                        <tbody>
                            {promotions.data.length === 0 ? (
                                <tr><td colSpan={7} className="py-6 text-center text-muted-foreground">Belum ada data kenaikan kelas.</td></tr>
                            ) : promotions.data.map((p) =>
                                editingId === p.id ? (
                                    <tr key={p.id} className="border-b">
                                        <td colSpan={7} className="py-3">
                                            <form onSubmit={(e) => handleUpdate(e, p.id)} className="flex flex-wrap items-center gap-2">
                                                <select className="rounded-md border px-2 py-1.5 text-sm" value={editForm.data.to_class_id} onChange={(e) => editForm.setData('to_class_id', e.target.value)}>
                                                    <option value="">- Ke Kelas -</option>
                                                    {classes.map((c) => <option key={c.id} value={c.id}>{c.name}</option>)}
                                                </select>
                                                <select className="rounded-md border px-2 py-1.5 text-sm" value={editForm.data.status} onChange={(e) => editForm.setData('status', e.target.value as typeof editForm.data.status)}>
                                                    <option value="naik">Naik Kelas</option>
                                                    <option value="tinggal">Tinggal Kelas</option>
                                                    <option value="lulus">Lulus</option>
                                                </select>
                                                <Input className="w-40" value={editForm.data.notes} onChange={(e) => editForm.setData('notes', e.target.value)} placeholder="Catatan" />
                                                <Button type="submit" size="sm" disabled={editForm.processing}>Simpan</Button>
                                                <Button type="button" size="sm" variant="ghost" onClick={() => setEditingId(null)}>Batal</Button>
                                            </form>
                                        </td>
                                    </tr>
                                ) : (
                                    <tr key={p.id} className="border-b last:border-0">
                                        <td className="py-3 pr-4">
                                            <div className="font-medium">{p.student.name}</div>
                                            <div className="text-xs text-muted-foreground">{p.student.nisn}</div>
                                        </td>
                                        <td className="py-3 pr-4">{p.academic_year.name}</td>
                                        <td className="py-3 pr-4">{p.from_class.name}</td>
                                        <td className="py-3 pr-4">{p.to_class?.name ?? '-'}</td>
                                        <td className="py-3 pr-4">
                                            <span className={`rounded-full px-2 py-0.5 text-xs ${statusColor[p.status]}`}>{statusLabel[p.status]}</span>
                                        </td>
                                        <td className="py-3 pr-4 text-muted-foreground">{p.notes ?? '-'}</td>
                                        <td className="flex gap-1 py-3">
                                            <Button variant="ghost" size="icon" onClick={() => startEdit(p)}><Pencil className="h-4 w-4" /></Button>
                                            <Button variant="ghost" size="icon" onClick={() => handleDelete(p.id)} disabled={deleteForm.processing}><Trash2 className="h-4 w-4 text-destructive" /></Button>
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

PromotionsIndex.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: dashboard() },
        { title: 'Kenaikan Kelas', href: ClassPromotionController.index().url },
    ],
};
