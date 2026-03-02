<script setup>
import { Head, Link } from '@inertiajs/vue3';
import BlogPostForm from '@/components/admin/blog/BlogPostForm.vue';
import AdminLayout from '@/layouts/AdminLayout.vue';

const props = defineProps({
    post: {
        type: Object,
        required: true,
    },
    categories: {
        type: Array,
        default: () => [],
    },
    tags: {
        type: Array,
        default: () => [],
    },
});

const breadcrumbs = [
    { title: 'Blog Posts', href: '/admin/blog/posts' },
    { title: 'Edit', href: `/admin/blog/posts/${props.post.id}/edit` },
];
</script>

<template>
    <Head title="Edit Blog Post" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl border bg-white px-5 py-4">
                <div>
                    <h1 class="text-2xl font-semibold">Edit Blog Post</h1>
                    <p class="text-sm text-muted-foreground">
                        Update content, publish settings, taxonomy, and SEO from the sidebar.
                    </p>
                </div>
                <Link href="/admin/blog/posts" class="text-sm font-medium text-muted-foreground underline-offset-4 hover:underline">
                    Back to Posts
                </Link>
            </div>

            <BlogPostForm
                :action="`/admin/blog/posts/${post.id}`"
                method="put"
                submit-label="Update"
                :categories="categories"
                :tags="tags"
                :initial="post"
            />
        </div>
    </AdminLayout>
</template>
