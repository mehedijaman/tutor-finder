<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import BlogTagForm from '@/components/admin/blog/BlogTagForm.vue';
import AdminLayout from '@/layouts/AdminLayout.vue';

const breadcrumbs = [
    { title: 'Blog Tags', href: '/admin/blog/tags' },
    { title: 'Create', href: '/admin/blog/tags/create' },
];

const initialValues = {
    name: '',
    slug: '',
    status: 'active',
};
</script>

<template>
    <Head title="Create Blog Tag" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-4 sm:p-6">
            <div
                class="flex flex-wrap items-start justify-between gap-4 rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6"
            >
                <div class="space-y-1">
                    <h1
                        class="text-2xl font-semibold tracking-tight text-slate-900"
                    >
                        Create Blog Tag
                    </h1>
                    <p class="text-sm text-slate-600">
                        Create a new tag to improve post discoverability.
                    </p>
                </div>
                <Link
                    href="/admin/blog/tags"
                    class="inline-flex h-9 items-center rounded-md border border-slate-200 px-3 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
                >
                    Back
                </Link>
            </div>

            <BlogTagForm
                action="/admin/blog/tags"
                method="post"
                submit-label="Create Tag"
                :initial="initialValues"
            />
        </div>
    </AdminLayout>
</template>
