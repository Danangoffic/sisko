import { Head, useForm } from '@inertiajs/react';
import { Pin, Pencil, Trash2 } from 'lucide-react';
import { useState } from 'react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import * as AnnouncementController from '@/actions/App/Http/Controllers/AnnouncementController';
import { dashboard } from '@/routes';

interface Announcement {
    id: number;
    title: string;
    content: string;
    target_role: 'all' | 'guru' | 'siswa';
    is_pinned: boolean;
    published_at: string;
    author: { id: number; name: string };
}

interface Props {
    announcements: { data: Announcement[]; current_page: number; last_page: number };
    canManage: boolean;
}

const targetLabel: Record<string, string> = { all: 'Semua', guru: 'Guru', siswa: 'Siswa' };
const emptyForm = { title: '', content: '', target_role: 'all' as const, is_pinned: false, published_at: '' };

export default function AnnouncementsIndex({ announcements, canManage }: Props) {
    const [editingId, setEditingId] = useState<number | null>(null);
    const [showForm, setShowForm] = useState(false);
    const createForm = useForm({ ...emptyForm });
    const editForm = useForm({ ...emptyForm });
    const deleteForm = useForm({});

    function handleCreate(e: React.FormEvent) {
        e.preventDefault();
        createForm.post(AnnouncementController.store().url, { onSuccess: () => { createForm.reset(); setShowForm(false); } });
    }

    function startEdit(a: Announcement) {
        setEditingId(a.id);
        editForm.setData({ title: a.title, content: a.content, target_role: a.target_role, is_pinned: a.is_pinned, published_at: a.published_at?.slice(0, 10) ?? '' });
    }

    function handleUpdate(e: React.FormEvent, id: number) {
        e.preventDefault();
        editForm.put(AnnouncementController.update(id).url, { onSuccess: () => setEditingId(null) });
    }

    function handleDelete(id: number) {
        if (!confirm('Hapus pengumuman ini?')) return;
        deleteForm.delete(AnnouncementController.destroy(id).url);
    }

    return (
        <>
            <Head title="Pengumuman" />
            <div className="mx-auto max-w-4xl p-6">
                <div className="mb-6 flex items-center justify-between">
                    <h1 className="text-2xl font-bold">Pengumuman</h1>
                    {canManage && (
                        <Button onClick={() => setShowForm(!showForm)}>{showForm ? 'Tutup' : 'Buat Pengumuman'}</Button>
                    )}
                </div>

                {/* Form Buat */}
                {canManage && showForm && (
                    <form onSubmit={handleCreate} className="mb-6 space-y-3 rounded-lg border p-4">
                        <div className="space-y-1">
                            <Label>Judul</Label>
                            <Input value={createForm.data.title} onChange={(e) => createForm.setData('title', e.target.value)} />
                            <InputError message={createForm.errors.title} />
                        </div>
                        <div className="space-y-1">
                            <Label>Konten</Label>
                            <textarea className="w-full rounded-md border px-3 py-2 text-sm" rows={4} value={createForm.data.content} onChange={(e) => createForm.setData('content', e.target.value)} />
                            <InputError message={createForm.errors.content} />
                        </div>
                        <div className="flex gap-4">
                            <div className="space-y-1">
                                <Label>Target</Label>
                                <select className="rounded-md border px-3 py-2 text-sm" value={createForm.data.target_role} onChange={(e) => createForm.setData('target_role', e.target.value as typeof createForm.data.target_role)}>
                                    <option value="all">Semua</option>
                                    <option value="guru">Guru</option>
                                    <option value="siswa">Siswa</option>
                                </select>
                            </div>
                            <div className="flex items-end gap-2">
                                <input type="checkbox" id="is_pinned" checked={createForm.data.is_pinned} onChange={(e) => createForm.setData('is_pinned', e.target.checked)} className="h-4 w-4" />
                                <Label htmlFor="is_pinned">Pin</Label>
                            </div>
                        </div>
                        <Button type="submit" disabled={createForm.processing}>Simpan</Button>
                    </form>
                )}

                {/* Daftar Pengumuman */}
                <div className="space-y-4">
                    {announcements.data.length === 0 ? (
                        <p className="text-center text-muted-foreground py-8">Belum ada pengumuman.</p>
                    ) : announcements.data.map((a) =>
                        editingId === a.id ? (
                            <form key={a.id} onSubmit={(e) => handleUpdate(e, a.id)} className="space-y-3 rounded-lg border p-4">
                                <Input value={editForm.data.title} onChange={(e) => editForm.setData('title', e.target.value)} />
                                <textarea className="w-full rounded-md border px-3 py-2 text-sm" rows={3} value={editForm.data.content} onChange={(e) => editForm.setData('content', e.target.value)} />
                                <div className="flex gap-4">
                                    <select className="rounded-md border px-3 py-2 text-sm" value={editForm.data.target_role} onChange={(e) => editForm.setData('target_role', e.target.value as typeof editForm.data.target_role)}>
                                        <option value="all">Semua</option>
                                        <option value="guru">Guru</option>
                                        <option value="siswa">Siswa</option>
                                    </select>
                                    <label className="flex items-center gap-2 text-sm">
                                        <input type="checkbox" checked={editForm.data.is_pinned} onChange={(e) => editForm.setData('is_pinned', e.target.checked)} />
                                        Pin
                                    </label>
                                </div>
                                <div className="flex gap-2">
                                    <Button type="submit" size="sm" disabled={editForm.processing}>Simpan</Button>
                                    <Button type="button" size="sm" variant="ghost" onClick={() => setEditingId(null)}>Batal</Button>
                                </div>
                            </form>
                        ) : (
                            <div key={a.id} className="rounded-lg border p-4">
                                <div className="mb-2 flex items-start justify-between">
                                    <div>
                                        <h3 className="flex items-center gap-2 font-semibold">
                                            {a.is_pinned && <Pin className="h-4 w-4 text-amber-500" />}
                                            {a.title}
                                        </h3>
                                        <div className="mt-1 flex gap-2 text-xs text-muted-foreground">
                                            <span>{a.author.name}</span>
                                            <span>·</span>
                                            <span>{new Date(a.published_at).toLocaleDateString('id-ID')}</span>
                                            <span>·</span>
                                            <span className="rounded-full bg-muted px-2 py-0.5">{targetLabel[a.target_role]}</span>
                                        </div>
                                    </div>
                                    {canManage && (
                                        <div className="flex gap-1">
                                            <Button variant="ghost" size="icon" onClick={() => startEdit(a)}><Pencil className="h-4 w-4" /></Button>
                                            <Button variant="ghost" size="icon" onClick={() => handleDelete(a.id)} disabled={deleteForm.processing}><Trash2 className="h-4 w-4 text-destructive" /></Button>
                                        </div>
                                    )}
                                </div>
                                <p className="whitespace-pre-line text-sm">{a.content}</p>
                            </div>
                        )
                    )}
                </div>
            </div>
        </>
    );
}

AnnouncementsIndex.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: dashboard() },
        { title: 'Pengumuman', href: AnnouncementController.index().url },
    ],
};
