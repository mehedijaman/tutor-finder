<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

type PaginationLink = {
    url: string | null;
    label: string;
    active: boolean;
};

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
    jobs: {
        data: JobItem[];
        links: PaginationLink[];
    };
    filters: {
        q: string;
        category: string;
        tuition_type: string;
        subject_id: number | null;
        city_id: number | null;
        min_salary: number | null;
        max_salary: number | null;
        sort: string;
    };
    categoryOptions: Array<{ id: number; name: string; slug: string }>;
    tuitionTypeOptions: Array<{ id: number; name: string; slug: string }>;
    subjectOptions: Array<{ id: number; name: string }>;
    cityOptions: Array<{ id: number; name: string }>;
    sortOptions: Array<{ value: string; label: string }>;
}>();

const query = ref(props.filters.q ?? '');
const category = ref(props.filters.category ?? '');
const tuitionType = ref(props.filters.tuition_type ?? '');
const subjectId = ref(props.filters.subject_id ? String(props.filters.subject_id) : '');
const cityId = ref(props.filters.city_id ? String(props.filters.city_id) : '');
const minSalary = ref(props.filters.min_salary != null ? String(props.filters.min_salary) : '');
const maxSalary = ref(props.filters.max_salary != null ? String(props.filters.max_salary) : '');
const sort = ref(props.filters.sort ?? 'newest');

