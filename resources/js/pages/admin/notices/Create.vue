<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import NoticeForm from '@/components/admin/notices/NoticeForm.vue';
import AdminLayout from '@/layouts/AdminLayout.vue';

const props = defineProps({
    audienceOptions: {
        type: Array,
        default: () => [],
    },
});

const breadcrumbs = [
    { title: 'Notices', href: '/admin/notices' },
    { title: 'Create', href: '/admin/notices/create' },
];

const initialValues = {
    title: '',
    body: '<p></p>',
    audience: 'both',
    expires_at: '',
    published_at: '',
    is_active: true,
};
</script>

<template>
    <Head title="Create Notice" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-4 sm:p-6 lg:p-8">
            <div
                class="flex flex-wrap items-start justify-between gap-4 rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6"
            >
                <div class="space-y-1">
                    <h1
                        class="text-2xl sm:text-3xl font-semibold tracking-tight text-slate-900"
                    >
                        Create Notice
                    </h1>
                    <p class="text-sm text-slate-600">
                        Add a new notice for tutors, guardians, or both.
                    </p>
                </div>
                <Link
                    href="/admin/notices"
                    class="inline-flex h-9 items-center rounded-md border border-slate-200 px-3 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
                >
                    Back
                </Link>
            </div>

            <NoticeForm
                action="/admin/notices"
                method="post"
                submit-label="Create Notice"
                :initial="initialValues"
                :audience-options="props.audienceOptions"
            />
        </div>
    </AdminLayout>
</template>
