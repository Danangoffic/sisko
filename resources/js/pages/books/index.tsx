import { Head, useForm } from '@inertiajs/react';
import { Pencil, Trash2 } from 'lucide-react';
import { useState } from 'react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import * as BookController from '@/actions/App/Http/Controllers/BookController';
import { dashboard } from '@/routes';

interface Book {
    id: number;
    title: string;
    author: string;
    isbn: string | null;
    publisher: string | null;
    year: number | null;
    category: string | null;
    stock: number;
}

interface Props {
    books: { data: Book[]; current_page: number; last_page: number };
}

const emptyForm = { title: '', author: '', isbn: '', publisher: '', year: '', category: '', stock: '1' };

export default function BooksIndex({ books }: Props) {
    const [editingId, setEditingId] = useState<number | null>(null);
    const createForm = useForm({ ...emptyForm });
    const editForm = useForm({ ...emptyForm });
    const deleteForm = useForm({});

    function handleCreate(e: React.FormEvent) {
        e.preventDefault();
        createForm.post(BookController.store().url, { onSuccess: () => createForm.reset() });
    }

    function startEdit(b: Book) {
        setEditingId(b.id);
        editForm.setData({ title: b.title, author: b.author, isbn: b.isbn ?? '', publisher: b.publisher ?? '', year: String(b.year ?? ''), category: b.category ?? '', stock: String(b.stock) });
    }

    function handleUpdate(e: React.FormEvent, id: number) {
        e.preventDefault();
        editForm.put(BookController.update(id).url, { onSuccess: () => setEditingId(null) });
    }

    function handleDelete(id: number) {
        if (!confirm('Hapus buku ini?')) return;
        deleteForm.delete(BookController.destroy(id).url);
    }

    return (
        <>
            <Head title="Perpustakaan - Buku" />
            <div className="flex h-full flex-1 flex-col gap-6 p-6 md:flex-row">
                <div className="w-full shrink-0 md:w-72">
                    <h2 className="mb-4 text-lg font-semibold">Tambah Buku</h2>
                    <form onSubmit={handleCreate} className="space-y-3">
                        {(['title', 'author', 'isbn', 'publisher', 'category'] as const).map((field) => (
                            <div key={field} className="space-y-1">
                                <Label className="capitalize">{field === 'isbn' ? 'ISBN' : field}</Label>
                                <Input value={createForm.data[field]} onChange={(e) => createForm.setData(field, e.target.value)} />
                                <InputError message={createForm.errors[field]} />
                            </div>
                        ))}
                        <div className="grid grid-cols-2 gap-2">
                            <div className="space-y-1">
                                <Label>Tahun</Label>
                                <Input type="number" value={createForm.data.year} onChange={(e) => createForm.setData('year', e.target.value)} />
                            </div>
                            <div className="space-y-1">
                                <Label>Stok</Label>
                                <Input type="number" value={createForm.data.stock} onChange={(e) => createForm.setData('stock', e.target.value)} />
                                <InputError message={createForm.errors.stock} />
                            </div>
                        </div>
                        <Button type="submit" disabled={createForm.processing} className="w-full">Simpan</Button>
                    </form>
                </div>

                <div className="flex-1 overflow-x-auto">
                    <h2 className="mb-4 text-lg font-semibold">Katalog Buku</h2>
                    <table className="w-full text-sm">
                        <thead>
                            <tr className="border-b text-left text-muted-foreground">
                                <th className="pb-2 pr-4 font-medium">Judul</th>
                                <th className="pb-2 pr-4 font-medium">Penulis</th>
                                <th className="pb-2 pr-4 font-medium">ISBN</th>
                                <th className="pb-2 pr-4 font-medium">Kategori</th>
                                <th className="pb-2 pr-4 font-medium">Stok</th>
                                <th className="pb-2" />
                            </tr>
                        </thead>
                        <tbody>
                            {books.data.length === 0 ? (
                                <tr><td colSpan={6} className="py-6 text-center text-muted-foreground">Belum ada buku.</td></tr>
                            ) : books.data.map((b) =>
                                editingId === b.id ? (
                                    <tr key={b.id} className="border-b">
                                        <td colSpan={6} className="py-3">
                                            <form onSubmit={(e) => handleUpdate(e, b.id)} className="flex flex-wrap gap-2">
                                                {(['title', 'author', 'isbn', 'category'] as const).map((f) => (
                                                    <Input key={f} className="min-w-24 flex-1" value={editForm.data[f]} onChange={(e) => editForm.setData(f, e.target.value)} placeholder={f} />
                                                ))}
                                                <Input className="w-16" type="number" value={editForm.data.stock} onChange={(e) => editForm.setData('stock', e.target.value)} placeholder="Stok" />
                                                <Button type="submit" size="sm" disabled={editForm.processing}>Simpan</Button>
                                                <Button type="button" size="sm" variant="ghost" onClick={() => setEditingId(null)}>Batal</Button>
                                            </form>
                                        </td>
                                    </tr>
                                ) : (
                                    <tr key={b.id} className="border-b last:border-0">
                                        <td className="py-3 pr-4 font-medium">{b.title}</td>
                                        <td className="py-3 pr-4">{b.author}</td>
                                        <td className="py-3 pr-4 font-mono text-xs">{b.isbn ?? '-'}</td>
                                        <td className="py-3 pr-4">{b.category ?? '-'}</td>
                                        <td className="py-3 pr-4 font-mono">{b.stock}</td>
                                        <td className="flex gap-1 py-3">
                                            <Button variant="ghost" size="icon" onClick={() => startEdit(b)}><Pencil className="h-4 w-4" /></Button>
                                            <Button variant="ghost" size="icon" onClick={() => handleDelete(b.id)} disabled={deleteForm.processing}><Trash2 className="h-4 w-4 text-destructive" /></Button>
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

BooksIndex.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: dashboard() },
        { title: 'Buku', href: BookController.index().url },
    ],
};
