import { Head } from '@inertiajs/react';
import { dashboard } from '@/routes';

interface Payment {
    id: number;
    amount: number;
    payment_method: string;
    paid_at: string;
}

interface Invoice {
    id: number;
    invoice_number: string;
    amount: number;
    due_date: string;
    status: 'pending' | 'paid' | 'overdue';
    month: string | null;
    payment_type: { name: string };
    payments: Payment[];
}

interface Student {
    name: string;
    nisn: string;
    school_class: { name: string } | null;
}

interface Props {
    invoices: { data: Invoice[]; current_page: number; last_page: number } | null;
    student: Student | null;
    midtransClientKey: string;
}

const STATUS_LABEL: Record<string, string> = {
    pending: 'Belum Lunas',
    paid: 'Lunas',
    overdue: 'Jatuh Tempo',
};

const STATUS_COLOR: Record<string, string> = {
    pending: 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400',
    paid: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
    overdue: 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
};

function formatRupiah(amount: number) {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(amount);
}

export default function PortalInvoices({ invoices, student }: Props) {
    return (
        <>
            <Head title="Tagihan Saya" />
            <div className="flex h-full flex-1 flex-col gap-6 p-6">
                <div>
                    <h1 className="text-xl font-semibold">Tagihan Saya</h1>
                    {student && (
                        <p className="text-sm text-muted-foreground">
                            {student.name} · {student.school_class?.name ?? 'Belum ada kelas'}
                        </p>
                    )}
                </div>

                {!invoices || invoices.data.length === 0 ? (
                    <p className="text-sm text-muted-foreground">Belum ada tagihan.</p>
                ) : (
                    <div className="space-y-3">
                        {invoices.data.map((inv) => (
                            <div key={inv.id} className="rounded-xl border bg-card p-4">
                                <div className="flex items-start justify-between gap-2">
                                    <div>
                                        <p className="font-medium">{inv.payment_type.name}</p>
                                        <p className="text-xs text-muted-foreground">
                                            {inv.invoice_number}
                                            {inv.month ? ` · ${inv.month}` : ''}
                                        </p>
                                    </div>
                                    <span className={`rounded px-2 py-0.5 text-xs font-medium ${STATUS_COLOR[inv.status]}`}>
                                        {STATUS_LABEL[inv.status]}
                                    </span>
                                </div>
                                <div className="mt-3 flex items-center justify-between">
                                    <div>
                                        <p className="text-lg font-bold">{formatRupiah(inv.amount)}</p>
                                        <p className="text-xs text-muted-foreground">
                                            Jatuh tempo:{' '}
                                            {new Date(inv.due_date).toLocaleDateString('id-ID', {
                                                day: 'numeric',
                                                month: 'long',
                                                year: 'numeric',
                                            })}
                                        </p>
                                    </div>
                                </div>
                                {inv.payments.length > 0 && (
                                    <div className="mt-3 border-t pt-3">
                                        <p className="mb-1 text-xs font-medium text-muted-foreground">Riwayat Pembayaran</p>
                                        {inv.payments.map((p) => (
                                            <div key={p.id} className="flex justify-between text-xs">
                                                <span>{new Date(p.paid_at).toLocaleDateString('id-ID')}</span>
                                                <span>{formatRupiah(p.amount)} · {p.payment_method}</span>
                                            </div>
                                        ))}
                                    </div>
                                )}
                            </div>
                        ))}
                    </div>
                )}
            </div>
        </>
    );
}

PortalInvoices.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: dashboard() },
        { title: 'Tagihan Saya', href: '/portal/invoices' },
    ],
};
