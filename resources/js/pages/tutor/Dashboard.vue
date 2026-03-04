<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import TutorLayout from '@/layouts/TutorLayout.vue';

const breadcrumbs = [{ title: 'Tutor Dashboard', href: '/tutor/dashboard' }];
</script>

<template>
    <Head title="Tutor Dashboard" />

    <TutorLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-4 sm:p-6">
            <div
                class="relative overflow-hidden rounded-2xl border border-blue-500/20 bg-gradient-to-r from-blue-700 to-blue-600 p-6 text-white shadow-sm sm:p-8"
            >
                <div
                    class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(255,255,255,0.26),transparent_44%)]"
                ></div>

                <h1 class="relative text-2xl font-semibold tracking-tight">
                    Tutor Dashboard
                </h1>
                <p class="relative mt-2 text-sm text-white/90 sm:text-base">
                    Manage your applications, profile, and tutoring workflow.
                </p>
            </div>

            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                <Link
                    href="/tutor/job-applications"
                    class="rounded-xl border border-slate-200/80 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-blue-200 hover:shadow-md"
                >
                    <p
                        class="text-base font-semibold tracking-tight text-slate-900"
                    >
                        Application Pipeline
                    </p>
                    <p class="mt-1 text-sm text-muted-foreground">
                        Track applied, shortlisted, and appointed jobs.
                    </p>
                </Link>

                <Link
                    href="/tutor/profile"
                    class="rounded-xl border border-slate-200/80 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-blue-200 hover:shadow-md"
                >
                    <p
                        class="text-base font-semibold tracking-tight text-slate-900"
                    >
                        Profile
                    </p>
                    <p class="mt-1 text-sm text-muted-foreground">
                        Update your bio, education, and teaching preferences.
                    </p>
                </Link>

                <Link
                    href="/tutor/notifications"
                    class="rounded-xl border border-slate-200/80 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-blue-200 hover:shadow-md"
                >
                    <p
                        class="text-base font-semibold tracking-tight text-slate-900"
                    >
                        Notifications
                    </p>
                    <p class="mt-1 text-sm text-muted-foreground">
                        Stay updated on new opportunities and status changes.
                    </p>
                </Link>
            </div>
        </div>
    </TutorLayout>
</template>
