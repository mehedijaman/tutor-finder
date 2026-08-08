<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import JobForm from '@/components/admin/jobs/JobForm.vue';
import GuardianLayout from '@/layouts/GuardianLayout.vue';

const props = defineProps({
    tuitionTypes: { type: Array, default: () => [] },
    categories: { type: Array, default: () => [] },
    schoolClasses: { type: Array, default: () => [] },
    countries: { type: Array, default: () => [] },
    cities: { type: Array, default: () => [] },
    areas: { type: Array, default: () => [] },
    subjects: { type: Array, default: () => [] },
    genderOptions: { type: Array, default: () => [] },
    dayOptions: { type: Array, default: () => [] },
});

const breadcrumbs = [
    { title: 'My Jobs', href: '/guardian/jobs' },
    { title: 'Create', href: '/guardian/jobs/create' },
];
</script>

<template>
    <Head title="Post Job" />

    <GuardianLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-4 sm:p-6 lg:p-8">
            <div
                class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6 dark:border-slate-800 dark:bg-slate-900"
            >
                <div class="flex items-center justify-between gap-3">
                    <h1
                        class="text-2xl font-semibold tracking-tight text-slate-900 sm:text-3xl dark:text-slate-100"
                    >
                        Post a New Job
                    </h1>
                    <Link
                        href="/guardian/jobs"
                        class="inline-flex items-center rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700"
                        >Back</Link
                    >
                </div>
            </div>

            <JobForm
                action="/guardian/jobs"
                method="post"
                submit-label="Submit for Review"
                cancel-href="/guardian/jobs"
                :is-admin="false"
                :tuition-types="props.tuitionTypes as any"
                :categories="props.categories as any"
                :school-classes="props.schoolClasses as any"
                :countries="props.countries as any"
                :cities="props.cities as any"
                :areas="props.areas as any"
                :subjects="props.subjects as any"
                :gender-options="props.genderOptions as any"
                :day-options="props.dayOptions as any"
            />
        </div>
    </GuardianLayout>
</template>
