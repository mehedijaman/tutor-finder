<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import JobForm from '@/components/admin/jobs/JobForm.vue';
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
        <div class="space-y-6 p-4 sm:p-6 lg:p-8">
            <div
                class="flex flex-wrap items-start justify-between gap-4 rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6"
            >
                <div class="space-y-1">
                    <h1
                        class="text-2xl sm:text-3xl font-semibold tracking-tight text-slate-900"
                    >
                        Edit Job
                    </h1>
                    <p class="text-sm text-slate-600">
                        Refine job requirements and update current status.
                    </p>
                </div>
                <Link
                    href="/admin/jobs"
                    class="inline-flex h-9 items-center rounded-md border border-slate-200 px-3 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
                    >Back</Link
                >
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
