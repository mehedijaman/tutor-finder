<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { onBeforeUnmount, ref, watch } from 'vue';
import DataTable from '@/components/admin/table/DataTable.vue';
import RowActionsDropdown from '@/components/admin/table/RowActionsDropdown.vue';
import InputError from '@/components/InputError.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';

const props = defineProps({
    items: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
    permissions: {
        type: Object,
        default: () => ({}),
    },
    resultMessage: {
        type: String,
        default: null,
    },
    errorMessage: {
        type: String,
        default: null,
    },
});

const breadcrumbs = [{ title: 'SMS Settings', href: '/settings/sms' }];
const baseUrl = '/settings/sms';

const columns = [
    { key: 'name', label: 'Name', sortable: true },
    { key: 'provider', label: 'Provider', sortable: true },
    { key: 'credential_keys', label: 'Credentials' },
    { key: 'has_complete_credentials', label: 'Readiness' },
    { key: 'is_active', label: 'Status' },
    { key: 'is_default', label: 'Default' },
    { key: 'updated_at', label: 'Updated', sortable: true },
    { key: 'actions', label: 'Actions', cellClass: 'w-[1%] whitespace-nowrap' },
];

const search = ref(props.filters.search ?? '');
const isTestSmsModalOpen = ref(false);
let searchDebounceTimer = null;
const testSmsForm = useForm({
    mobile: '',
    message: '',
});

watch(
    () => props.filters.search,
    (value) => {
        const normalized = value ?? '';

        if (normalized !== search.value) {
            search.value = normalized;
        }
    },
);

watch(search, (value) => {
    if (searchDebounceTimer) {
        clearTimeout(searchDebounceTimer);
    }

    searchDebounceTimer = setTimeout(() => {
        applyFilters({ search: value, page: 1 });
    }, 350);
});

onBeforeUnmount(() => {
    if (searchDebounceTimer) {
        clearTimeout(searchDebounceTimer);
    }
});

