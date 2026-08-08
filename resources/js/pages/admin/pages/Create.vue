<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import PageForm from '@/components/admin/pages/PageForm.vue';
import AdminLayout from '@/layouts/AdminLayout.vue';

defineProps({
    statusOptions: {
        type: Array,
        default: () => [],
    },
});

const breadcrumbs = [
    { title: 'Pages', href: '/admin/pages' },
    { title: 'Create Page', href: '/admin/pages/create' },
];
</script>

<template>
    <Head title="Create Page" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-4 sm:p-6 lg:p-8">
            <div
                class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6 dark:border-slate-800 dark:bg-slate-900"
            >
                <h1
                    class="text-2xl font-semibold tracking-tight text-slate-900 sm:text-3xl dark:text-slate-100"
                >
                    Create Page
                </h1>
            </div>

            <div
                class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6 dark:border-slate-800 dark:bg-slate-900"
            >
                <PageForm
                    action="/admin/pages"
                    submit-label="Create Page"
                    :status-options="statusOptions"
                />
            </div>
        </div>
    </AdminLayout>
</template>
