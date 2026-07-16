<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    Briefcase,
    Calendar,
    CheckCircle2,
    GraduationCap,
    MapPin,
    Plus,
    Search,
    User,
    Users,
} from 'lucide-vue-next';
import { computed, onBeforeUnmount, ref, watch } from 'vue';
import DataTable from '@/components/admin/table/DataTable.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import GuardianLayout from '@/layouts/GuardianLayout.vue';

const props = defineProps({
    items: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
    statusOptions: { type: Array, default: () => [] },
});

type JobStatusOption = {
    value: string;
    label: string;
};

type GuardianJobRow = {
    id: number;
    title: string;
    slug: string;
    status: string;
    is_expired?: boolean;
    open_applications_count?: number;
    applications_count?: number;
    has_assignment?: boolean;
    selected_tutor_name?: string | null;
    hiring_confirmed_at?: string | null;
    requested_tutor_id?: number | null;
    category_name?: string;
    class_name?: string;
    city_name?: string;
    area_name?: string;
    published_at?: string;
    expires_at?: string;
};

const breadcrumbs = [{ title: 'My Jobs', href: '/guardian/jobs' }];
const presetStatus = computed(() => props.filters.preset_status || '');
const baseUrl = computed(() => {
    const status = presetStatus.value;
    if (
        ['pending', 'live', 'confirmed', 'cancelled', 'closed'].includes(status)
    ) {
        return `/guardian/jobs/${status}`;
    }
    return '/guardian/jobs';
});

const columns = [
    { key: 'title', label: 'Job Details' },
    { key: 'category_info', label: 'Type & Class' },
    { key: 'location', label: 'Location' },
    { key: 'engagement', label: 'Engagement' },
    { key: 'hiring_status', label: 'Hiring Outcome' },
    { key: 'status', label: 'Status' },
];

const search = ref(props.filters.q ?? '');
const statusFilter = ref(props.filters.status || 'all');
let searchDebounceTimer: any = null;

const statusOptionsList = computed<JobStatusOption[]>(
    () => (props.statusOptions as JobStatusOption[] | undefined) ?? [],
);

watch(
    () => props.filters.q,
    (value) => {
        const normalized = value ?? '';
        if (search.value !== normalized) {
            search.value = normalized;
        }
    },
);

watch(search, (value) => {
    if (searchDebounceTimer) clearTimeout(searchDebounceTimer);
    searchDebounceTimer = setTimeout(() => {
        applyFilters({ q: value, page: 1 });
    }, 350);
});

watch(statusFilter, (value) => {
    if (presetStatus.value) return;
    applyFilters({ status: value === 'all' ? '' : value, page: 1 });
});

onBeforeUnmount(() => {
    if (searchDebounceTimer) clearTimeout(searchDebounceTimer);
});

