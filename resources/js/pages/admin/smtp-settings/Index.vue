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

interface SmtpSettingItem {
    id: number;
    name: string;
    driver: string;
    from_address: string | null;
    from_name: string | null;
    is_default: boolean;
    is_active: boolean;
    credential_keys: string[];
    configured_keys_count: number;
    required_keys_count: number;
    missing_required_keys: string[];
    has_complete_credentials: boolean;
    updated_at: string | null;
}

interface Filters {
    search: string;
    sort: string;
    direction: string;
}

interface Permissions {
    can_create: boolean;
    can_test: boolean;
}

const props = defineProps<{
    items: {
        data: SmtpSettingItem[];
        links: unknown[];
        meta: unknown;
    };
    filters: Filters;
    permissions: Permissions;
    resultMessage: string | null;
    errorMessage: string | null;
}>();

const breadcrumbs = [{ title: 'SMTP Settings', href: '/settings/smtp' }];
const baseUrl = '/settings/smtp';

const columns = [
    { key: 'name', label: 'Name', sortable: true },
    { key: 'driver', label: 'Driver', sortable: true },
    { key: 'from_address', label: 'From Address' },
    { key: 'credential_keys', label: 'Credentials' },
    { key: 'has_complete_credentials', label: 'Readiness' },
    { key: 'is_active', label: 'Status' },
    { key: 'is_default', label: 'Default' },
    { key: 'updated_at', label: 'Updated', sortable: true },
    { key: 'actions', label: 'Actions', cellClass: 'w-[1%] whitespace-nowrap' },
];

const search = ref(props.filters.search ?? '');
const isTestEmailModalOpen = ref(false);
const isDeleteModalOpen = ref(false);
const deletingItem = ref<SmtpSettingItem | null>(null);
let searchDebounceTimer: ReturnType<typeof setTimeout> | null = null;

const testEmailForm = useForm({
    email: '',
});

const deleteForm = useForm({});

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

function handleSort(columnKey: string) {
    const nextDirection =
        props.filters.sort === columnKey && props.filters.direction === 'asc'
            ? 'desc'
            : 'asc';

    applyFilters({ sort: columnKey, direction: nextDirection, page: 1 });
}

function handleRowAction(actionKey: string, row: SmtpSettingItem) {
    if (actionKey === 'edit') {
        router.visit(`/settings/smtp/${row.id}/edit`);
    } else if (actionKey === 'delete') {
        deletingItem.value = row;
        isDeleteModalOpen.value = true;
    }
}

function openTestEmailModal() {
    testEmailForm.reset();
    testEmailForm.clearErrors();
    isTestEmailModalOpen.value = true;
}

function handleTestEmailModalOpenChange(value: boolean) {
    isTestEmailModalOpen.value = value;

    if (!value) {
        testEmailForm.reset();
        testEmailForm.clearErrors();
    }
}

function submitTestEmail() {
    testEmailForm.post('/settings/smtp/test', {
        preserveScroll: true,
        onSuccess: () => {
            isTestEmailModalOpen.value = false;
            testEmailForm.reset();
            testEmailForm.clearErrors();
        },
    });
}

function handleDeleteModalOpenChange(value: boolean) {
    isDeleteModalOpen.value = value;

    if (!value) {
        deletingItem.value = null;
    }
}

