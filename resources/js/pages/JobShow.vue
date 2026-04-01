<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import JobShowContent from '@/components/jobs/JobShowContent.vue';
import PublicLayout from '@/layouts/PublicLayout.vue';
import TutorLayout from '@/layouts/TutorLayout.vue';

type JobDetail = {
    id: number;
    title: string;
    description: string;
    salary_amount: string | null;
    salary_currency: string | null;
    salary_negotiable: boolean;
    tuition_type_name: string | null;
    category_name: string | null;
    class_name: string | null;
    country_name: string | null;
    city_name: string | null;
    area_name: string | null;
    location: string | null;
    subject_names: string[];
    student_gender: string;
    tutor_gender: string;
    tuition_days: string[];
    days_per_week: number | null;
    tuition_time: string | null;
    tuition_duration: string | null;
    no_of_students: number | null;
    published_at: string | null;
    expires_at: string | null;
};

type ApplicationInfo = {
    id: number;
    status: string;
    expected_salary_amount: string | null;
    salary_currency: string | null;
    cancel_reason: string | null;
    created_at: string | null;
};

const props = defineProps<{
    job: JobDetail;
    meta: {
        title: string;
        description: string;
    };
    canApply: boolean;
    application: ApplicationInfo | null;
}>();

const page = usePage();
const isTutorDashboardRoute = computed(() => page.url.startsWith('/tutor/'));
const backToJobsHref = computed(() =>
    isTutorDashboardRoute.value ? '/tutor/jobs' : '/jobs',
);
const tutorBreadcrumbs = [{ title: 'Browse Jobs', href: '/tutor/jobs' }];
</script>

<template>
    <Head :title="meta.title">
        <meta name="description" :content="meta.description" />
    </Head>

    <component
        :is="isTutorDashboardRoute ? TutorLayout : PublicLayout"
        :breadcrumbs="isTutorDashboardRoute ? tutorBreadcrumbs : []"
    >
        <JobShowContent
            :job="job"
            :can-apply="canApply"
            :application="application"
            :back-to-jobs-href="backToJobsHref"
        />
    </component>
</template>
