<script setup lang="ts">
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
        <div class="space-y-6 p-4 sm:p-6">
            <div
                class="flex flex-wrap items-start justify-between gap-4 rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6"
            >
                <div class="space-y-1">
                    <h1
                        class="text-2xl font-semibold tracking-tight text-slate-900"
                    >
                        Edit Blog Tag
                    </h1>
                    <p class="text-sm text-slate-600">
                        Refine naming and publishing status for this tag.
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
                :action="`/admin/blog/tags/${tag.id}`"
                method="put"
                submit-label="Update Tag"
                :initial="tag"
            />
        </div>
    </AdminLayout>
</template>