function applyFilters(): void {
    router.get(
        '/jobs',
        {
            q: query.value,
            category: category.value,
            tuition_type: tuitionType.value,
            subject_id: subjectId.value,
            city_id: cityId.value,
            min_salary: minSalary.value,
            max_salary: maxSalary.value,
            sort: sort.value,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
}

function resetFilters(): void {
    query.value = '';
    category.value = '';
    tuitionType.value = '';
    subjectId.value = '';
    cityId.value = '';
    minSalary.value = '';
    maxSalary.value = '';
    sort.value = 'newest';

    applyFilters();
}

function formatPaginationLabel(label: string): string {
    return String(label ?? '')
        .replaceAll('&laquo;', '«')
        .replaceAll('&raquo;', '»')
        .replace(/<[^>]*>/g, '')
        .trim();
}

function salaryLabel(job: JobItem): string {
    if (job.salary_negotiable) {
        return 'Negotiable';
    }

    if (!job.salary_amount) {
        return 'Not specified';
    }

    return `${job.salary_currency ?? ''} ${job.salary_amount}`.trim();
}
</script>

<template>
    <Head title="Tutor Job Board" />

    <div class="min-h-screen bg-slate-50 p-6">
        <div class="mx-auto max-w-7xl space-y-6">
            <div class="space-y-2">
                <h1 class="text-3xl font-bold">Tutor Job Board</h1>
                <p class="text-sm text-muted-foreground">Discover active tuition opportunities and apply after reviewing details.</p>
            </div>

            <form class="grid gap-3 rounded-xl border bg-white p-4 lg:grid-cols-8" @submit.prevent="applyFilters">
                <input v-model="query" type="text" placeholder="Search jobs" class="h-10 rounded-md border px-3 text-sm lg:col-span-2" />

                <select v-model="category" class="h-10 rounded-md border px-3 text-sm">
                    <option value="">All categories</option>
                    <option v-for="item in categoryOptions" :key="item.id" :value="item.slug">{{ item.name }}</option>
                </select>

                <select v-model="tuitionType" class="h-10 rounded-md border px-3 text-sm">
                    <option value="">All tuition types</option>
                    <option v-for="item in tuitionTypeOptions" :key="item.id" :value="item.slug">{{ item.name }}</option>
                </select>

                <select v-model="subjectId" class="h-10 rounded-md border px-3 text-sm">
                    <option value="">All subjects</option>
                    <option v-for="item in subjectOptions" :key="item.id" :value="String(item.id)">{{ item.name }}</option>
                </select>

                <select v-model="cityId" class="h-10 rounded-md border px-3 text-sm">
                    <option value="">All cities</option>
                    <option v-for="item in cityOptions" :key="item.id" :value="String(item.id)">{{ item.name }}</option>
                </select>

                <div class="grid grid-cols-2 gap-2">
                    <input v-model="minSalary" type="number" min="0" placeholder="Min salary" class="h-10 rounded-md border px-3 text-sm" />
                    <input v-model="maxSalary" type="number" min="0" placeholder="Max salary" class="h-10 rounded-md border px-3 text-sm" />
                </div>

                <div class="flex gap-2 lg:col-span-8">
                    <select v-model="sort" class="h-10 rounded-md border px-3 text-sm">
                        <option v-for="option in sortOptions" :key="option.value" :value="option.value">{{ option.label }}</option>
                    </select>

                    <button type="submit" class="inline-flex h-10 items-center justify-center rounded-md bg-black px-4 text-sm text-white">
                        Apply Filters
                    </button>

                    <button type="button" class="inline-flex h-10 items-center justify-center rounded-md border px-4 text-sm" @click="resetFilters">
                        Reset
                    </button>
                </div>
            </form>

            <div v-if="jobs.data.length === 0" class="rounded-xl border bg-white p-10 text-center text-sm text-muted-foreground">
                No live jobs found for the current filters.
            </div>

            <div v-else class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                <article v-for="job in jobs.data" :key="job.id" class="flex h-full flex-col rounded-xl border bg-white p-5">
                    <div class="space-y-2">
                        <h2 class="line-clamp-2 text-lg font-semibold">{{ job.title }}</h2>
                        <p class="text-xs text-muted-foreground">
                            {{ job.city_name || 'Unknown city' }}
                            <span v-if="job.area_name">, {{ job.area_name }}</span>
                            <span v-if="job.country_name">, {{ job.country_name }}</span>
                        </p>
                    </div>

                    <div class="mt-3 flex flex-wrap gap-2 text-xs">
                        <span v-if="job.category_name" class="rounded-full border px-2 py-0.5">{{ job.category_name }}</span>
                        <span v-if="job.class_name" class="rounded-full border px-2 py-0.5">{{ job.class_name }}</span>
                        <span v-if="job.tuition_type_name" class="rounded-full border px-2 py-0.5">{{ job.tuition_type_name }}</span>
                    </div>

                    <p class="mt-4 line-clamp-3 text-sm text-muted-foreground">{{ job.description }}</p>

                    <div class="mt-4 space-y-1 text-sm text-slate-700">
                        <p><span class="font-medium">Salary:</span> {{ salaryLabel(job) }}</p>
                        <p><span class="font-medium">Days/Week:</span> {{ job.days_per_week ?? '—' }}</p>
                        <p><span class="font-medium">Time:</span> {{ job.tuition_time || 'Not specified' }}</p>
                    </div>

                    <div class="mt-4 flex flex-wrap gap-1 text-xs text-muted-foreground">
                        <span v-for="name in job.subject_names" :key="`${job.id}-${name}`" class="rounded bg-slate-100 px-2 py-0.5">{{ name }}</span>
                    </div>

                    <div class="mt-5 flex items-center justify-between pt-2 text-xs text-muted-foreground">
                        <span>{{ job.published_at ? new Date(job.published_at).toLocaleDateString() : '—' }}</span>
                        <Link :href="`/jobs/${job.slug}`" class="font-semibold text-blue-600 hover:underline">View Details</Link>
                    </div>
                </article>
            </div>

            <div v-if="jobs.links && jobs.links.length > 3" class="flex flex-wrap items-center justify-center gap-2">
                <template v-for="(link, index) in jobs.links" :key="`${index}-${link.label}`">
                    <span v-if="!link.url" class="inline-flex h-8 items-center justify-center rounded-md border px-3 text-xs text-muted-foreground">
                        {{ formatPaginationLabel(link.label) }}
                    </span>
                    <Link
                        v-else
                        :href="link.url"
                        preserve-scroll
                        class="inline-flex h-8 items-center justify-center rounded-md border px-3 text-xs"
                        :class="link.active ? 'bg-black text-white' : 'bg-white hover:bg-muted'"
                    >
                        {{ formatPaginationLabel(link.label) }}
                    </Link>
                </template>
            </div>
        </div>
    </div>
</template>
