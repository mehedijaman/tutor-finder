<script setup lang="ts">
import { Link, useForm, usePage } from '@inertiajs/vue3';
import {
    ArrowLeft,
    BookOpen,
    CalendarDays,
    CheckCircle2,
    Clock3,
    MapPin,
    Users,
    Wallet,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { login, register } from '@/routes';

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
    canApply: boolean;
    application: ApplicationInfo | null;
    backToJobsHref: string;
}>();

const page = usePage<{
    auth?: {
        user?: {
            role?: string;
        };
    };
    flash?: {
        status?: string;
        success?: string;
    };
    errors?: {
        job?: string;
    };
}>();

const viewerRole = computed(() => page.props.auth?.user?.role ?? null);
const isAuthenticated = computed(() => viewerRole.value !== null);
const isTutor = computed(() => viewerRole.value === 'tutor');

const isExpired = computed(() => {
    if (!props.job.expires_at) {
        return false;
    }

    return new Date(props.job.expires_at) < new Date();
});

const canApply = computed(() => {
    if (!isTutor.value) {
        return false;
    }

    if (isExpired.value) {
        return false;
    }

    return props.canApply;
});

const hasActiveApplication = computed(
    () =>
        isTutor.value &&
        props.application !== null &&
        props.application.status !== 'cancelled',
);
const hasCancelledApplication = computed(
    () => isTutor.value && props.application?.status === 'cancelled',
);

const primaryLocation = computed(() => {
    if (props.job.area_name && props.job.city_name) {
        return `${props.job.area_name}, ${props.job.city_name}`;
    }

    return (
        props.job.area_name ||
        props.job.city_name ||
        props.job.country_name ||
        'Location not specified'
    );
});

const applicationForm = useForm({
    cover_letter: '',
    expected_salary_amount: '',
    salary_currency: 'BDT',
});

const showApplicationForm = ref(false);

function formatSalary(amount: string | null, currency: string | null): string {
    if (!amount) {
        return '—';
    }

    const num = Number.parseFloat(amount);

    if (!Number.isFinite(num)) {
        return '—';
    }

    return new Intl.NumberFormat('en-BD', {
        style: 'currency',
        currency: currency === 'BDT' ? 'BDT' : 'USD',
        maximumFractionDigits: 0,
    }).format(num);
}

function formatDate(dateStr: string | null): string {
    if (!dateStr) {
        return '—';
    }

    const date = new Date(dateStr);

    return date.toLocaleDateString('en-GB', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    });
}

function formatDateTime(dateStr: string | null): string {
    if (!dateStr) {
        return '—';
    }

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

    return map[day.toLowerCase()] ?? day;
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
    applicationForm.post(`/tutor/jobs/${props.job.id}/apply`, {
        preserveScroll: true,
        onSuccess: () => {
            applicationForm.reset('cover_letter', 'expected_salary_amount');
            showApplicationForm.value = false;
        },
    });
}

function applicationStatusLabel(status: string): string {
    const map: Record<string, string> = {
        applied: 'Applied',
        shortlisted: 'Shortlisted',
        appointed: 'Appointed',
        confirmed: 'Confirmed',
        cancelled: 'Cancelled',
    };

    return map[status.toLowerCase()] ?? status;
}

function jobStateLabel(): string {
    if (isExpired.value) {
        return 'Expired';
    }

    if (props.application) {
        return applicationStatusLabel(props.application.status);
    }

    return 'Open';
}

function jobStateClass(): string {
    if (isExpired.value) {
        return 'border-red-200 bg-red-50 text-red-700';
    }

    if (props.application) {
        if (
            props.application.status === 'confirmed' ||
            props.application.status === 'shortlisted'
        ) {
            return 'border-emerald-200 bg-emerald-50 text-emerald-700';
        }

        if (props.application.status === 'cancelled') {
            return 'border-red-200 bg-red-50 text-red-700';
        }

        return 'border-amber-200 bg-amber-50 text-amber-700';
    }

    return 'border-emerald-200 bg-emerald-50 text-emerald-700';
}

function openApplicationForm(): void {
    showApplicationForm.value = true;
}

