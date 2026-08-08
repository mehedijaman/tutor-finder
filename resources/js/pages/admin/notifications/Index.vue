<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import DataTable from '@/components/admin/table/DataTable.vue';
import RowActionsDropdown from '@/components/admin/table/RowActionsDropdown.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import AdminLayout from '@/layouts/AdminLayout.vue';

const props = defineProps({
    items: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
    counts: { type: Object, default: () => ({}) },
});

const breadcrumbs = [{ title: 'Notifications', href: '/admin/notifications' }];
const baseUrl = '/admin/notifications';
const statusFilter = ref(props.filters.status || 'all');

const columns = [
    { key: 'title', label: 'Title' },
    { key: 'message', label: 'Message' },
    { key: 'event', label: 'Event' },
    { key: 'created_at', label: 'Created At' },
    { key: 'read_at', label: 'Read' },
    { key: 'actions', label: 'Actions', cellClass: 'w-[1%] whitespace-nowrap' },
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
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
});

function markAllAsRead() {
    router.patch(
        '/admin/notifications/read-all',
        {},
        {
            preserveScroll: true,
        },
    );
}

function actionItems(row: Record<string, unknown>) {
    return [
        {
            key: 'open',
            label: 'Open',
            show: !!row.url,
        },
        {
            key: 'mark-read',
            label: 'Mark as Read',
            show: !row.read_at,
        },
    ];
}

function onAction(action: string, row: Record<string, unknown>) {
    if (action === 'open' && row.url) {
        if (!row.read_at) {
            router.patch(
                `/admin/notifications/${row.id}/read`,
                {},
                {
                    preserveScroll: true,
                    onSuccess: () => {
                        router.visit(row.url as string);
                    },
                },
            );
        } else {
            router.visit(row.url as string);
        }

        return;
    }

    if (action === 'mark-read') {
        router.patch(
            `/admin/notifications/${row.id}/read`,
            {},
            {
                preserveScroll: true,
            },
        );
    }
}
</script>

<template>
    <Head title="Notifications" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-4 sm:p-6 lg:p-8">
            <div
                class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-6"
            >
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div class="space-y-1">
                        <h1
                            class="text-2xl font-semibold tracking-tight sm:text-3xl"
                        >
                            Notifications
                        </h1>
                        <p class="text-sm text-muted-foreground">
                            Unread: {{ counts.unread ?? 0 }} | Total:
                            {{ counts.all ?? 0 }}
                        </p>
                    </div>

                    <Button
                        variant="outline"
                        :disabled="(counts.unread ?? 0) === 0"
                        @click="markAllAsRead"
                    >
                        Mark All as Read
                    </Button>
                </div>
            </div>

            <div
                v-if="$page.props.flash?.status"
                class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800"
            >
                {{ $page.props.flash.status }}
            </div>

            <div
                class="grid gap-3 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:grid-cols-2 lg:grid-cols-3"
            >
                <Select v-model="statusFilter">
                    <SelectTrigger>
                        <SelectValue placeholder="All status" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="all">All</SelectItem>
                        <SelectItem value="unread">Unread</SelectItem>
                        <SelectItem value="read">Read</SelectItem>
                    </SelectContent>
                </Select>
            </div>

            <DataTable
                :items="items"
                :columns="columns"
                empty-text="No notifications found."
            >
                <template #cell-message="{ value }">
                    <p
                        class="line-clamp-2 max-w-xl text-sm text-muted-foreground"
                    >
                        {{ value || '—' }}
                    </p>
                </template>

                <template #cell-event="{ value }">
                    <Badge variant="outline">{{ value }}</Badge>
                </template>

                <template #cell-created_at="{ value }">{{
                    value ? new Date(value).toLocaleString() : '—'
                }}</template>

                <template #cell-read_at="{ value }">
                    <Badge :variant="value ? 'secondary' : 'default'">
                        {{ value ? 'Read' : 'Unread' }}
                    </Badge>
                </template>

                <template #cell-actions="{ row }">
                    <RowActionsDropdown
                        :actions="actionItems(row)"
                        @select="(action: string) => onAction(action, row)"
                    />
                </template>
            </DataTable>
        </div>
    </AdminLayout>
</template>
