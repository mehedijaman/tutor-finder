<script setup lang="ts">
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
        <div class="space-y-6 p-4 sm:p-6 lg:p-8">
            <div
                class="flex flex-wrap items-start justify-between gap-4 rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6"
            >
                <div class="space-y-1">
                    <h1
                        class="text-2xl font-semibold tracking-tight text-slate-900 sm:text-3xl"
                    >
                        Edit Area
                    </h1>
                    <p class="text-sm text-slate-600">
                        Update area details, city association, and status.
                    </p>
                </div>
                <Link
                    href="/admin/tuition/taxonomies/areas"
                    class="inline-flex h-9 items-center rounded-md border border-slate-200 px-3 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
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
