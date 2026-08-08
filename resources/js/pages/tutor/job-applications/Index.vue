<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    CircleCheck,
    CircleX,
    Clock,
    MapPin,
    Send,
    Star,
    UserRound,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';
import ConfirmDialog from '@/components/admin/dialogs/ConfirmDialog.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import TutorLayout from '@/layouts/TutorLayout.vue';

const props = defineProps({
    items: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
    statusCounts: { type: Object, default: () => ({}) },
});

const breadcrumbs = [
    { title: 'My Applications', href: '/tutor/job-applications' },
];

const statusMenus = [
    {
        key: 'all',
        label: 'All',
        href: '/tutor/job-applications',
        icon: Send,
    },
    {
        key: 'shortlisted',
        label: 'Shortlisted',
        href: '/tutor/job-applications/shortlisted',
        icon: Star,
    },
    {
        key: 'appointed',
        label: 'Appointed',
        href: '/tutor/job-applications/appointed',
        icon: UserRound,
    },
    {
        key: 'confirmed',
        label: 'Confirmed',
        href: '/tutor/job-applications/confirmed',
        icon: CircleCheck,
    },
    {
        key: 'cancelled',
        label: 'Canceled',
        href: '/tutor/job-applications/cancelled',
        icon: CircleX,
    },
];

const presetStatus = computed(() => props.filters.preset_status || '');
const appliedCount = computed(() => {
    const value = props.statusCounts?.applied;

    if (typeof value === 'number') {
        return value;
    }

    return 0;
});

const rows = computed(() => props.items?.data ?? []);
const links = computed(() => props.items?.links ?? []);
const currentPage = computed(() => props.items?.current_page ?? 1);
const lastPage = computed(() => props.items?.last_page ?? 1);
const hasPagination = computed(() => links.value.length > 3);
const previousLink = computed(() => links.value[0] ?? null);
const nextLink = computed(() =>
    links.value.length > 0 ? links.value[links.value.length - 1] : null,
);

const confirmOpen = ref(false);
const pendingRow = ref<any>(null);

function statusBadgeClass(status: string): string {
    const map: Record<string, string> = {
        confirmed:
            'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400',
        shortlisted:
            'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-400',
        appointed:
            'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-400',
        applied:
            'bg-slate-100 text-slate-700 dark:bg-slate-700/40 dark:text-slate-400',
        cancelled:
            'bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-400',
    };

    return (
        map[status] ??
        'bg-slate-100 text-slate-700 dark:bg-slate-700/40 dark:text-slate-400'
    );
}

function jobStatusBadgeClass(status: string): string {
    const map: Record<string, string> = {
        live: 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-900/50',
        pending:
            'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-900/50',
        confirmed:
            'bg-indigo-50 text-indigo-700 border-indigo-200 dark:bg-indigo-950/40 dark:text-indigo-300 dark:border-indigo-900/50',
        cancelled:
            'bg-slate-50 text-slate-600 border-slate-200 dark:bg-slate-800 dark:text-slate-400 dark:border-slate-700',
        closed: 'bg-slate-50 text-slate-500 border-slate-200 dark:bg-slate-800 dark:text-slate-400 dark:border-slate-700',
    };

    return (
        map[status] ??
        'bg-slate-50 text-slate-600 border-slate-200 dark:bg-slate-800 dark:text-slate-400 dark:border-slate-700'
    );
}

function handleViewJob(id: number): void {
    router.visit(`/jobs/${id}`);
}

function handleWithdraw(row: any): void {
    pendingRow.value = row;
    confirmOpen.value = true;
}

function confirmWithdraw(): void {
    if (!pendingRow.value) {
        return;
    }

    router.patch(
        `/tutor/job-applications/${pendingRow.value.id}/withdraw`,
        {},
        {
            preserveScroll: true,
            onFinish: () => {
                pendingRow.value = null;
            },
        },
    );

    confirmOpen.value = false;
}

function closeConfirm(): void {
    confirmOpen.value = false;
    pendingRow.value = null;
}

function formatCount(count: number): string {
    return String(count).padStart(2, '0');
}

function menuCount(key: string): number {
    const value = props.statusCounts?.[key];

    if (typeof value === 'number') {
        return value;
    }

    return 0;
}

function formatDate(dateString: string): string {
    if (!dateString) {
        return '—';
    }

    return new Date(dateString).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
}

function formatPaginationLabel(label: string): string {
    return String(label ?? '')
        .replaceAll('&laquo;', '«')
        .replaceAll('&raquo;', '»')
        .replace(/<[^>]*>/g, '')
        .trim();
}
</script>

