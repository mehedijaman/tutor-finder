<script setup>
import { Head, Link } from '@inertiajs/vue3';
import JobForm from '@/components/admin/tuition/jobs/JobForm.vue';
import AdminLayout from '@/layouts/AdminLayout.vue';

const props = defineProps({
    tuitionTypes: { type: Array, default: () => [] },
    categories: { type: Array, default: () => [] },
    schoolClasses: { type: Array, default: () => [] },
    countries: { type: Array, default: () => [] },
    cities: { type: Array, default: () => [] },
    areas: { type: Array, default: () => [] },
    subjects: { type: Array, default: () => [] },
    guardians: { type: Array, default: () => [] },
    statusOptions: { type: Array, default: () => [] },
    genderOptions: { type: Array, default: () => [] },
    dayOptions: { type: Array, default: () => [] },
});

const breadcrumbs = [
    { title: 'Jobs', href: '/admin/jobs' },
    { title: 'Create', href: '/admin/jobs/create' },
];
</script>

<template>
    <Head title="Create Job" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <div class="flex items-center justify-between gap-3">
                <h1 class="text-2xl font-semibold">Create Job</h1>
                <Link href="/admin/jobs" class="text-sm text-muted-foreground underline">Back</Link>
            </div>

            <JobForm
                action="/admin/jobs"
                method="post"
                submit-label="Create Job"
                cancel-href="/admin/jobs"
                :is-admin="true"
                :tuition-types="props.tuitionTypes"
                :categories="props.categories"
                :school-classes="props.schoolClasses"
                :countries="props.countries"
                :cities="props.cities"
                :areas="props.areas"
                :subjects="props.subjects"
                :guardians="props.guardians"
                :status-options="props.statusOptions"
                :gender-options="props.genderOptions"
                :day-options="props.dayOptions"
            />
        </div>
    </AdminLayout>
</template>
