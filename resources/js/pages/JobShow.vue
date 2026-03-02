<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { login } from '@/routes';

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

const applicationForm = useForm({
    cover_letter: '',
    expected_salary: '',
});

function salaryLabel(): string {
    if (props.job.salary_negotiable) {
        return 'Negotiable';
    }

    if (!props.job.salary_amount) {
        return 'Not specified';
    }

    return `${props.job.salary_currency ?? ''} ${props.job.salary_amount}`.trim();
}

function prettyDay(day: string): string {
    const map: Record<string, string> = {
        sun: 'Sunday',
        mon: 'Monday',
        tue: 'Tuesday',
        wed: 'Wednesday',
        thu: 'Thursday',
        fri: 'Friday',
        sat: 'Saturday',
    };

    return map[day] ?? day;
}

function submitApplication(): void {
    applicationForm.post(`/tutor/jobs/${props.job.slug}/apply`, {
        preserveScroll: true,
        onSuccess: () => {
            applicationForm.reset('cover_letter', 'expected_salary');
        },
    });
}

function applicationStatusLabel(status: string): string {
    if (status === 'shortlisted') {
        return 'Shortlisted';
    }

    if (status === 'rejected') {
        return 'Rejected';
    }

    if (status === 'withdrawn') {
        return 'Withdrawn';
    }

    return 'Pending';
}
</script>

<template>
    <Head :title="meta.title">
        <meta name="description" :content="meta.description">
    </Head>

    <div class="min-h-screen bg-slate-50 p-6">
        <div class="mx-auto max-w-5xl space-y-6">
            <Link href="/jobs" class="inline-block text-sm font-medium text-blue-600 hover:underline">← Back to Job Board</Link>

            <section class="rounded-xl border bg-white p-6">
                <div class="space-y-3">
                    <h1 class="text-2xl font-bold md:text-3xl">{{ job.title }}</h1>
                    <div class="flex flex-wrap gap-2 text-xs">
                        <span v-if="job.category_name" class="rounded-full border px-2 py-0.5">{{ job.category_name }}</span>
                        <span v-if="job.class_name" class="rounded-full border px-2 py-0.5">{{ job.class_name }}</span>
                        <span v-if="job.tuition_type_name" class="rounded-full border px-2 py-0.5">{{ job.tuition_type_name }}</span>
                    </div>
                    <p class="text-sm text-muted-foreground">
                        {{ job.city_name || 'Unknown city' }}
                        <span v-if="job.area_name">, {{ job.area_name }}</span>
                        <span v-if="job.country_name">, {{ job.country_name }}</span>
                    </p>
                </div>

                <div class="mt-6 grid gap-4 rounded-lg bg-slate-50 p-4 text-sm md:grid-cols-2">
                    <p><span class="font-medium">Salary:</span> {{ salaryLabel() }}</p>
                    <p><span class="font-medium">Students:</span> {{ job.no_of_students ?? 'Not specified' }}</p>
                    <p><span class="font-medium">Days/Week:</span> {{ job.days_per_week ?? 'Not specified' }}</p>
                    <p><span class="font-medium">Tuition Time:</span> {{ job.tuition_time || 'Not specified' }}</p>
                    <p><span class="font-medium">Duration:</span> {{ job.tuition_duration || 'Not specified' }}</p>
                    <p><span class="font-medium">Tutor Gender:</span> {{ job.tutor_gender }}</p>
                </div>

                <div class="mt-5 space-y-2">
                    <h2 class="text-lg font-semibold">Subjects</h2>
                    <div class="flex flex-wrap gap-2 text-sm">
                        <span v-for="subject in job.subject_names" :key="subject" class="rounded-md bg-slate-100 px-2 py-1">{{ subject }}</span>
                    </div>
                </div>

                <div v-if="job.tuition_days.length > 0" class="mt-5 space-y-2">
                    <h2 class="text-lg font-semibold">Tuition Days</h2>
                    <div class="flex flex-wrap gap-2 text-sm">
                        <span v-for="day in job.tuition_days" :key="day" class="rounded-md border px-2 py-1">{{ prettyDay(day) }}</span>
                    </div>
                </div>

                <div class="prose prose-sm mt-6 max-w-none whitespace-pre-line text-slate-700">
                    {{ job.description }}
                </div>

                <div class="mt-6 flex flex-wrap items-center justify-between gap-3 border-t pt-4 text-xs text-muted-foreground">
                    <span>Published: {{ job.published_at ? new Date(job.published_at).toLocaleString() : '—' }}</span>
                    <span>Expires: {{ job.expires_at ? new Date(job.expires_at).toLocaleString() : 'No expiry' }}</span>
                </div>

                <div class="mt-5">
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

                    <form v-if="isTutor && canApply" class="space-y-3 rounded-lg border bg-slate-50 p-4" @submit.prevent="submitApplication">
                        <h3 class="text-sm font-semibold">Apply for this job</h3>
                        <textarea
                            v-model="applicationForm.cover_letter"
                            rows="4"
                            class="w-full rounded-md border px-3 py-2 text-sm"
                            placeholder="Write a short cover letter (optional)"
                        />
                        <p v-if="applicationForm.errors.cover_letter" class="text-xs text-rose-600">
                            {{ applicationForm.errors.cover_letter }}
                        </p>

                        <input
                            v-model="applicationForm.expected_salary"
                            type="number"
                            min="0"
                            step="0.01"
                            class="h-10 w-full rounded-md border px-3 text-sm"
                            placeholder="Expected salary (optional)"
                        >
                        <p v-if="applicationForm.errors.expected_salary" class="text-xs text-rose-600">
                            {{ applicationForm.errors.expected_salary }}
                        </p>

                        <button
                            type="submit"
                            class="rounded-md bg-black px-5 py-2 text-sm text-white disabled:opacity-70"
                            :disabled="applicationForm.processing"
                        >
                            {{ applicationForm.processing ? 'Submitting...' : 'Apply Now' }}
                        </button>
                    </form>

                    <div v-else-if="isTutor && application" class="rounded-lg border bg-slate-50 p-4 text-sm">
                        <p class="font-medium">
                            Your application status: {{ applicationStatusLabel(application.status) }}
                        </p>
                        <p v-if="application.created_at" class="mt-1 text-muted-foreground">
                            Applied on {{ new Date(application.created_at).toLocaleString() }}
                        </p>
                        <Link href="/tutor/job-applications" class="mt-3 inline-block text-blue-600 hover:underline">
                            View My Applications
                        </Link>
                    </div>

                    <div v-else-if="!isAuthenticated" class="rounded-lg border bg-slate-50 p-4 text-sm">
                        <p class="text-muted-foreground">Sign in as tutor to apply for this job.</p>
                        <Link :href="login()" class="mt-3 inline-block text-blue-600 hover:underline">Go to Login</Link>
                    </div>

                    <div v-else class="rounded-lg border bg-slate-50 p-4 text-sm text-muted-foreground">
                        Job applications are available for tutor accounts only.
                    </div>
                </div>
            </section>
        </div>
    </div>
</template>
