<script setup lang="ts">
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, reactive, ref, watch } from 'vue';
import ConfirmDialog from '@/components/admin/dialogs/ConfirmDialog.vue';
import DataTable from '@/components/admin/table/DataTable.vue';
import RowActionsDropdown from '@/components/admin/table/RowActionsDropdown.vue';
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
    statusOptions: {
        type: Array,
        default: () => [],
    },
    roleOptions: {
        type: Array,
        default: () => [],
    },
});

type VerificationRow = {
    id: number;
    user_name?: string | null;
    user_email?: string | null;
    role: string;
    status: string;
    submitted_at?: string | null;
    fee_amount?: string | number | null;
    currency?: string | null;
    invoice_status?: string | null;
    invoice_no?: string | null;
    invoice_id?: number | null;
};

const breadcrumbs = [{ title: 'Verifications', href: '/admin/verifications' }];
const baseUrl = '/admin/verifications';

const columns = [
    { key: 'user_name', label: 'User' },
    { key: 'role', label: 'Role' },
    { key: 'status', label: 'Status' },
    { key: 'submitted_at', label: 'Submitted At' },
    { key: 'fee_amount', label: 'Fee' },
    { key: 'invoice_status', label: 'Invoice Status' },
    { key: 'actions', label: 'Actions', cellClass: 'w-[1%] whitespace-nowrap' },
];

const search = ref(props.filters.q ?? '');
const statusFilter = ref(props.filters.status || 'all');
const roleFilter = ref(props.filters.role || 'all');
let searchTimer: ReturnType<typeof setTimeout> | null = null;
const statusOptionsList = computed<string[]>(
    () => (props.statusOptions as string[] | undefined) ?? [],
);
const roleOptionsList = computed<string[]>(
    () => (props.roleOptions as string[] | undefined) ?? [],
);
const page = usePage();
const flashStatus = computed<string | null>(
    () => (page.props.flash as { status?: string } | undefined)?.status ?? null,
);

const confirmOpen = ref(false);
const confirmTitle = ref('Confirm Action');
const confirmDescription = ref('');
const confirmLabel = ref('Confirm');
const confirmDestructive = ref(false);
const pendingAction = ref<{ action: string; row: VerificationRow } | null>(
    null,
);

const rejectDialogOpen = ref(false);
const rejectForm = reactive({
    row: null as VerificationRow | null,
    decision_status: 'rejected',
    reason: '',
});

const invoiceDialogOpen = ref(false);
const invoiceForm = reactive({
    row: null as VerificationRow | null,
    amount: '',
    currency: 'BDT',
    due_at: '',
    expires_at: '',
    notes: '',
});

const markPaidDialogOpen = ref(false);
const markPaidForm = reactive({
    row: null as VerificationRow | null,
    payment_gateway: 'manual',
    payment_method: 'manual',
    payment_reference: '',
    paid_at: '',
    notes: '',
});

