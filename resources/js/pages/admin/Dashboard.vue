<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';

const breadcrumbs = [{ title: 'Admin Dashboard', href: '/admin/dashboard' }];
</script>

<template>
    <Head title="Admin Dashboard" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-4 sm:p-6">
            <div
                class="relative overflow-hidden rounded-2xl border border-blue-500/20 bg-gradient-to-r from-blue-700 to-blue-600 p-6 text-white shadow-sm sm:p-8"
            >
                <div
                    class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(255,255,255,0.26),transparent_44%)]"
                ></div>

                <h1 class="relative text-2xl font-semibold tracking-tight">
                    Admin Dashboard
                </h1>
                <p class="relative mt-2 text-sm text-white/90 sm:text-base">
                    Oversee users, platform operations, and system-level
                    settings from one place.
                </p>
            </div>

            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                <Link
                    class="group rounded-xl border border-slate-200/80 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-blue-200 hover:shadow-md"
                    href="/admin/users"
                >
                    <p
                        class="text-base font-semibold tracking-tight text-slate-900"
                    >
                        Manage Admin Users
                    </p>
                    <p class="mt-1 text-sm text-muted-foreground">
                        Create and maintain admin accounts.
                    </p>
                </Link>
                <Link
                    class="group rounded-xl border border-slate-200/80 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-blue-200 hover:shadow-md"
                    href="/admin/roles"
                >
                    <p
                        class="text-base font-semibold tracking-tight text-slate-900"
                    >
                        Manage Roles
                    </p>
                    <p class="mt-1 text-sm text-muted-foreground">
                        Configure role definitions and access levels.
                    </p>
                </Link>
                <Link
                    class="group rounded-xl border border-slate-200/80 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-blue-200 hover:shadow-md"
                    href="/admin/tutors"
                >
                    <p
                        class="text-base font-semibold tracking-tight text-slate-900"
                    >
                        Manage Tutors
                    </p>
                    <p class="mt-1 text-sm text-muted-foreground">
                        Review tutor profiles and verification states.
                    </p>
                </Link>
                <Link
                    class="group rounded-xl border border-slate-200/80 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-blue-200 hover:shadow-md"
                    href="/admin/guardians"
                >
                    <p
                        class="text-base font-semibold tracking-tight text-slate-900"
                    >
                        Manage Guardians
                    </p>
                    <p class="mt-1 text-sm text-muted-foreground">
                        Monitor guardian profiles and hiring activity.
                    </p>
                </Link>
                <Link
                    class="group rounded-xl border border-slate-200/80 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-blue-200 hover:shadow-md"
                    href="/settings/sms"
                >
                    <p
                        class="text-base font-semibold tracking-tight text-slate-900"
                    >
                        SMS Settings
                    </p>
                    <p class="mt-1 text-sm text-muted-foreground">
                        Configure OTP and notification delivery setup.
                    </p>
                </Link>
            </div>
        </div>
    </AdminLayout>
</template>