<template>
    <Head title="My Applications" />

    <TutorLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-4 sm:p-6 lg:p-8">
            <!-- Page Header -->
            <div
                class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6 dark:border-slate-800 dark:bg-slate-900"
            >
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h1
                            class="text-2xl font-semibold tracking-tight text-slate-900 sm:text-3xl dark:text-slate-100"
                        >
                            My Applications
                        </h1>
                        <p class="mt-1 text-sm text-muted-foreground">
                            Track job applications and guardian responses from
                            one timeline.
                        </p>
                    </div>

                    <Link
                        href="/jobs"
                        class="inline-flex items-center rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700"
                        >Browse Jobs</Link
                    >
                </div>
            </div>

            <!-- Flash Message -->
            <div
                v-if="($page.props.flash as any)?.status"
                class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-900/60 dark:bg-emerald-950/40 dark:text-emerald-300"
            >
                {{ ($page.props.flash as any).status }}
            </div>

            <!-- Status Tabs -->
            <div
                class="rounded-2xl border border-slate-200/80 bg-slate-50/60 px-4 shadow-sm dark:border-slate-800 dark:bg-slate-900/60"
            >
                <div
                    class="flex flex-wrap items-center justify-between gap-3 border-b border-blue-200/80 dark:border-slate-800"
                >
                    <div
                        class="w-full [scrollbar-width:none] overflow-x-auto [-ms-overflow-style:none] md:w-auto"
                    >
                        <div
                            class="flex min-w-max items-center gap-6 pr-2 [&::-webkit-scrollbar]:hidden"
                        >
                            <Link
                                v-for="menu in statusMenus"
                                :key="menu.key"
                                :href="menu.href"
                                class="inline-flex items-center gap-1.5 border-b-2 py-3 text-sm font-medium transition"
                                :class="
                                    (menu.key === 'all' &&
                                        presetStatus === '') ||
                                    presetStatus === menu.key
                                        ? 'border-blue-500 text-blue-500 dark:text-blue-400'
                                        : 'border-transparent text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200'
                                "
                            >
                                <component :is="menu.icon" class="h-4 w-4" />
                                <span>{{ menu.label }}</span>
                                <span>{{
                                    formatCount(menuCount(menu.key))
                                }}</span>
                            </Link>
                        </div>
                    </div>

                    <p
                        class="hidden pb-3 text-sm font-medium text-slate-600 md:block dark:text-slate-400"
                    >
                        Applied {{ formatCount(appliedCount) }}
                    </p>
                </div>
            </div>

            <!-- Application Cards -->
            <div
                v-if="rows.length"
                class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3"
            >
                <div
                    v-for="row in rows"
                    :key="row.id"
                    class="group relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md dark:border-slate-800 dark:bg-slate-900"
                >
                    <!-- Card Header -->
                    <div
                        class="border-b border-slate-100 p-5 dark:border-slate-800"
                    >
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0 flex-1">
                                <Link
                                    v-if="row.job?.id"
                                    :href="`/jobs/${row.job.id}`"
                                    class="line-clamp-2 text-base font-semibold text-slate-900 transition-colors hover:text-blue-600 dark:text-slate-100 dark:hover:text-blue-400"
                                >
                                    {{ row.job.title }}
                                </Link>
                                <p
                                    v-else
                                    class="line-clamp-2 text-base font-semibold text-slate-900 dark:text-slate-100"
                                >
                                    {{ row.job.title }}
                                </p>
                                <div
                                    class="mt-1.5 flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400"
                                >
                                    <MapPin class="h-3.5 w-3.5 shrink-0" />
                                    <span>{{
                                        row.job.city_name || 'Unknown city'
                                    }}</span>
                                </div>
                            </div>
                            <Badge
                                :class="
                                    statusBadgeClass(row.status) +
                                    ' shrink-0 rounded-lg border px-2.5 py-0.5 text-[11px] font-bold uppercase'
                                "
                            >
                                {{ row.status }}
                            </Badge>
                        </div>
                    </div>

                    <!-- Card Body -->
                    <div class="space-y-3 p-5">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-slate-500 dark:text-slate-400"
                                >Job Status</span
                            >
                            <Badge
                                variant="outline"
                                :class="
                                    jobStatusBadgeClass(row.job.status) +
                                    ' rounded-md border px-2 py-0.5 text-[11px] font-semibold uppercase'
                                "
                            >
                                {{ row.job.status }}
                            </Badge>
                        </div>

                        <div class="flex items-center justify-between text-sm">
                            <span class="text-slate-500 dark:text-slate-400"
                                >Expected Salary</span
                            >
                            <span
                                class="font-medium text-slate-700 dark:text-slate-300"
                            >
                                {{
                                    row.expected_salary_amount
                                        ? `${row.salary_currency || 'BDT'} ${Number(row.expected_salary_amount).toLocaleString('en-BD', { maximumFractionDigits: 0 })}`
                                        : '—'
                                }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between text-sm">
                            <span class="text-slate-500 dark:text-slate-400"
                                >Applied</span
                            >
                            <span
                                class="flex items-center gap-1.5 text-slate-600 dark:text-slate-400"
                            >
                                <Clock
                                    class="h-3.5 w-3.5 text-slate-400 dark:text-slate-500"
                                />
                                {{ formatDate(row.created_at) }}
                            </span>
                        </div>

                        <!-- Subject & CV Download -->
                        <div
                            class="flex items-center justify-between border-t border-slate-100 pt-2 text-xs dark:border-slate-800"
                        >
                            <div class="min-w-0 flex-1">
                                <span
                                    class="block text-[10px] font-semibold tracking-wider text-slate-400 uppercase dark:text-slate-500"
                                    >Subject</span
                                >
                                <span
                                    class="font-bold text-slate-800 dark:text-slate-200"
                                >
                                    {{
                                        row.job?.subject_names?.length
                                            ? row.job.subject_names.join(', ')
                                            : 'All Subjects'
                                    }}
                                </span>
                            </div>
                            <a
                                v-if="row.download_cv_url"
                                :href="row.download_cv_url"
                                target="_blank"
                                class="inline-flex items-center gap-1 rounded-lg border border-blue-100 bg-blue-50 px-2.5 py-1 font-bold text-blue-600 shadow-2xs hover:text-blue-700 hover:underline dark:border-blue-900/60 dark:bg-blue-950/40 dark:text-blue-300 dark:hover:text-blue-200"
                            >
                                <span>CV View</span>
                                <span>↓</span>
                            </a>
                        </div>

                        <div
                            v-if="row.cancel_reason"
                            class="rounded-lg bg-rose-50 p-3 text-xs text-rose-700 dark:bg-rose-950/40 dark:text-rose-300"
                        >
                            <span class="font-semibold">Cancel reason:</span>
                            {{ row.cancel_reason }}
                        </div>
                    </div>

                    <!-- Card Footer -->
                    <div
                        class="flex items-center gap-2 border-t border-slate-100 px-5 py-3 dark:border-slate-800"
                    >
                        <Button
                            v-if="row.job?.id"
                            variant="ghost"
                            size="sm"
                            class="h-8 flex-1 text-xs font-medium dark:text-slate-300 dark:hover:bg-slate-800"
                            @click="handleViewJob(row.job.id)"
                        >
                            View Job
                        </Button>
                        <Button
                            v-if="
                                ['applied', 'shortlisted'].includes(row.status)
                            "
                            variant="ghost"
                            size="sm"
                            class="h-8 flex-1 text-xs font-medium text-rose-600 hover:bg-rose-50 hover:text-rose-700 dark:text-rose-400 dark:hover:bg-rose-950/40 dark:hover:text-rose-300"
                            @click="handleWithdraw(row)"
                        >
                            Cancel
                        </Button>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div
                v-else
                class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-slate-200 bg-slate-50/50 px-6 py-16 text-center dark:border-slate-800 dark:bg-slate-900/50"
            >
                <div
                    class="flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 dark:bg-slate-800"
                >
                    <Send class="h-7 w-7 text-slate-400 dark:text-slate-500" />
                </div>
                <h3
                    class="mt-4 text-base font-semibold text-slate-700 dark:text-slate-200"
                >
                    No applications found
                </h3>
                <p
                    class="mt-1.5 max-w-sm text-sm text-slate-500 dark:text-slate-400"
                >
                    You haven't applied to any jobs yet. Browse available
                    tutoring opportunities to get started.
                </p>
                <Link
                    href="/jobs"
                    class="mt-5 inline-flex items-center rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700"
                >
                    Browse Jobs
                </Link>
            </div>

            <!-- Pagination -->
            <div v-if="hasPagination" class="flex items-center justify-between">
                <span
                    v-if="items.from && items.to && items.total"
                    class="text-xs text-slate-500 dark:text-slate-400"
                >
                    Showing {{ items.from }}–{{ items.to }} of
                    {{ items.total }}
                </span>

                <div class="ml-auto flex items-center gap-1.5">
                    <template
                        v-for="(link, index) in links"
                        :key="`${index}-${link.label}`"
                    >
                        <Button
                            v-if="!link.url"
                            variant="outline"
                            size="sm"
                            disabled
                            class="h-8 min-w-8 px-2.5 text-xs dark:border-slate-800 dark:text-slate-500"
                        >
                            {{ formatPaginationLabel(link.label) }}
                        </Button>

                        <Link
                            v-else
                            :href="link.url"
                            preserve-scroll
                            class="inline-flex h-8 min-w-8 items-center justify-center rounded-lg border px-2.5 text-xs font-medium transition-colors"
                            :class="
                                link.active
                                    ? 'border-blue-500 bg-blue-50 text-blue-700 shadow-sm dark:bg-blue-950/40 dark:text-blue-300'
                                    : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800'
                            "
                            :aria-current="link.active ? 'page' : undefined"
                        >
                            {{ formatPaginationLabel(link.label) }}
                        </Link>
                    </template>
                </div>
            </div>
        </div>
    </TutorLayout>

    <ConfirmDialog
        v-model:open="confirmOpen"
        title="Cancel Application"
        description="This will cancel your application for this job."
        confirm-label="Cancel Application"
        :destructive="true"
        @confirm="confirmWithdraw"
        @cancel="closeConfirm"
    />
</template>
