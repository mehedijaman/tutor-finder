<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AreaForm from '@/components/admin/tuition/taxonomies/AreaForm.vue';
import AdminLayout from '@/layouts/AdminLayout.vue';

const props = defineProps({
    area: { type: Object, required: true },
    cities: { type: Array, default: () => [] },
    statusOptions: { type: Array, default: () => [] },
});

const breadcrumbs = [
    { title: 'Areas', href: '/admin/tuition/taxonomies/areas' },
    {
        title: 'Edit',
        href: `/admin/tuition/taxonomies/areas/${props.area.id}/edit`,
    },
];
</script>

<template>
    <Head :title="`Edit Area - ${props.area.name}`" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <div class="flex items-center justify-between gap-3">
                <h1 class="text-2xl font-semibold">Edit Area</h1>
                <Link
                    href="/admin/tuition/taxonomies/areas"
                    class="text-sm text-muted-foreground underline"
                    >Back</Link
                >
            </div>

            <AreaForm
                :action="`/admin/tuition/taxonomies/areas/${props.area.id}`"
                method="put"
                submit-label="Update Area"
                :initial="props.area"
                :cities="props.cities"
                :status-options="props.statusOptions"
            />
        </div>
    </AdminLayout>
</template>
