<script setup>
import { Head, Link } from '@inertiajs/vue3';
import BlogPostForm from '@/components/admin/blog/BlogPostForm.vue';
import AdminLayout from '@/layouts/AdminLayout.vue';

const props = defineProps({
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
    { title: 'Create', href: '/admin/blog/posts/create' },
];

const initialValues = {
    title: '',
    slug: '',
    summary: '',
    content: '<p></p>',
    status: 'draft',
    published_at: '',
    category_ids: [],
    tag_ids: [],
    meta_title: '',
    meta_description: '',
    cover_url: null,
};
</script>

<template>
    <Head title="Create Blog Post" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl border bg-white px-5 py-4">
                <div>
                    <h1 class="text-2xl font-semibold">Create Blog Post</h1>
                    <p class="text-sm text-muted-foreground">
                        Draft and publish your new article from one workspace.
                    </p>
                </div>
                <Link href="/admin/blog/posts" class="text-sm font-medium text-muted-foreground underline-offset-4 hover:underline">
                    Back to Posts
                </Link>
            </div>

            <BlogPostForm
                action="/admin/blog/posts"
                method="post"
                submit-label="Publish"
                :categories="categories"
                :tags="tags"
                :initial="initialValues"
            />
        </div>
    </AdminLayout>
</template>
