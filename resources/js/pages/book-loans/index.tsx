import { Head, useForm } from '@inertiajs/react';
import { Trash2 } from 'lucide-react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import * as BookLoanController from '@/actions/App/Http/Controllers/BookLoanController';
import { dashboard } from '@/routes';

interface BookItem { id: number; title: string; stock: number }
interface StudentItem { id: number; name: string; nisn: string }

interface BookLoan {
    id: number;
    borrowed_at: string;
    due_date: string;
    returned_at: string | null;
    status: 'dipinjam' | 'dikembalikan' | 'terlambat';
    fine: number;
    book: { id: number; title: string };
    student: { id: number; name: string; nisn: string };
}

interface Props {
    loans: { data: BookLoan[]; current_page: number; last_page: number };
    books: BookItem[];
    students: StudentItem[];
}

const statusColor: Record<string, string> = {
    dipinjam: 'bg-blue-100 text-blue-700',
    dikembalikan: 'bg-green-100 text-green-700',
    terlambat: 'bg-red-100 text-red-700',
};

export default function BookLoansIndex({ loans, books, students }: Props) {
    const createForm = useForm({ book_id: '', student_id: '', borrowed_at: new Date().toISOString().slice(0, 10), due_date: '' });
    const deleteForm = useForm({});
    const returnForm = useForm({});

    function handleCreate(e: React.FormEvent) {
        e.preventDefault();
        createForm.post(BookLoanController.store().url, { onSuccess: () => createForm.reset() });
    }

    function handleReturn(id: number) {
        if (!confirm('Kembalikan buku ini?')) return;
        returnForm.post(BookLoanController.returnBook(id).url);
    }

    function handleDelete(id: number) {
        if (!confirm('Hapus data peminjaman ini?')) return;
        deleteForm.delete(BookLoanController.destroy(id).url);
    }

    return (
        <>
            <Head title="Peminjaman Buku" />
            <div className="flex h-full flex-1 flex-col gap-6 p-6 md:flex-row">
                <div className="w-full shrink-0 md:w-72">
                    <h2 className="mb-4 text-lg font-semibold">Pinjam Buku</h2>
                    <form onSubmit={handleCreate} className="space-y-3">
                        <div className="space-y-1">
                            <Label>Buku</Label>
                            <select className="w-full rounded-md border px-3 py-2 text-sm" value={createForm.data.book_id} onChange={(e) => createForm.setData('book_id', e.target.value)}>
                                <option value="">Pilih Buku</option>
                                {books.map((b) => <option key={b.id} value={b.id}>{b.title} (stok: {b.stock})</option>)}
                            </select>
                            <InputError message={createForm.errors.book_id} />
                        </div>
                        <div className="space-y-1">
                            <Label>Siswa</Label>
                            <select className="w-full rounded-md border px-3 py-2 text-sm" value={createForm.data.student_id} onChange={(e) => createForm.setData('student_id', e.target.value)}>
                                <option value="">Pilih Siswa</option>
                                {students.map((s) => <option key={s.id} value={s.id}>{s.name} ({s.nisn})</option>)}
                            </select>
                            <InputError message={createForm.errors.student_id} />
                        </div>
                        <div className="space-y-1">
                            <Label>Tanggal Pinjam</Label>
                            <Input type="date" value={createForm.data.borrowed_at} onChange={(e) => createForm.setData('borrowed_at', e.target.value)} />
                            <InputError message={createForm.errors.borrowed_at} />
                        </div>
                        <div className="space-y-1">
                            <Label>Batas Kembali</Label>
                            <Input type="date" value={createForm.data.due_date} onChange={(e) => createForm.setData('due_date', e.target.value)} />
                            <InputError message={createForm.errors.due_date} />
                        </div>
                        <Button type="submit" disabled={createForm.processing} className="w-full">Pinjam</Button>
                    </form>
                </div>

                <div className="flex-1 overflow-x-auto">
                    <h2 className="mb-4 text-lg font-semibold">Daftar Peminjaman</h2>
                    <table className="w-full text-sm">
                        <thead>
                            <tr className="border-b text-left text-muted-foreground">
                                <th className="pb-2 pr-4 font-medium">Buku</th>
                                <th className="pb-2 pr-4 font-medium">Siswa</th>
                                <th className="pb-2 pr-4 font-medium">Pinjam</th>
                                <th className="pb-2 pr-4 font-medium">Batas</th>
                                <th className="pb-2 pr-4 font-medium">Status</th>
                                <th className="pb-2 pr-4 font-medium">Denda</th>
                                <th className="pb-2" />
                            </tr>
                        </thead>
                        <tbody>
                            {loans.data.length === 0 ? (
                                <tr><td colSpan={7} className="py-6 text-center text-muted-foreground">Belum ada peminjaman.</td></tr>
                            ) : loans.data.map((l) => (
                                <tr key={l.id} className="border-b last:border-0">
                                    <td className="py-3 pr-4 font-medium">{l.book.title}</td>
                                    <td className="py-3 pr-4">{l.student.name}</td>
                                    <td className="py-3 pr-4">{l.borrowed_at}</td>
                                    <td className="py-3 pr-4">{l.due_date}</td>
                                    <td className="py-3 pr-4">
                                        <span className={`rounded-full px-2 py-0.5 text-xs ${statusColor[l.status]}`}>{l.status}</span>
                                    </td>
                                    <td className="py-3 pr-4 font-mono">{Number(l.fine) > 0 ? `Rp ${Number(l.fine).toLocaleString('id-ID')}` : '-'}</td>
                                    <td className="flex gap-1 py-3">
                                        {l.status === 'dipinjam' && (
                                            <Button size="sm" variant="outline" onClick={() => handleReturn(l.id)} disabled={returnForm.processing}>Kembalikan</Button>
                                        )}
                                        <Button variant="ghost" size="icon" onClick={() => handleDelete(l.id)} disabled={deleteForm.processing}><Trash2 className="h-4 w-4 text-destructive" /></Button>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            </div>
        </>
    );
}

BookLoansIndex.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: dashboard() },
        { title: 'Peminjaman Buku', href: BookLoanController.index().url },
    ],
};