const statusBadgeVariant = (
    status: string | null | undefined,
): 'default' | 'destructive' | 'secondary' | 'outline' => {
    if (status === 'verified') {
        return 'default';
    }

    if (status === 'rejected' || status === 'cancelled') {
        return 'destructive';
    }

    return 'secondary';
};

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
    () => props.filters.status,
    (value) => {
        const normalized = value || 'all';

        if (normalized !== statusFilter.value) {
            statusFilter.value = normalized;
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

watch(statusFilter, (value) => {
    applyFilters({ status: value === 'all' ? '' : value, page: 1 });
});

watch(roleFilter, (value) => {
    applyFilters({ role: value === 'all' ? '' : value, page: 1 });
});

onBeforeUnmount(() => {
    if (searchTimer) {
        clearTimeout(searchTimer);
    }
});

const hasActiveDialog = computed(() => {
    return (
        rejectDialogOpen.value ||
        invoiceDialogOpen.value ||
        markPaidDialogOpen.value
    );
});

function applyFilters(overrides = {}) {
    if (hasActiveDialog.value) {
        return;
    }

    router.get(
        baseUrl,
        {
            q: search.value,
            status: statusFilter.value === 'all' ? '' : statusFilter.value,
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

function openConfirm(action: string, row: VerificationRow): void {
    pendingAction.value = { action, row };
    confirmTitle.value = 'Confirm Action';
    confirmDescription.value = '';
    confirmLabel.value = 'Confirm';
    confirmDestructive.value = false;

    if (action === 'approve') {
        confirmTitle.value = 'Approve Verification';
        confirmDescription.value =
            'This will approve the verification request for invoice generation.';
        confirmLabel.value = 'Approve';
    }

    if (action === 'invoice') {
        confirmTitle.value = 'Generate Invoice';
        confirmDescription.value =
            'Generate verification invoice for this request?';
        confirmLabel.value = 'Generate';
    }

    confirmOpen.value = true;
}

function runConfirmedAction() {
    if (!pendingAction.value) {
        return;
    }

    const { action, row } = pendingAction.value;

    if (action === 'approve') {
        router.patch(
            `/admin/verifications/${row.id}/approve`,
            {},
            {
                preserveScroll: true,
            },
        );
    }

    if (action === 'invoice') {
        router.post(
            `/admin/verifications/${row.id}/invoice`,
            {},
            {
                preserveScroll: true,
            },
        );
    }

    confirmOpen.value = false;
    pendingAction.value = null;
}

function actionItemsForRow(
    row: VerificationRow,
): Array<{ key: string; label: string }> {
    const actions = [{ key: 'view', label: 'View' }];

    if (row.status === 'pending') {
        actions.push({ key: 'approve', label: 'Approve' });
    }

    if (['pending', 'approved', 'invoiced'].includes(row.status)) {
        actions.push({ key: 'reject', label: 'Reject / Cancel' });
    }

    const canGenerateInvoice =
        ['pending', 'approved'].includes(row.status) &&
        (!row.invoice_status || ['void'].includes(row.invoice_status));

    if (canGenerateInvoice) {
        actions.push({ key: 'invoice', label: 'Generate Invoice' });
    }

    const canMarkPaid =
        row.invoice_id &&
        ['unpaid', 'draft'].includes(row.invoice_status ?? '');

    if (canMarkPaid) {
        actions.push({ key: 'mark-paid', label: 'Mark Paid' });
    }

    return actions;
}

function handleAction(action: string, row: VerificationRow): void {
    if (action === 'view') {
        router.visit(`/admin/verifications/${row.id}`);
        return;
    }

    if (action === 'approve' || action === 'invoice') {
        openConfirm(action, row);
        return;
    }

    if (action === 'reject') {
        rejectForm.row = row;
        rejectForm.decision_status = 'rejected';
        rejectForm.reason = '';
        rejectDialogOpen.value = true;
        return;
    }

    if (action === 'mark-paid') {
        markPaidForm.row = row;
        markPaidForm.payment_gateway = 'manual';
        markPaidForm.payment_method = 'manual';
        markPaidForm.payment_reference = '';
        markPaidForm.paid_at = new Date().toISOString().slice(0, 16);
        markPaidForm.notes = '';
        markPaidDialogOpen.value = true;
    }
}

function submitReject() {
    if (!rejectForm.row) {
        return;
    }

    router.patch(
        `/admin/verifications/${rejectForm.row.id}/reject`,
        {
            decision_status: rejectForm.decision_status,
            reason: rejectForm.reason,
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                rejectDialogOpen.value = false;
            },
        },
    );
}

function openInvoiceDialog(row: VerificationRow): void {
    invoiceForm.row = row;
    invoiceForm.amount = row.fee_amount ? String(row.fee_amount) : '';
    invoiceForm.currency = row.currency || 'BDT';
    invoiceForm.due_at = '';
    invoiceForm.expires_at = '';
    invoiceForm.notes = '';
    invoiceDialogOpen.value = true;
}

function submitInvoice() {
    if (!invoiceForm.row) {
        return;
    }

    const payload = {
        amount: invoiceForm.amount || null,
        currency: invoiceForm.currency || null,
        due_at: invoiceForm.due_at || null,
        expires_at: invoiceForm.expires_at || null,
        notes: invoiceForm.notes || null,
    };

    router.post(`/admin/verifications/${invoiceForm.row.id}/invoice`, payload, {
        preserveScroll: true,
        onSuccess: () => {
            invoiceDialogOpen.value = false;
        },
    });
}

function submitMarkPaid() {
    if (!markPaidForm.row?.invoice_id) {
        return;
    }

    router.patch(
        `/admin/invoices/${markPaidForm.row.invoice_id}/mark-paid`,
        {
            payment_gateway: markPaidForm.payment_gateway,
            payment_method: markPaidForm.payment_method,
            payment_reference: markPaidForm.payment_reference || null,
            paid_at: markPaidForm.paid_at || null,
            notes: markPaidForm.notes || null,
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                markPaidDialogOpen.value = false;
            },
        },
    );
}
</script>

<template>
    <Head title="Verification Requests" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-4 sm:p-6">
            <div
                class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6"
            >
                <h1 class="text-2xl font-semibold tracking-tight">
                    Verification Requests
                </h1>
                <p class="mt-1 text-sm text-muted-foreground">
                    Review verification requests and manage invoice/payment
                    lifecycle.
                </p>
            </div>

            <div
                v-if="flashStatus"
                class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800"
            >
                {{ flashStatus }}
            </div>

            <div
                v-if="
                    $page.props.errors?.verification ||
                    $page.props.errors?.invoice
                "
                class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800"
            >
                {{
                    $page.props.errors.verification ||
                    $page.props.errors.invoice
                }}
            </div>

            <div
                class="grid gap-3 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm md:grid-cols-3"
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
                    <Label>Status</Label>
                    <Select v-model="statusFilter">
                        <SelectTrigger>
                            <SelectValue placeholder="All status" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">All status</SelectItem>
                            <SelectItem
                                v-for="status in statusOptionsList"
                                :key="status"
                                :value="status"
                                >{{ status }}</SelectItem
                            >
                        </SelectContent>
                    </Select>
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
                                v-for="role in roleOptionsList"
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
                empty-text="No verification requests found."
            >
                <template #cell-user_name="{ row }">
                    <div class="space-y-0.5">
                        <p class="font-medium">{{ row.user_name || '—' }}</p>
                        <p class="text-xs text-muted-foreground">
                            {{ row.user_email || '—' }}
                        </p>
                    </div>
                </template>

                <template #cell-role="{ value }">
                    <Badge variant="outline" class="uppercase">{{
                        value
                    }}</Badge>
                </template>

                <template #cell-status="{ value }">
                    <Badge :variant="statusBadgeVariant(value)">{{
                        value
                    }}</Badge>
                </template>

                <template #cell-submitted_at="{ value }">
                    {{ value ? new Date(value).toLocaleString() : '—' }}
                </template>

                <template #cell-fee_amount="{ row }">
                    {{ row.fee_amount }} {{ row.currency }}
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
                    <RowActionsDropdown
                        :actions="actionItemsForRow(row)"
                        @select="
                            (action) => {
                                if (
                                    action === 'invoice' &&
                                    ['pending', 'approved'].includes(
                                        row.status,
                                    ) &&
                                    (!row.invoice_status ||
                                        ['void'].includes(row.invoice_status))
                                ) {
                                    openInvoiceDialog(row);
                                    return;
                                }

                                handleAction(action, row);
                            }
                        "
                    />
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
            @cancel="pendingAction = null"
        />

        <Dialog
            :open="rejectDialogOpen"
            @update:open="rejectDialogOpen = $event"
        >
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Reject / Cancel Request</DialogTitle>
                    <DialogDescription
                        >Provide a reason for this decision.</DialogDescription
                    >
                </DialogHeader>

                <div class="grid gap-4 py-2">
                    <div class="grid gap-2">
                        <Label>Decision</Label>
                        <Select v-model="rejectForm.decision_status">
                            <SelectTrigger>
                                <SelectValue placeholder="Select decision" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="rejected">Reject</SelectItem>
                                <SelectItem value="cancelled"
                                    >Cancel</SelectItem
                                >
                            </SelectContent>
                        </Select>
                    </div>

                    <div class="grid gap-2">
                        <Label for="reject_reason">Reason</Label>
                        <textarea
                            id="reject_reason"
                            v-model="rejectForm.reason"
                            rows="4"
                            class="rounded-md border px-3 py-2 text-sm"
                            placeholder="Write decision reason"
                        ></textarea>
                    </div>
                </div>

                <DialogFooter>
                    <Button
                        type="button"
                        variant="outline"
                        @click="rejectDialogOpen = false"
                        >Cancel</Button
                    >
                    <Button
                        type="button"
                        variant="destructive"
                        @click="submitReject"
                        >Submit Decision</Button
                    >
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <Dialog
            :open="invoiceDialogOpen"
            @update:open="invoiceDialogOpen = $event"
        >
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Generate Invoice</DialogTitle>
                    <DialogDescription
                        >Set invoice amount and expiry
                        information.</DialogDescription
                    >
                </DialogHeader>

                <div class="grid gap-4 py-2">
                    <div class="grid gap-2">
                        <Label for="amount">Amount</Label>
                        <Input
                            id="amount"
                            v-model="invoiceForm.amount"
                            type="number"
                            min="1"
                            step="0.01"
                        />
                    </div>

                    <div class="grid gap-2">
                        <Label for="currency">Currency</Label>
                        <Input
                            id="currency"
                            v-model="invoiceForm.currency"
                            type="text"
                        />
                    </div>

                    <div class="grid gap-2">
                        <Label for="due_at">Due At</Label>
                        <Input
                            id="due_at"
                            v-model="invoiceForm.due_at"
                            type="datetime-local"
                        />
                    </div>

                    <div class="grid gap-2">
                        <Label for="expires_at">Expires At</Label>
                        <Input
                            id="expires_at"
                            v-model="invoiceForm.expires_at"
                            type="datetime-local"
                        />
                    </div>

                    <div class="grid gap-2">
                        <Label for="invoice_notes">Notes</Label>
                        <textarea
                            id="invoice_notes"
                            v-model="invoiceForm.notes"
                            rows="3"
                            class="rounded-md border px-3 py-2 text-sm"
                        ></textarea>
                    </div>
                </div>

                <DialogFooter>
                    <Button
                        type="button"
                        variant="outline"
                        @click="invoiceDialogOpen = false"
                        >Cancel</Button
                    >
                    <Button type="button" @click="submitInvoice"
                        >Generate</Button
                    >
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <Dialog
            :open="markPaidDialogOpen"
            @update:open="markPaidDialogOpen = $event"
        >
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Manual Mark Paid</DialogTitle>
                    <DialogDescription
                        >Use manual override to confirm invoice
                        payment.</DialogDescription
                    >
                </DialogHeader>

                <div class="grid gap-4 py-2">
                    <div class="grid gap-2">
                        <Label>Gateway</Label>
                        <Select v-model="markPaidForm.payment_gateway">
                            <SelectTrigger>
                                <SelectValue placeholder="Select gateway" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="manual">manual</SelectItem>
                                <SelectItem value="bkash">bkash</SelectItem>
                                <SelectItem value="sslcommerz"
                                    >sslcommerz</SelectItem
                                >
                            </SelectContent>
                        </Select>
                    </div>

                    <div class="grid gap-2">
                        <Label for="payment_method">Payment Method</Label>
                        <Input
                            id="payment_method"
                            v-model="markPaidForm.payment_method"
                            type="text"
                        />
                    </div>

                    <div class="grid gap-2">
                        <Label for="payment_reference">Payment Reference</Label>
                        <Input
                            id="payment_reference"
                            v-model="markPaidForm.payment_reference"
                            type="text"
                        />
                    </div>

                    <div class="grid gap-2">
                        <Label for="paid_at">Paid At</Label>
                        <Input
                            id="paid_at"
                            v-model="markPaidForm.paid_at"
                            type="datetime-local"
                        />
                    </div>

                    <div class="grid gap-2">
                        <Label for="mark_paid_notes">Notes</Label>
                        <textarea
                            id="mark_paid_notes"
                            v-model="markPaidForm.notes"
                            rows="3"
                            class="rounded-md border px-3 py-2 text-sm"
                        ></textarea>
                    </div>
                </div>

                <DialogFooter>
                    <Button
                        type="button"
                        variant="outline"
                        @click="markPaidDialogOpen = false"
                        >Cancel</Button
                    >
                    <Button type="button" @click="submitMarkPaid"
                        >Mark Paid</Button
                    >
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AdminLayout>
</template>
