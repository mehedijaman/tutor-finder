<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import {
    Search,
    Filter,
    Trash2,
    RefreshCcw,
    MoreHorizontal,
    Eye,
    ShieldAlert,
    ShieldCheck,
    Key,
    UserCircle,
    Shield,
    Phone,
    Mail,
    MapPin,
    Briefcase,
    UserPlus,
} from 'lucide-vue-next';
import { computed, onBeforeUnmount, ref, watch } from 'vue';
import ConfirmDialog from '@/components/admin/dialogs/ConfirmDialog.vue';
import ResetPasswordDialog from '@/components/admin/dialogs/ResetPasswordDialog.vue';
import DataTable from '@/components/admin/table/DataTable.vue';
import PageHeading from '@/components/PageHeading.vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { cn } from '@/lib/utils';

const props = defineProps<{
    items: any;
    filters: any;
}>();

const page = usePage();
const breadcrumbs = [{ title: 'Guardian Directory', href: '/admin/guardians' }];
const baseUrl = '/admin/guardians';

const search = ref(props.filters.search ?? '');
const statusFilter = ref(props.filters.status ? props.filters.status : 'all');
const verificationFilter = ref(
    props.filters.verification ? props.filters.verification : 'all',
);

const confirmOpen = ref(false);
const confirmTitle = ref('');
const confirmDescription = ref('');
const confirmLabel = ref('Confirm');
const confirmDestructive = ref(true);
const pendingAction = ref<any>(null);

const resetPasswordOpen = ref(false);
const resetPasswordUser = ref<any>(null);
let searchDebounceTimer: any = null;

const formErrors = computed(() => page.props.errors ?? {});
const filteredItemsCount = computed(() => props.items.total || 0);
const isTrash = computed(() => !!props.filters.trash);

const columns = [
    { key: 'identity', label: 'Guardian Identity', sortable: true },
    { key: 'contact', label: 'Contact Details' },
    { key: 'status', label: 'Status', sortable: true },
    { key: 'profile', label: 'Profile Details' },
    { key: 'created_at', label: 'Created', sortable: true },
    { key: 'actions', label: 'Actions', cellClass: 'w-[1%] whitespace-nowrap text-right' },
];

const sortKeyMap: Record<string, string> = {
    identity: 'name',
    status: 'status',
    created_at: 'created_at',
};

watch(
    () => props.filters.search,
    (val) => (search.value = val ?? ''),
);
watch(
    () => props.filters.status,
    (val) => (statusFilter.value = val || 'all'),
);
watch(
    () => props.filters.verification,
    (val) => (verificationFilter.value = val || 'all'),
);

watch(search, (value) => {
    if (searchDebounceTimer) clearTimeout(searchDebounceTimer);
    searchDebounceTimer = setTimeout(() => {
        applyFilters({ search: value, page: 1 });
    }, 400);
});

watch([statusFilter, verificationFilter], () => {
    applyFilters({
        status: statusFilter.value === 'all' ? '' : statusFilter.value,
        verification:
            verificationFilter.value === 'all' ? '' : verificationFilter.value,
        page: 1,
    });
});

onBeforeUnmount(() => {
    if (searchDebounceTimer) clearTimeout(searchDebounceTimer);
});

