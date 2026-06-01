import { Head, useForm } from '@inertiajs/react';
import { Pencil, Trash2 } from 'lucide-react';
import { useState } from 'react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { dashboard } from '@/routes';

interface AcademicYear { id: number; name: string }

interface GradeConfig {
    id: number;
    type: 'angka' | 'huruf' | 'deskripsi';
    passing_grade: number;
    scale_max: number;
    academic_year: AcademicYear;
}

interface Props {
    configs: GradeConfig[];
    academicYears: AcademicYear[];
}

const TYPES = ['angka', 'huruf', 'deskripsi'] as const;

const emptyForm = { academic_year_id: '', type: 'angka' as typeof TYPES[number], passing_grade: '75', scale_max: '100' };

export default function GradeConfigsIndex({ configs, academicYears }: Props) {
    const [editingId, setEditingId] = useState<number | null>(null);
    const createForm = useForm({ ...emptyForm });
    const editForm = useForm({ type: 'angka' as typeof TYPES[number], passing_grade: '', scale_max: '' });
    const deleteForm = useForm({});

    function handleCreate(e: React.FormEvent) {
        e.preventDefault();
        createForm.post('/grade-configs', { onSuccess: () => createForm.reset() });
    }

    function startEdit(c: GradeConfig) {
        setEditingId(c.id);
        editForm.setData({ type: c.type, passing_grade: String(c.passing_grade), scale_max: String(c.scale_max) });
    }

    function handleUpdate(e: React.FormEvent, id: number) {
        e.preventDefault();
        editForm.put(`/grade-configs/${id}`, { onSuccess: () => setEditingId(null) });
    }

    function handleDelete(id: number) {
        if (!confirm('Hapus konfigurasi ini?')) return;
        deleteForm.delete(`/grade-configs/${id}`);
    }

    return (
        <>
            <Head title="Konfigurasi Penilaian" />
            <div className="flex h-full flex-1 flex-col gap-6 p-6 md:flex-row">
                {/* Form */}
                <div className="w-full shrink-0 md:w-72">
                    <h2 className="mb-4 text-lg font-semibold">Tambah / Update Konfigurasi</h2>
                    <form onSubmit={handleCreate} className="space-y-3">
                        <div className="space-y-1">
                            <Label>Tahun Ajaran</Label>
                            <select
                                className="w-full rounded-md border px-3 py-2 text-sm"
                                value={createForm.data.academic_year_id}
                                onChange={(e) => createForm.setData('academic_year_id', e.target.value)}
                            >
                                <option value="">Pilih Tahun Ajaran</option>
                                {academicYears.map((ay) => (
                                    <option key={ay.id} value={ay.id}>{ay.name}</option>
                                ))}
                            </select>
                            <InputError message={createForm.errors.academic_year_id} />
                        </div>
                        <div className="space-y-1">
                            <Label>Tipe Penilaian</Label>
                            <select
                                className="w-full rounded-md border px-3 py-2 text-sm"
                                value={createForm.data.type}
                                onChange={(e) => createForm.setData('type', e.target.value as typeof TYPES[number])}
                            >
                                {TYPES.map((t) => <option key={t} value={t}>{t.charAt(0).toUpperCase() + t.slice(1)}</option>)}
                            </select>
                            <InputError message={createForm.errors.type} />
                        </div>
                        <div className="space-y-1">
                            <Label>Nilai Minimum Lulus (KKM)</Label>
                            <Input
                                type="number"
                                value={createForm.data.passing_grade}
                                onChange={(e) => createForm.setData('passing_grade', e.target.value)}
                            />
                            <InputError message={createForm.errors.passing_grade} />
                        </div>
                        <div className="space-y-1">
                            <Label>Nilai Maksimum (Skala)</Label>
                            <Input
                                type="number"
                                value={createForm.data.scale_max}
                                onChange={(e) => createForm.setData('scale_max', e.target.value)}
                            />
                            <InputError message={createForm.errors.scale_max} />
                        </div>
                        <Button type="submit" disabled={createForm.processing} className="w-full">Simpan</Button>
                    </form>

                    <div className="mt-6 rounded-lg border bg-muted/40 p-3 text-xs text-muted-foreground">
                        <p className="font-medium mb-1">Konversi Huruf Otomatis</p>
                        <p>A ≥ 85% · B ≥ 70% · C ≥ 55% · D ≥ 40% · E &lt; 40%</p>
                        <p className="mt-1">Persentase dihitung dari Nilai Maksimum.</p>
                    </div>
                </div>

                {/* Tabel */}
                <div className="flex-1 overflow-x-auto">
                    <h2 className="mb-4 text-lg font-semibold">Daftar Konfigurasi</h2>
                    <table className="w-full text-sm">
                        <thead>
                            <tr className="border-b text-left text-muted-foreground">
                                <th className="pb-2 pr-4 font-medium">Tahun Ajaran</th>
                                <th className="pb-2 pr-4 font-medium">Tipe</th>
                                <th className="pb-2 pr-4 font-medium">KKM</th>
                                <th className="pb-2 pr-4 font-medium">Skala Maks</th>
                                <th className="pb-2" />
                            </tr>
                        </thead>
                        <tbody>
                            {configs.length === 0 ? (
                                <tr><td colSpan={5} className="py-6 text-center text-muted-foreground">Belum ada konfigurasi.</td></tr>
                            ) : configs.map((c) =>
                                editingId === c.id ? (
                                    <tr key={c.id} className="border-b">
                                        <td colSpan={5} className="py-3">
                                            <form onSubmit={(e) => handleUpdate(e, c.id)} className="flex flex-wrap items-center gap-2">
                                                <span className="font-medium">{c.academic_year.name}</span>
                                                <select
                                                    className="rounded-md border px-2 py-1 text-sm"
                                                    value={editForm.data.type}
                                                    onChange={(e) => editForm.setData('type', e.target.value as typeof TYPES[number])}
                                                >
                                                    {TYPES.map((t) => <option key={t} value={t}>{t}</option>)}
                                                </select>
                                                <Input className="w-20" type="number" placeholder="KKM" value={editForm.data.passing_grade} onChange={(e) => editForm.setData('passing_grade', e.target.value)} />
                                                <Input className="w-20" type="number" placeholder="Maks" value={editForm.data.scale_max} onChange={(e) => editForm.setData('scale_max', e.target.value)} />
                                                <Button type="submit" size="sm" disabled={editForm.processing}>Simpan</Button>
                                                <Button type="button" size="sm" variant="ghost" onClick={() => setEditingId(null)}>Batal</Button>
                                            </form>
                                        </td>
                                    </tr>
                                ) : (
                                    <tr key={c.id} className="border-b last:border-0">
                                        <td className="py-3 pr-4 font-medium">{c.academic_year.name}</td>
                                        <td className="py-3 pr-4 capitalize">{c.type}</td>
                                        <td className="py-3 pr-4 font-mono">{c.passing_grade}</td>
                                        <td className="py-3 pr-4 font-mono">{c.scale_max}</td>
                                        <td className="flex gap-1 py-3">
                                            <Button variant="ghost" size="icon" onClick={() => startEdit(c)}><Pencil className="h-4 w-4" /></Button>
                                            <Button variant="ghost" size="icon" onClick={() => handleDelete(c.id)} disabled={deleteForm.processing}><Trash2 className="h-4 w-4 text-destructive" /></Button>
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

GradeConfigsIndex.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: dashboard() },
        { title: 'Konfigurasi Penilaian', href: '/grade-configs' },
    ],
};
