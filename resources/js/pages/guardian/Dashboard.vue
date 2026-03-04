<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import GuardianLayout from '@/layouts/GuardianLayout.vue';

const breadcrumbs = [
    { title: 'Guardian Dashboard', href: '/guardian/dashboard' },
];
</script>

<template>
    <Head title="Guardian Dashboard" />

    <GuardianLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-4 sm:p-6">
            <div
                class="relative overflow-hidden rounded-2xl border border-blue-500/20 bg-gradient-to-r from-blue-700 to-blue-600 p-6 text-white shadow-sm sm:p-8"
            >
                <div
                    class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(255,255,255,0.26),transparent_44%)]"
                ></div>

                <h1 class="relative text-2xl font-semibold tracking-tight">
                    Guardian Dashboard
                </h1>
                <p class="relative mt-2 text-sm text-white/90 sm:text-base">
                    Manage job posts, hiring progress, and tutor communication.
                </p>
            </div>

            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                <Link
                    href="/guardian/jobs/create"
                    class="rounded-xl border border-slate-200/80 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-blue-200 hover:shadow-md"
                >
                    <p
                        class="text-base font-semibold tracking-tight text-slate-900"
                    >
                        Post New Job
                    </p>
                    <p class="mt-1 text-sm text-muted-foreground">
                        Create a new tutoring requirement in minutes.
                    </p>
                </Link>

                <Link
                    href="/guardian/jobs"
                    class="rounded-xl border border-slate-200/80 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-blue-200 hover:shadow-md"
                >
                    <p
                        class="text-base font-semibold tracking-tight text-slate-900"
                    >
                        Hiring Pipeline
                    </p>
                    <p class="mt-1 text-sm text-muted-foreground">
                        Review live, confirmed, and closed job activity.
                    </p>
                </Link>

                <Link
                    href="/guardian/notifications"
                    class="rounded-xl border border-slate-200/80 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-blue-200 hover:shadow-md"
                >
                    <p
                        class="text-base font-semibold tracking-tight text-slate-900"
                    >
                        Notifications
                    </p>
                    <p class="mt-1 text-sm text-muted-foreground">
                        Monitor application updates and hiring milestones.
                    </p>
                </Link>
            </div>
        </div>
    </GuardianLayout>
</template>
