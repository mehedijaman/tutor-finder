<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import DataTable from '@/components/admin/table/DataTable.vue';
import TicketPriorityBadge from '@/components/support/TicketPriorityBadge.vue';
import TicketStatusBadge from '@/components/support/TicketStatusBadge.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import TutorLayout from '@/layouts/TutorLayout.vue';
import type { TicketRow } from '@/types/support';

const props = defineProps<{
    items: {
        data: TicketRow[];
        links: object[];
        current_page: number;
        last_page: number;
    };
    filters: {
        status?: string;
    };
    counts: {
        all: number;
        open: number;
        in_progress: number;
        closed: number;
    };
}>();

const breadcrumbs = [
    { title: 'Support Tickets', href: '/tutor/support-tickets' },
];

const baseUrl = '/tutor/support-tickets';
const statusFilter = ref(props.filters.status || 'all');

const columns = [
    { key: 'ticket_number', label: 'Ticket #' },
    { key: 'subject', label: 'Subject' },
    { key: 'category', label: 'Category' },
    { key: 'priority', label: 'Priority' },
    { key: 'status', label: 'Status' },
    { key: 'created_at', label: 'Created' },
    {
        key: 'actions',
        label: '',
        cellClass: 'w-[1%] whitespace-nowrap',
    },
];

watch(
    () => props.filters.status,
    (value) => {
        const normalized = value || 'all';
        if (statusFilter.value !== normalized) {
            statusFilter.value = normalized;
        }
    },
);

watch(statusFilter, (value) => {
    router.get(
        baseUrl,
        { status: value === 'all' ? '' : value },
        { preserveState: true, preserveScroll: true, replace: true },
    );
});

function formatCategory(category: string): string {
    return category.charAt(0).toUpperCase() + category.slice(1);
}
</script>

<template>
    <Head title="Support Tickets" />

    <TutorLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-4 sm:p-6">
            <div
                class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6"
            >
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div class="space-y-1">
                        <h1 class="text-2xl font-semibold tracking-tight">
                            Support Tickets
                        </h1>
                        <p class="text-sm text-muted-foreground">
                            Open: {{ counts.open ?? 0 }} | In Progress:
                            {{ counts.in_progress ?? 0 }} | Closed:
                            {{ counts.closed ?? 0 }} | Total:
                            {{ counts.all ?? 0 }}
                        </p>
                    </div>
                    <Link :href="`${baseUrl}/create`">
                        <Button>New Ticket</Button>
                    </Link>
                </div>
            </div>

            <div
                v-if="$page.props.flash?.status"
                class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800"
            >
                {{ $page.props.flash.status }}
            </div>

            <div
                class="grid gap-3 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm"
            >
                <div class="grid gap-2">
                    <Select v-model="statusFilter">
                        <SelectTrigger>
                            <SelectValue placeholder="All status" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">All Status</SelectItem>
                            <SelectItem value="open">Open</SelectItem>
                            <SelectItem value="in_progress">
                                In Progress
                            </SelectItem>
                            <SelectItem value="closed">Closed</SelectItem>
                        </SelectContent>
                    </Select>
                </div>
            </div>

            <DataTable
                :items="items"
                :columns="columns"
                empty-text="No support tickets found."
            >
                <template #cell-ticket_number="{ value }">
                    <span class="font-mono text-xs">{{ value }}</span>
                </template>

                <template #cell-subject="{ row }">
                    <Link
                        :href="`${baseUrl}/${row.id}`"
                        class="font-medium text-blue-600 hover:underline"
                    >
                        {{ row.subject }}
                    </Link>
                </template>

                <template #cell-category="{ value }">
                    <Badge variant="outline">
                        {{ formatCategory(value) }}
                    </Badge>
                </template>

                <template #cell-priority="{ value }">
                    <TicketPriorityBadge :priority="value" />
                </template>

                <template #cell-status="{ value }">
                    <TicketStatusBadge :status="value" />
                </template>

                <template #cell-created_at="{ value }">
                    {{ value ? new Date(value).toLocaleDateString() : '—' }}
                </template>

                <template #cell-actions="{ row }">
                    <Link :href="`${baseUrl}/${row.id}`">
                        <Button variant="outline" size="sm">View</Button>
                    </Link>
                </template>
            </DataTable>
        </div>
    </TutorLayout>
</template>