function submitDelete() {
    if (!deletingItem.value) {
        return;
    }

    deleteForm.delete(`/settings/smtp/${deletingItem.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            isDeleteModalOpen.value = false;
            deletingItem.value = null;
        },
    });
}
</script>

<template>
    <Head title="SMTP Settings" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <SettingsLayout full-width>
            <div class="space-y-6 p-4 sm:p-6 lg:p-8">
                <div
                    class="flex flex-wrap items-start justify-between gap-4 rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6"
                >
                    <div class="space-y-1">
                        <h1
                            class="text-2xl sm:text-3xl font-semibold tracking-tight text-slate-900"
                        >
                            SMTP Settings
                        </h1>
                        <p class="text-sm text-slate-600">
                            Manage email drivers, credentials, and delivery
                            testing.
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        <Button
                            v-if="permissions.can_test"
                            type="button"
                            variant="outline"
                            @click="openTestEmailModal"
                        >
                            Test Email
                        </Button>

                        <Link
                            v-if="permissions.can_create"
                            href="/settings/smtp/create"
                            class="inline-flex h-9 items-center rounded-md bg-slate-900 px-4 text-sm font-medium text-white transition hover:bg-slate-800"
                        >
                            Add SMTP Setting
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
                    class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm"
                >
                    <Input
                        v-model="search"
                        type="text"
                        placeholder="Search by name, driver, or from address"
                        class="max-w-md"
                    />
                </div>

                <DataTable
                    :items="items"
                    :columns="columns"
                    :sort-by="filters.sort"
                    :sort-direction="filters.direction"
                    empty-text="No SMTP settings found."
                    @sort="handleSort"
                >
                    <template #cell-from_address="{ row }">
                        <div v-if="row.from_address" class="space-y-0.5">
                            <div class="text-sm">{{ row.from_address }}</div>
                            <div
                                v-if="row.from_name"
                                class="text-xs text-muted-foreground"
                            >
                                {{ row.from_name }}
                            </div>
                        </div>
                        <span v-else class="text-muted-foreground">—</span>
                    </template>

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
                            :actions="[
                                { key: 'edit', label: 'Edit' },
                                {
                                    key: 'delete',
                                    label: 'Delete',
                                    variant: 'destructive',
                                },
                            ]"
                            @select="(action) => handleRowAction(action, row)"
                        />
                    </template>
                </DataTable>
            </div>

            <Dialog
                :open="isTestEmailModalOpen"
                @update:open="handleTestEmailModalOpenChange"
            >
                <DialogContent>
                    <DialogHeader class="space-y-2">
                        <DialogTitle>Send Test Email</DialogTitle>
                        <DialogDescription>
                            Send a test email using the current default active
                            SMTP configuration.
                        </DialogDescription>
                    </DialogHeader>

                    <form class="space-y-4" @submit.prevent="submitTestEmail">
                        <div class="grid gap-2">
                            <Label for="test-email-address">
                                Email Address
                            </Label>
                            <Input
                                id="test-email-address"
                                v-model="testEmailForm.email"
                                type="email"
                                placeholder="test@example.com"
                                autocomplete="email"
                            />
                            <InputError :message="testEmailForm.errors.email" />
                        </div>

                        <DialogFooter class="gap-2">
                            <Button
                                type="button"
                                variant="outline"
                                :disabled="testEmailForm.processing"
                                @click="handleTestEmailModalOpenChange(false)"
                            >
                                Cancel
                            </Button>
                            <Button
                                type="submit"
                                :disabled="testEmailForm.processing"
                            >
                                Send Email
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>

            <Dialog
                :open="isDeleteModalOpen"
                @update:open="handleDeleteModalOpenChange"
            >
                <DialogContent>
                    <DialogHeader class="space-y-2">
                        <DialogTitle>Delete SMTP Setting</DialogTitle>
                        <DialogDescription>
                            Are you sure you want to delete "{{
                                deletingItem?.name
                            }}"? This action cannot be undone.
                        </DialogDescription>
                    </DialogHeader>

                    <DialogFooter class="gap-2">
                        <Button
                            type="button"
                            variant="outline"
                            :disabled="deleteForm.processing"
                            @click="handleDeleteModalOpenChange(false)"
                        >
                            Cancel
                        </Button>
                        <Button
                            type="button"
                            variant="destructive"
                            :disabled="deleteForm.processing"
                            @click="submitDelete"
                        >
                            Delete
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </SettingsLayout>
    </AppLayout>
</template>
