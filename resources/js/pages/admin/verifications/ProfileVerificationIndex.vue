<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, ref, watch } from 'vue';
import DataTable from '@/components/admin/table/DataTable.vue';
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

const props = defineProps({
    items: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
    roleOptions: {
        type: Array,
        default: () => [],
    },
    bucket: {
        type: String,
        required: true,
    },
    title: {
        type: String,
        required: true,
    },
    description: {
        type: String,
        required: true,
    },
});

const breadcrumbs = [
    {
        title: 'Profile Verification',
        href: '/admin/profile-verification/pending',
    },
];

const columns = [
    { key: 'name', label: 'User' },
    { key: 'role', label: 'Role' },
    { key: 'verification_status', label: 'Verification' },
    { key: 'request_status', label: 'Request Status' },
    { key: 'submitted_at', label: 'Submitted At' },
    { key: 'invoice_status', label: 'Invoice Status' },
    { key: 'actions', label: 'Actions', cellClass: 'w-[1%] whitespace-nowrap' },
];

const search = ref(props.filters.q ?? '');
const roleFilter = ref(props.filters.role || 'all');
let searchTimer = null;

const baseUrl = computed(() => {
    if (props.bucket === 'unverified') {
        return '/admin/profile-verification/unverified';
    }

    if (props.bucket === 'verified') {
        return '/admin/profile-verification/verified';
    }

    return '/admin/profile-verification/pending';
});

watch(
    () => props.filters.q,
    (value) => {
        const normalized = value ?? '';

        if (normalized !== search.value) {
            search.value = normalized;
        }
    },
);

watch(
    () => props.filters.role,
    (value) => {
        const normalized = value || 'all';

        if (normalized !== roleFilter.value) {
            roleFilter.value = normalized;
        }
    },
);

watch(search, (value) => {
    if (searchTimer) {
        clearTimeout(searchTimer);
    }

    searchTimer = setTimeout(() => {
        applyFilters({ q: value, page: 1 });
    }, 350);
});

watch(roleFilter, (value) => {
    applyFilters({ role: value === 'all' ? '' : value, page: 1 });
});

onBeforeUnmount(() => {
    if (searchTimer) {
        clearTimeout(searchTimer);
    }
});

function applyFilters(overrides = {}) {
    router.get(
        baseUrl.value,
        {
            q: search.value,
            role: roleFilter.value === 'all' ? '' : roleFilter.value,
            ...overrides,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
}

function verificationBadgeVariant(status) {
    if (status === 'verified') {
        return 'default';
    }

    if (status === 'unverified') {
        return 'secondary';
    }

    return 'outline';
}

function manageUrl(row) {
    if (row.request_id) {
        return `/admin/verifications/${row.request_id}`;
    }

    return row.role === 'tutor'
        ? `/admin/tutors/${row.id}/edit`
        : `/admin/guardians/${row.id}/edit`;
}

function actionLabel(row) {
    return row.request_id ? 'View Request' : 'View Profile';
}
</script>

<template>
    <Head :title="title" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-4 p-6">
            <div class="space-y-1">
                <h1 class="text-2xl font-semibold">{{ title }}</h1>
                <p class="text-sm text-muted-foreground">{{ description }}</p>
            </div>

            <div
                v-if="$page.props.flash?.status"
                class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800"
            >
                {{ $page.props.flash.status }}
            </div>

            <div
                class="grid gap-3 rounded-xl border bg-white p-4 md:grid-cols-3"
            >
                <div class="grid gap-2 md:col-span-2">
                    <Label for="verification-search">Search</Label>
                    <Input
                        id="verification-search"
                        v-model="search"
                        type="text"
                        placeholder="Search by user name, email, or phone"
                    />
                </div>

                <div class="grid gap-2">
                    <Label>Role</Label>
                    <Select v-model="roleFilter">
                        <SelectTrigger>
                            <SelectValue placeholder="All roles" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">All roles</SelectItem>
                            <SelectItem
                                v-for="role in roleOptions"
                                :key="role"
                                :value="role"
                                >{{ role }}</SelectItem
                            >
                        </SelectContent>
                    </Select>
                </div>
            </div>

            <DataTable
                :items="items"
                :columns="columns"
                empty-text="No profiles found for this status."
            >
                <template #cell-name="{ row }">
                    <div class="space-y-0.5">
                        <p class="font-medium">{{ row.name || '—' }}</p>
                        <p class="text-xs text-muted-foreground">
                            {{ row.email || '—' }}
                        </p>
                        <p class="text-xs text-muted-foreground">
                            {{ row.phone || '—' }}
                        </p>
                    </div>
                </template>

                <template #cell-role="{ value }">
                    <Badge variant="outline" class="uppercase">{{
                        value
                    }}</Badge>
                </template>

                <template #cell-verification_status="{ value }">
                    <Badge :variant="verificationBadgeVariant(value)">{{
                        value
                    }}</Badge>
                </template>

                <template #cell-request_status="{ row }">
                    {{ row.request_status || 'No Request' }}
                </template>

                <template #cell-submitted_at="{ row }">
                    {{
                        row.submitted_at
                            ? new Date(row.submitted_at).toLocaleString()
                            : '—'
                    }}
                </template>

                <template #cell-invoice_status="{ row }">
                    <div class="space-y-0.5">
                        <p>{{ row.invoice_status || '—' }}</p>
                        <p class="text-xs text-muted-foreground">
                            {{ row.invoice_no || '' }}
                        </p>
                    </div>
                </template>

                <template #cell-actions="{ row }">
                    <Button as-child variant="outline" size="sm">
                        <Link :href="manageUrl(row)">
                            {{ actionLabel(row) }}
                        </Link>
                    </Button>
                </template>
            </DataTable>
        </div>
    </AdminLayout>
</template>
