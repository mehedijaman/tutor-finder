<script setup>
import { Head, Link } from '@inertiajs/vue3';
import TuitionTypeForm from '@/components/admin/tuition/taxonomies/TuitionTypeForm.vue';
import AdminLayout from '@/layouts/AdminLayout.vue';

const props = defineProps({
    statusOptions: { type: Array, default: () => [] },
});

const breadcrumbs = [
    { title: 'Tuition Types', href: '/admin/tuition/taxonomies/tuition-types' },
    { title: 'Create', href: '/admin/tuition/taxonomies/tuition-types/create' },
];
</script>

<template>
    <Head title="Create Tuition Type" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <div class="flex items-center justify-between gap-3">
                <h1 class="text-2xl font-semibold">Create Tuition Type</h1>
                <Link
                    href="/admin/tuition/taxonomies/tuition-types"
                    class="text-sm text-muted-foreground underline"
                    >Back</Link
                >
            </div>

            <TuitionTypeForm
                action="/admin/tuition/taxonomies/tuition-types"
                method="post"
                submit-label="Create Tuition Type"
                :status-options="props.statusOptions"
            />
        </div>
    </AdminLayout>
</template>
