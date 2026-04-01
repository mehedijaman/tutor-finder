<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { 
    Search, 
    Filter, 
    UserPlus, 
    Trash2, 
    RefreshCcw, 
    MoreHorizontal,
    Eye,
    ShieldAlert,
    ShieldCheck,
    Key,
    UserCircle,
    ChevronDown,
    Shield,
    Phone,
    Mail,
    MapPin,
    Briefcase
} from 'lucide-vue-next';
import { computed, onBeforeUnmount, ref, watch } from 'vue';
import ConfirmDialog from '@/components/admin/dialogs/ConfirmDialog.vue';
import ResetPasswordDialog from '@/components/admin/dialogs/ResetPasswordDialog.vue';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
} from '@/components/ui/card';
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
const verificationFilter = ref(props.filters.verification ? props.filters.verification : 'all');

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

// Watchers for filter synchronization
watch(() => props.filters.search, (val) => search.value = val ?? '');
watch(() => props.filters.status, (val) => statusFilter.value = val || 'all');
watch(() => props.filters.verification, (val) => verificationFilter.value = val || 'all');

watch(search, (value) => {
    if (searchDebounceTimer) clearTimeout(searchDebounceTimer);
    searchDebounceTimer = setTimeout(() => {
        applyFilters({ search: value, page: 1 });
    }, 400);
});

watch([statusFilter, verificationFilter], () => {
    applyFilters({ 
        status: statusFilter.value === 'all' ? '' : statusFilter.value,
        verification: verificationFilter.value === 'all' ? '' : verificationFilter.value,
        page: 1 
    });
});

onBeforeUnmount(() => {
    if (searchDebounceTimer) clearTimeout(searchDebounceTimer);
});

