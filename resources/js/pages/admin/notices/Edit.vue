<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import NoticeForm from '@/components/admin/notices/NoticeForm.vue';
import AdminLayout from '@/layouts/AdminLayout.vue';

const props = defineProps({
    notice: {
        type: Object,
        required: true,
    },
    audienceOptions: {
        type: Array,
        default: () => [],
    },
});

const breadcrumbs = [
    { title: 'Notices', href: '/admin/notices' },
    { title: 'Edit', href: `/admin/notices/${props.notice.id}/edit` },
];
</script>

<template>
    <Head title="Edit Notice" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-4 sm:p-6 lg:p-8">
            <div
                class="flex flex-wrap items-start justify-between gap-4 rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6 dark:border-slate-800 dark:bg-slate-900"
            >
                <div class="space-y-1">
                    <h1
                        class="text-2xl font-semibold tracking-tight text-slate-900 sm:text-3xl dark:text-slate-100"
                    >
                        Edit Notice
                    </h1>
                    <p class="text-sm text-slate-600 dark:text-slate-400">
                        Update notice content, audience, and schedule.
                    </p>
                </div>
                <Link
                    href="/admin/notices"
                    class="inline-flex h-9 items-center rounded-md border border-slate-200 bg-white px-3 text-sm font-medium text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700"
                >
                    Back
                </Link>
            </div>

            <NoticeForm
                :action="`/admin/notices/${notice.id}`"
                method="put"
                submit-label="Update Notice"
                :initial="notice"
                :audience-options="props.audienceOptions"
            />
        </div>
    </AdminLayout>
</template>
