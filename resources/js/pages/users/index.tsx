import { Head } from '@inertiajs/react';
import { dashboard } from '@/routes';

export default function UsersIndex() {
    return (
        <>
            <Head title="Manajemen Pengguna" />
            <div className="p-4">
                <h1 className="text-xl font-semibold">Manajemen Pengguna</h1>
            </div>
        </>
    );
}

UsersIndex.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: dashboard() },
        { title: 'Manajemen Pengguna', href: '/users' },
    ],
};
