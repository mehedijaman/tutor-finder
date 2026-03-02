<script setup>
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
        <div class="space-y-6 p-6">
            <div class="flex items-center justify-between gap-3">
                <h1 class="text-2xl font-semibold">Edit Blog Category</h1>
                <Link href="/admin/blog/categories" class="text-sm text-muted-foreground underline">
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
