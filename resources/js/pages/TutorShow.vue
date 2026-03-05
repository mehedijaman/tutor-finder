<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { CheckCircle } from 'lucide-vue-next';
import { computed, ref } from 'vue';

import ReviewForm from '@/components/tutors/ReviewForm.vue';
import ReviewSection from '@/components/tutors/ReviewSection.vue';
import StarRating from '@/components/tutors/StarRating.vue';
import PublicLayout from '@/layouts/PublicLayout.vue';

type TutorEducation = {
    id: number;
    degree: string;
    institute: string;
    department: string;
    graduation_year: number | null;
    result: string;
    is_current: boolean;
};

type TutorProfile = {
    id: number;
    gender: string | null;
    date_of_birth: string | null;
    bio: string | null;
    present_address: string | null;
    permanent_address: string | null;
    expected_salary_min: number | null;
    expected_salary_max: number | null;
    preferred_categories: string | null;
    preferred_classes: string | null;
    preferred_subjects: string | null;
    preferred_locations: string | null;
    available_days: string | null;
    available_time: string | null;
};

type Guardian = {
    id: number;
    name: string;
    photo_url: string | null;
};

type Review = {
    id: number;
    rating: number;
    comment: string | null;
    created_at: string;
    guardian: Guardian;
};

type PaginatedReviews = {
    data: Review[];
    current_page: number;
    last_page: number;
    next_page_url: string | null;
    prev_page_url: string | null;
    total: number;
};

type ReviewableAssignment = {
    assignment_id: number;
    job_title: string;
};

type Tutor = {
    id: number;
    name: string;
    email: string;
    phone: string | null;
    verified_at: string | null;
    created_at: string;
    tutor_profile: TutorProfile | null;
    tutor_educations: TutorEducation[];
    tutor_reviews_count: number;
    tutor_reviews_avg_rating: number | null;
};

const props = defineProps<{
    tutor: Tutor;
    reviews: PaginatedReviews;
    ratingDistribution: Record<number, number>;
    canReview: boolean;
    reviewableAssignments: ReviewableAssignment[];
    meta: {
        title: string;
        description: string;
    };
}>();

const isVerified = computed(() => !!props.tutor.verified_at);
const averageRating = computed(
    () => Number(props.tutor.tutor_reviews_avg_rating) || 0,
);
const totalReviews = computed(() => props.tutor.tutor_reviews_count ?? 0);

const page = usePage();
const successMessage = computed(
    () => (page.props.flash as { success?: string })?.success,
);

type EditReview = {
    id: number;
    rating: number;
    comment: string | null;
};

const editingReview = ref<EditReview | null>(null);

function handleEditReview(review: {
    id: number;
    rating: number;
    comment: string | null;
}): void {
    editingReview.value = {
        id: review.id,
        rating: review.rating,
        comment: review.comment,
    };
}

function cancelEdit(): void {
    editingReview.value = null;
}

const showReviewForm = computed(
    () => props.canReview || editingReview.value !== null,
);

function formatDate(dateStr: string | null): string {
    if (!dateStr) return '';
    const date = new Date(dateStr);
    return date.toLocaleDateString('en-GB', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    });
}

function parseJson(jsonStr: string | null): string[] {
    if (!jsonStr) return [];
    try {
        return JSON.parse(jsonStr) || [];
    } catch {
        return [];
    }
}

