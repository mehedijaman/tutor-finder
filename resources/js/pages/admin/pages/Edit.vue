<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import PageForm from '@/components/admin/pages/PageForm.vue';
import AdminLayout from '@/layouts/AdminLayout.vue';

const props = defineProps({
    page: {
        type: Object,
        required: true,
    },
    statusOptions: {
        type: Array,
        default: () => [],
    },
});

const breadcrumbs = [
    { title: 'Pages', href: '/admin/pages' },
    { title: 'Edit Page', href: `/admin/pages/${props.page.id}/edit` },
];
</script>

<template>
    <Head :title="`Edit: ${page.title}`" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-4 sm:p-6">
            <div
                class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6"
            >
                <h1 class="text-2xl font-semibold tracking-tight">Edit Page</h1>
            </div>

            <div
                class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6"
            >
                <PageForm
                    :action="`/admin/pages/${page.id}`"
                    method="put"
                    submit-label="Update Page"
                    :initial="page"
                    :status-options="statusOptions"
                    :is-system="page.is_system"
                />
            </div>
        </div>
    </AdminLayout>
</template>
