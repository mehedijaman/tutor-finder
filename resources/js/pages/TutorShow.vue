<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import {
    ArrowLeft,
    BadgeCheck,
    BookOpen,
    Calendar,
    CalendarDays,
    CheckCircle,
    Clock,
    GraduationCap,
    Mail,
    MapPin,
    Phone,
    ShieldAlert,
    Sparkles,
    Tag,
    User,
    Wallet,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';

import ReviewForm from '@/components/tutors/ReviewForm.vue';
import ReviewSection from '@/components/tutors/ReviewSection.vue';
import StarRating from '@/components/tutors/StarRating.vue';
import { Badge } from '@/components/ui/badge';
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
    preferred_tuition_types: Array<string | number> | null;
    preferred_categories: Array<string | number> | null;
    preferred_classes: Array<string | number> | null;
    preferred_subjects: Array<string | number> | null;
    preferred_locations: Array<string | number> | null;
    available_days: Array<string | number> | null;
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
    photo_url: string | null;
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

function normalizeList(value: unknown): string[] {
    if (Array.isArray(value)) {
        return value
            .map((item) => String(item).trim())
            .filter((item) => item !== '');
    }

    if (typeof value !== 'string' || value.trim() === '') {
        return [];
    }

    try {
        const parsed = JSON.parse(value);

        if (!Array.isArray(parsed)) {
            return [];
        }

        return parsed
            .map((item) => String(item).trim())
            .filter((item) => item !== '');
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

const dayLabelMap: Record<string, string> = {
    sat: 'Saturday',
    sun: 'Sunday',
    mon: 'Monday',
    tue: 'Tuesday',
    wed: 'Wednesday',
    thu: 'Thursday',
    fri: 'Friday',
};

const dayShortMap: Record<string, string> = {
    sat: 'Sat',
    sun: 'Sun',
    mon: 'Mon',
    tue: 'Tue',
    wed: 'Wed',
    thu: 'Thu',
    fri: 'Fri',
};

const formattedAvailableDays = computed(() => {
    const days = normalizeList(props.tutor.tutor_profile?.available_days);

    if (days.length === 0) {
        return [];
    }

    return days.map((day) => {
        const normalized = day.toLowerCase().slice(0, 3);

        return {
            key: day,
            short: dayShortMap[normalized] ?? day,
            full: dayLabelMap[normalized] ?? day,
        };
    });
});

const preferredSubjects = computed(() =>
    normalizeList(props.tutor.tutor_profile?.preferred_subjects),
);
const preferredCategories = computed(() =>
    normalizeList(props.tutor.tutor_profile?.preferred_categories),
);
const preferredClasses = computed(() =>
    normalizeList(props.tutor.tutor_profile?.preferred_classes),
);
const preferredLocations = computed(() =>
    normalizeList(props.tutor.tutor_profile?.preferred_locations),
);

const hasPreferences = computed(
    () =>
        preferredSubjects.value.length > 0 ||
        preferredCategories.value.length > 0 ||
        preferredClasses.value.length > 0,
);

const { tutor } = props;
</script>

<template>
    <Head :title="meta.title">
        <meta name="description" :content="meta.description" />
    </Head>

    <PublicLayout>
        <div
            class="min-h-screen bg-gradient-to-b from-slate-50 via-white to-slate-50/50"
        >
            <!-- Hero Banner -->
            <div
                class="relative overflow-hidden bg-gradient-to-br from-blue-600 via-blue-700 to-indigo-800"
            >
                <!-- Decorative elements -->
                <div
                    class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHZpZXdCb3g9IjAgMCA2MCA2MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48ZyBmaWxsPSJub25lIiBmaWxsLXJ1bGU9ImV2ZW5vZGQiPjxnIGZpbGw9IiNmZmYiIGZpbGwtb3BhY2l0eT0iMC4wNSI+PHBhdGggZD0iTTM2IDM0djItSDJ2LTJoMzR6bTAtMzBWNkgyVjRoMzR6TTIgNTBoMzR2Mkgydi0yeiIvPjwvZz48L2c+PC9zdmc+')] opacity-30"
                />
                <div
                    class="absolute -top-20 -left-20 h-72 w-72 rounded-full bg-white/5 blur-3xl"
                />
                <div
                    class="absolute -right-10 -bottom-10 h-56 w-56 rounded-full bg-indigo-400/10 blur-3xl"
                />

                <div
                    class="relative mx-auto max-w-7xl px-4 pt-8 pb-24 sm:px-6 lg:px-8"
                >
                    <Link
                        href="/tutors"
                        class="group inline-flex items-center gap-1.5 rounded-full bg-white/10 px-4 py-2 text-sm font-medium text-white/90 backdrop-blur-sm transition-all hover:bg-white/20 hover:text-white"
                    >
                        <ArrowLeft
                            class="h-4 w-4 transition-transform group-hover:-translate-x-0.5"
                        />
                        Back to Tutors
                    </Link>
                </div>
            </div>

            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Profile Card (overlapping hero) -->
                <div class="-mt-20 mb-8">
                    <div
                        class="rounded-3xl border border-white/80 bg-white/95 p-5 shadow-2xl shadow-slate-300/30 backdrop-blur-xl sm:p-7 lg:p-8"
                    >
                        <div
                            class="flex flex-col items-center gap-5 sm:flex-row sm:items-start sm:gap-7 lg:gap-8"
                        >
                            <!-- Avatar -->
                            <div class="relative shrink-0">
                                <div
                                    class="flex h-28 w-28 items-center justify-center rounded-2xl bg-gradient-to-br from-blue-500 via-blue-600 to-indigo-600 text-4xl font-bold text-white shadow-xl ring-[5px] shadow-blue-600/30 ring-white sm:h-36 sm:w-36 sm:text-5xl"
                                >
                                    <img
                                        v-if="tutor.photo_url"
                                        :src="tutor.photo_url"
                                        :alt="tutor.name"
                                        class="h-full w-full rounded-2xl object-cover"
                                    />
                                    <span v-else>{{
                                        tutor.name.charAt(0).toUpperCase()
                                    }}</span>
                                </div>
                                <div
                                    v-if="isVerified"
                                    class="absolute -right-1.5 -bottom-1.5 flex h-9 w-9 items-center justify-center rounded-full bg-gradient-to-br from-emerald-400 to-emerald-600 text-white shadow-lg ring-[3px] shadow-emerald-500/30 ring-white"
                                    title="Verified Tutor"
                                >
                                    <BadgeCheck class="h-5 w-5" />
                                </div>
                            </div>

                            <!-- Info -->
                            <div
                                class="flex min-w-0 flex-1 flex-col items-center text-center sm:items-start sm:text-left"
                            >
                                <div
                                    class="flex flex-col items-center gap-2.5 sm:flex-row sm:items-center"
                                >
                                    <h1
                                        class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl"
                                    >
                                        {{ tutor.name }}
                                    </h1>
                                    <Badge
                                        v-if="isVerified"
                                        class="border-emerald-200 bg-emerald-50 px-2.5 text-emerald-700"
                                    >
                                        <BadgeCheck class="mr-1 h-3.5 w-3.5" />
                                        Verified
                                    </Badge>
                                    <Badge
                                        v-else
                                        variant="outline"
                                        class="border-amber-200 bg-amber-50 px-2.5 text-amber-700"
                                    >
                                        <ShieldAlert class="mr-1 h-3.5 w-3.5" />
                                        Unverified
                                    </Badge>
                                </div>

                                <p class="mt-1.5 text-sm text-slate-400">
                                    Tutor ID: #{{ tutor.id }}
                                </p>

                                <!-- Rating -->
                                <div
                                    v-if="totalReviews > 0"
                                    class="mt-3 flex items-center gap-3"
                                >
                                    <StarRating
                                        :rating="averageRating"
                                        :review-count="totalReviews"
                                        size="md"
                                        show-value
                                    />
                                </div>

                                <!-- Contact Info Chips -->
                                <div
                                    class="mt-4 flex flex-wrap items-center justify-center gap-2 sm:justify-start"
                                >
                                    <span
                                        v-if="tutor.phone"
                                        class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-3 py-1.5 text-xs font-medium text-slate-600 transition-colors hover:bg-slate-200"
                                    >
                                        <Phone
                                            class="h-3.5 w-3.5 text-slate-400"
                                        />
                                        {{ tutor.phone }}
                                    </span>
                                    <span
                                        class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-3 py-1.5 text-xs font-medium text-slate-600 transition-colors hover:bg-slate-200"
                                    >
                                        <Mail
                                            class="h-3.5 w-3.5 text-slate-400"
                                        />
                                        {{ tutor.email }}
                                    </span>
                                    <span
                                        v-if="
                                            tutor.tutor_profile?.present_address
                                        "
                                        class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-3 py-1.5 text-xs font-medium text-slate-600 transition-colors hover:bg-slate-200"
                                    >
                                        <MapPin
                                            class="h-3.5 w-3.5 text-slate-400"
                                        />
                                        {{
                                            tutor.tutor_profile.present_address
                                        }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Main Content Grid -->
                <div class="grid gap-6 pb-20 lg:grid-cols-12 lg:gap-8">
                    <!-- Left Column - Sidebar -->
                    <aside
                        class="order-2 lg:order-1 lg:col-span-4 xl:col-span-3"
                    >
                        <div class="space-y-5 lg:sticky lg:top-24">
                            <!-- Salary Card -->
                            <div
                                class="overflow-hidden rounded-2xl border border-slate-200/60 bg-white shadow-sm"
                            >
                                <div
                                    class="bg-gradient-to-r from-blue-50 to-indigo-50 px-5 py-4"
                                >
                                    <div
                                        class="flex items-center gap-2 text-sm font-medium text-blue-700"
                                    >
                                        <Wallet class="h-4 w-4" />
                                        Expected Salary
                                    </div>
                                    <p
                                        class="mt-1.5 text-2xl font-bold tracking-tight text-slate-900"
                                    >
                                        {{ getSalaryRange() }}
                                    </p>
                                </div>
                            </div>

                            <!-- Quick Details Card -->
                            <div
                                class="rounded-2xl border border-slate-200/60 bg-white p-5 shadow-sm"
                            >
                                <h3
                                    class="mb-4 flex items-center gap-2 text-sm font-semibold text-slate-900"
                                >
                                    <Sparkles class="h-4 w-4 text-amber-500" />
                                    Quick Details
                                </h3>
                                <div class="space-y-4">
                                    <div
                                        class="flex items-center gap-3 text-sm"
                                    >
                                        <div
                                            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-600"
                                        >
                                            <User class="h-4 w-4" />
                                        </div>
                                        <div>
                                            <p
                                                class="text-xs font-medium text-slate-400"
                                            >
                                                Gender
                                            </p>
                                            <p
                                                class="font-medium text-slate-800"
                                            >
                                                {{
                                                    tutor.tutor_profile?.gender
                                                        ? tutor.tutor_profile
                                                              .gender === 'male'
                                                            ? 'Male'
                                                            : tutor
                                                                    .tutor_profile
                                                                    .gender ===
                                                                'female'
                                                              ? 'Female'
                                                              : 'Other'
                                                        : 'Not specified'
                                                }}
                                            </p>
                                        </div>
                                    </div>

                                    <div
                                        class="flex items-center gap-3 text-sm"
                                    >
                                        <div
                                            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-amber-50 text-amber-600"
                                        >
                                            <Clock class="h-4 w-4" />
                                        </div>
                                        <div>
                                            <p
                                                class="text-xs font-medium text-slate-400"
                                            >
                                                Available Time
                                            </p>
                                            <p
                                                class="font-medium text-slate-800"
                                            >
                                                {{
                                                    tutor.tutor_profile
                                                        ?.available_time ||
                                                    'Flexible'
                                                }}
                                            </p>
                                        </div>
                                    </div>

                                    <div
                                        class="flex items-center gap-3 text-sm"
                                    >
                                        <div
                                            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600"
                                        >
                                            <Calendar class="h-4 w-4" />
                                        </div>
                                        <div>
                                            <p
                                                class="text-xs font-medium text-slate-400"
                                            >
                                                Member Since
                                            </p>
                                            <p
                                                class="font-medium text-slate-800"
                                            >
                                                {{
                                                    formatDate(tutor.created_at)
                                                }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Available Days Card -->
                            <div
                                v-if="formattedAvailableDays.length > 0"
                                class="rounded-2xl border border-slate-200/60 bg-white p-5 shadow-sm"
                            >
                                <h3
                                    class="mb-3.5 flex items-center gap-2 text-sm font-semibold text-slate-900"
                                >
                                    <CalendarDays
                                        class="h-4 w-4 text-purple-500"
                                    />
                                    Available Days
                                </h3>
                                <div
                                    class="grid grid-cols-4 gap-1.5 sm:grid-cols-3 lg:grid-cols-4"
                                >
                                    <div
                                        v-for="day in formattedAvailableDays"
                                        :key="day.key"
                                        class="flex flex-col items-center rounded-xl border border-purple-100 bg-gradient-to-b from-purple-50 to-white px-2 py-2.5 text-center"
                                        :title="day.full"
                                    >
                                        <span
                                            class="text-xs font-bold text-purple-700"
                                        >
                                            {{ day.short }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Preferred Locations Card -->
                            <div
                                v-if="preferredLocations.length > 0"
                                class="rounded-2xl border border-slate-200/60 bg-white p-5 shadow-sm"
                            >
                                <h3
                                    class="mb-3 flex items-center gap-2 text-sm font-semibold text-slate-900"
                                >
                                    <MapPin class="h-4 w-4 text-rose-400" />
                                    Preferred Locations
                                </h3>
                                <div class="flex flex-wrap gap-1.5">
                                    <Badge
                                        v-for="location in preferredLocations"
                                        :key="location"
                                        variant="outline"
                                        class="rounded-lg border-slate-200 bg-slate-50 font-normal text-slate-600"
                                    >
                                        <MapPin
                                            class="mr-1 h-3 w-3 text-slate-400"
                                        />
                                        {{ location }}
                                    </Badge>
                                </div>
                            </div>
                        </div>
                    </aside>

                    <!-- Right Column - Main Content -->
                    <div
                        class="order-1 space-y-6 lg:order-2 lg:col-span-8 xl:col-span-9"
                    >
                        <!-- About Me -->
                        <section
                            class="rounded-2xl border border-slate-200/60 bg-white p-5 shadow-sm sm:p-6"
                        >
                            <h2
                                class="flex items-center gap-2.5 text-lg font-semibold text-slate-900"
                            >
                                <div
                                    class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-blue-50 to-blue-100 text-blue-600"
                                >
                                    <User class="h-4.5 w-4.5" />
                                </div>
                                About Me
                            </h2>
                            <div class="mt-4 pl-0 sm:pl-[46px]">
                                <p
                                    v-if="tutor.tutor_profile?.bio"
                                    class="text-[15px] leading-relaxed whitespace-pre-line text-slate-600"
                                >
                                    {{ tutor.tutor_profile.bio }}
                                </p>
                                <p v-else class="text-sm text-slate-400 italic">
                                    No bio provided
                                </p>
                            </div>
                        </section>

                        <!-- Academic Information -->
                        <section
                            class="rounded-2xl border border-slate-200/60 bg-white p-5 shadow-sm sm:p-6"
                        >
                            <h2
                                class="flex items-center gap-2.5 text-lg font-semibold text-slate-900"
                            >
                                <div
                                    class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-50 to-indigo-100 text-indigo-600"
                                >
                                    <GraduationCap class="h-4.5 w-4.5" />
                                </div>
                                Academic Information
                            </h2>
                            <div class="mt-4">
                                <div
                                    v-if="
                                        tutor.tutor_educations &&
                                        tutor.tutor_educations.length > 0
                                    "
                                    class="space-y-3"
                                >
                                    <div
                                        v-for="(
                                            edu, i
                                        ) in tutor.tutor_educations"
                                        :key="edu.id"
                                        class="group relative flex gap-4"
                                    >
                                        <!-- Timeline dot -->
                                        <div
                                            class="hidden shrink-0 flex-col items-center sm:flex"
                                        >
                                            <div
                                                class="flex h-8 w-8 items-center justify-center rounded-full bg-indigo-100 text-xs font-bold text-indigo-600 group-hover:bg-indigo-200"
                                            >
                                                {{ i + 1 }}
                                            </div>
                                            <div
                                                v-if="
                                                    i <
                                                    tutor.tutor_educations
                                                        .length -
                                                        1
                                                "
                                                class="mt-1 w-px flex-1 bg-slate-200"
                                            />
                                        </div>

                                        <!-- Card -->
                                        <div
                                            class="flex-1 rounded-xl border border-slate-100 bg-gradient-to-r from-slate-50/60 to-white p-4 transition-all group-hover:border-indigo-200/60 group-hover:shadow-sm"
                                        >
                                            <div
                                                class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between"
                                            >
                                                <div class="min-w-0 flex-1">
                                                    <p
                                                        class="font-semibold text-slate-900"
                                                    >
                                                        {{ edu.degree }}
                                                    </p>
                                                    <p
                                                        class="mt-0.5 text-sm text-slate-600"
                                                    >
                                                        {{ edu.institute }}
                                                    </p>
                                                    <p
                                                        v-if="edu.department"
                                                        class="text-sm text-slate-500"
                                                    >
                                                        {{ edu.department }}
                                                    </p>
                                                </div>
                                                <div
                                                    class="flex items-center gap-2 sm:flex-col sm:items-end sm:gap-1.5"
                                                >
                                                    <Badge
                                                        v-if="edu.result"
                                                        class="border-blue-200 bg-blue-50 font-medium text-blue-700"
                                                    >
                                                        {{ edu.result }}
                                                    </Badge>
                                                    <span
                                                        v-if="
                                                            edu.graduation_year
                                                        "
                                                        class="text-xs text-slate-500"
                                                    >
                                                        {{
                                                            edu.graduation_year
                                                        }}
                                                    </span>
                                                    <Badge
                                                        v-if="edu.is_current"
                                                        class="border-emerald-200 bg-emerald-50 text-emerald-700"
                                                    >
                                                        <span
                                                            class="mr-1 inline-block h-1.5 w-1.5 animate-pulse rounded-full bg-emerald-500"
                                                        />
                                                        Current
                                                    </Badge>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <p v-else class="text-sm text-slate-400 italic">
                                    No education details provided
                                </p>
                            </div>
                        </section>

                        <!-- Teaching Preferences -->
                        <section
                            v-if="hasPreferences"
                            class="rounded-2xl border border-slate-200/60 bg-white p-5 shadow-sm sm:p-6"
                        >
                            <h2
                                class="flex items-center gap-2.5 text-lg font-semibold text-slate-900"
                            >
                                <div
                                    class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-50 to-emerald-100 text-emerald-600"
                                >
                                    <BookOpen class="h-4.5 w-4.5" />
                                </div>
                                Teaching Preferences
                            </h2>

                            <div class="mt-5 space-y-5">
                                <!-- Subjects -->
                                <div v-if="preferredSubjects.length > 0">
                                    <p
                                        class="mb-2.5 flex items-center gap-1.5 text-xs font-semibold tracking-wider text-slate-500 uppercase"
                                    >
                                        <BookOpen class="h-3.5 w-3.5" />
                                        Subjects
                                    </p>
                                    <div class="flex flex-wrap gap-2">
                                        <Badge
                                            v-for="subject in preferredSubjects"
                                            :key="subject"
                                            class="rounded-lg border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-medium text-emerald-700"
                                        >
                                            {{ subject }}
                                        </Badge>
                                    </div>
                                </div>

                                <!-- Divider -->
                                <div
                                    v-if="
                                        preferredSubjects.length > 0 &&
                                        preferredCategories.length > 0
                                    "
                                    class="border-t border-slate-100"
                                />

                                <!-- Categories -->
                                <div v-if="preferredCategories.length > 0">
                                    <p
                                        class="mb-2.5 flex items-center gap-1.5 text-xs font-semibold tracking-wider text-slate-500 uppercase"
                                    >
                                        <Tag class="h-3.5 w-3.5" />
                                        Categories
                                    </p>
                                    <div class="flex flex-wrap gap-2">
                                        <Badge
                                            v-for="category in preferredCategories"
                                            :key="category"
                                            class="rounded-lg border-blue-200 bg-blue-50 px-3 py-1.5 text-xs font-medium text-blue-700"
                                        >
                                            {{ category }}
                                        </Badge>
                                    </div>
                                </div>

                                <!-- Divider -->
                                <div
                                    v-if="
                                        (preferredSubjects.length > 0 ||
                                            preferredCategories.length > 0) &&
                                        preferredClasses.length > 0
                                    "
                                    class="border-t border-slate-100"
                                />

                                <!-- Classes -->
                                <div v-if="preferredClasses.length > 0">
                                    <p
                                        class="mb-2.5 flex items-center gap-1.5 text-xs font-semibold tracking-wider text-slate-500 uppercase"
                                    >
                                        <GraduationCap class="h-3.5 w-3.5" />
                                        Classes
                                    </p>
                                    <div class="flex flex-wrap gap-2">
                                        <Badge
                                            v-for="cls in preferredClasses"
                                            :key="cls"
                                            class="rounded-lg border-purple-200 bg-purple-50 px-3 py-1.5 text-xs font-medium text-purple-700"
                                        >
                                            {{ cls }}
                                        </Badge>
                                    </div>
                                </div>
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
                            <CheckCircle class="h-4.5 w-4.5 shrink-0" />
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
