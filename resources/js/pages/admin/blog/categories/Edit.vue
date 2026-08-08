<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import BlogCategoryForm from '@/components/admin/blog/BlogCategoryForm.vue';
import AdminLayout from '@/layouts/AdminLayout.vue';

const props = defineProps({
    category: {
        type: Object,
        required: true,
    },
});

const breadcrumbs = [
    { title: 'Blog Categories', href: '/admin/blog/categories' },
    { title: 'Edit', href: `/admin/blog/categories/${props.category.id}/edit` },
];
</script>

<template>
    <Head title="Edit Blog Category" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-4 sm:p-6 lg:p-8">
            <div
                class="flex flex-wrap items-start justify-between gap-4 rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-6"
            >
                <div class="space-y-1">
                    <h1
                        class="text-2xl font-semibold tracking-tight text-slate-900 dark:text-slate-100 sm:text-3xl"
                    >
                        Edit Blog Category
                    </h1>
                    <p class="text-sm text-slate-600 dark:text-slate-400">
                        Update category metadata and publishing visibility.
                    </p>
                </div>
                <Link
                    href="/admin/blog/categories"
                    class="inline-flex h-9 items-center rounded-md border border-slate-200 px-3 text-sm font-medium text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
                >
                    Back
                </Link>
            </div>

            <BlogCategoryForm
                :action="`/admin/blog/categories/${category.id}`"
                method="put"
                submit-label="Update Category"
                :initial="category"
            />
        </div>
    </AdminLayout>
</template>
