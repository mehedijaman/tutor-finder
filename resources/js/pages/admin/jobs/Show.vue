<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    ArrowLeft,
    BadgeCheck,
    Briefcase,
    CalendarDays,
    CheckCircle2,
    Clock,
    Edit,
    Eye,
    FileText,
    MapPin,
    RefreshCw,
    Users,
    Wallet,
    X,
} from 'lucide-vue-next';
import { computed } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';

function handleReopen(): void {
    if (confirm('Are you sure you want to re-open this job to Live status?')) {
        router.patch(`/admin/jobs/${props.job.id}/reopen`, {}, { preserveScroll: true });
    }
}
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import AdminLayout from '@/layouts/AdminLayout.vue';

const props = defineProps<{
    job: Record<string, any>;
}>();

const breadcrumbs = [
    { title: 'Jobs', href: '/admin/jobs' },
    { title: `Job #${props.job.id}`, href: '#' },
];

const statusConfig = computed(() => {
    const s = props.job.status?.value ?? props.job.status;
    if (props.job.is_expired) {
        return { label: 'Expired', class: 'bg-rose-50 text-rose-700 border-rose-200' };
    }
    const map: Record<string, { label: string; class: string }> = {
        live: { label: 'Live', class: 'bg-emerald-50 text-emerald-700 border-emerald-200' },
        pending: { label: 'Pending', class: 'bg-amber-50 text-amber-700 border-amber-200' },
        confirmed: { label: 'Confirmed', class: 'bg-indigo-50 text-indigo-700 border-indigo-200' },
        cancelled: { label: 'Cancelled', class: 'bg-slate-50 text-slate-500 border-slate-200' },
        closed: { label: 'Closed', class: 'bg-slate-50 text-slate-500 border-slate-200' },
    };
    return map[s] ?? { label: s, class: 'bg-slate-50 text-slate-500 border-slate-200' };
});

function formatDate(d: string | null | undefined): string {
    if (!d) { return '—'; }
    return new Date(d).toLocaleDateString('en-GB', {
        day: '2-digit', month: 'short', year: 'numeric',
    });
}

function formatGender(g: string | null | undefined): string {
    if (!g) { return '—'; }
    const map: Record<string, string> = { male: 'Male', female: 'Female', any: 'Any' };
    return map[g?.toLowerCase?.() ?? ''] ?? g;
}
</script>

