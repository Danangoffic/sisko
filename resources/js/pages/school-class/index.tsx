import { Head, useForm } from '@inertiajs/react';
import { Trash2 } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import InputError from '@/components/input-error';
import * as SchoolClassController from '@/actions/App/Http/Controllers/SchoolClassController';
import { dashboard } from '@/routes';

interface SchoolClass {
    id: number;
    name: string;
    homeroom_teacher: string;
    students_count: number;
}

interface Props {
    classes: SchoolClass[];
}

export default function Index({ classes }: Props) {
    const { data, setData, post, processing, errors, reset } = useForm({
        name: '',
        homeroom_teacher: '',
    });

    const deleteForm = useForm({});

    function handleSubmit(e: React.FormEvent) {
        e.preventDefault();
        post(SchoolClassController.store().url, { onSuccess: () => reset() });
    }

    function handleDelete(id: number) {
        if (!confirm('Hapus kelas ini?')) return;
        deleteForm.delete(SchoolClassController.destroy(id).url);
    }

    return (
        <>
            <Head title="Manajemen Kelas" />
            <div className="flex h-full flex-1 flex-col gap-6 p-6 md:flex-row">
                {/* Form */}
                <div className="w-full shrink-0 md:w-72">
                    <h2 className="mb-4 text-lg font-semibold">Tambah Kelas</h2>
                    <form onSubmit={handleSubmit} className="space-y-4">
                        <div className="space-y-1">
                            <Label htmlFor="name">Nama Kelas</Label>
                            <Input
                                id="name"
                                value={data.name}
                                onChange={(e) => setData('name', e.target.value)}
                                placeholder="Contoh: X IPA 1"
                                maxLength={50}
                            />
                            <InputError message={errors.name} />
                        </div>
                        <div className="space-y-1">
                            <Label htmlFor="homeroom_teacher">Wali Kelas</Label>
                            <Input
                                id="homeroom_teacher"
                                value={data.homeroom_teacher}
                                onChange={(e) => setData('homeroom_teacher', e.target.value)}
                                placeholder="Nama wali kelas"
                                maxLength={100}
                            />
                            <InputError message={errors.homeroom_teacher} />
                        </div>
                        <Button type="submit" disabled={processing} className="w-full">
                            Simpan
                        </Button>
                    </form>
                </div>

                {/* Table */}
                <div className="flex-1 overflow-x-auto">
                    <h2 className="mb-4 text-lg font-semibold">Daftar Kelas</h2>
                    <table className="w-full text-sm">
                        <thead>
                            <tr className="border-b text-left text-muted-foreground">
                                <th className="pb-2 pr-4 font-medium">#</th>
                                <th className="pb-2 pr-4 font-medium">Nama Kelas</th>
                                <th className="pb-2 pr-4 font-medium">Wali Kelas</th>
                                <th className="pb-2 pr-4 text-center font-medium">Siswa</th>
                                <th className="pb-2" />
                            </tr>
                        </thead>
                        <tbody>
                            {classes.length === 0 ? (
                                <tr>
                                    <td colSpan={5} className="py-6 text-center text-muted-foreground">
                                        Belum ada kelas.
                                    </td>
                                </tr>
                            ) : (
                                classes.map((cls, i) => (
                                    <tr key={cls.id} className="border-b last:border-0">
                                        <td className="py-3 pr-4">{i + 1}</td>
                                        <td className="py-3 pr-4 font-medium">{cls.name}</td>
                                        <td className="py-3 pr-4">{cls.homeroom_teacher}</td>
                                        <td className="py-3 pr-4 text-center">{cls.students_count}</td>
                                        <td className="py-3">
                                            <Button
                                                variant="ghost"
                                                size="icon"
                                                onClick={() => handleDelete(cls.id)}
                                                disabled={deleteForm.processing}
                                            >
                                                <Trash2 className="h-4 w-4 text-destructive" />
                                            </Button>
                                        </td>
                                    </tr>
                                ))
                            )}
                        </tbody>
                    </table>
                </div>
            </div>
        </>
    );
}

Index.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: dashboard() },
        { title: 'Manajemen Kelas', href: SchoolClassController.index().url },
    ],
};
