<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import PublicLayout from '@/layouts/PublicLayout.vue';
import { login, register } from '@/routes';

type JobDetail = {
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
    expected_salary: string | null;
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

const page = usePage<{
    auth?: {
        user?: {
            role?: string;
        };
    };
}>();

const viewerRole = computed(() => page.props.auth?.user?.role ?? null);
const isAuthenticated = computed(() => viewerRole.value !== null);
const isTutor = computed(() => viewerRole.value === 'tutor');
const isGuardian = computed(() => viewerRole.value === 'guardian');
const isAdmin = computed(() => viewerRole.value === 'admin');

const isExpired = computed(() => {
    if (!props.job.expires_at) return false;
    return new Date(props.job.expires_at) < new Date();
});

const canApply = computed(() => {
    if (!isTutor.value) return false;
    if (isExpired.value) return false;
    return props.canApply;
});

const applicationForm = useForm({
    cover_letter: '',
    expected_salary: '',
});

const showApplicationForm = ref(false);

function formatSalary(amount: string | null, currency: string | null): string {
    if (!amount) return '—';
    const num = parseFloat(amount);
    return new Intl.NumberFormat('en-BD', {
        style: 'currency',
        currency: currency === 'BDT' ? 'BDT' : 'USD',
        maximumFractionDigits: 0,
    }).format(num);
}

function formatDate(dateStr: string | null): string {
    if (!dateStr) return '—';
    const date = new Date(dateStr);
    return date.toLocaleDateString('en-GB', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    });
}

