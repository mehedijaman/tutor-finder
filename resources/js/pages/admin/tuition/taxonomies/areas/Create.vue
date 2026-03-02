<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AreaForm from '@/components/admin/tuition/taxonomies/AreaForm.vue';
import AdminLayout from '@/layouts/AdminLayout.vue';

const props = defineProps({
    cities: { type: Array, default: () => [] },
    statusOptions: { type: Array, default: () => [] },
});

const breadcrumbs = [
    { title: 'Areas', href: '/admin/tuition/taxonomies/areas' },
    { title: 'Create', href: '/admin/tuition/taxonomies/areas/create' },
];
</script>

<template>
    <Head title="Create Area" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <div class="flex items-center justify-between gap-3">
                <h1 class="text-2xl font-semibold">Create Area</h1>
                <Link href="/admin/tuition/taxonomies/areas" class="text-sm text-muted-foreground underline">Back</Link>
            </div>

            <AreaForm
                action="/admin/tuition/taxonomies/areas"
                method="post"
                submit-label="Create Area"
                :cities="props.cities"
                :status-options="props.statusOptions"
            />
        </div>
    </AdminLayout>
</template>
