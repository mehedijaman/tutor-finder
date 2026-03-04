<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    Users,
    Shield,
    GraduationCap,
    UserCheck,
    MessageSquare,
    Settings,
    CreditCard,
    FileCheck,
    Layout,
    ArrowRight,
} from 'lucide-vue-next';
import AdminLayout from '@/layouts/AdminLayout.vue';

const breadcrumbs = [{ title: 'Admin Dashboard', href: '/admin/dashboard' }];
</script>

<template>
    <Head title="Admin Dashboard" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-8 p-4 sm:p-6 lg:p-8">
            <!-- Hero Section -->
            <div
                class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-slate-800 via-slate-900 to-slate-950 p-8 text-white shadow-2xl shadow-slate-900/30 sm:p-10"
            >
                <div
                    class="pointer-events-none absolute inset-0 bg-[radial-gradient(ellipse_at_top_right,rgba(59,130,246,0.2),transparent_50%)] opacity-80"
                ></div>
                <div
                    class="pointer-events-none absolute -bottom-20 -right-20 h-64 w-64 rounded-full bg-blue-500/10 blur-3xl"
                ></div>

                <div class="relative">
                    <p class="text-sm font-medium uppercase tracking-wider text-slate-400">
                        Control Center
                    </p>
                    <h1 class="mt-2 text-3xl font-bold tracking-tight sm:text-4xl">
                        Admin Dashboard
                    </h1>
                    <p class="mt-3 max-w-xl text-base text-slate-300 sm:text-lg">
                        Oversee users, platform operations, and system-level settings from one place.
                    </p>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                <Link
                    class="group relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:border-blue-200 hover:shadow-xl hover:shadow-blue-500/10 dark:border-slate-800 dark:bg-slate-900"
                    href="/admin/users"
                >
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-blue-500 to-blue-600 text-white shadow-lg shadow-blue-500/30 transition-transform duration-300 group-hover:scale-110">
                        <Users class="h-6 w-6" />
                    </div>
                    <h3 class="mt-5 text-lg font-semibold tracking-tight text-slate-900 dark:text-white">
                        Admin Users
                    </h3>
                    <p class="mt-2 text-sm leading-relaxed text-slate-600 dark:text-slate-400">
                        Create and maintain admin accounts.
                    </p>
                    <ArrowRight class="mt-4 h-5 w-5 text-slate-400 transition-transform duration-300 group-hover:translate-x-1 group-hover:text-blue-600" />
                </Link>

                <Link
                    class="group relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:border-violet-200 hover:shadow-xl hover:shadow-violet-500/10 dark:border-slate-800 dark:bg-slate-900"
                    href="/admin/roles"
                >
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-violet-500 to-violet-600 text-white shadow-lg shadow-violet-500/30 transition-transform duration-300 group-hover:scale-110">
                        <Shield class="h-6 w-6" />
                    </div>
                    <h3 class="mt-5 text-lg font-semibold tracking-tight text-slate-900 dark:text-white">
                        Roles & Permissions
                    </h3>
                    <p class="mt-2 text-sm leading-relaxed text-slate-600 dark:text-slate-400">
                        Configure role definitions and access levels.
                    </p>
                    <ArrowRight class="mt-4 h-5 w-5 text-slate-400 transition-transform duration-300 group-hover:translate-x-1 group-hover:text-violet-600" />
                </Link>

                <Link
                    class="group relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:border-emerald-200 hover:shadow-xl hover:shadow-emerald-500/10 dark:border-slate-800 dark:bg-slate-900"
                    href="/admin/tutors"
                >
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-emerald-500 to-emerald-600 text-white shadow-lg shadow-emerald-500/30 transition-transform duration-300 group-hover:scale-110">
                        <GraduationCap class="h-6 w-6" />
                    </div>
                    <h3 class="mt-5 text-lg font-semibold tracking-tight text-slate-900 dark:text-white">
                        Manage Tutors
                    </h3>
                    <p class="mt-2 text-sm leading-relaxed text-slate-600 dark:text-slate-400">
                        Review tutor profiles and verification states.
                    </p>
                    <ArrowRight class="mt-4 h-5 w-5 text-slate-400 transition-transform duration-300 group-hover:translate-x-1 group-hover:text-emerald-600" />
                </Link>

                <Link
                    class="group relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:border-amber-200 hover:shadow-xl hover:shadow-amber-500/10 dark:border-slate-800 dark:bg-slate-900"
                    href="/admin/guardians"
                >
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-amber-500 to-amber-600 text-white shadow-lg shadow-amber-500/30 transition-transform duration-300 group-hover:scale-110">
                        <UserCheck class="h-6 w-6" />
                    </div>
                    <h3 class="mt-5 text-lg font-semibold tracking-tight text-slate-900 dark:text-white">
                        Manage Guardians
                    </h3>
                    <p class="mt-2 text-sm leading-relaxed text-slate-600 dark:text-slate-400">
                        Monitor guardian profiles and hiring activity.
                    </p>
                    <ArrowRight class="mt-4 h-5 w-5 text-slate-400 transition-transform duration-300 group-hover:translate-x-1 group-hover:text-amber-600" />
                </Link>

                <Link
                    class="group relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:border-cyan-200 hover:shadow-xl hover:shadow-cyan-500/10 dark:border-slate-800 dark:bg-slate-900"
                    href="/admin/verifications"
                >
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-cyan-500 to-cyan-600 text-white shadow-lg shadow-cyan-500/30 transition-transform duration-300 group-hover:scale-110">
                        <FileCheck class="h-6 w-6" />
                    </div>
                    <h3 class="mt-5 text-lg font-semibold tracking-tight text-slate-900 dark:text-white">
                        Verifications
                    </h3>
                    <p class="mt-2 text-sm leading-relaxed text-slate-600 dark:text-slate-400">
                        Review and approve user verifications.
                    </p>
                    <ArrowRight class="mt-4 h-5 w-5 text-slate-400 transition-transform duration-300 group-hover:translate-x-1 group-hover:text-cyan-600" />
                </Link>

                <Link
                    class="group relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:border-rose-200 hover:shadow-xl hover:shadow-rose-500/10 dark:border-slate-800 dark:bg-slate-900"
                    href="/admin/finance/invoices"
                >
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-rose-500 to-rose-600 text-white shadow-lg shadow-rose-500/30 transition-transform duration-300 group-hover:scale-110">
                        <CreditCard class="h-6 w-6" />
                    </div>
                    <h3 class="mt-5 text-lg font-semibold tracking-tight text-slate-900 dark:text-white">
                        Finance & Payments
                    </h3>
                    <p class="mt-2 text-sm leading-relaxed text-slate-600 dark:text-slate-400">
                        Manage invoices, payments, and refunds.
                    </p>
                    <ArrowRight class="mt-4 h-5 w-5 text-slate-400 transition-transform duration-300 group-hover:translate-x-1 group-hover:text-rose-600" />
                </Link>

                <Link
                    class="group relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:border-indigo-200 hover:shadow-xl hover:shadow-indigo-500/10 dark:border-slate-800 dark:bg-slate-900"
                    href="/admin/content/pages"
                >
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-500 to-indigo-600 text-white shadow-lg shadow-indigo-500/30 transition-transform duration-300 group-hover:scale-110">
                        <Layout class="h-6 w-6" />
                    </div>
                    <h3 class="mt-5 text-lg font-semibold tracking-tight text-slate-900 dark:text-white">
                        CMS & Content
                    </h3>
                    <p class="mt-2 text-sm leading-relaxed text-slate-600 dark:text-slate-400">
                        Manage pages, blog, and FAQs.
                    </p>
                    <ArrowRight class="mt-4 h-5 w-5 text-slate-400 transition-transform duration-300 group-hover:translate-x-1 group-hover:text-indigo-600" />
                </Link>

                <Link
                    class="group relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:border-slate-300 hover:shadow-xl hover:shadow-slate-500/10 dark:border-slate-800 dark:bg-slate-900"
                    href="/settings/site"
                >
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-slate-600 to-slate-700 text-white shadow-lg shadow-slate-500/30 transition-transform duration-300 group-hover:scale-110">
                        <Settings class="h-6 w-6" />
                    </div>
                    <h3 class="mt-5 text-lg font-semibold tracking-tight text-slate-900 dark:text-white">
                        System Settings
                    </h3>
                    <p class="mt-2 text-sm leading-relaxed text-slate-600 dark:text-slate-400">
                        Configure site, SMS, SMTP, and payments.
                    </p>
                    <ArrowRight class="mt-4 h-5 w-5 text-slate-400 transition-transform duration-300 group-hover:translate-x-1 group-hover:text-slate-600" />
                </Link>
            </div>
        </div>
    </AdminLayout>
</template>
