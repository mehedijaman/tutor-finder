<script setup>
import { Head, Link } from '@inertiajs/vue3';
import CountryForm from '@/components/admin/tuition/taxonomies/CountryForm.vue';
import AdminLayout from '@/layouts/AdminLayout.vue';

const props = defineProps({
    statusOptions: { type: Array, default: () => [] },
});

const breadcrumbs = [
    { title: 'Countries', href: '/admin/tuition/taxonomies/countries' },
    { title: 'Create', href: '/admin/tuition/taxonomies/countries/create' },
];
</script>

<template>
    <Head title="Create Country" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <div class="flex items-center justify-between gap-3">
                <h1 class="text-2xl font-semibold">Create Country</h1>
                <Link
                    href="/admin/tuition/taxonomies/countries"
                    class="text-sm text-muted-foreground underline"
                    >Back</Link
                >
            </div>

            <CountryForm
                action="/admin/tuition/taxonomies/countries"
                method="post"
                submit-label="Create Country"
                :status-options="props.statusOptions"
            />
        </div>
    </AdminLayout>
</template>