function closeApplicationForm(): void {
    showApplicationForm.value = false;
    applicationForm.clearErrors();
}
</script>

<template>
    <div
        class="min-h-screen bg-gradient-to-b from-slate-50 via-white to-slate-100/60 pt-8 pb-28 lg:pt-12 lg:pb-12"
    >
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <Link
                :href="backToJobsHref"
                class="mb-6 inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition-colors hover:bg-slate-50"
            >
                <ArrowLeft class="h-4 w-4" />
                Back to Job Board
            </Link>

            <div class="grid gap-6 lg:grid-cols-12 lg:gap-8">
                <div class="space-y-6 lg:col-span-8">
                    <section
                        class="overflow-hidden rounded-3xl border border-slate-200/80 bg-white shadow-sm ring-1 ring-slate-900/5"
                    >
                        <div
                            class="bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 p-5 text-white sm:p-8"
                        >
                            <div
                                class="mb-4 flex flex-wrap items-center justify-between gap-3"
                            >
                                <span
                                    class="inline-flex items-center rounded-full bg-white/15 px-3 py-1 text-xs font-semibold tracking-wide text-white/95 uppercase"
                                >
                                    Job #{{ job.id }}
                                </span>
                                <span
                                    class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold"
                                    :class="jobStateClass()"
                                >
                                    {{ jobStateLabel() }}
                                </span>
                            </div>

                            <h1
                                class="text-2xl font-bold tracking-tight sm:text-3xl"
                            >
                                {{ job.title }}
                            </h1>

                            <div
                                class="mt-3 flex items-center gap-2 text-sm text-white/80"
                            >
                                <MapPin class="h-4 w-4 shrink-0" />
                                <span class="line-clamp-1">{{
                                    primaryLocation
                                }}</span>
                            </div>

                            <div class="mt-4 flex flex-wrap gap-2">
                                <span
                                    v-if="job.category_name"
                                    class="inline-flex items-center rounded-full border border-white/20 bg-white/10 px-2.5 py-1 text-xs font-medium text-white/90"
                                >
                                    {{ job.category_name }}
                                </span>
                                <span
                                    v-if="job.class_name"
                                    class="inline-flex items-center rounded-full border border-white/20 bg-white/10 px-2.5 py-1 text-xs font-medium text-white/90"
                                >
                                    {{ job.class_name }}
                                </span>
                                <span
                                    v-if="job.tuition_type_name"
                                    class="inline-flex items-center rounded-full border border-white/20 bg-white/10 px-2.5 py-1 text-xs font-medium text-white/90"
                                >
                                    {{ job.tuition_type_name }}
                                </span>
                            </div>

                            <div
                                class="mt-5 flex flex-wrap gap-4 text-xs text-white/75"
                            >
                                <span class="inline-flex items-center gap-1.5">
                                    <CalendarDays class="h-3.5 w-3.5" />
                                    Published {{ formatDate(job.published_at) }}
                                </span>
                                <span class="inline-flex items-center gap-1.5">
                                    <Clock3 class="h-3.5 w-3.5" />
                                    {{ isExpired ? 'Expired' : 'Expires' }}
                                    {{ formatDate(job.expires_at) }}
                                </span>
                            </div>
                        </div>

                        <div
                            class="grid grid-cols-2 gap-3 p-5 sm:grid-cols-4 sm:p-6"
                        >
                            <div
                                class="rounded-xl border border-slate-200 bg-slate-50/70 p-3"
                            >
                                <p
                                    class="text-[11px] font-medium tracking-wide text-slate-500 uppercase"
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
                                class="rounded-xl border border-slate-200 bg-slate-50/70 p-3"
                            >
                                <p
                                    class="text-[11px] font-medium tracking-wide text-slate-500 uppercase"
                                >
                                    Days / Week
                                </p>
                                <p
                                    class="mt-1 text-sm font-semibold text-slate-900"
                                >
                                    {{ job.days_per_week ?? '—' }}
                                </p>
                            </div>

                            <div
                                class="rounded-xl border border-slate-200 bg-slate-50/70 p-3"
                            >
                                <p
                                    class="text-[11px] font-medium tracking-wide text-slate-500 uppercase"
                                >
                                    Duration
                                </p>
                                <p
                                    class="mt-1 text-sm font-semibold text-slate-900"
                                >
                                    {{ job.tuition_duration || 'Flexible' }}
                                </p>
                            </div>

                            <div
                                class="rounded-xl border border-slate-200 bg-slate-50/70 p-3"
                            >
                                <p
                                    class="text-[11px] font-medium tracking-wide text-slate-500 uppercase"
                                >
                                    Students
                                </p>
                                <p
                                    class="mt-1 text-sm font-semibold text-slate-900"
                                >
                                    {{ job.no_of_students ?? '—' }}
                                </p>
                            </div>
                        </div>
                    </section>

                    <section
                        class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6"
                    >
                        <h2 class="text-lg font-semibold text-slate-900">
                            Job Description
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

                    <div class="grid gap-6 md:grid-cols-2">
                        <section
                            class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm"
                        >
                            <h2 class="text-lg font-semibold text-slate-900">
                                Subjects
                            </h2>
                            <div
                                v-if="job.subject_names.length > 0"
                                class="mt-3 flex flex-wrap gap-2"
                            >
                                <span
                                    v-for="subject in job.subject_names"
                                    :key="subject"
                                    class="inline-flex items-center rounded-lg border border-blue-200 bg-blue-50 px-3 py-1.5 text-sm font-medium text-blue-700"
                                >
                                    <BookOpen class="mr-1.5 h-3.5 w-3.5" />
                                    {{ subject }}
                                </span>
                            </div>
                            <p v-else class="mt-3 text-sm text-slate-400">
                                No subjects specified.
                            </p>
                        </section>

                        <section
                            class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm"
                        >
                            <h2 class="text-lg font-semibold text-slate-900">
                                Schedule
                            </h2>
                            <div
                                v-if="job.tuition_days.length > 0"
                                class="mt-3 flex flex-wrap gap-2"
                            >
                                <span
                                    v-for="day in job.tuition_days"
                                    :key="day"
                                    class="inline-flex items-center rounded-lg border border-slate-200 bg-slate-50 px-3 py-1.5 text-sm font-medium text-slate-700"
                                >
                                    {{ shortDay(day) }}
                                </span>
                            </div>
                            <p v-else class="mt-3 text-sm text-slate-400">
                                No schedule specified.
                            </p>

                            <div
                                class="mt-4 space-y-1.5 text-sm text-slate-600"
                            >
                                <p>
                                    <span class="font-medium text-slate-800"
                                        >Time:</span
                                    >
                                    {{ job.tuition_time || 'Flexible' }}
                                </p>
                                <p>
                                    <span class="font-medium text-slate-800"
                                        >Tutor Preference:</span
                                    >
                                    {{ genderLabel(job.tutor_gender) }}
                                </p>
                                <p>
                                    <span class="font-medium text-slate-800"
                                        >Student Gender:</span
                                    >
                                    {{ genderLabel(job.student_gender) }}
                                </p>
                            </div>
                        </section>
                    </div>

                    <section
                        class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6"
                    >
                        <h2 class="text-lg font-semibold text-slate-900">
                            Location Details
                        </h2>
                        <div class="mt-3 space-y-2 text-sm text-slate-700">
                            <p v-if="job.country_name">
                                <span class="font-medium text-slate-900"
                                    >Country:</span
                                >
                                {{ job.country_name }}
                            </p>
                            <p v-if="job.city_name">
                                <span class="font-medium text-slate-900"
                                    >City:</span
                                >
                                {{ job.city_name }}
                            </p>
                            <p v-if="job.area_name">
                                <span class="font-medium text-slate-900"
                                    >Area:</span
                                >
                                {{ job.area_name }}
                            </p>
                            <p v-if="job.location">
                                <span class="font-medium text-slate-900"
                                    >Address:</span
                                >
                                {{ job.location }}
                            </p>
                            <p
                                v-if="
                                    !job.country_name &&
                                    !job.city_name &&
                                    !job.area_name &&
                                    !job.location
                                "
                                class="text-slate-400"
                            >
                                Location details not available.
                            </p>
                        </div>
                    </section>
                </div>

                <aside class="hidden space-y-4 lg:col-span-4 lg:block">
                    <div class="sticky top-24 space-y-4">
                        <div
                            class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm"
                        >
                            <div
                                v-if="page.props.flash?.status"
                                class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800"
                            >
                                {{ page.props.flash.status }}
                            </div>
                            <div
                                v-if="page.props.errors?.job"
                                class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700"
                            >
                                {{ page.props.errors.job }}
                            </div>

                            <div
                                v-if="
                                    hasActiveApplication && !showApplicationForm
                                "
                                class="space-y-4"
                            >
                                <div
                                    class="rounded-xl border border-amber-200 bg-amber-50 p-4"
                                >
                                    <p class="font-semibold text-amber-800">
                                        Application Submitted
                                    </p>
                                    <p class="mt-1 text-sm text-amber-700">
                                        Status:
                                        {{
                                            applicationStatusLabel(
                                                application?.status || '',
                                            )
                                        }}
                                    </p>
                                    <p
                                        v-if="application?.created_at"
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
                                    class="flex h-11 w-full items-center justify-center rounded-xl border border-slate-200 bg-slate-100 px-4 text-sm font-medium text-slate-700 transition-colors hover:bg-slate-200"
                                >
                                    View My Applications
                                </Link>
                            </div>

                            <div
                                v-else-if="
                                    hasCancelledApplication &&
                                    canApply &&
                                    !showApplicationForm
                                "
                                class="space-y-4"
                            >
                                <div
                                    class="rounded-xl border border-red-200 bg-red-50 p-4"
                                >
                                    <p class="font-semibold text-red-800">
                                        Previous Application Cancelled
                                    </p>
                                    <p class="mt-1 text-sm text-red-700">
                                        You can submit a new application for
                                        this job.
                                    </p>
                                    <p
                                        v-if="application?.cancel_reason"
                                        class="mt-1 text-xs text-red-600"
                                    >
                                        Reason: {{ application.cancel_reason }}
                                    </p>
                                </div>
                                <button
                                    class="flex h-11 w-full items-center justify-center rounded-xl bg-blue-600 px-4 text-sm font-semibold text-white transition-colors hover:bg-blue-700"
                                    @click="openApplicationForm"
                                >
                                    Re-Apply for this Job
                                </button>
                            </div>

                            <div
                                v-else-if="
                                    isTutor && canApply && !showApplicationForm
                                "
                            >
                                <button
                                    class="flex h-11 w-full items-center justify-center rounded-xl bg-blue-600 px-4 text-sm font-semibold text-white transition-colors hover:bg-blue-700"
                                    @click="openApplicationForm"
                                >
                                    Apply for this Job
                                </button>
                                <p
                                    class="mt-3 text-center text-xs text-slate-500"
                                >
                                    Verified tutors usually get faster
                                    responses.
                                </p>
                            </div>

                            <form
                                v-else-if="
                                    isTutor && canApply && showApplicationForm
                                "
                                class="space-y-4"
                                @submit.prevent="submitApplication"
                            >
                                <h3
                                    class="text-base font-semibold text-slate-900"
                                >
                                    Submit Application
                                </h3>
                                <div>
                                    <label
                                        class="mb-1 block text-sm font-medium text-slate-700"
                                    >
                                        Cover Letter
                                    </label>
                                    <textarea
                                        v-model="applicationForm.cover_letter"
                                        rows="4"
                                        class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none"
                                        placeholder="Write a short cover letter (optional)"
                                    />
                                    <p
                                        v-if="
                                            applicationForm.errors.cover_letter
                                        "
                                        class="mt-1 text-xs text-rose-600"
                                    >
                                        {{
                                            applicationForm.errors.cover_letter
                                        }}
                                    </p>
                                </div>
                                <div>
                                    <label
                                        class="mb-1 block text-sm font-medium text-slate-700"
                                    >
                                        Expected Salary (BDT)
                                    </label>
                                    <input
                                        v-model="
                                            applicationForm.expected_salary_amount
                                        "
                                        type="number"
                                        min="0"
                                        step="100"
                                        class="h-11 w-full rounded-xl border border-slate-200 px-3 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none"
                                        placeholder="Expected salary (optional)"
                                    />
                                    <p
                                        v-if="
                                            applicationForm.errors
                                                .expected_salary_amount
                                        "
                                        class="mt-1 text-xs text-rose-600"
                                    >
                                        {{
                                            applicationForm.errors
                                                .expected_salary_amount
                                        }}
                                    </p>
                                </div>
                                <p
                                    v-if="applicationForm.errors.job"
                                    class="text-sm text-rose-600"
                                >
                                    {{ applicationForm.errors.job }}
                                </p>
                                <div class="flex gap-2">
                                    <button
                                        type="button"
                                        class="h-11 flex-1 rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm font-medium text-slate-700 transition-colors hover:bg-slate-100"
                                        @click="closeApplicationForm"
                                    >
                                        Cancel
                                    </button>
                                    <button
                                        type="submit"
                                        class="h-11 flex-1 rounded-xl bg-blue-600 px-4 text-sm font-semibold text-white transition-colors hover:bg-blue-700 disabled:opacity-70"
                                        :disabled="applicationForm.processing"
                                    >
                                        {{
                                            applicationForm.processing
                                                ? 'Submitting...'
                                                : 'Submit'
                                        }}
                                    </button>
                                </div>
                            </form>

                            <div v-else-if="!isAuthenticated" class="space-y-4">
                                <p class="text-sm text-slate-600">
                                    Sign in to apply for this job and start your
                                    tutoring journey.
                                </p>
                                <Link
                                    :href="login()"
                                    class="flex h-11 w-full items-center justify-center rounded-xl bg-blue-600 px-4 text-sm font-semibold text-white transition-colors hover:bg-blue-700"
                                >
                                    Sign in to Apply
                                </Link>
                                <Link
                                    :href="register()"
                                    class="flex h-11 w-full items-center justify-center rounded-xl border border-slate-200 bg-white px-4 text-sm font-medium text-slate-700 transition-colors hover:bg-slate-50"
                                >
                                    Create Tutor Account
                                </Link>
                            </div>

                            <div
                                v-else-if="isExpired"
                                class="rounded-xl border border-red-200 bg-red-50 p-4"
                            >
                                <p class="font-semibold text-red-800">
                                    Job Expired
                                </p>
                                <p class="mt-1 text-sm text-red-700">
                                    This job is no longer accepting
                                    applications.
                                </p>
                            </div>

                            <div
                                v-else
                                class="rounded-xl border border-slate-200 bg-slate-50 p-4"
                            >
                                <p class="text-sm text-slate-600">
                                    Job applications are available for tutor
                                    accounts only.
                                </p>
                            </div>
                        </div>

                        <div
                            class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm"
                        >
                            <h3 class="text-base font-semibold text-slate-900">
                                Why This Listing Stands Out
                            </h3>
                            <ul class="mt-3 space-y-2.5 text-sm text-slate-600">
                                <li class="flex items-start gap-2">
                                    <CheckCircle2
                                        class="mt-0.5 h-4 w-4 text-emerald-600"
                                    />
                                    Structured details with transparent
                                    requirements
                                </li>
                                <li class="flex items-start gap-2">
                                    <Wallet
                                        class="mt-0.5 h-4 w-4 text-blue-600"
                                    />
                                    Clear salary and schedule expectations
                                </li>
                                <li class="flex items-start gap-2">
                                    <Users
                                        class="mt-0.5 h-4 w-4 text-indigo-600"
                                    />
                                    Balanced student and tutor preference
                                    details
                                </li>
                            </ul>
                        </div>
                    </div>
                </aside>
            </div>
        </div>

        <div
            class="fixed inset-x-0 bottom-0 z-50 border-t border-slate-200/80 bg-white/95 px-4 py-3 backdrop-blur-sm lg:hidden"
        >
            <div
                class="mx-auto flex max-w-6xl items-center justify-between gap-3"
            >
                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-semibold text-slate-900">
                        {{ job.title }}
                    </p>
                    <p class="truncate text-xs text-slate-500">
                        {{ salaryLabel() }} · {{ primaryLocation }}
                    </p>
                </div>

                <Link
                    v-if="!isAuthenticated"
                    :href="login()"
                    class="inline-flex h-11 shrink-0 items-center justify-center rounded-xl bg-blue-600 px-5 text-sm font-semibold text-white transition-colors hover:bg-blue-700"
                >
                    Sign In
                </Link>

                <span
                    v-else-if="hasActiveApplication"
                    class="inline-flex h-11 shrink-0 items-center justify-center rounded-xl bg-amber-100 px-4 text-sm font-medium text-amber-700"
                >
                    {{ applicationStatusLabel(application?.status || '') }}
                </span>

                <button
                    v-else-if="isTutor && canApply"
                    class="inline-flex h-11 shrink-0 items-center justify-center rounded-xl bg-blue-600 px-5 text-sm font-semibold text-white transition-colors hover:bg-blue-700"
                    @click="openApplicationForm"
                >
                    {{ hasCancelledApplication ? 'Re-Apply' : 'Apply Now' }}
                </button>

                <span
                    v-else-if="isExpired"
                    class="inline-flex h-11 shrink-0 items-center justify-center rounded-xl bg-slate-100 px-5 text-sm font-medium text-slate-500"
                >
                    Expired
                </span>

                <span
                    v-else
                    class="inline-flex h-11 shrink-0 items-center justify-center rounded-xl bg-slate-100 px-5 text-sm font-medium text-slate-500"
                >
                    Tutors Only
                </span>
            </div>
        </div>

        <Teleport to="body">
            <div
                v-if="showApplicationForm && canApply"
                class="fixed inset-0 z-50 flex items-end justify-center sm:items-center lg:hidden"
            >
                <div
                    class="absolute inset-0 bg-slate-900/50"
                    @click="closeApplicationForm"
                />
                <div
                    class="relative max-h-[85vh] w-full overflow-y-auto rounded-t-2xl bg-white p-5 sm:max-w-md sm:rounded-2xl"
                >
                    <div class="mb-4 flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-slate-900">
                            Apply for this Job
                        </h3>
                        <button
                            class="rounded-lg p-1 text-slate-400 hover:text-slate-600"
                            aria-label="Close application form"
                            @click="closeApplicationForm"
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

                    <form class="space-y-4" @submit.prevent="submitApplication">
                        <div>
                            <label
                                class="mb-1.5 block text-sm font-medium text-slate-700"
                            >
                                Cover Letter
                            </label>
                            <textarea
                                v-model="applicationForm.cover_letter"
                                rows="3"
                                class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none"
                                placeholder="Write a short cover letter (optional)"
                            />
                            <p
                                v-if="applicationForm.errors.cover_letter"
                                class="mt-1 text-xs text-rose-600"
                            >
                                {{ applicationForm.errors.cover_letter }}
                            </p>
                        </div>

                        <div>
                            <label
                                class="mb-1.5 block text-sm font-medium text-slate-700"
                            >
                                Expected Salary (BDT)
                            </label>
                            <input
                                v-model="applicationForm.expected_salary_amount"
                                type="number"
                                min="0"
                                step="100"
                                class="h-11 w-full rounded-xl border border-slate-200 px-3 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none"
                                placeholder="Your expected salary"
                            />
                            <p
                                v-if="
                                    applicationForm.errors
                                        .expected_salary_amount
                                "
                                class="mt-1 text-xs text-rose-600"
                            >
                                {{
                                    applicationForm.errors
                                        .expected_salary_amount
                                }}
                            </p>
                        </div>

                        <p
                            v-if="applicationForm.errors.job"
                            class="text-sm text-rose-600"
                        >
                            {{ applicationForm.errors.job }}
                        </p>

                        <button
                            type="submit"
                            class="flex h-11 w-full items-center justify-center rounded-xl bg-blue-600 px-4 text-sm font-semibold text-white transition-colors hover:bg-blue-700 disabled:opacity-70"
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
</template>
