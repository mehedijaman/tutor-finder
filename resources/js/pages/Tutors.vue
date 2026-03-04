<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { List, Filter } from 'lucide-vue-next';
import { ref, computed } from 'vue';
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
    preferred_categories: string | null;
    preferred_classes: string | null;
    preferred_subjects: string | null;
    preferred_locations: string | null;
    available_days: string | null;
    available_time: string | null;
};

type Tutor = {
    id: number;
    name: string;
    slug: string;
    email: string;
    phone: string | null;
    verified_at: string | null;
    created_at: string;
    photo_url: string | null;
    tutor_profile: TutorProfile | null;
    tutor_educations: TutorEducation[];
};

const props = defineProps<{
    tutors: {
        data: Tutor[];
        links: PaginationLink[];
    };
    total: number;
    meta: {
        title: string;
        description: string;
    };
    filters: {
        area?: string;
        gender?: string;
        min_budget?: string;
        max_budget?: string;
    };
}>();

const filtersOpen = ref(false);

const tutorList = computed(() => props.tutors.data ?? []);
const hasTutors = computed(() => tutorList.value.length > 0);

function formatPaginationLabel(label: string): string {
    return String(label ?? '')
        .replaceAll('&laquo;', '«')
        .replaceAll('&raquo;', '»')
        .replace(/<[^>]*>/g, '')
        .trim();
}
</script>

<template>
    <Head :title="meta.title">
        <meta name="description" :content="meta.description" />
    </Head>

    <PublicLayout>
        <div class="min-h-screen bg-slate-50 py-12">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-10 text-center sm:text-left">
                    <h1
                        class="text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl"
                    >
                        Find Expert Tutors
                    </h1>
                    <p class="mt-3 max-w-2xl text-lg text-slate-600">
                        Browse our verified tutors to find the perfect match for
                        your learning needs.
                    </p>
                </div>

                <!-- Top Bar -->
                <div class="mb-6 flex items-center justify-between">
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
                    </button>
                </div>

                <!-- Tutors Grid -->
                <div
                    v-if="hasTutors"
                    class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4"
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
                <div
                    v-if="props.tutors.links && props.tutors.links.length > 3"
                    class="mt-10 flex justify-center"
                >
                    <nav class="flex items-center gap-1">
                        <template
                            v-for="(link, index) in props.tutors.links"
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
        <TutorFiltersDrawer
            :open="filtersOpen"
            :filters="filters"
            @close="filtersOpen = false"
        />
    </PublicLayout>
</template>
