<script setup>
import { Head, Link } from '@inertiajs/vue3';
import BlogTagForm from '@/components/admin/blog/BlogTagForm.vue';
import AdminLayout from '@/layouts/AdminLayout.vue';

const props = defineProps({
    tag: {
        type: Object,
        required: true,
    },
});

const breadcrumbs = [
    { title: 'Blog Tags', href: '/admin/blog/tags' },
    { title: 'Edit', href: `/admin/blog/tags/${props.tag.id}/edit` },
];
</script>

<template>
    <Head title="Edit Blog Tag" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <div class="flex items-center justify-between gap-3">
                <h1 class="text-2xl font-semibold">Edit Blog Tag</h1>
                <Link
                    href="/admin/blog/tags"
                    class="text-sm text-muted-foreground underline"
                >
                    Back
                </Link>
            </div>

            <BlogTagForm
                :action="`/admin/blog/tags/${tag.id}`"
                method="put"
                submit-label="Update Tag"
                :initial="tag"
            />
        </div>
    </AdminLayout>
</template>
