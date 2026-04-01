<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { List, Filter } from 'lucide-vue-next';
import { ref, computed } from 'vue';
import PublicPagination from '@/components/public/PublicPagination.vue';
import TutorCard from '@/components/tutors/TutorCard.vue';
import TutorFiltersDrawer from '@/components/tutors/TutorFiltersDrawer.vue';
import PublicLayout from '@/layouts/PublicLayout.vue';

type PaginationLink = {
    url: string | null;
    label: string;
    active: boolean;
};

type TutorEducation = {
    id: number;
    degree: string;
    institute: string;
    department: string;
    graduation_year: number | null;
    result: string | null;
    is_current: boolean;
};

type TutorProfile = {
    id: number;
    gender: string | null;
    bio: string | null;
    present_address: string | null;
    expected_salary_min: number | null;
    expected_salary_max: number | null;
    preferred_tuition_types: string[] | null;
    preferred_categories: string[] | null;
    preferred_classes: string[] | null;
    preferred_subjects: string[] | null;
    preferred_locations: string[] | null;
    available_days: string[] | null;
    available_time: string | null;
};

type Tutor = {
    id: number;
    name: string;
    slug: string;
    verified_at: string | null;
    created_at: string;
    photo_url: string | null;
    tutor_profile: TutorProfile | null;
    tutor_educations: TutorEducation[];
};

type FilterOption = {
    id: number;
    name: string;
};

type ClassFilterOption = {
    id: number;
    name: string;
    category_id: number;
};

type SubjectFilterOption = {
    id: number;
    name: string;
    class_id: number;
};

type DayFilterOption = {
    value: string;
    label: string;
};

type SimpleSelectOption = {
    value: string;
    label: string;
};

const props = defineProps<{
    tutors: {
        data: Tutor[];
        current_page: number;
        last_page: number;
        from: number | null;
        to: number | null;
        total: number;
        links: PaginationLink[];
    };
    total: number;
    meta: {
        title: string;
        description: string;
    };
    filters: {
        area?: string | null;
        gender?: string | null;
        min_budget?: string | null;
        max_budget?: string | null;
        tuition_type_id?: number | null;
        category_id?: number | null;
        class_id?: number | null;
        subject_id?: number | null;
        location_id?: number | null;
        available_day?: string | null;
        verified?: string | null;
    };
    filterOptions: {
        tuitionTypes: FilterOption[];
        categories: FilterOption[];
        classes: ClassFilterOption[];
        subjects: SubjectFilterOption[];
        locations: FilterOption[];
        days: DayFilterOption[];
        genders: SimpleSelectOption[];
        verified: SimpleSelectOption[];
    };
}>();

const filtersOpen = ref(false);

const tutorList = computed(() => props.tutors.data ?? []);
const hasTutors = computed(() => tutorList.value.length > 0);
const activeFilterCount = computed(() => {
    const values = [
        props.filters.area,
        props.filters.gender,
        props.filters.min_budget,
        props.filters.max_budget,
        props.filters.tuition_type_id,
        props.filters.category_id,
        props.filters.class_id,
        props.filters.subject_id,
        props.filters.location_id,
        props.filters.available_day,
        props.filters.verified,
    ];

    return values.filter(
        (value) =>
            value !== null &&
            value !== undefined &&
            String(value).trim() !== '',
    ).length;
});
</script>

<template>
    <Head :title="meta.title">
        <meta name="description" :content="meta.description" />
    </Head>

    <PublicLayout>
        <div
            class="min-h-screen bg-linear-to-b from-slate-50 via-white to-slate-50/50 py-12"
        >
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-10 text-center sm:text-left">
                    <h1
                        class="text-4xl font-bold tracking-tight text-slate-900 sm:text-5xl"
                    >
                        Find Expert Tutors
                    </h1>
                    <p class="mt-3 max-w-2xl text-lg text-slate-600">
                        Browse our verified tutors to find the perfect match for
                        your learning needs.
                    </p>
                </div>

                <!-- Top Bar -->
                <div
                    class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div class="flex items-center gap-2 text-sm text-slate-600">
                        <List class="h-5 w-5" />
                        <span>
                            <span class="font-semibold text-slate-900">{{
                                total
                            }}</span>
                            tutors found
                        </span>
                    </div>
                    <button
                        @click="filtersOpen = true"
                        class="flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 shadow-sm transition-colors hover:bg-slate-50"
                    >
                        <Filter class="h-4 w-4" />
                        Filters
                        <span
                            v-if="activeFilterCount > 0"
                            class="rounded-full bg-slate-900 px-1.5 py-0.5 text-[11px] font-semibold text-white"
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
                    class="rounded-2xl border border-slate-200 bg-white p-12 text-center shadow-md"
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
                            d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"
                        />
                    </svg>
                    <h3 class="mt-4 text-xl font-semibold text-slate-900">
                        No tutors found
                    </h3>
                    <p class="mt-2 text-slate-600">
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

        <!-- Filters Drawer -->
        <TutorFiltersDrawer
            :open="filtersOpen"
            :filters="filters"
            :filter-options="filterOptions"
            @close="filtersOpen = false"
        />
    </PublicLayout>
</template>
