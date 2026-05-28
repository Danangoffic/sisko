import { Head, useForm } from '@inertiajs/react';
import { Pencil, Trash2 } from 'lucide-react';
import { useState } from 'react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import * as SubjectController from '@/actions/App/Http/Controllers/SubjectController';
import { dashboard } from '@/routes';

interface Subject {
    id: number;
    name: string;
    code: string;
    description: string | null;
}

interface Props {
    subjects: Subject[];
}

const emptyForm = { name: '', code: '', description: '' };

export default function SubjectsIndex({ subjects }: Props) {
    const [editingId, setEditingId] = useState<number | null>(null);
    const createForm = useForm({ ...emptyForm });
    const editForm = useForm({ ...emptyForm });
    const deleteForm = useForm({});

    function handleCreate(e: React.FormEvent) {
        e.preventDefault();
        createForm.post(SubjectController.store().url, { onSuccess: () => createForm.reset() });
    }

    function startEdit(s: Subject) {
        setEditingId(s.id);
        editForm.setData({ name: s.name, code: s.code, description: s.description ?? '' });
    }

    function handleUpdate(e: React.FormEvent, id: number) {
        e.preventDefault();
        editForm.put(SubjectController.update(id).url, { onSuccess: () => setEditingId(null) });
    }

    function handleDelete(id: number) {
        if (!confirm('Hapus mata pelajaran ini?')) return;
        deleteForm.delete(SubjectController.destroy(id).url);
    }

    return (
        <>
            <Head title="Mata Pelajaran" />
            <div className="flex h-full flex-1 flex-col gap-6 p-6 md:flex-row">
                {/* Form Tambah */}
                <div className="w-full shrink-0 md:w-72">
                    <h2 className="mb-4 text-lg font-semibold">Tambah Mata Pelajaran</h2>
                    <form onSubmit={handleCreate} className="space-y-3">
                        {(['name', 'code', 'description'] as const).map((field) => (
                            <div key={field} className="space-y-1">
                                <Label htmlFor={field} className="capitalize">
                                    {field === 'name' ? 'Nama' : field === 'code' ? 'Kode' : 'Deskripsi'}
                                </Label>
                                <Input
                                    id={field}
                                    value={createForm.data[field]}
                                    onChange={(e) => createForm.setData(field, e.target.value)}
                                />
                                <InputError message={createForm.errors[field]} />
                            </div>
                        ))}
                        <Button type="submit" disabled={createForm.processing} className="w-full">Simpan</Button>
                    </form>
                </div>

                {/* Tabel */}
                <div className="flex-1 overflow-x-auto">
                    <h2 className="mb-4 text-lg font-semibold">Daftar Mata Pelajaran</h2>
                    <table className="w-full text-sm">
                        <thead>
                            <tr className="border-b text-left text-muted-foreground">
                                <th className="pb-2 pr-4 font-medium">#</th>
                                <th className="pb-2 pr-4 font-medium">Nama</th>
                                <th className="pb-2 pr-4 font-medium">Kode</th>
                                <th className="pb-2 pr-4 font-medium">Deskripsi</th>
                                <th className="pb-2" />
                            </tr>
                        </thead>
                        <tbody>
                            {subjects.length === 0 ? (
                                <tr><td colSpan={5} className="py-6 text-center text-muted-foreground">Belum ada mata pelajaran.</td></tr>
                            ) : subjects.map((s, i) =>
                                editingId === s.id ? (
                                    <tr key={s.id} className="border-b">
                                        <td colSpan={5} className="py-3">
                                            <form onSubmit={(e) => handleUpdate(e, s.id)} className="flex flex-wrap gap-2">
                                                {(['name', 'code', 'description'] as const).map((field) => (
                                                    <div key={field} className="flex-1 min-w-24">
                                                        <Input value={editForm.data[field]} onChange={(e) => editForm.setData(field, e.target.value)} placeholder={field} />
                                                        <InputError message={editForm.errors[field]} />
                                                    </div>
                                                ))}
                                                <Button type="submit" size="sm" disabled={editForm.processing}>Simpan</Button>
                                                <Button type="button" size="sm" variant="ghost" onClick={() => setEditingId(null)}>Batal</Button>
                                            </form>
                                        </td>
                                    </tr>
                                ) : (
                                    <tr key={s.id} className="border-b last:border-0">
                                        <td className="py-3 pr-4">{i + 1}</td>
                                        <td className="py-3 pr-4 font-medium">{s.name}</td>
                                        <td className="py-3 pr-4 font-mono">{s.code}</td>
                                        <td className="py-3 pr-4 text-muted-foreground">{s.description ?? '-'}</td>
                                        <td className="flex gap-1 py-3">
                                            <Button variant="ghost" size="icon" onClick={() => startEdit(s)}><Pencil className="h-4 w-4" /></Button>
                                            <Button variant="ghost" size="icon" onClick={() => handleDelete(s.id)} disabled={deleteForm.processing}><Trash2 className="h-4 w-4 text-destructive" /></Button>
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

SubjectsIndex.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: dashboard() },
        { title: 'Mata Pelajaran', href: SubjectController.index().url },
    ],
};
