<script setup>
import { Head, Link } from '@inertiajs/vue3';
import CityForm from '@/components/admin/tuition/taxonomies/CityForm.vue';
import AdminLayout from '@/layouts/AdminLayout.vue';

const props = defineProps({
    city: { type: Object, required: true },
    countries: { type: Array, default: () => [] },
    statusOptions: { type: Array, default: () => [] },
});

const breadcrumbs = [
    { title: 'Cities', href: '/admin/tuition/taxonomies/cities' },
    { title: 'Edit', href: `/admin/tuition/taxonomies/cities/${props.city.id}/edit` },
];
</script>

<template>
    <Head :title="`Edit City - ${props.city.name}`" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <div class="flex items-center justify-between gap-3">
                <h1 class="text-2xl font-semibold">Edit City</h1>
                <Link href="/admin/tuition/taxonomies/cities" class="text-sm text-muted-foreground underline">Back</Link>
            </div>

            <CityForm
                :action="`/admin/tuition/taxonomies/cities/${props.city.id}`"
                method="put"
                submit-label="Update City"
                :initial="props.city"
                :countries="props.countries"
                :status-options="props.statusOptions"
            />
        </div>
    </AdminLayout>
</template>
