import { Head, usePage } from '@inertiajs/react';
import { AlertTriangle, BookOpen, CalendarCheck, GraduationCap, Receipt, School, TrendingUp, Users } from 'lucide-react';
import { dashboard } from '@/routes';
import type { Auth } from '@/types';

interface Announcement {
    id: number;
    title: string;
    content: string;
    is_pinned: boolean;
    published_at: string;
    author: { name: string };
}

interface AdminStats {
    total_siswa: number;
    total_guru: number;
    total_kelas: number;
    invoice_pending: number;
    invoice_overdue: number;
    absensi_hari_ini: number;
}

interface GuruStats {
    jadwal_hari_ini: number;
    absensi_hari_ini: number;
    total_nilai_diinput: number;
}

interface SiswaStats {
    tagihan_pending: number;
    tagihan_overdue: number;
    rata_rata_nilai: number | null;
    ranking: number | null;
    kelas: string | null;
}

interface Props {
    announcements: Announcement[];
    stats: AdminStats | GuruStats | SiswaStats;
}

function StatCard({
    label,
    value,
    icon: Icon,
    variant = 'default',
}: {
    label: string;
    value: string | number;
    icon: React.ElementType;
    variant?: 'default' | 'warning' | 'danger';
}) {
    const variantClass = {
        default: 'bg-card border-border',
        warning: 'bg-amber-50 border-amber-200 dark:bg-amber-950/30 dark:border-amber-800',
        danger: 'bg-red-50 border-red-200 dark:bg-red-950/30 dark:border-red-800',
    }[variant];

    const iconClass = {
        default: 'text-muted-foreground',
        warning: 'text-amber-600 dark:text-amber-400',
        danger: 'text-red-600 dark:text-red-400',
    }[variant];

    return (
        <div className={`flex items-center gap-4 rounded-xl border p-4 ${variantClass}`}>
            <div className={`rounded-lg bg-background p-2 shadow-xs ${iconClass}`}>
                <Icon className="h-5 w-5" />
            </div>
            <div>
                <p className="text-2xl font-bold">{value}</p>
                <p className="text-sm text-muted-foreground">{label}</p>
            </div>
        </div>
    );
}

function AdminDashboard({ stats }: { stats: AdminStats }) {
    return (
        <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <StatCard label="Total Siswa" value={stats.total_siswa} icon={Users} />
            <StatCard label="Total Guru" value={stats.total_guru} icon={GraduationCap} />
            <StatCard label="Total Kelas" value={stats.total_kelas} icon={School} />
            <StatCard label="Absensi Hari Ini" value={stats.absensi_hari_ini} icon={CalendarCheck} />
            <StatCard
                label="Invoice Pending"
                value={stats.invoice_pending}
                icon={Receipt}
                variant={stats.invoice_pending > 0 ? 'warning' : 'default'}
            />
            <StatCard
                label="Invoice Overdue"
                value={stats.invoice_overdue}
                icon={AlertTriangle}
                variant={stats.invoice_overdue > 0 ? 'danger' : 'default'}
            />
        </div>
    );
}

function GuruDashboard({ stats }: { stats: GuruStats }) {
    return (
        <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <StatCard label="Jadwal Hari Ini" value={stats.jadwal_hari_ini} icon={CalendarCheck} />
            <StatCard label="Absensi Diinput Hari Ini" value={stats.absensi_hari_ini} icon={BookOpen} />
            <StatCard label="Total Nilai Diinput" value={stats.total_nilai_diinput} icon={GraduationCap} />
        </div>
    );
}

function SiswaDashboard({ stats }: { stats: SiswaStats }) {
    return (
        <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            {stats.kelas && (
                <StatCard label="Kelas" value={stats.kelas} icon={School} />
            )}
            <StatCard
                label="Tagihan Belum Lunas"
                value={stats.tagihan_pending}
                icon={Receipt}
                variant={stats.tagihan_pending > 0 ? 'warning' : 'default'}
            />
            <StatCard
                label="Tagihan Overdue"
                value={stats.tagihan_overdue}
                icon={AlertTriangle}
                variant={stats.tagihan_overdue > 0 ? 'danger' : 'default'}
            />
            {stats.rata_rata_nilai !== null && (
                <StatCard label="Rata-rata Nilai Terakhir" value={stats.rata_rata_nilai ?? '-'} icon={TrendingUp} />
            )}
            {stats.ranking !== null && (
                <StatCard label="Ranking Terakhir" value={`#${stats.ranking}`} icon={GraduationCap} />
            )}
        </div>
    );
}

export default function Dashboard({ announcements, stats }: Props) {
    const { auth } = usePage<{ auth: Auth }>().props;
    const role = auth.user.role;

    return (
        <>
            <Head title="Dashboard" />
            <div className="flex h-full flex-1 flex-col gap-6 p-6">
                {/* Statistik */}
                <section>
                    <h2 className="mb-4 text-lg font-semibold">Ringkasan</h2>
                    {role === 'admin' && <AdminDashboard stats={stats as AdminStats} />}
                    {role === 'guru' && <GuruDashboard stats={stats as GuruStats} />}
                    {role === 'siswa' && <SiswaDashboard stats={stats as SiswaStats} />}
                </section>

                {/* Pengumuman Terbaru */}
                <section>
                    <h2 className="mb-4 text-lg font-semibold">Pengumuman Terbaru</h2>
                    {announcements.length === 0 ? (
                        <p className="text-sm text-muted-foreground">Belum ada pengumuman.</p>
                    ) : (
                        <div className="space-y-3">
                            {announcements.map((a) => (
                                <div
                                    key={a.id}
                                    className={`rounded-xl border p-4 ${a.is_pinned ? 'border-primary/30 bg-primary/5' : 'bg-card'}`}
                                >
                                    <div className="flex items-start justify-between gap-2">
                                        <div>
                                            <p className="font-medium">
                                                {a.is_pinned && (
                                                    <span className="mr-2 rounded bg-primary px-1.5 py-0.5 text-xs text-primary-foreground">
                                                        Pinned
                                                    </span>
                                                )}
                                                {a.title}
                                            </p>
                                            <p className="mt-1 line-clamp-2 text-sm text-muted-foreground">{a.content}</p>
                                        </div>
                                        <span className="shrink-0 text-xs text-muted-foreground">
                                            {new Date(a.published_at).toLocaleDateString('id-ID', {
                                                day: 'numeric',
                                                month: 'short',
                                                year: 'numeric',
                                            })}
                                        </span>
                                    </div>
                                    <p className="mt-2 text-xs text-muted-foreground">— {a.author.name}</p>
                                </div>
                            ))}
                        </div>
                    )}
                </section>
            </div>
        </>
    );
}

Dashboard.layout = {
    breadcrumbs: [
        {
            title: 'Dashboard',
            href: dashboard(),
        },
    ],
};
