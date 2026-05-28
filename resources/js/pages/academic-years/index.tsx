import { Head, useForm } from '@inertiajs/react';
import { ChevronDown, ChevronRight, Pencil, Plus, Trash2 } from 'lucide-react';
import { useState } from 'react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import * as AcademicYearController from '@/actions/App/Http/Controllers/AcademicYearController';
import { dashboard } from '@/routes';

interface Semester {
    id: number;
    name: 'Ganjil' | 'Genap';
    start_date: string;
    end_date: string;
    is_active: boolean;
}

interface AcademicYear {
    id: number;
    name: string;
    start_date: string;
    end_date: string;
    is_active: boolean;
    semesters: Semester[];
}

interface Props {
    academicYears: AcademicYear[];
}

const emptyAY = { name: '', start_date: '', end_date: '', is_active: false };
const emptySem = { name: 'Ganjil' as 'Ganjil' | 'Genap', start_date: '', end_date: '', is_active: false };

export default function AcademicYearsIndex({ academicYears }: Props) {
    const [editingAY, setEditingAY] = useState<number | null>(null);
    const [addingSemFor, setAddingSemFor] = useState<number | null>(null);
    const [editingSem, setEditingSem] = useState<number | null>(null);
    const [expandedAY, setExpandedAY] = useState<number[]>([]);

    const createForm = useForm({ ...emptyAY });
    const editAYForm = useForm({ ...emptyAY });
    const semForm = useForm({ ...emptySem });
    const editSemForm = useForm({ ...emptySem });
    const deleteForm = useForm({});

    function handleCreateAY(e: React.FormEvent) {
        e.preventDefault();
        createForm.post(AcademicYearController.store().url, { onSuccess: () => createForm.reset() });
    }

    function startEditAY(ay: AcademicYear) {
        setEditingAY(ay.id);
        editAYForm.setData({ name: ay.name, start_date: ay.start_date, end_date: ay.end_date, is_active: ay.is_active });
    }

    function handleUpdateAY(e: React.FormEvent, id: number) {
        e.preventDefault();
        editAYForm.put(AcademicYearController.update(id).url, { onSuccess: () => setEditingAY(null) });
    }

    function handleDeleteAY(id: number) {
        if (!confirm('Hapus tahun ajaran ini? Semua semester di dalamnya juga akan dihapus.')) return;
        deleteForm.delete(AcademicYearController.destroy(id).url);
    }

    function handleAddSemester(e: React.FormEvent, ayId: number) {
        e.preventDefault();
        semForm.post(AcademicYearController.storeSemester(ayId).url, {
            onSuccess: () => { semForm.reset(); setAddingSemFor(null); },
        });
    }

    function startEditSem(sem: Semester) {
        setEditingSem(sem.id);
        editSemForm.setData({ name: sem.name, start_date: sem.start_date, end_date: sem.end_date, is_active: sem.is_active });
    }

    function handleUpdateSem(e: React.FormEvent, id: number) {
        e.preventDefault();
        editSemForm.put(AcademicYearController.updateSemester(id).url, { onSuccess: () => setEditingSem(null) });
    }

    function handleDeleteSem(id: number) {
        if (!confirm('Hapus semester ini?')) return;
        deleteForm.delete(AcademicYearController.destroySemester(id).url);
    }

    function toggleExpand(id: number) {
        setExpandedAY((prev) => prev.includes(id) ? prev.filter((x) => x !== id) : [...prev, id]);
    }

    return (
        <>
            <Head title="Tahun Ajaran" />
            <div className="flex h-full flex-1 flex-col gap-6 p-6 md:flex-row">
                {/* Form Tambah Tahun Ajaran */}
                <div className="w-full shrink-0 md:w-72">
                    <h2 className="mb-4 text-lg font-semibold">Tambah Tahun Ajaran</h2>
                    <form onSubmit={handleCreateAY} className="space-y-3">
                        <div className="space-y-1">
                            <Label htmlFor="name">Nama (e.g. 2025/2026)</Label>
                            <Input id="name" value={createForm.data.name} onChange={(e) => createForm.setData('name', e.target.value)} placeholder="2025/2026" />
                            <InputError message={createForm.errors.name} />
                        </div>
                        <div className="space-y-1">
                            <Label htmlFor="start_date">Tanggal Mulai</Label>
                            <Input id="start_date" type="date" value={createForm.data.start_date} onChange={(e) => createForm.setData('start_date', e.target.value)} />
                            <InputError message={createForm.errors.start_date} />
                        </div>
                        <div className="space-y-1">
                            <Label htmlFor="end_date">Tanggal Selesai</Label>
                            <Input id="end_date" type="date" value={createForm.data.end_date} onChange={(e) => createForm.setData('end_date', e.target.value)} />
                            <InputError message={createForm.errors.end_date} />
                        </div>
                        <div className="flex items-center gap-2">
                            <input
                                id="is_active"
                                type="checkbox"
                                checked={createForm.data.is_active}
                                onChange={(e) => createForm.setData('is_active', e.target.checked)}
                                className="h-4 w-4"
                            />
                            <Label htmlFor="is_active">Aktif</Label>
                        </div>
                        <Button type="submit" disabled={createForm.processing} className="w-full">Simpan</Button>
                    </form>
                </div>

                {/* Daftar Tahun Ajaran */}
                <div className="flex-1">
                    <h2 className="mb-4 text-lg font-semibold">Daftar Tahun Ajaran</h2>
                    {academicYears.length === 0 ? (
                        <p className="text-muted-foreground">Belum ada tahun ajaran.</p>
                    ) : (
                        <div className="space-y-3">
                            {academicYears.map((ay) => (
                                <div key={ay.id} className="rounded-lg border">
                                    {/* Header Tahun Ajaran */}
                                    {editingAY === ay.id ? (
                                        <form onSubmit={(e) => handleUpdateAY(e, ay.id)} className="flex flex-wrap items-center gap-2 p-3">
                                            <Input className="w-32" value={editAYForm.data.name} onChange={(e) => editAYForm.setData('name', e.target.value)} placeholder="2025/2026" />
                                            <Input className="w-36" type="date" value={editAYForm.data.start_date} onChange={(e) => editAYForm.setData('start_date', e.target.value)} />
                                            <Input className="w-36" type="date" value={editAYForm.data.end_date} onChange={(e) => editAYForm.setData('end_date', e.target.value)} />
                                            <label className="flex items-center gap-1 text-sm">
                                                <input type="checkbox" checked={editAYForm.data.is_active} onChange={(e) => editAYForm.setData('is_active', e.target.checked)} />
                                                Aktif
                                            </label>
                                            <Button type="submit" size="sm" disabled={editAYForm.processing}>Simpan</Button>
                                            <Button type="button" size="sm" variant="ghost" onClick={() => setEditingAY(null)}>Batal</Button>
                                        </form>
                                    ) : (
                                        <div className="flex items-center justify-between p-3">
                                            <button className="flex items-center gap-2 font-medium" onClick={() => toggleExpand(ay.id)}>
                                                {expandedAY.includes(ay.id) ? <ChevronDown className="h-4 w-4" /> : <ChevronRight className="h-4 w-4" />}
                                                {ay.name}
                                                {ay.is_active && <span className="rounded-full bg-green-100 px-2 py-0.5 text-xs text-green-700">Aktif</span>}
                                            </button>
                                            <div className="flex items-center gap-1 text-sm text-muted-foreground">
                                                <span>{ay.start_date} – {ay.end_date}</span>
                                                <Button variant="ghost" size="icon" onClick={() => startEditAY(ay)}><Pencil className="h-4 w-4" /></Button>
                                                <Button variant="ghost" size="icon" onClick={() => handleDeleteAY(ay.id)} disabled={deleteForm.processing}><Trash2 className="h-4 w-4 text-destructive" /></Button>
                                            </div>
                                        </div>
                                    )}

                                    {/* Semester list */}
                                    {expandedAY.includes(ay.id) && (
                                        <div className="border-t px-4 pb-3 pt-2">
                                            <div className="mb-2 flex items-center justify-between">
                                                <span className="text-sm font-medium text-muted-foreground">Semester</span>
                                                <Button size="sm" variant="outline" onClick={() => { setAddingSemFor(ay.id); semForm.reset(); }}>
                                                    <Plus className="mr-1 h-3 w-3" /> Tambah
                                                </Button>
                                            </div>

                                            {addingSemFor === ay.id && (
                                                <form onSubmit={(e) => handleAddSemester(e, ay.id)} className="mb-3 flex flex-wrap items-start gap-2 rounded-md bg-muted/40 p-2">
                                                    <select className="rounded-md border px-2 py-1.5 text-sm" value={semForm.data.name} onChange={(e) => semForm.setData('name', e.target.value as 'Ganjil' | 'Genap')}>
                                                        <option value="Ganjil">Ganjil</option>
                                                        <option value="Genap">Genap</option>
                                                    </select>
                                                    <div>
                                                        <Input className="w-36" type="date" value={semForm.data.start_date} onChange={(e) => semForm.setData('start_date', e.target.value)} />
                                                        <InputError message={semForm.errors.start_date} />
                                                    </div>
                                                    <div>
                                                        <Input className="w-36" type="date" value={semForm.data.end_date} onChange={(e) => semForm.setData('end_date', e.target.value)} />
                                                        <InputError message={semForm.errors.end_date} />
                                                    </div>
                                                    <label className="flex items-center gap-1 text-sm">
                                                        <input type="checkbox" checked={semForm.data.is_active} onChange={(e) => semForm.setData('is_active', e.target.checked)} />
                                                        Aktif
                                                    </label>
                                                    <Button type="submit" size="sm" disabled={semForm.processing}>Simpan</Button>
                                                    <Button type="button" size="sm" variant="ghost" onClick={() => setAddingSemFor(null)}>Batal</Button>
                                                </form>
                                            )}

                                            {ay.semesters.length === 0 ? (
                                                <p className="text-sm text-muted-foreground">Belum ada semester.</p>
                                            ) : (
                                                <div className="space-y-1">
                                                    {ay.semesters.map((sem) =>
                                                        editingSem === sem.id ? (
                                                            <form key={sem.id} onSubmit={(e) => handleUpdateSem(e, sem.id)} className="flex flex-wrap items-center gap-2 rounded-md bg-muted/40 p-2">
                                                                <select className="rounded-md border px-2 py-1.5 text-sm" value={editSemForm.data.name} onChange={(e) => editSemForm.setData('name', e.target.value as 'Ganjil' | 'Genap')}>
                                                                    <option value="Ganjil">Ganjil</option>
                                                                    <option value="Genap">Genap</option>
                                                                </select>
                                                                <Input className="w-36" type="date" value={editSemForm.data.start_date} onChange={(e) => editSemForm.setData('start_date', e.target.value)} />
                                                                <Input className="w-36" type="date" value={editSemForm.data.end_date} onChange={(e) => editSemForm.setData('end_date', e.target.value)} />
                                                                <label className="flex items-center gap-1 text-sm">
                                                                    <input type="checkbox" checked={editSemForm.data.is_active} onChange={(e) => editSemForm.setData('is_active', e.target.checked)} />
                                                                    Aktif
                                                                </label>
                                                                <Button type="submit" size="sm" disabled={editSemForm.processing}>Simpan</Button>
                                                                <Button type="button" size="sm" variant="ghost" onClick={() => setEditingSem(null)}>Batal</Button>
                                                            </form>
                                                        ) : (
                                                            <div key={sem.id} className="flex items-center justify-between rounded-md px-2 py-1.5 text-sm hover:bg-muted/40">
                                                                <span>
                                                                    Semester {sem.name}
                                                                    {sem.is_active && <span className="ml-2 rounded-full bg-green-100 px-2 py-0.5 text-xs text-green-700">Aktif</span>}
                                                                </span>
                                                                <div className="flex items-center gap-1 text-muted-foreground">
                                                                    <span>{sem.start_date} – {sem.end_date}</span>
                                                                    <Button variant="ghost" size="icon" className="h-7 w-7" onClick={() => startEditSem(sem)}><Pencil className="h-3 w-3" /></Button>
                                                                    <Button variant="ghost" size="icon" className="h-7 w-7" onClick={() => handleDeleteSem(sem.id)} disabled={deleteForm.processing}><Trash2 className="h-3 w-3 text-destructive" /></Button>
                                                                </div>
                                                            </div>
                                                        )
                                                    )}
                                                </div>
                                            )}
                                        </div>
                                    )}
                                </div>
                            ))}
                        </div>
                    )}
                </div>
            </div>
        </>
    );
}

AcademicYearsIndex.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: dashboard() },
        { title: 'Tahun Ajaran', href: AcademicYearController.index().url },
    ],
};
