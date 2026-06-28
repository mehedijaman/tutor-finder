<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import DataTable from '@/components/admin/table/DataTable.vue';
import TicketPriorityBadge from '@/components/support/TicketPriorityBadge.vue';
import TicketStatusBadge from '@/components/support/TicketStatusBadge.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import AdminLayout from '@/layouts/AdminLayout.vue';
import type { SelectOption } from '@/types';
import type { TicketRow } from '@/types/support';

interface AdminUser {
    id: number;
    name: string;
}

const props = defineProps<{
    items: {
        data: TicketRow[];
        links: object[];
        current_page: number;
        last_page: number;
    };
    filters: {
        q?: string;
        status?: string;
        priority?: string;
        category?: string;
        assigned_to?: string;
        sort?: string;
        direction?: string;
    };
    counts: {
        all: number;
        open: number;
        in_progress: number;
        closed: number;
    };
    priorityOptions: SelectOption[];
    categoryOptions: SelectOption[];
    statusOptions: SelectOption[];
    adminUsers: AdminUser[];
}>();

const breadcrumbs = [
    { title: 'Support Tickets', href: '/admin/support-tickets' },
];

const baseUrl = '/admin/support-tickets';
const search = ref(props.filters.q || '');
const statusFilter = ref(props.filters.status || 'all');
const priorityFilter = ref(props.filters.priority || 'all');
const categoryFilter = ref(props.filters.category || 'all');
const assignedFilter = ref(props.filters.assigned_to || 'all');

const columns = [
    { key: 'ticket_number', label: 'Ticket #', sortable: true },
    { key: 'subject', label: 'Subject' },
    { key: 'user_name', label: 'User' },
    { key: 'category', label: 'Category' },
    { key: 'priority', label: 'Priority', sortable: true },
    { key: 'status', label: 'Status', sortable: true },
    { key: 'assigned_admin_name', label: 'Assigned To' },
    { key: 'created_at', label: 'Created', sortable: true },
    {
        key: 'actions',
        label: '',
        cellClass: 'w-[1%] whitespace-nowrap',
    },
];

let debounceTimer: ReturnType<typeof setTimeout>;

watch(
    () => props.filters,
    (value) => {
        search.value = value.q || '';
        statusFilter.value = value.status || 'all';
        priorityFilter.value = value.priority || 'all';
        categoryFilter.value = value.category || 'all';
        assignedFilter.value = value.assigned_to || 'all';
    },
);

watch(search, () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => applyFilters(), 350);
});

watch([statusFilter, priorityFilter, categoryFilter, assignedFilter], () => {
    applyFilters();
});

function applyFilters(): void {
    const params: Record<string, string> = {};
    if (search.value) {
        params.q = search.value;
    }
    if (statusFilter.value && statusFilter.value !== 'all') {
        params.status = statusFilter.value;
    }
    if (priorityFilter.value && priorityFilter.value !== 'all') {
        params.priority = priorityFilter.value;
    }
    if (categoryFilter.value && categoryFilter.value !== 'all') {
        params.category = categoryFilter.value;
    }
    if (assignedFilter.value && assignedFilter.value !== 'all') {
        params.assigned_to = assignedFilter.value;
    }

    router.get(baseUrl, params, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
}

function handleSort(column: string): void {
    const isSameColumn = props.filters.sort === column;
    const direction =
        isSameColumn && props.filters.direction === 'asc' ? 'desc' : 'asc';

    router.get(
        baseUrl,
        {
            ...props.filters,
            sort: column,
            direction,
        },
        { preserveState: true, preserveScroll: true, replace: true },
    );
}

function formatCategory(category: string): string {
    return category.charAt(0).toUpperCase() + category.slice(1);
}
</script>

<template>
    <Head title="Support Tickets" />

    <AdminLayout :breadcrumbs="breadcrumbs">
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
                </div>
            </div>

            <div
                v-if="$page.props.flash?.status"
                class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800"
            >
                {{ $page.props.flash.status }}
            </div>

            <div
                class="grid gap-3 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm md:grid-cols-2 lg:grid-cols-3"
            >
                <div class="grid gap-2">
                    <Label for="ticket-search">Search</Label>
                    <Input
                        id="ticket-search"
                        v-model="search"
                        type="text"
                        placeholder="Search by ticket #, subject, or user"
                    />
                </div>

                <div class="grid gap-2">
                    <Label>Status</Label>
                    <Select v-model="statusFilter">
                        <SelectTrigger>
                            <SelectValue placeholder="All status" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">All Status</SelectItem>
                            <SelectItem
                                v-for="opt in statusOptions"
                                :key="opt.value"
                                :value="opt.value"
                            >
                                {{ opt.label }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <div class="grid gap-2">
                    <Label>Priority</Label>
                    <Select v-model="priorityFilter">
                        <SelectTrigger>
                            <SelectValue placeholder="All priorities" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">
                                All Priorities
                            </SelectItem>
                            <SelectItem
                                v-for="opt in priorityOptions"
                                :key="opt.value"
                                :value="opt.value"
                            >
                                {{ opt.label }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <div class="grid gap-2">
                    <Label>Category</Label>
                    <Select v-model="categoryFilter">
                        <SelectTrigger>
                            <SelectValue placeholder="All categories" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">
                                All Categories
                            </SelectItem>
                            <SelectItem
                                v-for="opt in categoryOptions"
                                :key="opt.value"
                                :value="opt.value"
                            >
                                {{ opt.label }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <div class="grid gap-2">
                    <Label>Assigned To</Label>
                    <Select v-model="assignedFilter">
                        <SelectTrigger>
                            <SelectValue placeholder="All admins" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">All Admins</SelectItem>
                            <SelectItem value="unassigned">
                                Unassigned
                            </SelectItem>
                            <SelectItem
                                v-for="admin in adminUsers"
                                :key="admin.id"
                                :value="String(admin.id)"
                            >
                                {{ admin.name }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </div>
            </div>

            <DataTable
                :items="items"
                :columns="columns"
                :sort-by="filters.sort"
                :sort-direction="filters.direction"
                empty-text="No support tickets found."
                @sort="handleSort"
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

                <template #cell-user_name="{ value }">
                    {{ value ?? '—' }}
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

                <template #cell-assigned_admin_name="{ value }">
                    <span :class="value ? '' : 'text-slate-400'">
                        {{ value ?? 'Unassigned' }}
                    </span>
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
    </AdminLayout>
</template>
