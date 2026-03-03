<script setup>
import { Head, Link } from '@inertiajs/vue3';
import CountryForm from '@/components/admin/tuition/taxonomies/CountryForm.vue';
import AdminLayout from '@/layouts/AdminLayout.vue';

const props = defineProps({
    country: { type: Object, required: true },
    statusOptions: { type: Array, default: () => [] },
});

const breadcrumbs = [
    { title: 'Countries', href: '/admin/tuition/taxonomies/countries' },
    {
        title: 'Edit',
        href: `/admin/tuition/taxonomies/countries/${props.country.id}/edit`,
    },
];
</script>

<template>
    <Head :title="`Edit Country - ${props.country.name}`" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <div class="flex items-center justify-between gap-3">
                <h1 class="text-2xl font-semibold">Edit Country</h1>
                <Link
                    href="/admin/tuition/taxonomies/countries"
                    class="text-sm text-muted-foreground underline"
                    >Back</Link
                >
            </div>

            <CountryForm
                :action="`/admin/tuition/taxonomies/countries/${props.country.id}`"
                method="put"
                submit-label="Update Country"
                :status-options="props.statusOptions"
                :initial="props.country"
            />
        </div>
    </AdminLayout>
</template>
