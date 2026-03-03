<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    MapPin,
    Wallet,
    Calendar,
    BookOpen,
    Clock,
    Share2,
    Filter,
} from 'lucide-vue-next';
import { ref } from 'vue';

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
    job: JobItem;
}>();

const copied = ref(false);

function formatDate(dateStr: string | null): string {
    if (!dateStr) return '';
    const date = new Date(dateStr);
    return date.toLocaleDateString('en-GB', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    });
}

function formatSalary(job: JobItem): string {
    if (job.salary_negotiable) return 'Negotiable';
    if (!job.salary_amount) return 'Not specified';
    return `৳ ${parseInt(job.salary_amount).toLocaleString()}`;
}

function getGenderClass(gender: string): string {
    const g = gender.toLowerCase();
    if (g === 'female') return 'text-rose-600 bg-rose-50 border-rose-200';
    if (g === 'male') return 'text-indigo-600 bg-indigo-50 border-indigo-200';
    return 'text-slate-600 bg-slate-50 border-slate-200';
}

function getGenderLabel(gender: string): string {
    const g = gender.toLowerCase();
    if (g === 'female') return 'Female tutor preferred';
    if (g === 'male') return 'Male tutor preferred';
    return 'Any tutor preferred';
}

async function shareJob(): Promise<void> {
    const url = `${window.location.origin}/jobs/${props.job.slug}`;
    try {
        await navigator.clipboard.writeText(url);
        copied.value = true;
        setTimeout(() => {
            copied.value = false;
        }, 2000);
    } catch {
        // Fallback
        const input = document.createElement('input');
        input.value = url;
        document.body.appendChild(input);
        input.select();
        document.execCommand('copy');
        document.body.removeChild(input);
        copied.value = true;
        setTimeout(() => {
            copied.value = false;
        }, 2000);
    }
}
</script>

<template>
    <article
        class="group relative rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition-all duration-200 hover:-translate-y-1 hover:shadow-md"
    >
        <!-- Decorative gradient blob -->
        <div
            class="pointer-events-none absolute top-0 right-0 hidden h-full w-32 opacity-5 md:block"
        >
            <div
                class="h-full w-full rounded-l-3xl bg-gradient-to-b from-blue-400 to-purple-500 blur-2xl"
            ></div>
        </div>

        <!-- Header -->
        <div class="relative">
            <h3
                class="line-clamp-2 text-lg font-semibold text-slate-900 md:text-xl"
            >
                {{ job.title }}
            </h3>
            <div
                class="mt-2 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-slate-500"
            >
                <span>Job ID: #{{ job.id }}</span>
                <span class="hidden sm:inline">•</span>
                <span class="flex items-center gap-1">
                    <Calendar class="h-3 w-3" />
                    Posted {{ formatDate(job.published_at) }}
                </span>
            </div>
        </div>

        <!-- Key Facts Row -->
        <div class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-3">
            <!-- Tuition Type -->
            <div class="flex items-center gap-2 text-sm">
                <div
                    class="flex h-8 w-8 items-center justify-center rounded-lg bg-purple-50"
                >
                    <Clock class="h-4 w-4 text-purple-600" />
                </div>
                <div>
                    <p class="text-xs text-slate-500">Type</p>
                    <p class="font-medium text-slate-800">
                        {{ job.tuition_type_name || '—' }}
                    </p>
                </div>
            </div>

            <!-- Salary -->
            <div class="flex items-center gap-2 text-sm">
                <div
                    class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-50"
                >
                    <Wallet class="h-4 w-4 text-emerald-600" />
                </div>
                <div>
                    <p class="text-xs text-slate-500">Salary</p>
                    <p class="font-medium text-slate-800">
                        {{ formatSalary(job) }}
                    </p>
                </div>
            </div>

            <!-- Subjects -->
            <div class="flex items-center gap-2 text-sm">
                <div
                    class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-50"
                >
                    <BookOpen class="h-4 w-4 text-blue-600" />
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-xs text-slate-500">Subjects</p>
                    <p class="line-clamp-1 truncate font-medium text-slate-800">
                        {{ job.subject_names.slice(0, 2).join(', ') || '—' }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Location -->
        <div class="mt-4 flex items-center gap-2 text-sm text-slate-600">
            <MapPin class="h-4 w-4 text-slate-400" />
            <span>
                {{ job.area_name || job.city_name || 'Location not specified' }}
                <span
                    v-if="job.area_name && job.city_name"
                    class="text-slate-400"
                    >, {{ job.city_name }}</span
                >
            </span>
        </div>

        <!-- Tutor Gender Preference -->
        <div class="mt-4">
            <span
                class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-medium"
                :class="getGenderClass(job.tutor_gender)"
            >
                {{ getGenderLabel(job.tutor_gender) }}
            </span>
        </div>

        <!-- Footer Actions -->
        <div
            class="mt-6 flex items-center justify-between border-t border-slate-100 pt-4"
        >
            <Link
                :href="`/jobs/${job.slug}`"
                class="text-sm font-medium text-blue-600 transition-colors hover:text-blue-700"
            >
                View Details
            </Link>
            <button
                @click="shareJob"
                class="flex items-center gap-1.5 text-sm text-slate-500 transition-colors hover:text-slate-700"
            >
                <Share2 class="h-4 w-4" />
                <span v-if="copied">Copied!</span>
                <span v-else>Share</span>
            </button>
        </div>
    </article>
</template>