function applyFilters(overrides = {}) {
    router.get(baseUrl, {
        trash: props.filters.trash ? 1 : 0,
        search: search.value,
        status: statusFilter.value === 'all' ? '' : statusFilter.value,
        verification: verificationFilter.value === 'all' ? '' : verificationFilter.value,
        sort: props.filters.sort ?? 'created_at',
        direction: props.filters.direction ?? 'desc',
        ...overrides,
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
}

function handleSort(key: string) {
    const direction = props.filters.sort === key && props.filters.direction === 'asc' ? 'desc' : 'asc';
    applyFilters({ sort: key, direction, page: 1 });
}

const statusColor = (status: string) => {
    switch (status) {
        case 'active': return 'bg-emerald-50 text-emerald-700 border-emerald-100';
        case 'suspended': return 'bg-red-50 text-red-700 border-red-100';
        default: return 'bg-slate-50 text-slate-700 border-slate-100';
    }
};

const verificationStatusColor = (status: string) => {
    switch (status?.toLowerCase()) {
        case 'verified': return 'bg-blue-50 text-blue-700 border-blue-100';
        case 'pending': return 'bg-amber-50 text-amber-700 border-amber-100';
        default: return 'bg-slate-50 text-slate-600 border-slate-100';
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
            confirmDescription.value = 'This guardian will be hidden and moved to the recycle bin.';
            confirmLabel.value = 'Move to Trash';
            confirmDestructive.value = true;
            break;
        case 'force-delete':
            confirmTitle.value = 'Permanently Delete?';
            confirmDescription.value = 'This action is irreversible. All guardian data will be lost forever.';
            confirmLabel.value = 'Delete Permanently';
            confirmDestructive.value = true;
            break;
        case 'empty-recycle-bin':
            confirmTitle.value = 'Empty Recycle Bin?';
            confirmDescription.value = 'Are you sure you want to permanently delete all trashed guardian records?';
            confirmLabel.value = 'Empty Now';
            confirmDestructive.value = true;
            break;
        case 'restore':
            confirmTitle.value = 'Restore Profile?';
            confirmDescription.value = 'This profile will be restored to its previous state.';
            confirmLabel.value = 'Restore Now';
            break;
        case 'suspend':
            confirmTitle.value = 'Suspend Account?';
            confirmDescription.value = 'The guardian will lose dashboard access until unsuspended.';
            confirmLabel.value = 'Suspend Account';
            confirmDestructive.value = true;
            break;
        case 'unsuspend':
            confirmTitle.value = 'Activate Account?';
            confirmDescription.value = 'Restore account access for this guardian.';
            confirmLabel.value = 'Activate Now';
            break;
    }
    confirmOpen.value = true;
}

function runConfirmedAction() {
    if (!pendingAction.value) return;
    const { action, row } = pendingAction.value;

    const routes: any = {
        'delete': () => router.delete(`/admin/guardians/${row.id}`),
        'force-delete': () => router.delete(`/admin/guardians/${row.id}/force`),
        'restore': () => router.patch(`/admin/guardians/${row.id}/restore`),
        'restore-all': () => router.patch('/admin/guardians/recycle-bin/restore-all'),
        'empty-recycle-bin': () => router.delete('/admin/guardians/recycle-bin/empty'),
        'suspend': () => router.patch(`/admin/guardians/${row.id}/status`, { status: 'suspended' }),
        'unsuspend': () => router.patch(`/admin/guardians/${row.id}/status`, { status: 'active' }),
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
    return !page.props.auth?.impersonation?.is_impersonating && row.id !== page.props.auth?.user?.id && row.status === 'active';
};

const formatDate = (date: string) => {
    if (!date) return '—';
    return new Date(date).toLocaleDateString('en-US', {
        month: 'short', day: 'numeric', year: 'numeric'
    });
};

function submitResetPassword(payload: any) {
    if (!resetPasswordUser.value) return;
    router.put(`/admin/guardians/${resetPasswordUser.value.id}/password`, payload, {
        onSuccess: () => {
            resetPasswordOpen.value = false;
            resetPasswordUser.value = null;
        },
    });
}

function closeResetPasswordDialog() {
    resetPasswordOpen.value = false;
    resetPasswordUser.value = null;
}
</script>

<template>
    <Head title="Guardian Directory" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-4 sm:p-6 lg:p-8 max-w-[1600px] mx-auto">
            <!-- Header -->
            <div class="flex flex-col gap-6 md:flex-row md:items-center md:justify-between">
                <div class="space-y-1">
                    <h1 class="text-3xl font-bold tracking-tight text-slate-900 leading-none">
                        {{ filters.trash ? 'Recycle Bin' : 'Guardian Directory' }}
                    </h1>
                    <p class="text-sm font-medium text-slate-500 pt-1">
                        {{ filters.trash 
                            ? 'Manage recently deleted guardian profiles.' 
                            : `Total of ${filteredItemsCount} guardians accounts.` 
                        }}
                    </p>
                </div>

                <div class="flex items-center gap-2.5">
                    <Button v-if="!filters.trash" as-child class="rounded-xl shadow-lg shadow-indigo-100 bg-indigo-600 hover:bg-indigo-700">
                        <Link href="/admin/guardians/create">
                            <UserPlus class="mr-2 h-4 w-4" /> Add Guardian
                        </Link>
                    </Button>

                    <Button variant="outline" as-child class="rounded-xl border-slate-200">
                        <Link :href="filters.trash ? '/admin/guardians' : '/admin/guardians?trash=1'">
                            <Trash2 v-if="!filters.trash" class="mr-2 h-4 w-4 text-slate-400" />
                            <RefreshCcw v-else class="mr-2 h-4 w-4 text-slate-400" />
                            {{ filters.trash ? 'Back to Directory' : 'Recycle Bin' }}
                        </Link>
                    </Button>
                    
                    <template v-if="filters.trash">
                        <Button variant="outline" @click="openConfirm('restore-all')" class="rounded-xl border-emerald-200 text-emerald-700 hover:bg-emerald-50">
                            Restore All
                        </Button>
                        <Button variant="destructive" @click="openConfirm('empty-recycle-bin')" class="rounded-xl shadow-lg shadow-red-100">
                            Clear Trash
                        </Button>
                    </template>
                </div>
            </div>

            <!-- Filters -->
            <Card class="rounded-2xl border-slate-200/60 shadow-sm overflow-hidden">
                <CardContent class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-[1fr_200px_200px] gap-4">
                        <div class="relative">
                            <Search class="absolute left-3.5 top-3.5 h-4 w-4 text-slate-400" />
                            <Input
                                v-model="search"
                                placeholder="Search by name, phone or email identifier..."
                                class="pl-10 h-11 rounded-xl border-slate-200 bg-slate-50/50 focus-visible:ring-indigo-500"
                            />
                        </div>

                        <Select v-model="statusFilter">
                            <SelectTrigger class="h-11 rounded-xl border-slate-200">
                                <Filter class="mr-2 h-4 w-4 text-slate-400" />
                                <SelectValue placeholder="All Status" />
                            </SelectTrigger>
                            <SelectContent class="rounded-xl">
                                <SelectItem value="all">All Status</SelectItem>
                                <SelectItem value="active">Active</SelectItem>
                                <SelectItem value="suspended">Suspended</SelectItem>
                            </SelectContent>
                        </Select>

                        <Select v-model="verificationFilter">
                            <SelectTrigger class="h-11 rounded-xl border-slate-200">
                                <Shield class="mr-2 h-4 w-4 text-slate-400" />
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
                </CardContent>
            </Card>

            <!-- Grid -->
            <div class="rounded-2xl border border-slate-200/60 bg-white shadow-sm overflow-hidden overflow-x-auto">
                <table class="w-full text-left border-collapse min-w-[900px]">
                    <thead>
                        <tr class="bg-slate-50/50 border-b border-slate-100">
                            <th @click="handleSort('name')" class="p-4 px-6 text-[10px] font-bold uppercase tracking-widest text-slate-400 cursor-pointer hover:text-indigo-600 transition-colors">
                                <div class="flex items-center gap-2">
                                    Guardian Identity
                                    <ChevronDown v-if="filters.sort === 'name'" :class="cn('h-3 w-3', filters.direction === 'desc' ? 'rotate-180' : '')" />
                                </div>
                            </th>
                            <th class="p-4 px-6 text-[10px] font-bold uppercase tracking-widest text-slate-400">Contact Details</th>
                            <th @click="handleSort('status')" class="p-4 px-6 text-[10px] font-bold uppercase tracking-widest text-slate-400 cursor-pointer hover:text-indigo-600 transition-colors">
                                <div class="flex items-center gap-2">
                                    Status
                                    <ChevronDown v-if="filters.sort === 'status'" :class="cn('h-3 w-3', filters.direction === 'desc' ? 'rotate-180' : '')" />
                                </div>
                            </th>
                            <th class="p-4 px-6 text-[10px] font-bold uppercase tracking-widest text-slate-400">Profile Details</th>
                            <th @click="handleSort('created_at')" class="p-4 px-6 text-[10px] font-bold uppercase tracking-widest text-slate-400 cursor-pointer hover:text-indigo-600 transition-colors">
                                <div class="flex items-center gap-2">
                                    Created
                                    <ChevronDown v-if="filters.sort === 'created_at'" :class="cn('h-3 w-3', filters.direction === 'desc' ? 'rotate-180' : '')" />
                                </div>
                            </th>
                            <th class="p-4 px-6 text-[10px] font-bold uppercase tracking-widest text-slate-400 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <tr v-for="row in items.data" :key="row.id" class="group transition-colors hover:bg-slate-50/20">
                            <td class="p-4 px-6">
                                <div class="flex items-center gap-4">
                                    <Avatar class="h-10 w-10 border-2 border-white shadow-sm ring-1 ring-slate-100">
                                        <AvatarFallback class="bg-indigo-50 text-indigo-700 font-bold uppercase text-xs">
                                            {{ row.name?.split(' ').map((n: string) => n[0]).join('').slice(0, 2) }}
                                        </AvatarFallback>
                                    </Avatar>
                                    <div class="space-y-0.5">
                                        <p class="font-bold text-slate-900 tracking-tight leading-tight">{{ row.name }}</p>
                                        <div class="flex items-center gap-1.5 pt-0.5">
                                            <Badge variant="outline" :class="cn('h-4 text-[8px] font-bold uppercase tracking-widest px-1.5 rounded-md', verificationStatusColor(row.verification_status))">
                                                {{ row.verification_status || 'Unverified' }}
                                            </Badge>
                                            <p class="text-[9px] font-medium text-slate-400 uppercase tracking-tighter">#{{ row.id }}</p>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="p-4 px-6">
                                <div class="space-y-1.5">
                                    <div class="flex items-center gap-2 text-xs font-semibold text-slate-700">
                                        <Phone class="h-3 w-3 text-emerald-400" /> {{ row.phone || '—' }}
                                    </div>
                                    <div class="flex items-center gap-2 text-[10px] font-medium text-slate-400 truncate max-w-[180px]">
                                        <Mail class="h-3 w-3 opacity-60" /> {{ row.email || '—' }}
                                    </div>
                                </div>
                            </td>
                            <td class="p-4 px-6">
                                <Badge variant="outline" :class="cn('rounded-full font-bold uppercase text-[9px] tracking-widest px-2.5 py-0.5', statusColor(row.status))">
                                    {{ row.status?.replace('_', ' ') }}
                                </Badge>
                            </td>
                            <td class="p-4 px-6">
                                <div class="space-y-1.5">
                                    <div class="flex items-center gap-2 text-[11px] font-bold text-slate-700">
                                        <Briefcase class="h-3 w-3 text-indigo-400" /> 
                                        <span class="truncate max-w-[140px]">{{ row.occupation || 'No Occupation' }}</span>
                                    </div>
                                    <div class="flex items-center gap-2 text-[10px] font-medium text-slate-400 truncate max-w-[180px]">
                                        <MapPin class="h-3 w-3 opacity-60 text-slate-300" /> {{ row.address || 'No address' }}
                                    </div>
                                </div>
                            </td>
                            <td class="p-4 px-6 text-xs font-bold text-slate-600">
                                {{ formatDate(row.created_at) }}
                            </td>
                            <td class="p-4 px-6 text-right">
                                <DropdownMenu>
                                    <DropdownMenuTrigger as-child>
                                        <Button variant="ghost" size="icon" class="h-8 w-8 rounded-xl ring-1 ring-slate-100 hover:bg-white hover:ring-slate-200">
                                            <MoreHorizontal class="h-4 w-4" />
                                        </Button>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent align="end" class="w-48 rounded-xl p-1.5 shadow-xl border-slate-100">
                                        <DropdownMenuLabel class="text-[9px] font-bold uppercase tracking-widest text-slate-400 px-2 py-1.5">Management Options</DropdownMenuLabel>
                                        <DropdownMenuItem @click="handleRowAction('view', row)" class="rounded-lg gap-2 cursor-pointer font-bold text-xs py-2.5">
                                            <Eye class="h-4 w-4 text-indigo-500" /> View Details
                                        </DropdownMenuItem>
                                        
                                        <template v-if="!filters.trash">
                                            <DropdownMenuItem @click="handleRowAction('reset-password', row)" class="rounded-lg gap-2 cursor-pointer font-bold text-xs py-2.5">
                                                <Key class="h-4 w-4 text-emerald-500" /> Reset Password
                                            </DropdownMenuItem>
                                            <DropdownMenuItem v-if="canImpersonateRow(row)" @click="handleRowAction('impersonate', row)" class="rounded-lg gap-2 cursor-pointer font-bold text-xs py-2.5">
                                                <UserCircle class="h-4 w-4 text-blue-500" /> Impersonate
                                            </DropdownMenuItem>
                                            <DropdownMenuSeparator />
                                            <DropdownMenuItem @click="handleRowAction(row.status === 'active' ? 'suspend' : 'unsuspend', row)" class="rounded-lg gap-2 cursor-pointer font-bold text-xs py-2.5">
                                                <ShieldAlert v-if="row.status === 'active'" class="h-4 w-4 text-orange-500" />
                                                <ShieldCheck v-else class="h-4 w-4 text-emerald-500" />
                                                {{ row.status === 'active' ? 'Suspend Account' : 'Re-activate Account' }}
                                            </DropdownMenuItem>
                                            <DropdownMenuItem @click="handleRowAction('delete', row)" class="rounded-lg gap-2 cursor-pointer font-bold text-xs py-2.5 text-red-600 focus:text-red-700 focus:bg-red-50/50">
                                                <Trash2 class="h-4 w-4" /> Send to Trash
                                            </DropdownMenuItem>
                                        </template>

                                        <template v-else>
                                            <DropdownMenuItem @click="handleRowAction('restore', row)" class="rounded-lg gap-2 cursor-pointer font-bold text-xs py-2.5 text-emerald-600 focus:text-emerald-700 focus:bg-emerald-50/50">
                                                <RefreshCcw class="h-4 w-4" /> Restore Account
                                            </DropdownMenuItem>
                                            <DropdownMenuItem @click="handleRowAction('force-delete', row)" class="rounded-lg gap-2 cursor-pointer font-bold text-xs py-2.5 text-red-600 focus:text-red-700 focus:bg-red-50/50">
                                                <Trash2 class="h-4 w-4" /> Delete Forever
                                            </DropdownMenuItem>
                                        </template>
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </td>
                        </tr>
                        <tr v-if="items.data.length === 0">
                            <td colspan="6" class="p-16 text-center">
                                <div class="flex flex-col items-center justify-center space-y-4">
                                    <div class="h-16 w-16 rounded-2xl bg-slate-50 flex items-center justify-center ring-1 ring-slate-100">
                                        <Search class="h-7 w-7 text-slate-200" />
                                    </div>
                                    <div class="space-y-1">
                                        <p class="text-sm font-bold text-slate-900 uppercase tracking-tighter">No guardians found</p>
                                        <p class="text-xs font-medium text-slate-400">Refine your search parameters or check filters.</p>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="flex flex-col md:flex-row items-center justify-between gap-4 px-2">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em]">
                    Displaying results {{ items.from || 0 }} to {{ items.to || 0 }} of total {{ items.total || 0 }}
                </p>
                <div class="flex items-center gap-1.5 overflow-x-auto pb-2 md:pb-0 max-w-full">
                    <Button 
                        v-for="link in items.links" 
                        :key="link.label"
                        variant="outline"
                        size="sm"
                        class="h-8 min-w-[32px] rounded-lg font-bold text-[10px] uppercase transition-all"
                        :class="link.active ? 'bg-indigo-600 border-indigo-600 text-white hover:bg-indigo-700' : 'bg-white border-slate-200 text-slate-500 hover:text-indigo-600'"
                        :disabled="!link.url || link.active"
                        @click="router.visit(link.url)"
                    >
                        <span v-html="link.label"></span>
                    </Button>
                </div>
            </div>
        </div>

        <ConfirmDialog
            v-model:open="confirmOpen"
            :title="confirmTitle"
            :description="confirmDescription"
            :confirm-label="confirmLabel"
            :destructive="confirmDestructive"
            @confirm="runConfirmedAction"
            @cancel="pendingAction = null"
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

<style scoped>
@keyframes slideIn {
    from { opacity: 0; transform: translateY(8px); }
    to { opacity: 1; transform: translateY(0); }
}

tbody tr {
    animation: slideIn 0.4s ease-out forwards;
}

tbody tr:nth-child(1) { animation-delay: 0.05s; }
tbody tr:nth-child(2) { animation-delay: 0.1s; }
tbody tr:nth-child(3) { animation-delay: 0.15s; }
tbody tr:nth-child(4) { animation-delay: 0.2s; }
tbody tr:nth-child(5) { animation-delay: 0.25s; }

::-webkit-scrollbar {
    height: 6px;
}

::-webkit-scrollbar-track {
    background: transparent;
}

::-webkit-scrollbar-thumb {
    background: #f1f5f9;
    border-radius: 10px;
}

::-webkit-scrollbar-thumb:hover {
    background: #e2e8f0;
}
</style>