function getSalaryRange(): string {
    const min = props.tutor.tutor_profile?.expected_salary_min;
    const max = props.tutor.tutor_profile?.expected_salary_max;

    if (min && max) {
        return `৳ ${min.toLocaleString()} - ৳ ${max.toLocaleString()}`;
    }
    if (min) {
        return `৳ ${min.toLocaleString()}`;
    }
    if (max) {
        return `Up to ৳ ${max.toLocaleString()}`;
    }
    return 'Negotiable';
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
                <div class="mb-8">
                    <Link
                        href="/tutors"
                        class="mb-4 inline-flex items-center text-sm font-medium text-blue-600 hover:text-blue-500"
                    >
                        <svg
                            class="mr-1 h-4 w-4"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="2"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M10 19l-7-7m0 0l7-7m-7 7h18"
                            />
                        </svg>
                        Back to Tutors
                    </Link>
                </div>

                <!-- Main Grid -->
                <div class="grid gap-10 lg:grid-cols-12">
                    <!-- Left Column - Profile Summary -->
                    <aside class="lg:col-span-4">
                        <div
                            class="sticky top-24 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"
                        >
                            <!-- Profile Image -->
                            <div class="flex flex-col items-center">
                                <div
                                    class="flex h-32 w-32 items-center justify-center overflow-hidden rounded-2xl border-2 border-slate-100 bg-blue-100 text-4xl font-bold text-blue-600"
                                >
                                    {{ tutor.name.charAt(0).toUpperCase() }}
                                </div>

                                <!-- Verified Badge -->
                                <div
                                    v-if="isVerified"
                                    class="mt-3 inline-flex items-center rounded-full bg-emerald-100 px-3 py-1 text-sm font-semibold text-emerald-700"
                                >
                                    <svg
                                        class="mr-1 h-4 w-4"
                                        fill="currentColor"
                                        viewBox="0 0 20 20"
                                    >
                                        <path
                                            fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                            clip-rule="evenodd"
                                        />
                                    </svg>
                                    Verified Tutor
                                </div>
                                <div
                                    v-else
                                    class="mt-3 inline-flex items-center rounded-full bg-amber-100 px-3 py-1 text-sm font-semibold text-amber-700"
                                >
                                    Unverified
                                </div>

                                <h1
                                    class="mt-4 text-center text-2xl font-bold text-slate-900"
                                >
                                    {{ tutor.name }}
                                </h1>
                                <p class="mt-1 text-sm text-slate-500">
                                    Tutor ID: #{{ tutor.id }}
                                </p>
                            </div>

                            <!-- Rating Summary -->
                            <div
                                v-if="totalReviews > 0"
                                class="mt-4 flex flex-col items-center gap-1"
                            >
                                <StarRating
                                    :rating="averageRating"
                                    :review-count="totalReviews"
                                    size="md"
                                    show-value
                                />
                            </div>

                            <!-- Stats -->
                            <div class="mt-6 border-t border-slate-100 pt-6">
                                <div class="text-center">
                                    <p
                                        class="text-3xl font-bold text-slate-900"
                                    >
                                        {{ getSalaryRange() }}
                                    </p>
                                    <p class="mt-1 text-sm text-slate-500">
                                        Expected Salary
                                    </p>
                                </div>
                            </div>

                            <!-- CTA Buttons -->
                            <div class="mt-6 space-y-3">
                                <button
                                    class="h-11 w-full rounded-xl bg-blue-600 px-4 text-sm font-semibold text-white transition-all duration-200 hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:outline-none active:scale-[0.98]"
                                >
                                    Hire This Tutor
                                </button>
                                <button
                                    class="h-11 w-full rounded-xl border border-slate-300 px-4 text-sm font-medium text-slate-700 transition-all duration-200 hover:bg-slate-50 focus:ring-2 focus:ring-slate-500 focus:ring-offset-2 focus:outline-none active:scale-[0.98]"
                                >
                                    Save Profile
                                </button>
                            </div>

                            <!-- Quick Info -->
                            <div
                                class="mt-6 space-y-3 border-t border-slate-100 pt-6"
                            >
                                <div class="flex items-center gap-3 text-sm">
                                    <svg
                                        class="h-5 w-5 text-slate-400"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"
                                        />
                                    </svg>
                                    <span class="text-slate-600">{{
                                        tutor.phone || 'Not provided'
                                    }}</span>
                                </div>
                                <div class="flex items-center gap-3 text-sm">
                                    <svg
                                        class="h-5 w-5 text-slate-400"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
                                        />
                                    </svg>
                                    <span class="text-slate-600">{{
                                        tutor.email
                                    }}</span>
                                </div>
                                <div
                                    v-if="tutor.tutor_profile?.present_address"
                                    class="flex items-center gap-3 text-sm"
                                >
                                    <svg
                                        class="h-5 w-5 text-slate-400"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"
                                        />
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"
                                        />
                                    </svg>
                                    <span class="text-slate-600">{{
                                        tutor.tutor_profile.present_address
                                    }}</span>
                                </div>
                            </div>
                        </div>
                    </aside>

                    <!-- Right Column - Details -->
                    <div class="space-y-6 lg:col-span-8">
                        <!-- About Me -->
                        <section
                            class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"
                        >
                            <h2
                                class="mb-4 text-lg font-semibold text-slate-900"
                            >
                                About Me
                            </h2>
                            <p
                                v-if="tutor.tutor_profile?.bio"
                                class="leading-relaxed text-slate-600"
                            >
                                {{ tutor.tutor_profile.bio }}
                            </p>
                            <p v-else class="text-slate-400 italic">
                                No bio provided
                            </p>
                        </section>

                        <!-- Academic Information -->
                        <section
                            class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"
                        >
                            <h2
                                class="mb-4 text-lg font-semibold text-slate-900"
                            >
                                Academic Information
                            </h2>
                            <div
                                v-if="
                                    tutor.tutor_educations &&
                                    tutor.tutor_educations.length > 0
                                "
                                class="space-y-4"
                            >
                                <div
                                    v-for="edu in tutor.tutor_educations"
                                    :key="edu.id"
                                    class="rounded-xl border border-slate-100 bg-slate-50 p-4"
                                >
                                    <div
                                        class="flex items-start justify-between"
                                    >
                                        <div>
                                            <p
                                                class="font-semibold text-slate-900"
                                            >
                                                {{ edu.degree }}
                                            </p>
                                            <p class="text-sm text-slate-600">
                                                {{ edu.institute }}
                                            </p>
                                            <p class="text-sm text-slate-500">
                                                {{ edu.department }}
                                            </p>
                                        </div>
                                        <div class="text-right">
                                            <p
                                                class="text-sm font-medium text-slate-700"
                                            >
                                                {{ edu.result }}
                                            </p>
                                            <p
                                                v-if="edu.graduation_year"
                                                class="text-xs text-slate-500"
                                            >
                                                {{ edu.graduation_year }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <p v-else class="text-slate-400 italic">
                                No education details provided
                            </p>
                        </section>

                        <!-- Teaching Details -->
                        <section
                            class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"
                        >
                            <h2
                                class="mb-4 text-lg font-semibold text-slate-900"
                            >
                                Teaching Details
                            </h2>
                            <div class="grid gap-4 md:grid-cols-2">
                                <div
                                    class="rounded-xl border border-slate-100 p-4"
                                >
                                    <p class="text-sm text-slate-500">Gender</p>
                                    <p class="mt-1 font-medium text-slate-800">
                                        {{
                                            tutor.tutor_profile?.gender
                                                ? tutor.tutor_profile.gender ===
                                                  'male'
                                                    ? 'Male'
                                                    : tutor.tutor_profile
                                                            .gender === 'female'
                                                      ? 'Female'
                                                      : 'Other'
                                                : 'Not specified'
                                        }}
                                    </p>
                                </div>
                                <div
                                    class="rounded-xl border border-slate-100 p-4"
                                >
                                    <p class="text-sm text-slate-500">
                                        Available Time
                                    </p>
                                    <p class="mt-1 font-medium text-slate-800">
                                        {{
                                            tutor.tutor_profile
                                                ?.available_time || 'Flexible'
                                        }}
                                    </p>
                                </div>
                                <div
                                    class="rounded-xl border border-slate-100 p-4"
                                >
                                    <p class="text-sm text-slate-500">
                                        Expected Salary
                                    </p>
                                    <p class="mt-1 font-medium text-slate-800">
                                        {{ getSalaryRange() }}
                                    </p>
                                </div>
                                <div
                                    class="rounded-xl border border-slate-100 p-4"
                                >
                                    <p class="text-sm text-slate-500">
                                        Member Since
                                    </p>
                                    <p class="mt-1 font-medium text-slate-800">
                                        {{ formatDate(tutor.created_at) }}
                                    </p>
                                </div>
                            </div>
                        </section>

                        <!-- Preferred Subjects -->
                        <section
                            v-if="tutor.tutor_profile?.preferred_subjects"
                            class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"
                        >
                            <h2
                                class="mb-4 text-lg font-semibold text-slate-900"
                            >
                                Preferred Subjects
                            </h2>
                            <div class="flex flex-wrap gap-2">
                                <span
                                    v-for="subject in parseJson(
                                        tutor.tutor_profile.preferred_subjects,
                                    )"
                                    :key="subject"
                                    class="inline-flex items-center rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-sm font-medium text-emerald-700"
                                >
                                    {{ subject }}
                                </span>
                            </div>
                        </section>

                        <!-- Preferred Categories -->
                        <section
                            v-if="tutor.tutor_profile?.preferred_categories"
                            class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"
                        >
                            <h2
                                class="mb-4 text-lg font-semibold text-slate-900"
                            >
                                Preferred Categories
                            </h2>
                            <div class="flex flex-wrap gap-2">
                                <span
                                    v-for="category in parseJson(
                                        tutor.tutor_profile
                                            .preferred_categories,
                                    )"
                                    :key="category"
                                    class="inline-flex items-center rounded-full border border-blue-200 bg-blue-50 px-3 py-1.5 text-sm font-medium text-blue-700"
                                >
                                    {{ category }}
                                </span>
                            </div>
                        </section>

                        <!-- Preferred Classes -->
                        <section
                            v-if="tutor.tutor_profile?.preferred_classes"
                            class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"
                        >
                            <h2
                                class="mb-4 text-lg font-semibold text-slate-900"
                            >
                                Preferred Classes
                            </h2>
                            <div class="flex flex-wrap gap-2">
                                <span
                                    v-for="cls in parseJson(
                                        tutor.tutor_profile.preferred_classes,
                                    )"
                                    :key="cls"
                                    class="inline-flex items-center rounded-full border border-purple-200 bg-purple-50 px-3 py-1.5 text-sm font-medium text-purple-700"
                                >
                                    {{ cls }}
                                </span>
                            </div>
                        </section>

                        <!-- Preferred Locations -->
                        <section
                            v-if="tutor.tutor_profile?.preferred_locations"
                            class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"
                        >
                            <h2
                                class="mb-4 text-lg font-semibold text-slate-900"
                            >
                                Preferred Locations
                            </h2>
                            <div class="flex flex-wrap gap-2">
                                <span
                                    v-for="location in parseJson(
                                        tutor.tutor_profile.preferred_locations,
                                    )"
                                    :key="location"
                                    class="inline-flex items-center rounded-full border border-slate-200 bg-slate-50 px-3 py-1.5 text-sm font-medium text-slate-700"
                                >
                                    <svg
                                        class="mr-1 h-4 w-4 text-slate-400"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"
                                        />
                                    </svg>
                                    {{ location }}
                                </span>
                            </div>
                        </section>

                        <!-- Reviews & Ratings -->
                        <ReviewSection
                            :reviews="reviews"
                            :average-rating="averageRating"
                            :total-reviews="totalReviews"
                            :rating-distribution="ratingDistribution"
                            @edit="handleEditReview"
                        />

                        <!-- Flash Message -->
                        <div
                            v-if="successMessage"
                            class="flex items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700 dark:border-emerald-800 dark:bg-emerald-950 dark:text-emerald-300"
                        >
                            <CheckCircle class="h-4.5 w-4.5 flex-shrink-0" />
                            {{ successMessage }}
                        </div>

                        <!-- Write Review Form -->
                        <ReviewForm
                            v-if="showReviewForm"
                            :tutor-id="tutor.id"
                            :assignments="reviewableAssignments"
                            :edit-review="editingReview"
                            @cancel-edit="cancelEdit"
                        />
                    </div>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>