function applyFilters(overrides = {}) {
    router.get(
        baseUrl.value,
        {
            q: search.value,
            status: presetStatus.value
                ? ''
                : statusFilter.value === 'all'
                  ? ''
                  : statusFilter.value,
            ...overrides,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
}

function statusBadge(row: GuardianJobRow) {
    if (row.status === 'live' && row.is_expired) {
        return {
            label: 'Expired',
            variant: 'destructive',
            class: 'bg-rose-50 text-rose-700 border-rose-100',
        };
    }
    if (row.status === 'live') {
        return {
            label: 'Live',
            variant: 'default',
            class: 'bg-emerald-50 text-emerald-700 border-emerald-100',
        };
    }
    if (row.status === 'pending') {
        return {
            label: 'Pending',
            variant: 'secondary',
            class: 'bg-amber-50 text-amber-700 border-amber-100',
        };
    }
    if (row.status === 'confirmed') {
        return {
            label: 'Confirmed',
            variant: 'outline',
            class: 'bg-indigo-50 text-indigo-700 border-indigo-100',
        };
    }
    return {
        label: row.status,
        variant: 'secondary',
        class: 'bg-slate-50 text-slate-700 border-slate-100',
    };
}
</script>

<template>
    <Head title="My Jobs" />

    <GuardianLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-8 p-4 sm:p-6 lg:p-8">
            <!-- Header Section -->
            <div class="flex flex-wrap items-center justify-between gap-6 pb-2">
                <div class="space-y-1">
                    <div class="flex items-center gap-3">
                        <div
                            class="rounded-xl bg-indigo-600 p-2 text-white shadow-lg shadow-indigo-200"
                        >
                            <Briefcase class="h-6 w-6" />
                        </div>
                        <h1
                            class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl"
                        >
                            My Job Postings
                        </h1>
                    </div>
                    <p class="pl-11 text-sm text-slate-500">
                        Manage your tuition requirements and connect with the
                        best tutors.
                    </p>
                </div>

                <Button
                    as-child
                    class="h-auto rounded-xl bg-indigo-600 px-6 py-2.5 text-sm font-bold text-white shadow-lg shadow-indigo-200 transition-all hover:bg-indigo-700 hover:shadow-indigo-300 active:scale-95"
                >
                    <Link
                        href="/guardian/jobs/create"
                        class="flex items-center gap-2"
                    >
                        <Plus class="h-5 w-5" />
                        Post New Job
                    </Link>
                </Button>
            </div>

            <!-- Filters Section -->
            <div
                class="flex flex-wrap items-center gap-4 rounded-3xl border border-slate-200/80 bg-white/50 p-4 shadow-sm backdrop-blur-sm"
            >
                <div class="relative min-w-0 flex-1">
                    <Search
                        class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-slate-400"
                    />
                    <Input
                        v-model="search"
                        type="text"
                        placeholder="Search by title or job details..."
                        class="h-11 rounded-2xl border-slate-200 bg-white/80 pl-10 transition-all focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5"
                    />
                </div>

                <Select v-if="!presetStatus" v-model="statusFilter">
                    <SelectTrigger
                        class="h-11 w-full rounded-2xl border-slate-200 bg-white/80 sm:w-[180px]"
                    >
                        <SelectValue placeholder="All Statuses" />
                    </SelectTrigger>
                    <SelectContent
                        class="rounded-2xl border-slate-200 shadow-xl"
                    >
                        <SelectItem value="all" class="rounded-lg"
                            >All Statuses</SelectItem
                        >
                        <SelectItem
                            v-for="status in statusOptionsList"
                            :key="status.value"
                            :value="status.value"
                            class="rounded-lg"
                        >
                            {{ status.label }}
                        </SelectItem>
                    </SelectContent>
                </Select>
            </div>

            <!-- Table Section -->
            <div
                class="overflow-hidden rounded-3xl border border-slate-200/80 bg-white shadow-xl shadow-slate-200/40"
            >
                <DataTable
                    :items="items"
                    :columns="columns"
                    empty-text="You haven't posted any jobs matching these filters."
                    class="border-none"
                    row-class="group hover:bg-slate-50/80 transition-colors"
                >
                    <!-- Job Details -->
                    <template #cell-title="{ row }">
                        <div class="flex flex-col gap-1 pb-1">
                            <Link
                                :href="`/guardian/jobs/${row.id}`"
                                class="line-clamp-1 font-bold text-slate-900 transition-colors group-hover:text-indigo-600"
                            >
                                {{ row.title }}
                            </Link>
                            <div class="flex items-center gap-2">
                                <span
                                    class="text-[10px] font-bold tracking-tight text-slate-400 uppercase"
                                    >ID: #{{ row.id }}</span
                                >
                                <Badge
                                    v-if="row.requested_tutor_id"
                                    variant="outline"
                                    class="h-4 border-indigo-200 bg-indigo-50 px-1.5 text-[10px] text-indigo-700"
                                >
                                    Direct Request
                                </Badge>
                            </div>
                        </div>
                    </template>

                    <!-- Type & Class -->
                    <template #cell-category_info="{ row }">
                        <div class="flex flex-col gap-0.5">
                            <span
                                class="flex items-center gap-1 text-sm font-bold text-slate-800"
                            >
                                <GraduationCap
                                    class="h-3.5 w-3.5 text-slate-400"
                                />
                                {{ row.category_name }}
                            </span>
                            <span
                                class="ml-4.5 text-xs font-medium text-slate-500"
                                >{{ row.class_name }}</span
                            >
                        </div>
                    </template>

                    <!-- Location -->
                    <template #cell-location="{ row }">
                        <div class="flex flex-col gap-0.5">
                            <span
                                class="flex items-center gap-1 pr-4 text-sm font-semibold text-slate-800"
                            >
                                <MapPin class="h-3.5 w-3.5 text-slate-400" />
                                {{ row.area_name }}
                            </span>
                            <span
                                class="ml-4.5 text-xs font-medium text-slate-500"
                                >{{ row.city_name }}</span
                            >
                        </div>
                    </template>

                    <!-- Engagement -->
                    <template #cell-engagement="{ row }">
                        <Link
                            :href="`/guardian/jobs/${row.id}/applications`"
                            class="group/link flex flex-col gap-1"
                        >
                            <div class="flex items-center gap-2">
                                <div class="flex -space-x-1.5 overflow-hidden">
                                    <div
                                        v-for="i in Math.min(
                                            3,
                                            row.applications_count || 0,
                                        )"
                                        :key="i"
                                        class="flex inline-block h-6 w-6 items-center justify-center rounded-full bg-slate-100 ring-2 ring-white"
                                    >
                                        <User class="h-3 w-3 text-slate-400" />
                                    </div>
                                </div>
                                <span
                                    class="text-sm font-black text-indigo-600 group-hover/link:underline"
                                >
                                    {{ row.applications_count ?? 0 }}
                                </span>
                            </div>
                            <span
                                class="flex items-center gap-1 text-[10px] font-bold tracking-tight text-slate-400 uppercase"
                            >
                                <Users class="h-3 w-3" />
                                {{ row.open_applications_count ?? 0 }} Pending
                                Review
                            </span>
                        </Link>
                    </template>

                    <!-- Hiring Outcome -->
                    <template #cell-hiring_status="{ row }">
                        <div v-if="row.has_assignment" class="space-y-1">
                            <div class="flex items-center gap-1.5">
                                <div
                                    class="flex h-6 w-6 items-center justify-center rounded-full bg-emerald-100"
                                >
                                    <CheckCircle2
                                        class="h-3.5 w-3.5 text-emerald-600"
                                    />
                                </div>
                                <span
                                    class="text-sm font-bold text-slate-800"
                                    >{{
                                        row.selected_tutor_name ||
                                        'Tutor Assigned'
                                    }}</span
                                >
                            </div>
                            <p
                                v-if="row.hiring_confirmed_at"
                                class="flex items-center gap-1 pl-7 text-[10px] text-slate-400"
                            >
                                <Calendar class="h-3 w-3" />
                                Confirmed on
                                {{
                                    new Date(
                                        row.hiring_confirmed_at,
                                    ).toLocaleDateString()
                                }}
                            </p>
                        </div>
                        <div v-else class="flex flex-col gap-1">
                            <div class="flex items-center gap-2">
                                <div
                                    class="h-2 w-2 animate-pulse rounded-full bg-indigo-500"
                                ></div>
                                <span
                                    class="text-xs font-bold tracking-wider text-slate-700 uppercase"
                                    >Recruiting</span
                                >
                            </div>
                            <p class="pl-4 text-[10px] text-slate-400">
                                Reviewing matches...
                            </p>
                        </div>
                    </template>

                    <!-- Status -->
                    <template #cell-status="{ row }">
                        <Badge
                            :variant="statusBadge(row).variant as any"
                            :class="
                                statusBadge(row).class +
                                ' h-6 rounded-lg border px-2.5 text-[11px] font-bold tracking-wide uppercase'
                            "
                        >
                            {{ statusBadge(row).label }}
                        </Badge>
                    </template>

                    <!-- Dates row was here but removed per columns, adding it back if needed, but columns say title, category, location, engagement, hiring, status -->
                </DataTable>
            </div>
        </div>
    </GuardianLayout>
</template>