function formatDateTime(dateStr: string | null): string {
    if (!dateStr) return '—';
    const date = new Date(dateStr);
    return date.toLocaleDateString('en-GB', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}

function shortDay(day: string): string {
    const map: Record<string, string> = {
        sun: 'Sun',
        mon: 'Mon',
        tue: 'Tue',
        wed: 'Wed',
        thu: 'Thu',
        fri: 'Fri',
        sat: 'Sat',
    };
    return map[day] ?? day;
}

function genderLabel(gender: string): string {
    const map: Record<string, string> = {
        male: 'Male',
        female: 'Female',
        any: 'Any',
    };
    return map[gender.toLowerCase()] ?? gender;
}

function salaryLabel(): string {
    if (props.job.salary_negotiable) {
        return 'Negotiable';
    }
    if (!props.job.salary_amount) {
        return 'Not specified';
    }
    return formatSalary(props.job.salary_amount, props.job.salary_currency);
}

function submitApplication(): void {
    applicationForm.post(`/tutor/jobs/${props.job.slug}/apply`, {
        preserveScroll: true,
        onSuccess: () => {
            applicationForm.reset('cover_letter', 'expected_salary');
            showApplicationForm.value = false;
        },
    });
}

function applicationStatusLabel(status: string): string {
    const map: Record<string, string> = {
        pending: 'Pending',
        shortlisted: 'Shortlisted',
        rejected: 'Rejected',
        withdrawn: 'Withdrawn',
    };
    return map[status.toLowerCase()] ?? status;
}

function getStatusBadgeClass(): string {
    if (isExpired.value) {
        return 'bg-red-100 text-red-700 border-red-200';
    }
    if (props.application) {
        if (props.application.status === 'shortlisted') {
            return 'bg-emerald-100 text-emerald-700 border-emerald-200';
        }
        if (props.application.status === 'rejected') {
            return 'bg-red-100 text-red-700 border-red-200';
        }
        return 'bg-amber-100 text-amber-700 border-amber-200';
    }
    return 'bg-emerald-100 text-emerald-700 border-emerald-200';
}
</script>

<template>
    <Head :title="meta.title">
        <meta name="description" :content="meta.description" />
    </Head>

    <PublicLayout>
        <div class="bg-slate-50 px-4 pb-24 sm:px-6 lg:px-8 lg:pb-8">
            <div class="mx-auto max-w-6xl pt-4 lg:pt-6">
                <!-- Breadcrumb & Back -->
                <nav class="mb-4 flex items-center gap-2 text-sm">
                    <Link
                        href="/jobs"
                        class="flex items-center gap-1 text-slate-500 transition-colors hover:text-blue-600"
                    >
                        <svg
                            class="h-4 w-4"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M15 19l-7-7 7-7"
                            />
                        </svg>
                        <span class="hidden sm:inline">Back to Job Board</span>
                        <span class="sm:hidden">Back</span>
                    </Link>
                </nav>

                <!-- Main Grid -->
                <div class="grid gap-6 lg:grid-cols-12 lg:gap-8">
                    <!-- Left Content -->
                    <div class="lg:col-span-8">
                        <!-- Header Card -->
                        <section
                            class="rounded-2xl border border-slate-200/70 bg-white p-5 shadow-sm sm:p-6"
                        >
                            <!-- Title -->
                            <h1
                                class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl"
                            >
                                {{ job.title }}
                            </h1>

                            <!-- Location -->
                            <div
                                class="mt-2 flex items-center gap-1 text-sm text-slate-600"
                            >
                                <svg
                                    class="h-4 w-4"
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
                                <span>
                                    {{
                                        job.area_name ||
                                        job.city_name ||
                                        'Location not specified'
                                    }}
                                    <span
                                        v-if="job.area_name && job.city_name"
                                        class="text-slate-400"
                                        >, {{ job.city_name }}</span
                                    >
                                </span>
                            </div>

                            <!-- Meta Chips -->
                            <div class="mt-4 flex flex-wrap gap-2">
                                <span
                                    v-if="job.category_name"
                                    class="inline-flex items-center rounded-full border border-slate-200 px-3 py-1 text-xs font-medium text-slate-700"
                                >
                                    {{ job.category_name }}
                                </span>
                                <span
                                    v-if="job.class_name"
                                    class="inline-flex items-center rounded-full border border-slate-200 px-3 py-1 text-xs font-medium text-slate-700"
                                >
                                    {{ job.class_name }}
                                </span>
                                <span
                                    v-if="job.tuition_type_name"
                                    class="inline-flex items-center rounded-full border border-slate-200 px-3 py-1 text-xs font-medium text-slate-700"
                                >
                                    {{ job.tuition_type_name }}
                                </span>
                                <span
                                    v-if="application"
                                    class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-medium"
                                    :class="getStatusBadgeClass()"
                                >
                                    {{
                                        applicationStatusLabel(
                                            application.status,
                                        )
                                    }}
                                </span>
                                <span
                                    v-else-if="isExpired"
                                    class="inline-flex items-center rounded-full border border-red-200 bg-red-100 px-3 py-1 text-xs font-medium text-red-700"
                                >
                                    Expired
                                </span>
                            </div>

                            <!-- Published/Expires -->
                            <div
                                class="mt-4 flex flex-wrap items-center gap-x-4 gap-y-2 border-t border-slate-100 pt-4 text-xs text-slate-500"
                            >
                                <span class="flex items-center gap-1">
                                    <svg
                                        class="h-3.5 w-3.5"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2-2-2 0 00H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                                        />
                                    </svg>
                                    Published:
                                    {{ formatDate(job.published_at) }}
                                </span>
                                <span class="flex items-center gap-1">
                                    <svg
                                        class="h-3.5 w-3.5"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                                        />
                                    </svg>
                                    <span
                                        :class="{ 'text-red-600': isExpired }"
                                    >
                                        {{
                                            isExpired
                                                ? 'Expired: '
                                                : 'Expires: '
                                        }}{{ formatDate(job.expires_at) }}
                                    </span>
                                </span>
                            </div>
                        </section>

                        <!-- Key Stats Strip -->
                        <section
                            class="mt-6 rounded-2xl border border-slate-200/70 bg-white p-5 shadow-sm"
                        >
                            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                                <div
                                    class="rounded-xl border border-slate-100 bg-slate-50/50 p-3"
                                >
                                    <p
                                        class="text-xs font-medium text-slate-500"
                                    >
                                        Salary
                                    </p>
                                    <p
                                        class="mt-1 text-sm font-semibold text-slate-900"
                                    >
                                        {{ salaryLabel() }}
                                    </p>
                                </div>
                                <div
                                    class="rounded-xl border border-slate-100 bg-slate-50/50 p-3"
                                >
                                    <p
                                        class="text-xs font-medium text-slate-500"
                                    >
                                        Days/Week
                                    </p>
                                    <p
                                        class="mt-1 text-sm font-semibold text-slate-900"
                                    >
                                        {{ job.days_per_week ?? '—' }}
                                    </p>
                                </div>
                                <div
                                    class="rounded-xl border border-slate-100 bg-slate-50/50 p-3"
                                >
                                    <p
                                        class="text-xs font-medium text-slate-500"
                                    >
                                        Time
                                    </p>
                                    <p
                                        class="mt-1 text-sm font-semibold text-slate-900"
                                    >
                                        {{ job.tuition_time || '—' }}
                                    </p>
                                </div>
                                <div
                                    class="rounded-xl border border-slate-100 bg-slate-50/50 p-3"
                                >
                                    <p
                                        class="text-xs font-medium text-slate-500"
                                    >
                                        Duration
                                    </p>
                                    <p
                                        class="mt-1 text-sm font-semibold text-slate-900"
                                    >
                                        {{ job.tuition_duration || '—' }}
                                    </p>
                                </div>
                                <div
                                    class="rounded-xl border border-slate-100 bg-slate-50/50 p-3"
                                >
                                    <p
                                        class="text-xs font-medium text-slate-500"
                                    >
                                        Students
                                    </p>
                                    <p
                                        class="mt-1 text-sm font-semibold text-slate-900"
                                    >
                                        {{ job.no_of_students ?? '—' }}
                                    </p>
                                </div>
                                <div
                                    class="rounded-xl border border-slate-100 bg-slate-50/50 p-3"
                                >
                                    <p
                                        class="text-xs font-medium text-slate-500"
                                    >
                                        Tutor Gender
                                    </p>
                                    <p
                                        class="mt-1 text-sm font-semibold text-slate-900"
                                    >
                                        {{ genderLabel(job.tutor_gender) }}
                                    </p>
                                </div>
                            </div>
                        </section>

                        <!-- Description Card -->
                        <section
                            class="mt-6 rounded-2xl border border-slate-200/70 bg-white p-5 shadow-sm sm:p-6"
                        >
                            <h2 class="text-lg font-semibold text-slate-900">
                                Job Details
                            </h2>
                            <div
                                v-if="job.description"
                                class="mt-4 leading-relaxed whitespace-pre-line text-slate-700"
                            >
                                {{ job.description }}
                            </div>
                            <p v-else class="mt-4 text-sm text-slate-400">
                                No description provided.
                            </p>
                        </section>

                        <!-- Subjects & Schedule Grid -->
                        <div class="mt-6 grid gap-6 md:grid-cols-2">
                            <!-- Subjects Card -->
                            <section
                                class="rounded-2xl border border-slate-200/70 bg-white p-5 shadow-sm"
                            >
                                <h2
                                    class="text-lg font-semibold text-slate-900"
                                >
                                    Subjects
                                </h2>
                                <div
                                    v-if="job.subject_names.length > 0"
                                    class="mt-3 flex flex-wrap gap-2"
                                >
                                    <span
                                        v-for="subject in job.subject_names"
                                        :key="subject"
                                        class="inline-flex items-center rounded-lg bg-blue-50 px-3 py-1.5 text-sm font-medium text-blue-700"
                                    >
                                        {{ subject }}
                                    </span>
                                </div>
                                <p v-else class="mt-3 text-sm text-slate-400">
                                    No subjects specified.
                                </p>
                            </section>

                            <!-- Schedule Card -->
                            <section
                                class="rounded-2xl border border-slate-200/70 bg-white p-5 shadow-sm"
                            >
                                <h2
                                    class="text-lg font-semibold text-slate-900"
                                >
                                    Schedule
                                </h2>
                                <div
                                    v-if="job.tuition_days.length > 0"
                                    class="mt-3 flex flex-wrap gap-2"
                                >
                                    <span
                                        v-for="day in job.tuition_days"
                                        :key="day"
                                        class="inline-flex items-center rounded-lg border border-slate-200 px-3 py-1.5 text-sm font-medium text-slate-700"
                                    >
                                        {{ shortDay(day) }}
                                    </span>
                                </div>
                                <p v-else class="mt-3 text-sm text-slate-400">
                                    No schedule specified.
                                </p>
                                <p
                                    v-if="job.tuition_time"
                                    class="mt-3 text-sm text-slate-600"
                                >
                                    <span class="font-medium">Time:</span>
                                    {{ job.tuition_time }}
                                </p>
                                <p
                                    v-if="job.days_per_week"
                                    class="mt-1 text-sm text-slate-600"
                                >
                                    <span class="font-medium">Days/Week:</span>
                                    {{ job.days_per_week }}
                                </p>
                            </section>
                        </div>

                        <!-- Location Card -->
                        <section
                            class="mt-6 rounded-2xl border border-slate-200/70 bg-white p-5 shadow-sm sm:p-6"
                        >
                            <h2 class="text-lg font-semibold text-slate-900">
                                Location
                            </h2>
                            <div class="mt-3 space-y-2 text-sm text-slate-700">
                                <p v-if="job.country_name">
                                    <span class="font-medium">Country:</span>
                                    {{ job.country_name }}
                                </p>
                                <p v-if="job.city_name">
                                    <span class="font-medium">City:</span>
                                    {{ job.city_name }}
                                </p>
                                <p v-if="job.area_name">
                                    <span class="font-medium">Area:</span>
                                    {{ job.area_name }}
                                </p>
                                <p v-if="job.location">
                                    <span class="font-medium">Address:</span>
                                    {{ job.location }}
                                </p>
                                <p
                                    v-if="
                                        !job.country_name &&
                                        !job.city_name &&
                                        !job.area_name
                                    "
                                    class="text-slate-400"
                                >
                                    Location details not available.
                                </p>
                            </div>
                        </section>
                    </div>

                    <!-- Right Sidebar (Desktop) -->
                    <aside class="hidden lg:col-span-4 lg:block">
                        <div class="sticky top-24 space-y-4">
                            <!-- Apply Card -->
                            <div
                                class="rounded-2xl border border-slate-200/70 bg-white p-5 shadow-sm"
                            >
                                <!-- Flash Messages -->
                                <div
                                    v-if="$page.props.flash?.status"
                                    class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800"
                                >
                                    {{ $page.props.flash.status }}
                                </div>
                                <div
                                    v-if="$page.props.errors?.job"
                                    class="mb-4 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700"
                                >
                                    {{ $page.props.errors.job }}
                                </div>

                                <!-- Already Applied -->
                                <div
                                    v-if="
                                        isTutor &&
                                        application &&
                                        !showApplicationForm
                                    "
                                    class="space-y-4"
                                >
                                    <div
                                        class="rounded-lg border border-amber-200 bg-amber-50 p-4"
                                    >
                                        <p class="font-medium text-amber-800">
                                            Application Submitted
                                        </p>
                                        <p class="mt-1 text-sm text-amber-700">
                                            Status:
                                            {{
                                                applicationStatusLabel(
                                                    application.status,
                                                )
                                            }}
                                        </p>
                                        <p
                                            v-if="application.created_at"
                                            class="mt-1 text-xs text-amber-600"
                                        >
                                            {{
                                                formatDateTime(
                                                    application.created_at,
                                                )
                                            }}
                                        </p>
                                    </div>
                                    <Link
                                        href="/tutor/job-applications"
                                        class="flex w-full items-center justify-center rounded-xl bg-slate-100 px-4 py-2.5 text-sm font-medium text-slate-700 transition-colors hover:bg-slate-200"
                                    >
                                        View My Applications
                                    </Link>
                                </div>

                                <!-- Application Form -->
                                <div
                                    v-else-if="
                                        isTutor &&
                                        canApply &&
                                        !showApplicationForm
                                    "
                                >
                                    <button
                                        @click="showApplicationForm = true"
                                        class="flex w-full items-center justify-center rounded-xl bg-blue-600 px-4 py-3 text-sm font-semibold text-white transition-all hover:bg-blue-700 focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 focus-visible:outline-none active:scale-[0.98]"
                                    >
                                        Apply for this Job
                                    </button>
                                    <p
                                        class="mt-3 text-center text-xs text-slate-500"
                                    >
                                        Verified tutors get priority response
                                    </p>
                                </div>

                                <!-- Application Form Expanded -->
                                <form
                                    v-else-if="
                                        isTutor &&
                                        canApply &&
                                        showApplicationForm
                                    "
                                    class="space-y-4"
                                    @submit.prevent="submitApplication"
                                >
                                    <h3 class="font-semibold text-slate-900">
                                        Submit Application
                                    </h3>
                                    <div>
                                        <label
                                            class="mb-1 block text-sm font-medium text-slate-700"
                                            >Cover Letter</label
                                        >
                                        <textarea
                                            v-model="
                                                applicationForm.cover_letter
                                            "
                                            rows="4"
                                            class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none"
                                            placeholder="Write a short cover letter (optional)"
                                        />
                                    </div>
                                    <div>
                                        <label
                                            class="mb-1 block text-sm font-medium text-slate-700"
                                            >Expected Salary</label
                                        >
                                        <input
                                            v-model="
                                                applicationForm.expected_salary
                                            "
                                            type="number"
                                            min="0"
                                            step="100"
                                            class="h-11 w-full rounded-xl border border-slate-200 px-3 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none"
                                            placeholder="Expected salary (optional)"
                                        />
                                    </div>
                                    <div class="flex gap-2">
                                        <button
                                            type="button"
                                            class="flex-1 rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm font-medium text-slate-700 transition-colors hover:bg-slate-100"
                                            @click="showApplicationForm = false"
                                        >
                                            Cancel
                                        </button>
                                        <button
                                            type="submit"
                                            class="flex-1 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-blue-700 disabled:opacity-70"
                                            :disabled="
                                                applicationForm.processing
                                            "
                                        >
                                            {{
                                                applicationForm.processing
                                                    ? 'Submitting...'
                                                    : 'Submit'
                                            }}
                                        </button>
                                    </div>
                                </form>

                                <!-- Guest User -->
                                <div
                                    v-else-if="!isAuthenticated"
                                    class="space-y-4"
                                >
                                    <p class="text-sm text-slate-600">
                                        Sign in to apply for this job and start
                                        your tutoring journey.
                                    </p>
                                    <Link
                                        :href="login()"
                                        class="flex w-full items-center justify-center rounded-xl bg-blue-600 px-4 py-3 text-sm font-semibold text-white transition-all hover:bg-blue-700 focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 focus-visible:outline-none active:scale-[0.98]"
                                    >
                                        Sign in to Apply
                                    </Link>
                                    <Link
                                        :href="register()"
                                        class="flex w-full items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 transition-colors hover:bg-slate-50"
                                    >
                                        Create Tutor Account
                                    </Link>
                                </div>

                                <!-- Expired Job -->
                                <div v-else-if="isExpired" class="space-y-3">
                                    <div
                                        class="rounded-lg border border-red-200 bg-red-50 p-4"
                                    >
                                        <p class="font-medium text-red-800">
                                            Job Expired
                                        </p>
                                        <p class="mt-1 text-sm text-red-700">
                                            This job is no longer accepting
                                            applications.
                                        </p>
                                    </div>
                                </div>

                                <!-- Other Roles -->
                                <div
                                    v-else
                                    class="rounded-lg border border-slate-200 bg-slate-50 p-4"
                                >
                                    <p class="text-sm text-slate-600">
                                        Job applications are available for tutor
                                        accounts only.
                                    </p>
                                </div>
                            </div>

                            <!-- Quick Info Card -->
                            <div
                                class="rounded-2xl border border-slate-200/70 bg-white p-5 shadow-sm"
                            >
                                <h3 class="font-semibold text-slate-900">
                                    Quick Info
                                </h3>
                                <ul
                                    class="mt-3 space-y-2 text-sm text-slate-600"
                                >
                                    <li class="flex items-start gap-2">
                                        <svg
                                            class="mt-0.5 h-4 w-4 text-slate-400"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                                            />
                                        </svg>
                                        <span>Verified guardian</span>
                                    </li>
                                    <li class="flex items-start gap-2">
                                        <svg
                                            class="mt-0.5 h-4 w-4 text-slate-400"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M13 10V3L4 14h7v7l9-11h-7z"
                                            />
                                        </svg>
                                        <span>Quick response time</span>
                                    </li>
                                    <li class="flex items-start gap-2">
                                        <svg
                                            class="mt-0.5 h-4 w-4 text-slate-400"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                                            />
                                        </svg>
                                        <span>Flexible schedule</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </aside>
                </div>
            </div>

            <!-- Mobile Sticky Bottom CTA -->
            <div
                class="fixed inset-x-0 bottom-0 z-50 border-t border-slate-200/80 bg-white/95 px-4 py-3 backdrop-blur-sm lg:hidden"
            >
                <div
                    class="mx-auto flex max-w-6xl items-center justify-between gap-3"
                >
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-medium text-slate-900">
                            {{ job.title }}
                        </p>
                        <p class="truncate text-xs text-slate-500">
                            {{ job.area_name || job.city_name }}
                        </p>
                    </div>
                    <Link
                        v-if="!isAuthenticated"
                        :href="login()"
                        class="flex h-11 shrink-0 items-center justify-center rounded-xl bg-blue-600 px-5 text-sm font-semibold text-white transition-all hover:bg-blue-700 active:scale-[0.98]"
                    >
                        Sign In
                    </Link>
                    <button
                        v-else-if="isTutor && canApply"
                        @click="showApplicationForm = true"
                        class="flex h-11 shrink-0 items-center justify-center rounded-xl bg-blue-600 px-5 text-sm font-semibold text-white transition-all hover:bg-blue-700 active:scale-[0.98]"
                    >
                        Apply Now
                    </button>
                    <span
                        v-else-if="isExpired"
                        class="flex h-11 shrink-0 items-center justify-center rounded-xl bg-slate-100 px-5 text-sm font-medium text-slate-500"
                    >
                        Expired
                    </span>
                    <span
                        v-else-if="application"
                        class="flex h-11 shrink-0 items-center justify-center rounded-xl bg-amber-100 px-5 text-sm font-medium text-amber-700"
                    >
                        Applied
                    </span>
                    <span
                        v-else
                        class="flex h-11 shrink-0 items-center justify-center rounded-xl bg-slate-100 px-5 text-sm font-medium text-slate-500"
                    >
                        Tutors Only
                    </span>
                </div>
            </div>

            <!-- Mobile Application Modal -->
            <Teleport to="body">
                <div
                    v-if="showApplicationForm && canApply"
                    class="fixed inset-0 z-50 flex items-end justify-center sm:items-center"
                >
                    <div
                        class="absolute inset-0 bg-slate-900/50"
                        @click="showApplicationForm = false"
                    />
                    <div
                        class="relative w-full rounded-t-2xl bg-white p-5 sm:max-w-md sm:rounded-2xl"
                    >
                        <div class="mb-4 flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-slate-900">
                                Apply for this Job
                            </h3>
                            <button
                                @click="showApplicationForm = false"
                                class="rounded-lg p-1 text-slate-400 hover:text-slate-600"
                            >
                                <svg
                                    class="h-5 w-5"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"
                                    />
                                </svg>
                            </button>
                        </div>
                        <form
                            @submit.prevent="submitApplication"
                            class="space-y-4"
                        >
                            <div>
                                <label
                                    class="mb-1.5 block text-sm font-medium text-slate-700"
                                    >Cover Letter</label
                                >
                                <textarea
                                    v-model="applicationForm.cover_letter"
                                    rows="3"
                                    class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none"
                                    placeholder="Write a short cover letter (optional)"
                                />
                            </div>
                            <div>
                                <label
                                    class="mb-1.5 block text-sm font-medium text-slate-700"
                                    >Expected Salary (BDT)</label
                                >
                                <input
                                    v-model="applicationForm.expected_salary"
                                    type="number"
                                    min="0"
                                    step="100"
                                    class="h-11 w-full rounded-xl border border-slate-200 px-3 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none"
                                    placeholder="Your expected salary"
                                />
                            </div>
                            <button
                                type="submit"
                                class="flex w-full items-center justify-center rounded-xl bg-blue-600 px-4 py-3 text-sm font-semibold text-white transition-all hover:bg-blue-700 active:scale-[0.98] disabled:opacity-70"
                                :disabled="applicationForm.processing"
                            >
                                {{
                                    applicationForm.processing
                                        ? 'Submitting...'
                                        : 'Submit Application'
                                }}
                            </button>
                        </form>
                    </div>
                </div>
            </Teleport>
        </div>
    </PublicLayout>
</template>
