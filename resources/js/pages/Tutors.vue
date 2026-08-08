<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Filter, List } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import PublicPagination from '@/components/public/PublicPagination.vue';
import TutorCard from '@/components/tutors/TutorCard.vue';
import TutorFiltersDrawer from '@/components/tutors/TutorFiltersDrawer.vue';
import PublicLayout from '@/layouts/PublicLayout.vue';

type PaginationLink = {
    url: string | null;
    label: string;
    active: boolean;
};

type TutorItem = {
    id: number;
    user_id: number;
    name: string;
    avatar: string | null;
    gender: string | null;
    bio: string | null;
    present_address: string | null;
    expected_salary_min: string | null;
    expected_salary_max: string | null;
    preferred_locations: Array<{ id: number; name: string }>;
    preferred_subjects: Array<{ id: number; name: string }>;
    preferred_classes: Array<{ id: number; name: string }>;
    preferred_categories: Array<{ id: number; name: string }>;
    preferred_tuition_types: Array<{ id: number; name: string }>;
    educations: Array<{
        degree: string | null;
        institute: string | null;
        department: string | null;
        graduation_year: number | null;
    }>;
    verification_status: string;
    is_verified: boolean;
};

const props = defineProps<{
    tutors: {
        data: TutorItem[];
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
        gender: string;
        location_id: number | null;
        subject_id: number | null;
        class_id: number | null;
        category_id: number | null;
        tuition_type_id: number | null;
        degree: string;
        institute: string;
        min_salary: number | null;
        max_salary: number | null;
        verification: string;
        sort: string;
    };
    locationOptions: Array<{ id: number; name: string }>;
    subjectOptions: Array<{ id: number; name: string }>;
    classOptions: Array<{ id: number; name: string }>;
    categoryOptions: Array<{ id: number; name: string }>;
    tuitionTypeOptions: Array<{ id: number; name: string }>;
    genderOptions: Array<{ value: string; label: string }>;
    sortOptions: Array<{ value: string; label: string }>;
    verificationOptions: Array<{ value: string; label: string }>;
    meta: {
        title: string;
        description: string;
    };
}>();

const filtersOpen = ref(false);
const tutorList = computed(() => props.tutors.data ?? []);
const hasTutors = computed(() => tutorList.value.length > 0);

const activeFilterCount = computed(() => {
    let count = 0;
    const f = props.filters;
    if (f.q) count++;
    if (f.gender) count++;
    if (f.location_id) count++;
    if (f.subject_id) count++;
    if (f.class_id) count++;
    if (f.category_id) count++;
    if (f.tuition_type_id) count++;
    if (f.degree) count++;
    if (f.institute) count++;
    if (f.min_salary || f.max_salary) count++;
    if (f.verification) count++;
    return count;
});
</script>

<template>
    <Head>
        <title>{{ meta.title }}</title>
        <meta name="description" :content="meta.description" />
    </Head>

    <PublicLayout>
        <div
            class="min-h-screen bg-linear-to-b from-slate-50 via-white to-slate-50/50 py-12 dark:from-slate-950 dark:via-slate-900 dark:to-slate-950"
        >
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-10 text-center sm:text-left">
                    <h1
                        class="text-4xl font-bold tracking-tight text-slate-900 sm:text-5xl dark:text-slate-100"
                    >
                        Find Expert Tutors
                    </h1>
                    <p
                        class="mt-3 max-w-2xl text-lg text-slate-600 dark:text-slate-400"
                    >
                        Browse our verified tutors to find the perfect match for
                        your learning needs.
                    </p>
                </div>

                <!-- Top Bar -->
                <div
                    class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div
                        class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400"
                    >
                        <List class="h-5 w-5" />
                        <span>
                            <span
                                class="font-semibold text-slate-900 dark:text-slate-100"
                                >{{ total }}</span
                            >
                            tutors found
                        </span>
                    </div>
                    <button
                        @click="filtersOpen = true"
                        class="flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 shadow-sm transition-colors hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
                    >
                        <Filter class="h-4 w-4" />
                        Filters
                        <span
                            v-if="activeFilterCount > 0"
                            class="rounded-full bg-slate-900 px-1.5 py-0.5 text-[11px] font-semibold text-white dark:bg-slate-100 dark:text-slate-900"
                        >
                            {{ activeFilterCount }}
                        </span>
                    </button>
                </div>

                <!-- Tutors Grid -->
                <div
                    v-if="hasTutors"
                    class="grid gap-6 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4"
                >
                    <TutorCard
                        v-for="tutor in tutorList"
                        :key="tutor.id"
                        :tutor="tutor"
                    />
                </div>

                <!-- Empty State -->
                <div
                    v-else
                    class="rounded-2xl border border-slate-200 bg-white p-12 text-center shadow-md dark:border-slate-800 dark:bg-slate-900"
                >
                    <svg
                        class="mx-auto h-16 w-16 text-slate-300 dark:text-slate-600"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="1.5"
                            d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"
                        />
                    </svg>
                    <h3
                        class="mt-4 text-xl font-semibold text-slate-900 dark:text-slate-100"
                    >
                        No tutors found
                    </h3>
                    <p class="mt-2 text-slate-600 dark:text-slate-400">
                        Try adjusting your filters to find more tutors
                    </p>
                </div>

                <!-- Pagination -->
                <PublicPagination
                    class="mt-10"
                    :links="props.tutors.links"
                    :current-page="props.tutors.current_page"
                    :last-page="props.tutors.last_page"
                    :from="props.tutors.from"
                    :to="props.tutors.to"
                    :total="props.tutors.total"
                />
            </div>
        </div>

        <TutorFiltersDrawer
            :open="filtersOpen"
            :filters="filters"
            :location-options="locationOptions"
            :subject-options="subjectOptions"
            :class-options="classOptions"
            :category-options="categoryOptions"
            :tuition-type-options="tuitionTypeOptions"
            :gender-options="genderOptions"
            :sort-options="sortOptions"
            :verification-options="verificationOptions"
            @close="filtersOpen = false"
        />
    </PublicLayout>
</template>