function applyFilters(overrides = {}) {
    router.get(
        baseUrl,
        {
            trash: props.filters.trash ? 1 : 0,
            search: search.value,
            status: statusFilter.value === 'all' ? '' : statusFilter.value,
            verification:
                verificationFilter.value === 'all'
                    ? ''
                    : verificationFilter.value,
            sort: props.filters.sort ?? 'created_at',
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
    const key = sortKeyMap[columnKey] ?? columnKey;
    const direction =
        props.filters.sort === key && props.filters.direction === 'asc'
            ? 'desc'
            : 'asc';
    applyFilters({ sort: key, direction, page: 1 });
}

const statusColor = (status: string) => {
    switch (status) {
        case 'active':
            return 'bg-emerald-50 text-emerald-700 border-emerald-100';
        case 'suspended':
            return 'bg-red-50 text-red-700 border-red-100';
        default:
            return 'bg-slate-50 text-slate-700 border-slate-100';
    }
};

const verificationStatusColor = (status: string) => {
    switch (status?.toLowerCase()) {
        case 'verified':
            return 'bg-blue-50 text-blue-700 border-blue-100';
        case 'pending':
            return 'bg-amber-50 text-amber-700 border-amber-100';
        default:
            return 'bg-slate-50 text-slate-600 border-slate-100';
    }
};

function openConfirm(action: string, row: any = null) {
    pendingAction.value = { action, row };
    confirmTitle.value = 'Confirm Action';
    confirmDescription.value = '';
    confirmLabel.value = 'Confirm';
    confirmDestructive.value = false;

    switch (action) {
        case 'delete':
            confirmTitle.value = 'Move to Recycle Bin?';
            confirmDescription.value =
                'This guardian will be hidden and moved to the recycle bin.';
            confirmLabel.value = 'Move to Trash';
            confirmDestructive.value = true;
            break;
        case 'force-delete':
            confirmTitle.value = 'Permanently Delete?';
            confirmDescription.value =
                'This action is irreversible. All guardian data will be lost forever.';
            confirmLabel.value = 'Delete Permanently';
            confirmDestructive.value = true;
            break;
        case 'empty-recycle-bin':
            confirmTitle.value = 'Empty Recycle Bin?';
            confirmDescription.value =
                'Are you sure you want to permanently delete all trashed guardian records?';
            confirmLabel.value = 'Empty Now';
            confirmDestructive.value = true;
            break;
        case 'restore':
            confirmTitle.value = 'Restore Profile?';
            confirmDescription.value =
                'This profile will be restored to its previous state.';
            confirmLabel.value = 'Restore Now';
            break;
        case 'suspend':
            confirmTitle.value = 'Suspend Account?';
            confirmDescription.value =
                'The guardian will lose dashboard access until unsuspended.';
            confirmLabel.value = 'Suspend Account';
            confirmDestructive.value = true;
            break;
        case 'unsuspend':
            confirmTitle.value = 'Activate Account?';
            confirmDescription.value =
                'Restore account access for this guardian.';
            confirmLabel.value = 'Activate Now';
            break;
    }
    confirmOpen.value = true;
}

function runConfirmedAction() {
    if (!pendingAction.value) return;
    const { action, row } = pendingAction.value;

    const routes: any = {
        delete: () => router.delete(`/admin/guardians/${row.id}`),
        'force-delete': () => router.delete(`/admin/guardians/${row.id}/force`),
        restore: () => router.patch(`/admin/guardians/${row.id}/restore`),
        'restore-all': () =>
            router.patch('/admin/guardians/recycle-bin/restore-all'),
        'empty-recycle-bin': () =>
            router.delete('/admin/guardians/recycle-bin/empty'),
        suspend: () =>
            router.patch(`/admin/guardians/${row.id}/status`, {
                status: 'suspended',
            }),
        unsuspend: () =>
            router.patch(`/admin/guardians/${row.id}/status`, {
                status: 'active',
            }),
    };

    if (routes[action]) routes[action]();
    confirmOpen.value = false;
    pendingAction.value = null;
}

function handleRowAction(actionKey: string, row: any) {
    if (actionKey === 'view') {
        router.visit(`/admin/guardians/${row.id}`);
    } else if (actionKey === 'reset-password') {
        resetPasswordUser.value = row;
        resetPasswordOpen.value = true;
    } else if (actionKey === 'impersonate') {
        router.post(`/admin/impersonation/${row.id}`);
    } else {
        openConfirm(actionKey, row);
    }
}

const canImpersonateRow = (row: any) => {
    return (
        !page.props.auth?.impersonation?.is_impersonating &&
        row.id !== page.props.auth?.user?.id &&
        row.status === 'active'
    );
};

const formatDate = (date: string) => {
    if (!date) return '—';
    return new Date(date).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
};

function submitResetPassword(payload: any) {
    if (!resetPasswordUser.value) return;
    router.put(
        `/admin/guardians/${resetPasswordUser.value.id}/password`,
        payload,
        {
            onSuccess: () => {
                resetPasswordOpen.value = false;
                resetPasswordUser.value = null;
            },
        },
    );
}

function closeResetPasswordDialog() {
    resetPasswordOpen.value = false;
    resetPasswordUser.value = null;
}
</script>

<template>
    <Head :title="isTrash ? 'Recycle Bin' : 'Guardian Directory'" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto max-w-[1600px] space-y-6 p-4 sm:p-6 lg:p-8">
            <PageHeading
                :title="isTrash ? 'Recycle Bin' : 'Guardian Directory'"
                :description="isTrash ? 'Manage recently deleted guardian profiles.' : `Total of ${filteredItemsCount} guardian accounts.`"
            >
                <template #actions>
                    <Button
                        v-if="!isTrash"
                        as-child
                    >
                        <Link href="/admin/guardians/create">
                            <UserPlus class="mr-2 h-4 w-4" />
                            Add Guardian
                        </Link>
                    </Button>

                    <Button
                        variant="outline"
                        as-child
                    >
                        <Link
                            :href="
                                isTrash
                                    ? '/admin/guardians'
                                    : '/admin/guardians?trash=1'
                            "
                        >
                            <Trash2
                                v-if="!isTrash"
                                class="mr-2 h-4 w-4 text-muted-foreground"
                            />
                            <RefreshCcw
                                v-else
                                class="mr-2 h-4 w-4 text-muted-foreground"
                            />
                            {{ isTrash ? 'Back to Directory' : 'Recycle Bin' }}
                        </Link>
                    </Button>

                    <template v-if="isTrash">
                        <Button
                            variant="outline"
                            @click="openConfirm('restore-all')"
                            class="border-emerald-200 text-emerald-700 hover:bg-emerald-50"
                        >
                            Restore All
                        </Button>
                        <Button
                            variant="destructive"
                            @click="openConfirm('empty-recycle-bin')"
                            class="shadow-lg shadow-red-100"
                        >
                            Clear Trash
                        </Button>
                    </template>
                </template>
            </PageHeading>

            <!-- Filters -->
            <div
                class="flex flex-col gap-4 rounded-2xl border border-border/60 bg-card p-5 shadow-sm sm:flex-row sm:p-6"
            >
                <div class="relative flex-1">
                    <Search
                        class="absolute top-1/2 left-3.5 h-4 w-4 -translate-y-1/2 text-muted-foreground"
                    />
                    <Input
                        v-model="search"
                        placeholder="Search by name, phone or email..."
                        class="h-11 rounded-xl border-border bg-muted/30 pl-10 focus-visible:ring-primary"
                    />
                </div>

                <Select v-model="statusFilter">
                    <SelectTrigger
                        class="h-11 w-full rounded-xl border-border sm:w-auto sm:min-w-[180px]"
                    >
                        <Filter class="mr-2 h-4 w-4 text-muted-foreground shrink-0" />
                        <SelectValue placeholder="All Status" />
                    </SelectTrigger>
                    <SelectContent class="rounded-xl">
                        <SelectItem value="all">All Status</SelectItem>
                        <SelectItem value="active">Active</SelectItem>
                        <SelectItem value="suspended">Suspended</SelectItem>
                    </SelectContent>
                </Select>

                <Select v-model="verificationFilter">
                    <SelectTrigger
                        class="h-11 w-full rounded-xl border-border sm:w-auto sm:min-w-[180px]"
                    >
                        <Shield class="mr-2 h-4 w-4 text-muted-foreground shrink-0" />
                        <SelectValue placeholder="Verification" />
                    </SelectTrigger>
                    <SelectContent class="rounded-xl">
                        <SelectItem value="all">All Records</SelectItem>
                        <SelectItem value="pending">Pending</SelectItem>
                        <SelectItem value="verified">Verified</SelectItem>
                        <SelectItem value="unverified">Unverified</SelectItem>
                    </SelectContent>
                </Select>
            </div>

            <DataTable
                :items="items"
                :columns="columns"
                :sort-by="filters.sort ?? ''"
                :sort-direction="filters.direction ?? 'desc'"
                @sort="handleSort"
            >
                <template #cell-identity="{ row }">
                    <div class="flex items-center gap-4">
                        <Avatar
                            class="h-10 w-10 border-2 border-border shadow-sm"
                        >
                            <AvatarImage
                                v-if="row.photo_url"
                                :src="row.photo_url"
                                :alt="row.name"
                            />
                            <AvatarFallback
                                class="bg-primary/10 text-xs font-bold text-primary uppercase"
                            >
                                {{
                                    row.name
                                        ?.split(' ')
                                        .map((n: string) => n[0])
                                        .join('')
                                        .slice(0, 2)
                                }}
                            </AvatarFallback>
                        </Avatar>
                        <div class="space-y-0.5">
                            <p class="leading-tight font-bold tracking-tight text-card-foreground">
                                {{ row.name }}
                            </p>
                            <div class="flex items-center gap-1.5 pt-0.5">
                                <Badge
                                    variant="outline"
                                    :class="
                                        cn(
                                            'h-4 rounded-md px-1.5 text-[8px] font-bold tracking-widest uppercase',
                                            verificationStatusColor(
                                                row.verification_status,
                                            ),
                                        )
                                    "
                                >
                                    {{ row.verification_status || 'Unverified' }}
                                </Badge>
                                <span class="text-[9px] font-medium tracking-tighter text-muted-foreground uppercase">
                                    #{{ row.id }}
                                </span>
                            </div>
                        </div>
                    </div>
                </template>

                <template #cell-contact="{ row }">
                    <div class="space-y-1.5">
                        <div class="flex items-center gap-2 text-xs font-semibold text-card-foreground">
                            <Phone class="h-3 w-3 text-emerald-400" />
                            {{ row.phone || '—' }}
                        </div>
                        <div class="flex max-w-[180px] items-center gap-2 truncate text-[10px] font-medium text-muted-foreground">
                            <Mail class="h-3 w-3 opacity-60" />
                            {{ row.email || '—' }}
                        </div>
                    </div>
                </template>

                <template #cell-status="{ row }">
                    <Badge
                        variant="outline"
                        :class="
                            cn(
                                'rounded-full px-2.5 py-0.5 text-[9px] font-bold tracking-widest uppercase',
                                statusColor(row.status),
                            )
                        "
                    >
                        {{ row.status?.replace('_', ' ') }}
                    </Badge>
                </template>

                <template #cell-profile="{ row }">
                    <div class="space-y-1.5">
                        <div class="flex items-center gap-2 text-[11px] font-bold text-card-foreground">
                            <Briefcase class="h-3 w-3 text-primary" />
                            <span class="max-w-[140px] truncate">{{ row.occupation || 'No Occupation' }}</span>
                        </div>
                        <div class="flex max-w-[180px] items-center gap-2 truncate text-[10px] font-medium text-muted-foreground">
                            <MapPin class="h-3 w-3 text-muted-foreground opacity-60" />
                            {{ row.address || 'No address' }}
                        </div>
                    </div>
                </template>

                <template #cell-created_at="{ row }">
                    <p class="text-xs font-bold text-card-foreground">
                        {{ formatDate(row.created_at) }}
                    </p>
                </template>

                <template #cell-actions="{ row }">
                    <div class="flex items-center justify-end">
                        <DropdownMenu>
                            <DropdownMenuTrigger as-child>
                                <Button
                                    variant="ghost"
                                    size="icon"
                                    class="h-8 w-8 rounded-xl hover:bg-muted"
                                >
                                    <MoreHorizontal class="h-4 w-4" />
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent
                                align="end"
                                class="w-48 rounded-xl border-border p-1.5 shadow-xl"
                            >
                                <DropdownMenuLabel
                                    class="px-2 py-1.5 text-[9px] font-bold tracking-widest text-muted-foreground uppercase"
                                >
                                    Management Options
                                </DropdownMenuLabel>
                                <DropdownMenuItem
                                    @click="handleRowAction('view', row)"
                                    class="cursor-pointer gap-2 rounded-lg py-2.5 text-xs font-bold"
                                >
                                    <Eye class="h-4 w-4 text-primary" />
                                    View Details
                                </DropdownMenuItem>

                                <template v-if="!isTrash">
                                    <DropdownMenuItem
                                        @click="handleRowAction('reset-password', row)"
                                        class="cursor-pointer gap-2 rounded-lg py-2.5 text-xs font-bold"
                                    >
                                        <Key class="h-4 w-4 text-emerald-500" />
                                        Reset Password
                                    </DropdownMenuItem>
                                    <DropdownMenuItem
                                        v-if="canImpersonateRow(row)"
                                        @click="handleRowAction('impersonate', row)"
                                        class="cursor-pointer gap-2 rounded-lg py-2.5 text-xs font-bold"
                                    >
                                        <UserCircle class="h-4 w-4 text-blue-500" />
                                        Impersonate
                                    </DropdownMenuItem>
                                    <DropdownMenuSeparator />
                                    <DropdownMenuItem
                                        @click="handleRowAction(row.status === 'active' ? 'suspend' : 'unsuspend', row)"
                                        class="cursor-pointer gap-2 rounded-lg py-2.5 text-xs font-bold"
                                    >
                                        <ShieldAlert
                                            v-if="row.status === 'active'"
                                            class="h-4 w-4 text-orange-500"
                                        />
                                        <ShieldCheck
                                            v-else
                                            class="h-4 w-4 text-emerald-500"
                                        />
                                        {{ row.status === 'active' ? 'Suspend Account' : 'Re-activate Account' }}
                                    </DropdownMenuItem>
                                    <DropdownMenuItem
                                        @click="handleRowAction('delete', row)"
                                        class="cursor-pointer gap-2 rounded-lg py-2.5 text-xs font-bold text-red-600 focus:bg-red-50/50 focus:text-red-700"
                                    >
                                        <Trash2 class="h-4 w-4" />
                                        Send to Trash
                                    </DropdownMenuItem>
                                </template>

                                <template v-else>
                                    <DropdownMenuItem
                                        @click="handleRowAction('restore', row)"
                                        class="cursor-pointer gap-2 rounded-lg py-2.5 text-xs font-bold text-emerald-600 focus:bg-emerald-50/50 focus:text-emerald-700"
                                    >
                                        <RefreshCcw class="h-4 w-4" />
                                        Restore Account
                                    </DropdownMenuItem>
                                    <DropdownMenuItem
                                        @click="handleRowAction('force-delete', row)"
                                        class="cursor-pointer gap-2 rounded-lg py-2.5 text-xs font-bold text-red-600 focus:bg-red-50/50 focus:text-red-700"
                                    >
                                        <Trash2 class="h-4 w-4" />
                                        Delete Forever
                                    </DropdownMenuItem>
                                </template>
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </div>
                </template>
            </DataTable>
        </div>

        <ConfirmDialog
            v-model:open="confirmOpen"
            :title="confirmTitle"
            :description="confirmDescription"
            :confirm-label="confirmLabel"
            :destructive="confirmDestructive"
            @confirm="runConfirmedAction"
        />

        <ResetPasswordDialog
            v-model:open="resetPasswordOpen"
            :user-name="resetPasswordUser?.name"
            :errors="formErrors"
            @submit="submitResetPassword"
            @cancel="closeResetPasswordDialog"
        />
    </AdminLayout>
</template>
