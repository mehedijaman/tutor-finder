<script setup lang="ts">
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
        <div class="space-y-6 p-4 sm:p-6">
            <div
                class="flex flex-wrap items-start justify-between gap-4 rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6"
            >
                <div class="space-y-1">
                    <h1
                        class="text-2xl font-semibold tracking-tight text-slate-900"
                    >
                        Edit Blog Post
                    </h1>
                    <p class="text-sm text-slate-600">
                        Update content, publish settings, taxonomy, and SEO from
                        the sidebar.
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