function applyFilters(overrides = {}) {
    router.get(
        baseUrl,
        {
            search: search.value,
            sort: props.filters.sort ?? 'updated_at',
            direction: props.filters.direction ?? 'desc',
            ...overrides,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
}

function handleSort(columnKey) {
    const nextDirection =
        props.filters.sort === columnKey && props.filters.direction === 'asc'
            ? 'desc'
            : 'asc';

    applyFilters({ sort: columnKey, direction: nextDirection, page: 1 });
}

function handleRowAction(actionKey, row) {
    if (actionKey === 'edit') {
        router.visit(`/settings/sms/${row.id}/edit`);
    }
}

function openTestSmsModal() {
    testSmsForm.reset();
    testSmsForm.clearErrors();
    isTestSmsModalOpen.value = true;
}

function handleTestSmsModalOpenChange(value) {
    isTestSmsModalOpen.value = value;

    if (!value) {
        testSmsForm.reset();
        testSmsForm.clearErrors();
    }
}

function submitTestSms() {
    testSmsForm.post('/settings/sms/test', {
        preserveScroll: true,
        onSuccess: () => {
            isTestSmsModalOpen.value = false;
            testSmsForm.reset();
            testSmsForm.clearErrors();
        },
    });
}
</script>

<template>
    <Head title="SMS Settings" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <SettingsLayout full-width>
            <div class="space-y-6 p-4 sm:p-6 lg:p-8">
                <div
                    class="flex flex-wrap items-start justify-between gap-4 rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-6"
                >
                    <div class="space-y-1">
                        <h1
                            class="text-2xl font-semibold tracking-tight text-slate-900 dark:text-slate-100 sm:text-3xl"
                        >
                            SMS Settings
                        </h1>
                        <p class="text-sm text-slate-600 dark:text-slate-400">
                            Manage SMS providers, credentials, and delivery
                            testing.
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        <Button
                            v-if="permissions.can_test"
                            type="button"
                            variant="outline"
                            @click="openTestSmsModal"
                        >
                            Test SMS
                        </Button>

                        <Link
                            v-if="permissions.can_create"
                            href="/settings/sms/create"
                            class="inline-flex h-9 items-center rounded-md bg-slate-900 px-4 text-sm font-medium text-white transition hover:bg-slate-800"
                        >
                            Add SMS Setting
                        </Link>
                    </div>
                </div>

                <div
                    v-if="resultMessage"
                    class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800"
                >
                    {{ resultMessage }}
                </div>

                <div
                    v-if="errorMessage"
                    class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800"
                >
                    {{ errorMessage }}
                </div>

                <div
                    class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                >
                    <Input
                        v-model="search"
                        type="text"
                        placeholder="Search by name or provider"
                        class="max-w-md"
                    />
                </div>

                <DataTable
                    :items="items"
                    :columns="columns"
                    :sort-by="filters.sort"
                    :sort-direction="filters.direction"
                    empty-text="No SMS settings found."
                    @sort="handleSort"
                >
                    <template #cell-credential_keys="{ row }">
                        <div class="space-y-1">
                            <div class="text-sm">
                                {{
                                    row.configured_keys_count ??
                                    row.credential_keys?.length ??
                                    0
                                }}/{{ row.required_keys_count ?? 0 }}
                                required keys configured
                            </div>
                            <div class="text-xs text-muted-foreground">
                                {{
                                    row.credential_keys?.length
                                        ? row.credential_keys.join(', ')
                                        : 'No keys configured'
                                }}
                            </div>
                        </div>
                    </template>

                    <template #cell-has_complete_credentials="{ row }">
                        <div class="space-y-1">
                            <Badge
                                :variant="
                                    row.has_complete_credentials
                                        ? 'default'
                                        : 'destructive'
                                "
                            >
                                {{
                                    row.has_complete_credentials
                                        ? 'Ready'
                                        : 'Incomplete'
                                }}
                            </Badge>
                            <div
                                v-if="
                                    !row.has_complete_credentials &&
                                    row.missing_required_keys?.length
                                "
                                class="text-xs text-rose-700"
                            >
                                Missing:
                                {{ row.missing_required_keys.join(', ') }}
                            </div>
                        </div>
                    </template>

                    <template #cell-is_active="{ row }">
                        <Badge
                            :variant="row.is_active ? 'default' : 'secondary'"
                        >
                            {{ row.is_active ? 'Active' : 'Inactive' }}
                        </Badge>
                    </template>

                    <template #cell-is_default="{ row }">
                        <Badge
                            :variant="row.is_default ? 'default' : 'secondary'"
                        >
                            {{ row.is_default ? 'Default' : 'No' }}
                        </Badge>
                    </template>

                    <template #cell-updated_at="{ value }">
                        {{ value ? new Date(value).toLocaleString() : '—' }}
                    </template>

                    <template #cell-actions="{ row }">
                        <RowActionsDropdown
                            :actions="[{ key: 'edit', label: 'Edit' }]"
                            @select="(action) => handleRowAction(action, row)"
                        />
                    </template>
                </DataTable>
            </div>

            <Dialog
                :open="isTestSmsModalOpen"
                @update:open="handleTestSmsModalOpenChange"
            >
                <DialogContent>
                    <DialogHeader class="space-y-2">
                        <DialogTitle>Send Test SMS</DialogTitle>
                        <DialogDescription>
                            Send a test message using the current default active
                            SMS gateway.
                        </DialogDescription>
                    </DialogHeader>

                    <form class="space-y-4" @submit.prevent="submitTestSms">
                        <div class="grid gap-2">
                            <Label for="test-sms-mobile">Mobile Number</Label>
                            <Input
                                id="test-sms-mobile"
                                v-model="testSmsForm.mobile"
                                type="text"
                                placeholder="017XXXXXXXX"
                                autocomplete="tel"
                            />
                            <InputError :message="testSmsForm.errors.mobile" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="test-sms-message">SMS Message</Label>
                            <textarea
                                id="test-sms-message"
                                v-model="testSmsForm.message"
                                rows="4"
                                class="rounded-md border px-3 py-2 text-sm"
                                placeholder="Write a short test message"
                            ></textarea>
                            <InputError :message="testSmsForm.errors.message" />
                            <InputError :message="testSmsForm.errors.sms" />
                        </div>

                        <DialogFooter class="gap-2">
                            <Button
                                type="button"
                                variant="outline"
                                :disabled="testSmsForm.processing"
                                @click="handleTestSmsModalOpenChange(false)"
                            >
                                Cancel
                            </Button>
                            <Button
                                type="submit"
                                :disabled="testSmsForm.processing"
                            >
                                Send SMS
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>
        </SettingsLayout>
    </AppLayout>
</template>
