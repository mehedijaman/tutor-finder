<script setup>
import { Head, Link } from '@inertiajs/vue3';
import BlogCategoryForm from '@/components/admin/blog/BlogCategoryForm.vue';
import AdminLayout from '@/layouts/AdminLayout.vue';

const breadcrumbs = [
    { title: 'Blog Categories', href: '/admin/blog/categories' },
    { title: 'Create', href: '/admin/blog/categories/create' },
];

const initialValues = {
    name: '',
    slug: '',
    description: '',
    status: 'active',
    meta_title: '',
    meta_description: '',
    image_url: null,
};
</script>

<template>
    <Head title="Create Blog Category" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <div class="flex items-center justify-between gap-3">
                <h1 class="text-2xl font-semibold">Create Blog Category</h1>
                <Link href="/admin/blog/categories" class="text-sm text-muted-foreground underline">
                    Back
                </Link>
            </div>

            <BlogCategoryForm
                action="/admin/blog/categories"
                method="post"
                submit-label="Create Category"
                :initial="initialValues"
            />
        </div>
    </AdminLayout>
</template>
