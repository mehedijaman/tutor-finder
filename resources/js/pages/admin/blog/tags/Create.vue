<script setup>
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
        <div class="space-y-6 p-6">
            <div class="flex items-center justify-between gap-3">
                <h1 class="text-2xl font-semibold">Create Blog Tag</h1>
                <Link href="/admin/blog/tags" class="text-sm text-muted-foreground underline">
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
