<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import CategoryForm from '@/components/admin/tuition/taxonomies/CategoryForm.vue';
import AdminLayout from '@/layouts/AdminLayout.vue';

const props = defineProps({
    statusOptions: { type: Array, default: () => [] },
});

const breadcrumbs = [
    { title: 'Categories', href: '/admin/tuition/taxonomies/categories' },
    { title: 'Create', href: '/admin/tuition/taxonomies/categories/create' },
];
</script>

<template>
    <Head title="Create Category" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-4 sm:p-6 lg:p-8">
            <div
                class="flex flex-wrap items-start justify-between gap-4 rounded-2xl border border-slate-200/80 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm sm:p-6"
            >
                <div class="space-y-1">
                    <h1
                        class="text-2xl font-semibold tracking-tight text-slate-900 dark:text-slate-100 sm:text-3xl"
                    >
                        Create Category
                    </h1>
                    <p class="text-sm text-slate-600 dark:text-slate-400">
                        Add a category for grouping classes.
                    </p>
                </div>
                <Link
                    href="/admin/tuition/taxonomies/categories"
                    class="inline-flex h-9 items-center rounded-md border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 px-3 text-sm font-medium text-slate-700 dark:text-slate-200 transition hover:bg-slate-50 dark:hover:bg-slate-700"
                    >Back</Link
                >
            </div>

            <CategoryForm
                action="/admin/tuition/taxonomies/categories"
                method="post"
                submit-label="Create Category"
                :status-options="props.statusOptions"
            />
        </div>
    </AdminLayout>
</template>
