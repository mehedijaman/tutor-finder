<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';

const breadcrumbs = [
    { title: 'Admin Dashboard', href: '/admin/dashboard' },
];
</script>

<template>
    <Head title="Admin Dashboard" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-4 p-6">
            <div class="rounded-xl border bg-white p-6 shadow-sm">
                <h1 class="text-2xl font-semibold">Admin Dashboard</h1>
                <p class="mt-2 text-sm text-muted-foreground">
                    Manage admins, tutors, and guardians.
                </p>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <Link class="rounded-lg border bg-white p-4 text-sm hover:bg-muted/50" href="/admin/users">
                    Manage Admin Users
                </Link>
                <Link class="rounded-lg border bg-white p-4 text-sm hover:bg-muted/50" href="/admin/roles">
                    Manage Roles
                </Link>
                <Link class="rounded-lg border bg-white p-4 text-sm hover:bg-muted/50" href="/admin/tutors">
                    Manage Tutors
                </Link>
                <Link class="rounded-lg border bg-white p-4 text-sm hover:bg-muted/50" href="/admin/guardians">
                    Manage Guardians
                </Link>
                <Link class="rounded-lg border bg-white p-4 text-sm hover:bg-muted/50" href="/admin/sms-settings">
                    SMS Settings
                </Link>
            </div>
        </div>
    </AdminLayout>
</template>
