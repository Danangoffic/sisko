import { Head, useForm } from '@inertiajs/react';
import { Pencil, Trash2 } from 'lucide-react';
import { useState } from 'react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import * as PaymentTypeController from '@/actions/App/Http/Controllers/PaymentTypeController';
import { dashboard } from '@/routes';

interface PaymentType {
    id: number;
    name: string;
    amount: number;
    is_recurring: boolean;
    recurring_period: string | null;
}

interface Props {
    paymentTypes: PaymentType[];
}

const emptyForm = { name: '', amount: '', is_recurring: false, recurring_period: '' };

export default function PaymentTypesIndex({ paymentTypes }: Props) {
    const [editingId, setEditingId] = useState<number | null>(null);
    const createForm = useForm({ ...emptyForm });
    const editForm = useForm({ ...emptyForm });
    const deleteForm = useForm({});

    function handleCreate(e: React.FormEvent) {
        e.preventDefault();
        createForm.post(PaymentTypeController.store().url, { onSuccess: () => createForm.reset() });
    }

    function startEdit(pt: PaymentType) {
        setEditingId(pt.id);
        editForm.setData({ name: pt.name, amount: String(pt.amount), is_recurring: pt.is_recurring, recurring_period: pt.recurring_period ?? '' });
    }

    function handleUpdate(e: React.FormEvent, id: number) {
        e.preventDefault();
        editForm.put(PaymentTypeController.update(id).url, { onSuccess: () => setEditingId(null) });
    }

    function handleDelete(id: number) {
        if (!confirm('Hapus jenis pembayaran ini?')) return;
        deleteForm.delete(PaymentTypeController.destroy(id).url);
    }

    return (
        <>
            <Head title="Jenis Pembayaran" />
            <div className="flex h-full flex-1 flex-col gap-6 p-6 md:flex-row">
                <div className="w-full shrink-0 md:w-72">
                    <h2 className="mb-4 text-lg font-semibold">Tambah Jenis Pembayaran</h2>
                    <form onSubmit={handleCreate} className="space-y-3">
                        <div className="space-y-1">
                            <Label>Nama</Label>
                            <Input value={createForm.data.name} onChange={(e) => createForm.setData('name', e.target.value)} />
                            <InputError message={createForm.errors.name} />
                        </div>
                        <div className="space-y-1">
                            <Label>Jumlah (Rp)</Label>
                            <Input type="number" value={createForm.data.amount} onChange={(e) => createForm.setData('amount', e.target.value)} />
                            <InputError message={createForm.errors.amount} />
                        </div>
                        <div className="flex items-center gap-2">
                            <input type="checkbox" id="is_recurring" checked={createForm.data.is_recurring} onChange={(e) => createForm.setData('is_recurring', e.target.checked)} className="h-4 w-4" />
                            <Label htmlFor="is_recurring">Berulang</Label>
                        </div>
                        {createForm.data.is_recurring && (
                            <div className="space-y-1">
                                <Label>Periode</Label>
                                <select className="w-full rounded-md border px-3 py-2 text-sm" value={createForm.data.recurring_period} onChange={(e) => createForm.setData('recurring_period', e.target.value)}>
                                    <option value="">Pilih</option>
                                    <option value="bulanan">Bulanan</option>
                                    <option value="semester">Semester</option>
                                    <option value="tahunan">Tahunan</option>
                                </select>
                            </div>
                        )}
                        <Button type="submit" disabled={createForm.processing} className="w-full">Simpan</Button>
                    </form>
                </div>

                <div className="flex-1 overflow-x-auto">
                    <h2 className="mb-4 text-lg font-semibold">Daftar Jenis Pembayaran</h2>
                    <table className="w-full text-sm">
                        <thead>
                            <tr className="border-b text-left text-muted-foreground">
                                <th className="pb-2 pr-4 font-medium">#</th>
                                <th className="pb-2 pr-4 font-medium">Nama</th>
                                <th className="pb-2 pr-4 font-medium">Jumlah</th>
                                <th className="pb-2 pr-4 font-medium">Berulang</th>
                                <th className="pb-2" />
                            </tr>
                        </thead>
                        <tbody>
                            {paymentTypes.length === 0 ? (
                                <tr><td colSpan={5} className="py-6 text-center text-muted-foreground">Belum ada data.</td></tr>
                            ) : paymentTypes.map((pt, i) =>
                                editingId === pt.id ? (
                                    <tr key={pt.id} className="border-b">
                                        <td colSpan={5} className="py-3">
                                            <form onSubmit={(e) => handleUpdate(e, pt.id)} className="flex flex-wrap gap-2">
                                                <Input className="w-32" value={editForm.data.name} onChange={(e) => editForm.setData('name', e.target.value)} />
                                                <Input className="w-28" type="number" value={editForm.data.amount} onChange={(e) => editForm.setData('amount', e.target.value)} />
                                                <Button type="submit" size="sm" disabled={editForm.processing}>Simpan</Button>
                                                <Button type="button" size="sm" variant="ghost" onClick={() => setEditingId(null)}>Batal</Button>
                                            </form>
                                        </td>
                                    </tr>
                                ) : (
                                    <tr key={pt.id} className="border-b last:border-0">
                                        <td className="py-3 pr-4">{i + 1}</td>
                                        <td className="py-3 pr-4 font-medium">{pt.name}</td>
                                        <td className="py-3 pr-4 font-mono">Rp {Number(pt.amount).toLocaleString('id-ID')}</td>
                                        <td className="py-3 pr-4">{pt.is_recurring ? pt.recurring_period : '-'}</td>
                                        <td className="flex gap-1 py-3">
                                            <Button variant="ghost" size="icon" onClick={() => startEdit(pt)}><Pencil className="h-4 w-4" /></Button>
                                            <Button variant="ghost" size="icon" onClick={() => handleDelete(pt.id)} disabled={deleteForm.processing}><Trash2 className="h-4 w-4 text-destructive" /></Button>
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

PaymentTypesIndex.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: dashboard() },
        { title: 'Jenis Pembayaran', href: PaymentTypeController.index().url },
    ],
};
