<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import BlogPostForm from '@/components/admin/blog/BlogPostForm.vue';
import AdminLayout from '@/layouts/AdminLayout.vue';

defineProps({
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
        <div class="space-y-6 p-4 sm:p-6 lg:p-8">
            <div
                class="flex flex-wrap items-start justify-between gap-4 rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6"
            >
                <div class="space-y-1">
                    <h1
                        class="text-2xl font-semibold tracking-tight text-slate-900 sm:text-3xl"
                    >
                        Create Blog Post
                    </h1>
                    <p class="text-sm text-slate-600">
                        Draft and publish your new article from one workspace.
                    </p>
                </div>
                <Link
                    href="/admin/blog/posts"
                    class="inline-flex h-9 items-center rounded-md border border-slate-200 px-3 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
                >
                    Back
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