<template>
    <Head :title="`Job #${job.id} — ${job.title}`" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-4 sm:p-6 lg:p-8">
            <!-- Header -->
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div class="space-y-1">
                    <div class="flex flex-wrap items-center gap-3">
                        <div class="rounded-xl bg-blue-600 p-2 text-white shadow-lg shadow-blue-200">
                            <Briefcase class="h-5 w-5" />
                        </div>
                        <h1 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">
                            {{ job.title }}
                        </h1>
                        <Badge :class="`border px-2.5 py-1 text-xs font-bold uppercase tracking-wide ${statusConfig.class}`">
                            {{ statusConfig.label }}
                        </Badge>
                    </div>
                    <p class="pl-12 text-sm text-slate-400">
                        Job ID: <span class="font-mono font-bold text-slate-600">{{ job.id }}</span>
                        <span v-if="job.created_by_name"> · Created by {{ job.created_by_name }}</span>
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <Link
                        :href="`/admin/jobs/${job.id}/applications`"
                        class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50"
                    >
                        <Users class="h-4 w-4" />
                        Applications
                        <Badge variant="secondary" class="ml-1 bg-blue-50 text-blue-600">
                            {{ job.applications_count ?? 0 }}
                        </Badge>
                    </Link>
                    <Button
                        v-if="['cancelled', 'closed'].includes(job.status)"
                        type="button"
                        class="inline-flex items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm font-bold text-emerald-700 shadow-sm transition hover:bg-emerald-100"
                        @click="handleReopen"
                    >
                        <RefreshCw class="h-4 w-4" />
                        Re-open Job
                    </Button>
                    <Link
                        :href="`/admin/jobs/${job.id}/edit`"
                        class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2 text-sm font-bold text-white shadow-lg shadow-blue-200 transition hover:bg-blue-700"
                    >
                        <Edit class="h-4 w-4" />
                        Edit Job
                    </Link>
                    <Link
                        href="/admin/jobs"
                        class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-500 shadow-sm transition hover:bg-slate-50"
                    >
                        <ArrowLeft class="h-4 w-4" />
                        Back
                    </Link>
                </div>
            </div>

            <!-- Stats Row -->
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                <Card class="border-blue-100 bg-blue-50/40">
                    <CardContent class="flex items-center gap-3 p-4">
                        <Eye class="h-5 w-5 text-blue-500" />
                        <div>
                            <p class="text-xl font-black text-blue-700">{{ job.view_count ?? 0 }}</p>
                            <p class="text-xs font-semibold text-blue-500/80">Views</p>
                        </div>
                    </CardContent>
                </Card>
                <Card class="border-violet-100 bg-violet-50/40">
                    <CardContent class="flex items-center gap-3 p-4">
                        <Users class="h-5 w-5 text-violet-500" />
                        <div>
                            <p class="text-xl font-black text-violet-700">{{ job.applications_count ?? 0 }}</p>
                            <p class="text-xs font-semibold text-violet-500/80">Applications</p>
                        </div>
                    </CardContent>
                </Card>
                <Card class="border-emerald-100 bg-emerald-50/40">
                    <CardContent class="flex items-center gap-3 p-4">
                        <Wallet class="h-5 w-5 text-emerald-500" />
                        <div>
                            <p class="text-xl font-black text-emerald-700">
                                {{ job.salary_negotiable ? 'Negotiable' : `${job.salary_currency} ${job.salary_amount ?? '—'}` }}
                            </p>
                            <p class="text-xs font-semibold text-emerald-500/80">Salary</p>
                        </div>
                    </CardContent>
                </Card>
                <Card class="border-amber-100 bg-amber-50/40">
                    <CardContent class="flex items-center gap-3 p-4">
                        <CalendarDays class="h-5 w-5 text-amber-500" />
                        <div>
                            <p class="text-xl font-black text-amber-700">{{ job.days_per_week ?? '—' }}</p>
                            <p class="text-xs font-semibold text-amber-500/80">Days/Week</p>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <div class="grid gap-6 lg:grid-cols-3">
                <!-- Left Column -->
                <div class="space-y-6 lg:col-span-2">
                    <!-- Course Details -->
                    <Card>
                        <CardHeader class="pb-3">
                            <CardTitle class="flex items-center gap-2 text-base">
                                <FileText class="h-4 w-4 text-blue-500" />
                                Course Details
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <p class="text-xs font-semibold text-slate-400 uppercase">Type</p>
                                <p class="font-semibold text-slate-800">{{ job.tuition_type ?? '—' }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold text-slate-400 uppercase">Category</p>
                                <p class="font-semibold text-slate-800">{{ job.category ?? '—' }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold text-slate-400 uppercase">Class</p>
                                <p class="font-semibold text-slate-800">{{ job.class ?? '—' }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold text-slate-400 uppercase">Subjects</p>
                                <div class="flex flex-wrap gap-1 pt-1">
                                    <Badge
                                        v-for="subject in job.subjects"
                                        :key="subject"
                                        variant="secondary"
                                        class="bg-blue-50 text-blue-700 text-xs"
                                    >
                                        {{ subject }}
                                    </Badge>
                                    <span v-if="!job.subjects?.length" class="text-slate-400">—</span>
                                </div>
                            </div>
                            <div>
                                <p class="text-xs font-semibold text-slate-400 uppercase">Student Gender</p>
                                <p class="font-semibold text-slate-800">{{ formatGender(job.student_gender?.value ?? job.student_gender) }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold text-slate-400 uppercase">Preferred Tutor Gender</p>
                                <p class="font-semibold text-slate-800">{{ formatGender(job.tutor_gender?.value ?? job.tutor_gender) }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold text-slate-400 uppercase">No. of Students</p>
                                <p class="font-semibold text-slate-800">{{ job.no_of_students ?? '—' }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold text-slate-400 uppercase">Tuition Days</p>
                                <p class="font-semibold text-slate-800 capitalize">
                                    {{ job.tuition_days?.join(', ') || '—' }}
                                </p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold text-slate-400 uppercase">Time</p>
                                <p class="font-semibold text-slate-800">{{ job.tuition_time ?? '—' }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold text-slate-400 uppercase">Duration</p>
                                <p class="font-semibold text-slate-800">{{ job.tuition_duration ?? '—' }}</p>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Location -->
                    <Card>
                        <CardHeader class="pb-3">
                            <CardTitle class="flex items-center gap-2 text-base">
                                <MapPin class="h-4 w-4 text-rose-500" />
                                Location
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <p class="text-xs font-semibold text-slate-400 uppercase">Country</p>
                                <p class="font-semibold text-slate-800">{{ job.country ?? '—' }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold text-slate-400 uppercase">City</p>
                                <p class="font-semibold text-slate-800">{{ job.city ?? '—' }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold text-slate-400 uppercase">Area</p>
                                <p class="font-semibold text-slate-800">{{ job.area ?? '—' }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold text-slate-400 uppercase">Address</p>
                                <p class="font-semibold text-slate-800">{{ job.location ?? '—' }}</p>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Description -->
                    <Card v-if="job.description">
                        <CardHeader class="pb-3">
                            <CardTitle class="text-base">Description / Requirements</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <p class="whitespace-pre-wrap text-sm leading-relaxed text-slate-700">{{ job.description }}</p>
                        </CardContent>
                    </Card>

                    <!-- Cancellation Reason -->
                    <Card v-if="job.cancellation_reason" class="border-rose-200 bg-rose-50/40">
                        <CardHeader class="pb-3">
                            <CardTitle class="flex items-center gap-2 text-base text-rose-700">
                                <X class="h-4 w-4" />
                                Cancellation Reason
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <p class="text-sm text-rose-800">{{ job.cancellation_reason }}</p>
                        </CardContent>
                    </Card>
                </div>

                <!-- Right Column -->
                <div class="space-y-6">
                    <!-- Guardian Info -->
                    <Card>
                        <CardHeader class="pb-3">
                            <CardTitle class="flex items-center gap-2 text-base">
                                <Users class="h-4 w-4 text-violet-500" />
                                Guardian
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-3 text-sm">
                            <div>
                                <p class="text-xs font-semibold text-slate-400 uppercase">Name</p>
                                <Link
                                    :href="`/admin/guardians/${job.guardian_id}`"
                                    class="font-semibold text-blue-600 hover:underline"
                                >
                                    {{ job.guardian_name ?? '—' }}
                                </Link>
                            </div>
                            <div>
                                <p class="text-xs font-semibold text-slate-400 uppercase">Phone</p>
                                <p class="font-semibold text-slate-800">{{ job.guardian_phone ?? '—' }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold text-slate-400 uppercase">Email</p>
                                <p class="font-semibold text-slate-800">{{ job.guardian_email ?? '—' }}</p>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Assigned Tutor -->
                    <Card v-if="job.has_assignment" class="border-emerald-200 bg-emerald-50/30">
                        <CardHeader class="pb-3">
                            <CardTitle class="flex items-center gap-2 text-base text-emerald-700">
                                <BadgeCheck class="h-4 w-4" />
                                Assigned Tutor
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-3 text-sm">
                            <div>
                                <p class="text-xs font-semibold text-emerald-500/80 uppercase">Name</p>
                                <Link
                                    :href="`/admin/tutors/${job.selected_tutor_id}`"
                                    class="font-semibold text-blue-600 hover:underline"
                                >
                                    {{ job.selected_tutor_name ?? '—' }}
                                </Link>
                            </div>
                            <div>
                                <p class="text-xs font-semibold text-emerald-500/80 uppercase">Phone</p>
                                <p class="font-semibold text-slate-800">{{ job.selected_tutor_phone ?? '—' }}</p>
                            </div>
                            <div v-if="job.assignment_appointed_at">
                                <p class="text-xs font-semibold text-emerald-500/80 uppercase">Appointed At</p>
                                <p class="font-semibold text-slate-800">{{ formatDate(job.assignment_appointed_at) }}</p>
                            </div>
                            <div v-if="job.assignment_confirmed_at">
                                <p class="text-xs font-semibold text-emerald-500/80 uppercase">Confirmed At</p>
                                <p class="font-semibold text-slate-800">{{ formatDate(job.assignment_confirmed_at) }}</p>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Workflow Timestamps -->
                    <Card>
                        <CardHeader class="pb-3">
                            <CardTitle class="flex items-center gap-2 text-base">
                                <Clock class="h-4 w-4 text-amber-500" />
                                Workflow Timestamps
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-3 text-sm">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-semibold text-slate-400 uppercase">Created</span>
                                <span class="font-semibold text-slate-700">{{ formatDate(job.created_at) }}</span>
                            </div>
                            <Separator />
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-semibold text-slate-400 uppercase">Published</span>
                                <span class="font-semibold text-slate-700">{{ formatDate(job.published_at) }}</span>
                            </div>
                            <Separator />
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-semibold text-slate-400 uppercase">Expires</span>
                                <span
                                    class="font-semibold"
                                    :class="job.is_expired ? 'text-rose-600' : 'text-slate-700'"
                                >
                                    {{ formatDate(job.expires_at) }}
                                </span>
                            </div>
                            <Separator v-if="job.confirmed_at" />
                            <div v-if="job.confirmed_at" class="flex items-center justify-between">
                                <span class="text-xs font-semibold text-slate-400 uppercase">Confirmed</span>
                                <span class="font-semibold text-emerald-600">{{ formatDate(job.confirmed_at) }}</span>
                            </div>
                            <Separator v-if="job.confirmed_by_name" />
                            <div v-if="job.confirmed_by_name">
                                <span class="text-xs font-semibold text-slate-400 uppercase">Confirmed By</span>
                                <p class="mt-0.5 font-semibold text-slate-700">{{ job.confirmed_by_name }}</p>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Quick Actions -->
                    <Card>
                        <CardHeader class="pb-3">
                            <CardTitle class="text-base">Quick Actions</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-2">
                            <Link
                                :href="`/admin/jobs/${job.id}/applications`"
                                class="flex w-full items-center justify-center gap-2 rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                            >
                                <Users class="h-4 w-4" />
                                Manage Applications
                            </Link>
                            <Link
                                :href="`/admin/jobs/${job.id}/edit`"
                                class="flex w-full items-center justify-center gap-2 rounded-xl border border-blue-200 bg-blue-50 px-4 py-2.5 text-sm font-semibold text-blue-700 transition hover:bg-blue-100"
                            >
                                <Edit class="h-4 w-4" />
                                Edit Job
                            </Link>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
