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
                class="flex flex-wrap items-start justify-between gap-4 rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6"
            >
                <div class="space-y-1">
                    <h1
                        class="text-2xl sm:text-3xl font-semibold tracking-tight text-slate-900"
                    >
                        Create Category
                    </h1>
                    <p class="text-sm text-slate-600">
                        Add a new tuition category for classifying jobs.
                    </p>
                </div>
                <Link
                    href="/admin/tuition/taxonomies/categories"
                    class="inline-flex h-9 items-center rounded-md border border-slate-200 px-3 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
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
