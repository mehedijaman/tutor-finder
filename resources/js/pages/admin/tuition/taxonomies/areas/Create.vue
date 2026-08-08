<script setup lang="ts">
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
        <div class="space-y-6 p-4 sm:p-6 lg:p-8">
            <div
                class="flex flex-wrap items-start justify-between gap-4 rounded-2xl border border-slate-200/80 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm sm:p-6"
            >
                <div class="space-y-1">
                    <h1
                        class="text-2xl font-semibold tracking-tight text-slate-900 dark:text-slate-100 sm:text-3xl"
                    >
                        Create Area
                    </h1>
                    <p class="text-sm text-slate-600 dark:text-slate-400">
                        Add an area within a selected city.
                    </p>
                </div>
                <Link
                    href="/admin/tuition/taxonomies/areas"
                    class="inline-flex h-9 items-center rounded-md border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 px-3 text-sm font-medium text-slate-700 dark:text-slate-200 transition hover:bg-slate-50 dark:hover:bg-slate-700"
                    >Back</Link
                >
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
