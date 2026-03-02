<script setup>
import { Head, Link } from '@inertiajs/vue3';
import JobForm from '@/components/admin/tuition/jobs/JobForm.vue';
import AdminLayout from '@/layouts/AdminLayout.vue';

const props = defineProps({
    job: { type: Object, required: true },
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
    { title: 'Edit', href: `/admin/jobs/${props.job.id}/edit` },
];
</script>

<template>
    <Head title="Edit Job" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <div class="flex items-center justify-between gap-3">
                <h1 class="text-2xl font-semibold">Edit Job</h1>
                <Link href="/admin/jobs" class="text-sm text-muted-foreground underline">Back</Link>
            </div>

            <JobForm
                :action="`/admin/jobs/${props.job.id}`"
                method="put"
                submit-label="Update Job"
                cancel-href="/admin/jobs"
                :is-admin="true"
                :initial="props.job"
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
