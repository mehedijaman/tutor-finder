<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { List, Filter, Plus } from 'lucide-vue-next';
import { ref, computed } from 'vue';
import JobCard from '@/components/jobs/JobCard.vue';
import JobFiltersDrawer from '@/components/jobs/JobFiltersDrawer.vue';
import { Button } from '@/components/ui/button';
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

const page = usePage();
const user = computed(() => page.props.auth?.user);
const canCreateJob = computed(() => {
    return (
        user.value &&
        (user.value.role === 'guardian' || user.value.role === 'admin')
    );
});

const createJobUrl = computed(() => {
    if (!user.value) return '#';
    if (user.value.role === 'guardian') return '/guardian/jobs/create';
    if (user.value.role === 'admin') return '/admin/jobs/create';
    return '#';
});

const filtersOpen = ref(false);

const jobList = computed(() => props.jobs.data ?? []);
const hasJobs = computed(() => jobList.value.length > 0);

function formatPaginationLabel(label: string): string {
    return String(label ?? '')
        .replaceAll('&laquo;', '«')
        .replaceAll('&raquo;', '»')
        .replace(/<[^>]*>/g, '')
        .trim();
}
</script>

<template>
    <Head title="Tutor Job Board" />

    <PublicLayout>
        <div class="min-h-screen bg-slate-50 py-12">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div
                    class="mb-10 flex flex-col gap-6 text-center sm:flex-row sm:items-center sm:justify-between sm:text-left"
                >
                    <div>
                        <h1
                            class="text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl"
                        >
                            Tuition Job Board
                        </h1>
                        <p class="mt-3 max-w-2xl text-lg text-slate-600">
                            Explore active tuition opportunities and apply to
                            the ones that match your expertise.
                        </p>
                    </div>

                    <div v-if="canCreateJob" class="flex-shrink-0">
                        <Button as-child size="lg" class="w-full sm:w-auto">
                            <Link :href="createJobUrl">
                                <Plus class="mr-2 h-4 w-4" />
                                Post a Job
                            </Link>
                        </Button>
                    </div>
                </div>

                <!-- Top Bar -->
                <div class="mb-6 flex items-center justify-between">
                    <div class="flex items-center gap-2 text-sm text-slate-600">
                        <List class="h-5 w-5" />
                        <span>
                            <span class="font-semibold text-slate-900">{{
                                total
                            }}</span>
                            jobs found
                        </span>
                    </div>
                    <button
                        @click="filtersOpen = true"
                        class="flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 shadow-sm transition-colors hover:bg-slate-50"
                    >
                        <Filter class="h-4 w-4" />
                        Filters
                    </button>
                </div>

                <!-- Jobs Grid -->
                <div v-if="hasJobs" class="grid gap-6 sm:grid-cols-2">
                    <JobCard v-for="job in jobList" :key="job.id" :job="job" />
                </div>

                <!-- Empty State -->
                <div
                    v-else
                    class="rounded-2xl border border-slate-200 bg-white p-12 text-center"
                >
                    <svg
                        class="mx-auto h-16 w-16 text-slate-300"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="1.5"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"
                        />
                    </svg>
                    <h3 class="mt-4 text-xl font-semibold text-slate-900">
                        No jobs found
                    </h3>
                    <p class="mt-2 text-slate-600">
                        Try adjusting your filters to find more jobs
                    </p>
                </div>

                <!-- Pagination -->
                <div
                    v-if="props.jobs.links && props.jobs.links.length > 3"
                    class="mt-10 flex justify-center"
                >
                    <nav class="flex items-center gap-1">
                        <template
                            v-for="(link, index) in props.jobs.links"
                            :key="`${index}-${link.label}`"
                        >
                            <span
                                v-if="!link.url"
                                class="inline-flex h-10 items-center justify-center rounded-lg border px-4 text-sm text-slate-400"
                            >
                                {{ formatPaginationLabel(link.label) }}
                            </span>
                            <Link
                                v-else
                                :href="link.url"
                                preserve-scroll
                                class="inline-flex h-10 items-center justify-center rounded-lg border px-4 text-sm font-medium transition-all duration-200"
                                :class="[
                                    link.active
                                        ? 'border-blue-600 bg-blue-600 text-white'
                                        : 'border-slate-200 bg-white text-slate-600 hover:border-slate-300 hover:bg-slate-50',
                                ]"
                            >
                                {{ formatPaginationLabel(link.label) }}
                            </Link>
                        </template>
                    </nav>
                </div>
            </div>
        </div>

        <!-- Filters Drawer -->
        <JobFiltersDrawer
            :open="filtersOpen"
            :filters="filters"
            :category-options="categoryOptions"
            :tuition-type-options="tuitionTypeOptions"
            :subject-options="subjectOptions"
            :city-options="cityOptions"
            :sort-options="sortOptions"
            :gender-options="genderOptions"
            :days-options="daysOptions"
            @close="filtersOpen = false"
        />
    </PublicLayout>
</template>
