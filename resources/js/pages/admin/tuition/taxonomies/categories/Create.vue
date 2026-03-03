<script setup>
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
        <div class="space-y-6 p-6">
            <div class="flex items-center justify-between gap-3">
                <h1 class="text-2xl font-semibold">Create Category</h1>
                <Link
                    href="/admin/tuition/taxonomies/categories"
                    class="text-sm text-muted-foreground underline"
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
