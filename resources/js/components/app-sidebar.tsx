import { usePage } from '@inertiajs/react';
import { Link } from '@inertiajs/react';
import {
    BookOpen,
    BookUser,
    CalendarDays,
    ClipboardList,
    FolderGit2,
    GraduationCap,
    LayoutGrid,
    Library,
    Megaphone,
    Receipt,
    School,
    ScrollText,
    TrendingUp,
    UserCog,
    Users,
    Wallet,
} from 'lucide-react';
import AppLogo from '@/components/app-logo';
import { NavFooter } from '@/components/nav-footer';
import { NavUser } from '@/components/nav-user';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarGroup,
    SidebarGroupLabel,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import * as AcademicYearController from '@/actions/App/Http/Controllers/AcademicYearController';
import * as AnnouncementController from '@/actions/App/Http/Controllers/AnnouncementController';
import * as AttendanceController from '@/actions/App/Http/Controllers/AttendanceController';
import * as BookController from '@/actions/App/Http/Controllers/BookController';
import * as BookLoanController from '@/actions/App/Http/Controllers/BookLoanController';
import * as ClassPromotionController from '@/actions/App/Http/Controllers/ClassPromotionController';
import * as GradeController from '@/actions/App/Http/Controllers/GradeController';
import * as InvoiceController from '@/actions/App/Http/Controllers/InvoiceController';
import * as PaymentTypeController from '@/actions/App/Http/Controllers/PaymentTypeController';
import * as ReportCardController from '@/actions/App/Http/Controllers/ReportCardController';
import * as ScheduleController from '@/actions/App/Http/Controllers/ScheduleController';
import * as SchoolClassController from '@/actions/App/Http/Controllers/SchoolClassController';
import * as StudentController from '@/actions/App/Http/Controllers/StudentController';
import * as SubjectController from '@/actions/App/Http/Controllers/SubjectController';
import * as TeacherController from '@/actions/App/Http/Controllers/TeacherController';
import { useCurrentUrl } from '@/hooks/use-current-url';
import type { Auth, NavItem } from '@/types';

interface NavGroup {
    label: string;
    items: NavItem[];
}

const footerNavItems: NavItem[] = [
    {
        title: 'Repository',
        href: 'https://github.com/Danangoffic/sisko',
        icon: FolderGit2,
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits#react',
        icon: BookOpen,
    },
];

function buildNavGroups(role: string): NavGroup[] {
    const groups: NavGroup[] = [];

    // Semua role
    groups.push({
        label: 'Umum',
        items: [
            { title: 'Dashboard', href: dashboard(), icon: LayoutGrid },
            { title: 'Pengumuman', href: AnnouncementController.index().url, icon: Megaphone },
        ],
    });

    const academicItems = [
        { title: 'Kelas', href: SchoolClassController.index().url, icon: School },
        { title: 'Absensi', href: AttendanceController.index().url, icon: ClipboardList },
        { title: 'Penilaian', href: GradeController.index().url, icon: GraduationCap },
        { title: 'Rapor', href: ReportCardController.index().url, icon: ScrollText },
    ];

    if (role === 'admin') {
        academicItems.splice(1, 0, {
            title: 'Jadwal',
            href: ScheduleController.index().url,
            icon: CalendarDays,
        });
    }

    // Admin & Guru
    if (role === 'admin' || role === 'guru') {
        groups.push({
            label: 'Akademik',
            items: academicItems,
        });
    }

    // Admin only
    if (role === 'admin') {
        groups.push({
            label: 'Data Master',
            items: [
                { title: 'Siswa', href: StudentController.index().url, icon: Users },
                { title: 'Guru', href: TeacherController.index().url, icon: UserCog },
                { title: 'Mata Pelajaran', href: SubjectController.index().url, icon: BookUser },
                { title: 'Tahun Ajaran', href: AcademicYearController.index().url, icon: CalendarDays },
                { title: 'Kenaikan Kelas', href: ClassPromotionController.index().url, icon: TrendingUp },
                { title: 'Konfigurasi Nilai', href: '/grade-configs', icon: GraduationCap },
            ],
        });

        groups.push({
            label: 'Keuangan',
            items: [
                { title: 'Jenis Pembayaran', href: PaymentTypeController.index().url, icon: Wallet },
                { title: 'Invoice', href: InvoiceController.index().url, icon: Receipt },
            ],
        });

        groups.push({
            label: 'Perpustakaan',
            items: [
                { title: 'Buku', href: BookController.index().url, icon: Library },
                { title: 'Peminjaman', href: BookLoanController.index().url, icon: BookOpen },
            ],
        });

        groups.push({
            label: 'Administrasi',
            items: [
                { title: 'Pengguna', href: '/users', icon: Users },
            ],
        });
    }

    // Siswa
    if (role === 'siswa') {
        groups.push({
            label: 'Portal Saya',
            items: [
                { title: 'Jadwal Saya', href: '/portal/schedule', icon: CalendarDays },
                { title: 'Absensi Saya', href: '/portal/attendances', icon: ClipboardList },
                { title: 'Nilai Saya', href: '/portal/grades', icon: GraduationCap },
                { title: 'Rapor Saya', href: '/portal/report-cards', icon: ScrollText },
                { title: 'Tagihan Saya', href: '/portal/invoices', icon: Receipt },
            ],
        });
    }

    return groups;
}

function NavGroup({ group }: { group: NavGroup }) {
    const { isCurrentUrl } = useCurrentUrl();

    return (
        <SidebarGroup className="px-2 py-0">
            <SidebarGroupLabel>{group.label}</SidebarGroupLabel>
            <SidebarMenu>
                {group.items.map((item) => (
                    <SidebarMenuItem key={item.title}>
                        <SidebarMenuButton
                            asChild
                            isActive={isCurrentUrl(item.href)}
                            tooltip={{ children: item.title }}
                        >
                            <Link href={item.href} prefetch>
                                {item.icon && <item.icon />}
                                <span>{item.title}</span>
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                ))}
            </SidebarMenu>
        </SidebarGroup>
    );
}

export function AppSidebar() {
    const { auth } = usePage<{ auth: Auth }>().props;
    const role = auth.user.role;
    const groups = buildNavGroups(role);

    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href={dashboard()} prefetch>
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                {groups.map((group) => (
                    <NavGroup key={group.label} group={group} />
                ))}
            </SidebarContent>

            <SidebarFooter>
                <NavFooter items={footerNavItems} className="mt-auto" />
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
