<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import JobBoardContent from '@/components/jobs/JobBoardContent.vue';
import PublicLayout from '@/layouts/PublicLayout.vue';

type PaginationLink = {
    url: string | null;
    label: string;
    active: boolean;
};

type JobItem = {
    id: number;
    title: string;
    slug: string;
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
    subject_names: string[];
    student_gender: string;
    tutor_gender: string;
    days_per_week: number | null;
    tuition_time: string | null;
    published_at: string | null;
    expires_at: string | null;
};

const props = defineProps<{
    jobs: {
        data: JobItem[];
        current_page: number;
        last_page: number;
        from: number | null;
        to: number | null;
        total: number;
        links: PaginationLink[];
    };
    total: number;
    filters: {
        q: string;
        category: string;
        tuition_type: string;
        subject_id: number | null;
        city_id: number | null;
        tutor_gender: string;
        days_per_week: number | null;
        min_salary: number | null;
        max_salary: number | null;
        sort: string;
    };
    categoryOptions: Array<{ id: number; name: string; slug: string }>;
    tuitionTypeOptions: Array<{ id: number; name: string; slug: string }>;
    subjectOptions: Array<{ id: number; name: string }>;
    cityOptions: Array<{ id: number; name: string }>;
    sortOptions: Array<{ value: string; label: string }>;
    genderOptions: Array<{ value: string; label: string }>;
    daysOptions: Array<{ value: string; label: string }>;
}>();
</script>

<template>
    <Head title="Job Board" />

    <PublicLayout>
        <JobBoardContent
            v-bind="props"
            page-title="Job Board"
            page-description="Explore active tuition opportunities and apply to the ones that match your expertise."
        />
    </PublicLayout>
</template>
