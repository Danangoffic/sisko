import { Head, useForm } from '@inertiajs/react';
import { Pencil, Trash2 } from 'lucide-react';
import { useState } from 'react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import * as TeacherController from '@/actions/App/Http/Controllers/TeacherController';
import { dashboard } from '@/routes';

interface Teacher {
    id: number;
    nip: string | null;
    phone: string | null;
    address: string | null;
    user: {
        id: number;
        name: string;
        email: string;
    };
}

interface Props {
    teachers: {
        data: Teacher[];
        current_page: number;
        last_page: number;
    };
}

const emptyForm = { name: '', email: '', nip: '', phone: '', address: '' };

export default function TeachersIndex({ teachers }: Props) {
    const [editingId, setEditingId] = useState<number | null>(null);

    const createForm = useForm({ ...emptyForm });
    const editForm = useForm({ ...emptyForm });
    const deleteForm = useForm({});

    function handleCreate(e: React.FormEvent) {
        e.preventDefault();
        createForm.post(TeacherController.store().url, { onSuccess: () => createForm.reset() });
    }

    function startEdit(teacher: Teacher) {
        setEditingId(teacher.id);
        editForm.setData({
            name: teacher.user.name,
            email: teacher.user.email,
            nip: teacher.nip ?? '',
            phone: teacher.phone ?? '',
            address: teacher.address ?? '',
        });
    }

    function handleUpdate(e: React.FormEvent, id: number) {
        e.preventDefault();
        editForm.put(TeacherController.update(id).url, { onSuccess: () => setEditingId(null) });
    }

    function handleDelete(id: number) {
        if (!confirm('Hapus guru ini? Akun pengguna juga akan dihapus.')) return;
        deleteForm.delete(TeacherController.destroy(id).url);
    }

    return (
        <>
            <Head title="Manajemen Guru" />
            <div className="flex h-full flex-1 flex-col gap-6 p-6 md:flex-row">
                {/* Form Tambah */}
                <div className="w-full shrink-0 md:w-72">
                    <h2 className="mb-4 text-lg font-semibold">Tambah Guru</h2>
                    <form onSubmit={handleCreate} className="space-y-3">
                        {(['name', 'email', 'nip', 'phone', 'address'] as const).map((field) => (
                            <div key={field} className="space-y-1">
                                <Label htmlFor={field} className="capitalize">
                                    {field === 'nip' ? 'NIP' : field === 'phone' ? 'No. Telepon' : field}
                                </Label>
                                <Input
                                    id={field}
                                    type={field === 'email' ? 'email' : 'text'}
                                    value={createForm.data[field]}
                                    onChange={(e) => createForm.setData(field, e.target.value)}
                                />
                                <InputError message={createForm.errors[field]} />
                            </div>
                        ))}
                        <Button type="submit" disabled={createForm.processing} className="w-full">
                            Simpan
                        </Button>
                    </form>
                </div>

                {/* Tabel */}
                <div className="flex-1 overflow-x-auto">
                    <h2 className="mb-4 text-lg font-semibold">Daftar Guru</h2>
                    <table className="w-full text-sm">
                        <thead>
                            <tr className="border-b text-left text-muted-foreground">
                                <th className="pb-2 pr-4 font-medium">#</th>
                                <th className="pb-2 pr-4 font-medium">Nama</th>
                                <th className="pb-2 pr-4 font-medium">Email</th>
                                <th className="pb-2 pr-4 font-medium">NIP</th>
                                <th className="pb-2 pr-4 font-medium">Telepon</th>
                                <th className="pb-2" />
                            </tr>
                        </thead>
                        <tbody>
                            {teachers.data.length === 0 ? (
                                <tr>
                                    <td colSpan={6} className="py-6 text-center text-muted-foreground">
                                        Belum ada data guru.
                                    </td>
                                </tr>
                            ) : (
                                teachers.data.map((teacher, i) =>
                                    editingId === teacher.id ? (
                                        <tr key={teacher.id} className="border-b">
                                            <td colSpan={6} className="py-3">
                                                <form
                                                    onSubmit={(e) => handleUpdate(e, teacher.id)}
                                                    className="flex flex-wrap gap-2"
                                                >
                                                    {(['name', 'email', 'nip', 'phone'] as const).map((field) => (
                                                        <div key={field} className="flex-1 space-y-1">
                                                            <Input
                                                                value={editForm.data[field]}
                                                                onChange={(e) => editForm.setData(field, e.target.value)}
                                                                placeholder={field}
                                                            />
                                                            <InputError message={editForm.errors[field]} />
                                                        </div>
                                                    ))}
                                                    <Button type="submit" size="sm" disabled={editForm.processing}>
                                                        Simpan
                                                    </Button>
                                                    <Button
                                                        type="button"
                                                        size="sm"
                                                        variant="ghost"
                                                        onClick={() => setEditingId(null)}
                                                    >
                                                        Batal
                                                    </Button>
                                                </form>
                                            </td>
                                        </tr>
                                    ) : (
                                        <tr key={teacher.id} className="border-b last:border-0">
                                            <td className="py-3 pr-4">{(teachers.current_page - 1) * 15 + i + 1}</td>
                                            <td className="py-3 pr-4 font-medium">{teacher.user.name}</td>
                                            <td className="py-3 pr-4">{teacher.user.email}</td>
                                            <td className="py-3 pr-4">{teacher.nip ?? '-'}</td>
                                            <td className="py-3 pr-4">{teacher.phone ?? '-'}</td>
                                            <td className="flex gap-1 py-3">
                                                <Button
                                                    variant="ghost"
                                                    size="icon"
                                                    onClick={() => startEdit(teacher)}
                                                >
                                                    <Pencil className="h-4 w-4" />
                                                </Button>
                                                <Button
                                                    variant="ghost"
                                                    size="icon"
                                                    onClick={() => handleDelete(teacher.id)}
                                                    disabled={deleteForm.processing}
                                                >
                                                    <Trash2 className="h-4 w-4 text-destructive" />
                                                </Button>
                                            </td>
                                        </tr>
                                    ),
                                )
                            )}
                        </tbody>
                    </table>
                </div>
            </div>
        </>
    );
}

TeachersIndex.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: dashboard() },
        { title: 'Manajemen Guru', href: TeacherController.index().url },
    ],
};
