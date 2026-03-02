<script setup>
import { Head, Link } from '@inertiajs/vue3';
import CityForm from '@/components/admin/tuition/taxonomies/CityForm.vue';
import AdminLayout from '@/layouts/AdminLayout.vue';

const props = defineProps({
    countries: { type: Array, default: () => [] },
    statusOptions: { type: Array, default: () => [] },
});

const breadcrumbs = [
    { title: 'Cities', href: '/admin/tuition/taxonomies/cities' },
    { title: 'Create', href: '/admin/tuition/taxonomies/cities/create' },
];
</script>

<template>
    <Head title="Create City" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <div class="flex items-center justify-between gap-3">
                <h1 class="text-2xl font-semibold">Create City</h1>
                <Link href="/admin/tuition/taxonomies/cities" class="text-sm text-muted-foreground underline">Back</Link>
            </div>

            <CityForm
                action="/admin/tuition/taxonomies/cities"
                method="post"
                submit-label="Create City"
                :countries="props.countries"
                :status-options="props.statusOptions"
            />
        </div>
    </AdminLayout>
</template>
