import { Head, useForm } from '@inertiajs/react';
import { Trash2 } from 'lucide-react';
import { useState } from 'react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import * as InvoiceController from '@/actions/App/Http/Controllers/InvoiceController';
import { dashboard } from '@/routes';

interface StudentItem { id: number; name: string; nisn: string }
interface PaymentTypeItem { id: number; name: string; amount: number }
interface Payment { id: number; amount: number; payment_method: string; paid_at: string }

interface Invoice {
    id: number;
    invoice_number: string;
    amount: number;
    due_date: string;
    status: 'pending' | 'paid' | 'overdue';
    paid_at: string | null;
    month: string | null;
    student: { id: number; name: string; nisn: string };
    payment_type: { id: number; name: string };
    payments: Payment[];
}

interface Props {
    invoices: { data: Invoice[]; current_page: number; last_page: number };
    students: StudentItem[];
    paymentTypes: PaymentTypeItem[];
}

const statusColor: Record<string, string> = {
    pending: 'bg-yellow-100 text-yellow-700',
    paid: 'bg-green-100 text-green-700',
    overdue: 'bg-red-100 text-red-700',
};

export default function InvoicesIndex({ invoices, students, paymentTypes }: Props) {
    const [payingId, setPayingId] = useState<number | null>(null);
    const createForm = useForm({ student_id: '', payment_type_id: '', amount: '', due_date: '', month: '' });
    const payForm = useForm({ amount: '', payment_method: 'cash', transaction_id: '' });
    const deleteForm = useForm({});

    function handleCreate(e: React.FormEvent) {
        e.preventDefault();
        createForm.post(InvoiceController.store().url, { onSuccess: () => createForm.reset() });
    }

    function handlePay(e: React.FormEvent, id: number) {
        e.preventDefault();
        payForm.post(InvoiceController.pay(id).url, { onSuccess: () => { payForm.reset(); setPayingId(null); } });
    }

    function handleDelete(id: number) {
        if (!confirm('Hapus invoice ini?')) return;
        deleteForm.delete(InvoiceController.destroy(id).url);
    }

    function onPaymentTypeChange(id: string) {
        createForm.setData('payment_type_id', id);
        const pt = paymentTypes.find((p) => String(p.id) === id);
        if (pt) createForm.setData('amount', String(pt.amount));
    }

    return (
        <>
            <Head title="Invoice" />
            <div className="flex h-full flex-1 flex-col gap-6 p-6 md:flex-row">
                {/* Form Buat Invoice */}
                <div className="w-full shrink-0 md:w-80">
                    <h2 className="mb-4 text-lg font-semibold">Buat Invoice</h2>
                    <form onSubmit={handleCreate} className="space-y-3">
                        <div className="space-y-1">
                            <Label>Siswa</Label>
                            <select className="w-full rounded-md border px-3 py-2 text-sm" value={createForm.data.student_id} onChange={(e) => createForm.setData('student_id', e.target.value)}>
                                <option value="">Pilih Siswa</option>
                                {students.map((s) => <option key={s.id} value={s.id}>{s.name} ({s.nisn})</option>)}
                            </select>
                            <InputError message={createForm.errors.student_id} />
                        </div>
                        <div className="space-y-1">
                            <Label>Jenis Pembayaran</Label>
                            <select className="w-full rounded-md border px-3 py-2 text-sm" value={createForm.data.payment_type_id} onChange={(e) => onPaymentTypeChange(e.target.value)}>
                                <option value="">Pilih</option>
                                {paymentTypes.map((pt) => <option key={pt.id} value={pt.id}>{pt.name} - Rp {Number(pt.amount).toLocaleString('id-ID')}</option>)}
                            </select>
                            <InputError message={createForm.errors.payment_type_id} />
                        </div>
                        <div className="space-y-1">
                            <Label>Jumlah (Rp)</Label>
                            <Input type="number" value={createForm.data.amount} onChange={(e) => createForm.setData('amount', e.target.value)} />
                            <InputError message={createForm.errors.amount} />
                        </div>
                        <div className="space-y-1">
                            <Label>Jatuh Tempo</Label>
                            <Input type="date" value={createForm.data.due_date} onChange={(e) => createForm.setData('due_date', e.target.value)} />
                            <InputError message={createForm.errors.due_date} />
                        </div>
                        <div className="space-y-1">
                            <Label>Bulan (opsional)</Label>
                            <Input type="month" value={createForm.data.month} onChange={(e) => createForm.setData('month', e.target.value)} />
                        </div>
                        <Button type="submit" disabled={createForm.processing} className="w-full">Buat Invoice</Button>
                    </form>
                </div>

                {/* Tabel Invoice */}
                <div className="flex-1 overflow-x-auto">
                    <h2 className="mb-4 text-lg font-semibold">Daftar Invoice</h2>
                    <table className="w-full text-sm">
                        <thead>
                            <tr className="border-b text-left text-muted-foreground">
                                <th className="pb-2 pr-4 font-medium">No. Invoice</th>
                                <th className="pb-2 pr-4 font-medium">Siswa</th>
                                <th className="pb-2 pr-4 font-medium">Jenis</th>
                                <th className="pb-2 pr-4 font-medium">Jumlah</th>
                                <th className="pb-2 pr-4 font-medium">Jatuh Tempo</th>
                                <th className="pb-2 pr-4 font-medium">Status</th>
                                <th className="pb-2" />
                            </tr>
                        </thead>
                        <tbody>
                            {invoices.data.length === 0 ? (
                                <tr><td colSpan={7} className="py-6 text-center text-muted-foreground">Belum ada invoice.</td></tr>
                            ) : invoices.data.map((inv) => (
                                <tr key={inv.id} className="border-b last:border-0">
                                    {payingId === inv.id ? (
                                        <td colSpan={7} className="py-3">
                                            <form onSubmit={(e) => handlePay(e, inv.id)} className="flex flex-wrap items-center gap-2">
                                                <span className="font-mono text-xs">{inv.invoice_number}</span>
                                                <Input className="w-28" type="number" placeholder="Jumlah" value={payForm.data.amount} onChange={(e) => payForm.setData('amount', e.target.value)} />
                                                <select className="rounded-md border px-2 py-1.5 text-sm" value={payForm.data.payment_method} onChange={(e) => payForm.setData('payment_method', e.target.value)}>
                                                    <option value="cash">Cash</option>
                                                    <option value="transfer">Transfer</option>
                                                </select>
                                                <Input className="w-32" placeholder="ID Transaksi" value={payForm.data.transaction_id} onChange={(e) => payForm.setData('transaction_id', e.target.value)} />
                                                <Button type="submit" size="sm" disabled={payForm.processing}>Bayar</Button>
                                                <Button type="button" size="sm" variant="ghost" onClick={() => setPayingId(null)}>Batal</Button>
                                            </form>
                                        </td>
                                    ) : (
                                        <>
                                            <td className="py-3 pr-4 font-mono text-xs">{inv.invoice_number}</td>
                                            <td className="py-3 pr-4">{inv.student.name}</td>
                                            <td className="py-3 pr-4">{inv.payment_type.name}</td>
                                            <td className="py-3 pr-4 font-mono">Rp {Number(inv.amount).toLocaleString('id-ID')}</td>
                                            <td className="py-3 pr-4">{inv.due_date}</td>
                                            <td className="py-3 pr-4">
                                                <span className={`rounded-full px-2 py-0.5 text-xs capitalize ${statusColor[inv.status]}`}>{inv.status}</span>
                                            </td>
                                            <td className="flex gap-1 py-3">
                                                {inv.status !== 'paid' && (
                                                    <Button size="sm" variant="outline" onClick={() => { setPayingId(inv.id); payForm.setData('amount', String(inv.amount)); }}>Bayar</Button>
                                                )}
                                                <Button variant="ghost" size="icon" onClick={() => handleDelete(inv.id)} disabled={deleteForm.processing}><Trash2 className="h-4 w-4 text-destructive" /></Button>
                                            </td>
                                        </>
                                    )}
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            </div>
        </>
    );
}

InvoicesIndex.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: dashboard() },
        { title: 'Invoice', href: InvoiceController.index().url },
    ],
};
