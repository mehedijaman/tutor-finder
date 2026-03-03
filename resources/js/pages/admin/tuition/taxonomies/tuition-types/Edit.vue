<script setup>
import { Head, Link } from '@inertiajs/vue3';
import TuitionTypeForm from '@/components/admin/tuition/taxonomies/TuitionTypeForm.vue';
import AdminLayout from '@/layouts/AdminLayout.vue';

const props = defineProps({
    tuitionType: { type: Object, required: true },
    statusOptions: { type: Array, default: () => [] },
});

const breadcrumbs = [
    { title: 'Tuition Types', href: '/admin/tuition/taxonomies/tuition-types' },
    {
        title: 'Edit',
        href: `/admin/tuition/taxonomies/tuition-types/${props.tuitionType.id}/edit`,
    },
];
</script>

<template>
    <Head :title="`Edit Tuition Type - ${props.tuitionType.name}`" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <div class="flex items-center justify-between gap-3">
                <h1 class="text-2xl font-semibold">Edit Tuition Type</h1>
                <Link
                    href="/admin/tuition/taxonomies/tuition-types"
                    class="text-sm text-muted-foreground underline"
                    >Back</Link
                >
            </div>

            <TuitionTypeForm
                :action="`/admin/tuition/taxonomies/tuition-types/${props.tuitionType.id}`"
                method="put"
                submit-label="Update Tuition Type"
                :initial="props.tuitionType"
                :status-options="props.statusOptions"
            />
        </div>
    </AdminLayout>
</template>
