<script setup lang="ts">
import { Link, useForm, usePage } from '@inertiajs/vue3';
import { BookOpen, Calendar, MapPin, Share2, UserRound } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';

type JobItem = {
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
    subject_names: string[];
    student_gender: string;
    tutor_gender: string;
    days_per_week: number | null;
    tuition_time: string | null;
    published_at: string | null;
    expires_at: string | null;
};

const props = withDefaults(
    defineProps<{
        job: JobItem;
        detailsBasePath?: string;
    }>(),
    {
        detailsBasePath: '/jobs',
    },
);

const page = usePage<{
    auth?: {
        user?: {
            role?: string;
        };
    };
}>();

const copied = ref(false);
const showApplyModal = ref(false);
const isTutor = computed(() => page.props.auth?.user?.role === 'tutor');
const isExpired = computed(() => {
    if (!props.job.expires_at) {
        return false;
    }

    return new Date(props.job.expires_at) < new Date();
});
const subjectPreview = computed(() => props.job.subject_names.slice(0, 3));
const remainingSubjectsCount = computed(() =>
    Math.max(props.job.subject_names.length - subjectPreview.value.length, 0),
);
const locationLabel = computed(() => {
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

function formatDate(dateStr: string | null): string {
    if (!dateStr) {
        return '';
    }

    const date = new Date(dateStr);

    return date.toLocaleDateString('en-GB', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    });
}

function formatSalary(item: JobItem): string {
    if (item.salary_amount) {
        return `${item.salary_amount} ${item.salary_currency || 'BDT'}`;
    }

    if (item.salary_negotiable) {
        return 'Negotiable';
    }

    return 'Negotiable';
}

function genderPreferenceLabel(code: string): string {
    switch (code) {
        case 'male':
            return 'Male Tutor Preferred';
        case 'female':
            return 'Female Tutor Preferred';
        default:
            return 'Any Tutor Gender Allowed';
    }
}

function genderPreferenceClass(code: string): string {
    switch (code) {
        case 'male':
            return 'border-blue-200 dark:border-blue-900/60 bg-blue-50 dark:bg-blue-950/40 text-blue-700 dark:text-blue-300';
        case 'female':
            return 'border-pink-200 dark:border-pink-900/60 bg-pink-50 dark:bg-pink-950/40 text-pink-700 dark:text-pink-300';
        default:
            return 'border-slate-200 dark:border-slate-800 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300';
    }
}

async function shareJob(): Promise<void> {
    const url = `${window.location.origin}${props.detailsBasePath}/${props.job.id}`;

    try {
        await navigator.clipboard.writeText(url);
    } catch {
        const input = document.createElement('input');

        input.value = url;
        document.body.appendChild(input);
        input.select();
        document.execCommand('copy');
        document.body.removeChild(input);
    }

    copied.value = true;

    setTimeout(() => {
        copied.value = false;
    }, 1800);
}

function openApplyModal(): void {
    showApplyModal.value = true;
}

function closeApplyModal(): void {
    showApplyModal.value = false;
    applicationForm.clearErrors();
}

function submitApplication(): void {
    applicationForm.post(`/tutor/jobs/${props.job.id}/apply`, {
        preserveScroll: true,
        onSuccess: () => {
            applicationForm.reset('cover_letter', 'expected_salary_amount');
            closeApplyModal();
        },
    });
}
</script>

<template>
    <article
        class="group relative flex h-full flex-col overflow-hidden rounded-3xl border border-slate-200/80 bg-white shadow-sm ring-1 ring-slate-900/5 transition-all duration-300 hover:-translate-y-1.5 hover:shadow-xl hover:ring-slate-900/10 dark:border-slate-800 dark:bg-slate-900 dark:ring-white/10"
    >
        <div
            class="absolute inset-x-0 top-0 h-1.5 bg-gradient-to-r from-blue-600 via-cyan-500 to-emerald-500"
        />

        <div class="p-5 sm:p-6">
            <div class="mb-4 flex items-start justify-between gap-2">
                <span
                    class="inline-flex items-center rounded-full border border-slate-200 bg-slate-100 px-2.5 py-1 text-[11px] font-semibold tracking-wide text-slate-600 uppercase dark:border-slate-800 dark:bg-slate-800 dark:text-slate-300"
                >
                    {{ job.tuition_type_name || 'Tuition' }}
                </span>

                <span
                    class="inline-flex items-center rounded-full border px-2.5 py-1 text-[11px] font-semibold"
                    :class="
                        isExpired
                            ? 'border-red-200 bg-red-50 text-red-700 dark:border-red-900/50 dark:bg-red-950/40 dark:text-red-300'
                            : 'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-900/50 dark:bg-emerald-950/40 dark:text-emerald-300'
                    "
                >
                    {{ isExpired ? 'Expired' : 'Open' }}
                </span>
            </div>

            <h3
                class="line-clamp-2 text-xl font-semibold tracking-tight text-slate-900 dark:text-slate-100"
            >
                {{ job.title }}
            </h3>

            <p
                class="mt-2 line-clamp-2 text-sm leading-relaxed text-slate-600 dark:text-slate-400"
            >
                {{ job.description || 'No description provided.' }}
            </p>

            <div
                class="mt-4 flex flex-wrap items-center gap-3 text-xs text-slate-500 dark:text-slate-400"
            >
                <span class="inline-flex items-center gap-1">
                    <Calendar class="h-3.5 w-3.5" />
                    Posted {{ formatDate(job.published_at) || 'Recently' }}
                </span>
                <span class="inline-flex items-center gap-1">
                    <MapPin class="h-3.5 w-3.5" />
                    {{ locationLabel }}
                </span>
            </div>

            <div class="mt-5 grid grid-cols-2 gap-3 text-sm">
                <div
                    class="rounded-xl border border-slate-200 bg-slate-50/70 p-3 dark:border-slate-800 dark:bg-slate-800/50"
                >
                    <p
                        class="text-[11px] font-medium tracking-wide text-slate-500 uppercase dark:text-slate-400"
                    >
                        Salary
                    </p>
                    <p
                        class="mt-1 font-semibold text-slate-900 dark:text-slate-100"
                    >
                        {{ formatSalary(job) }}
                    </p>
                </div>

                <div
                    class="rounded-xl border border-slate-200 bg-slate-50/70 p-3 dark:border-slate-800 dark:bg-slate-800/50"
                >
                    <p
                        class="text-[11px] font-medium tracking-wide text-slate-500 uppercase dark:text-slate-400"
                    >
                        Schedule
                    </p>
                    <p
                        class="mt-1 font-semibold text-slate-900 dark:text-slate-100"
                    >
                        {{
                            job.days_per_week
                                ? `${job.days_per_week} Days/Week`
                                : 'Flexible'
                        }}
                    </p>
                </div>

                <div
                    class="rounded-xl border border-slate-200 bg-slate-50/70 p-3 dark:border-slate-800 dark:bg-slate-800/50"
                >
                    <p
                        class="text-[11px] font-medium tracking-wide text-slate-500 uppercase dark:text-slate-400"
                    >
                        Category
                    </p>
                    <p
                        class="mt-1 font-semibold text-slate-900 dark:text-slate-100"
                    >
                        {{ job.category_name || 'General' }}
                    </p>
                </div>

                <div
                    class="rounded-xl border border-slate-200 bg-slate-50/70 p-3 dark:border-slate-800 dark:bg-slate-800/50"
                >
                    <p
                        class="text-[11px] font-medium tracking-wide text-slate-500 uppercase dark:text-slate-400"
                    >
                        Class
                    </p>
                    <p
                        class="mt-1 font-semibold text-slate-900 dark:text-slate-100"
                    >
                        {{ job.class_name || 'Not specified' }}
                    </p>
                </div>
            </div>

            <div class="mt-4 flex flex-wrap gap-2">
                <span
                    v-for="subject in subjectPreview"
                    :key="subject"
                    class="inline-flex items-center rounded-lg border border-blue-200 bg-blue-50 px-2.5 py-1 text-xs font-medium text-blue-700 dark:border-blue-900/60 dark:bg-blue-950/40 dark:text-blue-300"
                >
                    <BookOpen class="mr-1 h-3 w-3" />
                    {{ subject }}
                </span>
                <span
                    v-if="remainingSubjectsCount > 0"
                    class="inline-flex items-center rounded-lg border border-slate-200 bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-600 dark:border-slate-800 dark:bg-slate-800 dark:text-slate-300"
                >
                    +{{ remainingSubjectsCount }} more
                </span>
            </div>

            <div class="mt-4">
                <span
                    class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold"
                    :class="genderPreferenceClass(job.tutor_gender)"
                >
                    <UserRound class="mr-1.5 h-3.5 w-3.5" />
                    {{ genderPreferenceLabel(job.tutor_gender) }}
                </span>
            </div>
        </div>

        <div
            class="mt-auto border-t border-slate-200/70 p-4 sm:p-5 dark:border-slate-800"
        >
            <div
                class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between"
            >
                <Link
                    :href="`${props.detailsBasePath}/${job.id}`"
                    class="inline-flex h-10 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700 transition-colors hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700"
                >
                    View Details
                </Link>

                <div class="flex items-center gap-2">
                    <Button
                        v-if="isTutor && !isExpired"
                        size="sm"
                        class="h-10 rounded-xl bg-blue-600 px-4 text-white hover:bg-blue-700"
                        @click="openApplyModal"
                    >
                        Apply
                    </Button>

                    <button
                        class="inline-flex h-10 items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 text-sm font-medium text-slate-600 transition-colors hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700"
                        @click="shareJob"
                    >
                        <Share2 class="h-4 w-4" />
                        <span>{{ copied ? 'Copied!' : 'Share' }}</span>
                    </button>
                </div>
            </div>
        </div>
    </article>

    <Dialog :open="showApplyModal" @update:open="showApplyModal = $event">
        <DialogContent
            class="sm:max-w-lg dark:border-slate-800 dark:bg-slate-900"
        >
            <DialogHeader>
                <DialogTitle class="text-slate-900 dark:text-slate-100"
                    >Apply for {{ job.title }}</DialogTitle
                >
                <DialogDescription class="text-slate-500 dark:text-slate-400">
                    Submit your application details below. The job poster will
                    review your application.
                </DialogDescription>
            </DialogHeader>

            <form class="space-y-4" @submit.prevent="submitApplication">
                <div>
                    <label
                        class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-300"
                    >
                        Cover Letter / Note
                    </label>
                    <textarea
                        v-model="applicationForm.cover_letter"
                        rows="4"
                        class="w-full rounded-xl border border-slate-300 bg-white p-3 text-sm text-slate-900 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100"
                        placeholder="Explain why you are a good fit for this tuition..."
                    ></textarea>
                    <p
                        v-if="applicationForm.errors.cover_letter"
                        class="mt-1 text-xs text-red-600 dark:text-red-400"
                    >
                        {{ applicationForm.errors.cover_letter }}
                    </p>
                </div>

                <div>
                    <label
                        class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-300"
                    >
                        Expected Monthly Salary (Optional)
                    </label>
                    <input
                        v-model="applicationForm.expected_salary_amount"
                        type="number"
                        min="0"
                        step="100"
                        class="w-full rounded-xl border border-slate-300 bg-white p-3 text-sm text-slate-900 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100"
                        placeholder="e.g. 5000"
                    />
                    <p
                        v-if="applicationForm.errors.expected_salary_amount"
                        class="mt-1 text-xs text-red-600 dark:text-red-400"
                    >
                        {{ applicationForm.errors.expected_salary_amount }}
                    </p>
                </div>

                <DialogFooter>
                    <Button
                        type="button"
                        variant="outline"
                        class="dark:border-slate-700 dark:text-slate-300"
                        @click="closeApplyModal"
                    >
                        Cancel
                    </Button>

                    <Button
                        type="submit"
                        class="bg-blue-600 text-white hover:bg-blue-700"
                        :disabled="applicationForm.processing"
                    >
                        {{
                            applicationForm.processing
                                ? 'Submitting...'
                                : 'Submit Application'
                        }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
