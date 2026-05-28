import { Head, useForm } from '@inertiajs/react';
import { Pencil, Trash2 } from 'lucide-react';
import { useState } from 'react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import * as StudentController from '@/actions/App/Http/Controllers/StudentController';
import { dashboard } from '@/routes';

interface SchoolClass {
    id: number;
    name: string;
}

interface Student {
    id: number;
    nisn: string;
    name: string;
    gender: 'L' | 'P';
    tempat_lahir: string | null;
    tanggal_lahir: string | null;
    alamat: string | null;
    no_telp_ortu: string | null;
    nama_ortu: string | null;
    school_class: SchoolClass;
    user: { id: number; name: string; email: string } | null;
}

interface Props {
    students: {
        data: Student[];
        current_page: number;
        last_page: number;
    };
    classes: SchoolClass[];
}

const emptyForm = {
    school_class_id: '',
    nisn: '',
    name: '',
    gender: 'L' as 'L' | 'P',
    email: '',
    tempat_lahir: '',
    tanggal_lahir: '',
    alamat: '',
    no_telp_ortu: '',
    nama_ortu: '',
};

export default function StudentsIndex({ students, classes }: Props) {
    const [editingId, setEditingId] = useState<number | null>(null);
    const [filterClass, setFilterClass] = useState('');

    const createForm = useForm({ ...emptyForm });
    const editForm = useForm({ ...emptyForm });
    const deleteForm = useForm({});

    function handleCreate(e: React.FormEvent) {
        e.preventDefault();
        createForm.post(StudentController.store().url, { onSuccess: () => createForm.reset() });
    }

    function startEdit(student: Student) {
        setEditingId(student.id);
        editForm.setData({
            school_class_id: String(student.school_class.id),
            nisn: student.nisn,
            name: student.name,
            gender: student.gender,
            email: student.user?.email ?? '',
            tempat_lahir: student.tempat_lahir ?? '',
            tanggal_lahir: student.tanggal_lahir ?? '',
            alamat: student.alamat ?? '',
            no_telp_ortu: student.no_telp_ortu ?? '',
            nama_ortu: student.nama_ortu ?? '',
        });
    }

    function handleUpdate(e: React.FormEvent, id: number) {
        e.preventDefault();
        editForm.put(StudentController.update(id).url, { onSuccess: () => setEditingId(null) });
    }

    function handleDelete(id: number) {
        if (!confirm('Hapus siswa ini? Akun pengguna juga akan dihapus jika ada.')) return;
        deleteForm.delete(StudentController.destroy(id).url);
    }

    const filtered = filterClass
        ? students.data.filter((s) => String(s.school_class.id) === filterClass)
        : students.data;

    return (
        <>
            <Head title="Manajemen Siswa" />
            <div className="flex h-full flex-1 flex-col gap-6 p-6 md:flex-row">
                {/* Form Tambah */}
                <div className="w-full shrink-0 md:w-72">
                    <h2 className="mb-4 text-lg font-semibold">Tambah Siswa</h2>
                    <form onSubmit={handleCreate} className="space-y-3">
                        <div className="space-y-1">
                            <Label htmlFor="school_class_id">Kelas</Label>
                            <select
                                id="school_class_id"
                                className="w-full rounded-md border px-3 py-2 text-sm"
                                value={createForm.data.school_class_id}
                                onChange={(e) => createForm.setData('school_class_id', e.target.value)}
                            >
                                <option value="">Pilih Kelas</option>
                                {classes.map((c) => (
                                    <option key={c.id} value={c.id}>
                                        {c.name}
                                    </option>
                                ))}
                            </select>
                            <InputError message={createForm.errors.school_class_id} />
                        </div>

                        {(['nisn', 'name', 'email', 'tempat_lahir', 'no_telp_ortu', 'nama_ortu'] as const).map((field) => (
                            <div key={field} className="space-y-1">
                                <Label htmlFor={field} className="capitalize">
                                    {fieldLabel(field)}
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

                        <div className="space-y-1">
                            <Label htmlFor="tanggal_lahir">Tanggal Lahir</Label>
                            <Input
                                id="tanggal_lahir"
                                type="date"
                                value={createForm.data.tanggal_lahir}
                                onChange={(e) => createForm.setData('tanggal_lahir', e.target.value)}
                            />
                            <InputError message={createForm.errors.tanggal_lahir} />
                        </div>

                        <div className="space-y-1">
                            <Label htmlFor="gender">Jenis Kelamin</Label>
                            <select
                                id="gender"
                                className="w-full rounded-md border px-3 py-2 text-sm"
                                value={createForm.data.gender}
                                onChange={(e) => createForm.setData('gender', e.target.value as 'L' | 'P')}
                            >
                                <option value="L">Laki-laki</option>
                                <option value="P">Perempuan</option>
                            </select>
                            <InputError message={createForm.errors.gender} />
                        </div>

                        <div className="space-y-1">
                            <Label htmlFor="alamat">Alamat</Label>
                            <textarea
                                id="alamat"
                                className="w-full rounded-md border px-3 py-2 text-sm"
                                rows={2}
                                value={createForm.data.alamat}
                                onChange={(e) => createForm.setData('alamat', e.target.value)}
                            />
                            <InputError message={createForm.errors.alamat} />
                        </div>

                        <Button type="submit" disabled={createForm.processing} className="w-full">
                            Simpan
                        </Button>
                    </form>
                </div>

                {/* Tabel */}
                <div className="flex-1 overflow-x-auto">
                    <div className="mb-4 flex items-center justify-between gap-4">
                        <h2 className="text-lg font-semibold">Daftar Siswa</h2>
                        <select
                            className="rounded-md border px-3 py-1.5 text-sm"
                            value={filterClass}
                            onChange={(e) => setFilterClass(e.target.value)}
                        >
                            <option value="">Semua Kelas</option>
                            {classes.map((c) => (
                                <option key={c.id} value={c.id}>
                                    {c.name}
                                </option>
                            ))}
                        </select>
                    </div>

                    <table className="w-full text-sm">
                        <thead>
                            <tr className="border-b text-left text-muted-foreground">
                                <th className="pb-2 pr-4 font-medium">#</th>
                                <th className="pb-2 pr-4 font-medium">NISN</th>
                                <th className="pb-2 pr-4 font-medium">Nama</th>
                                <th className="pb-2 pr-4 font-medium">Kelas</th>
                                <th className="pb-2 pr-4 font-medium">L/P</th>
                                <th className="pb-2 pr-4 font-medium">Ortu</th>
                                <th className="pb-2 pr-4 font-medium">Akun</th>
                                <th className="pb-2" />
                            </tr>
                        </thead>
                        <tbody>
                            {filtered.length === 0 ? (
                                <tr>
                                    <td colSpan={8} className="py-6 text-center text-muted-foreground">
                                        Belum ada data siswa.
                                    </td>
                                </tr>
                            ) : (
                                filtered.map((student, i) =>
                                    editingId === student.id ? (
                                        <tr key={student.id} className="border-b">
                                            <td colSpan={8} className="py-3">
                                                <form
                                                    onSubmit={(e) => handleUpdate(e, student.id)}
                                                    className="flex flex-wrap gap-2"
                                                >
                                                    <select
                                                        className="rounded-md border px-2 py-1 text-sm"
                                                        value={editForm.data.school_class_id}
                                                        onChange={(e) => editForm.setData('school_class_id', e.target.value)}
                                                    >
                                                        {classes.map((c) => (
                                                            <option key={c.id} value={c.id}>
                                                                {c.name}
                                                            </option>
                                                        ))}
                                                    </select>
                                                    {(['nisn', 'name', 'email', 'nama_ortu', 'no_telp_ortu'] as const).map((field) => (
                                                        <div key={field} className="flex-1 min-w-24">
                                                            <Input
                                                                value={editForm.data[field]}
                                                                onChange={(e) => editForm.setData(field, e.target.value)}
                                                                placeholder={fieldLabel(field)}
                                                            />
                                                            <InputError message={editForm.errors[field]} />
                                                        </div>
                                                    ))}
                                                    <select
                                                        className="rounded-md border px-2 py-1 text-sm"
                                                        value={editForm.data.gender}
                                                        onChange={(e) => editForm.setData('gender', e.target.value as 'L' | 'P')}
                                                    >
                                                        <option value="L">L</option>
                                                        <option value="P">P</option>
                                                    </select>
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
                                        <tr key={student.id} className="border-b last:border-0">
                                            <td className="py-3 pr-4">{(students.current_page - 1) * 15 + i + 1}</td>
                                            <td className="py-3 pr-4 font-mono">{student.nisn}</td>
                                            <td className="py-3 pr-4 font-medium">{student.name}</td>
                                            <td className="py-3 pr-4">{student.school_class.name}</td>
                                            <td className="py-3 pr-4">{student.gender}</td>
                                            <td className="py-3 pr-4">{student.nama_ortu ?? '-'}</td>
                                            <td className="py-3 pr-4">
                                                {student.user ? (
                                                    <span className="rounded-full bg-green-100 px-2 py-0.5 text-xs text-green-700">
                                                        {student.user.email}
                                                    </span>
                                                ) : (
                                                    <span className="text-muted-foreground">-</span>
                                                )}
                                            </td>
                                            <td className="flex gap-1 py-3">
                                                <Button variant="ghost" size="icon" onClick={() => startEdit(student)}>
                                                    <Pencil className="h-4 w-4" />
                                                </Button>
                                                <Button
                                                    variant="ghost"
                                                    size="icon"
                                                    onClick={() => handleDelete(student.id)}
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

function fieldLabel(field: string): string {
    const labels: Record<string, string> = {
        nisn: 'NISN',
        name: 'Nama',
        email: 'Email (opsional)',
        tempat_lahir: 'Tempat Lahir',
        no_telp_ortu: 'No. Telp Ortu',
        nama_ortu: 'Nama Ortu',
    };
    return labels[field] ?? field;
}

StudentsIndex.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: dashboard() },
        { title: 'Manajemen Siswa', href: StudentController.index().url },
    ],
};
