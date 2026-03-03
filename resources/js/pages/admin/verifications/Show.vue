<script setup>
import { Head, router } from '@inertiajs/vue3';
import { computed, reactive, ref } from 'vue';
import ConfirmDialog from '@/components/admin/dialogs/ConfirmDialog.vue';
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
    verification: {
        type: Object,
        required: true,
    },
    profileSnapshot: {
        type: Object,
        default: null,
    },
    educationSnapshot: {
        type: Array,
        default: () => [],
    },
});

const breadcrumbs = [
    { title: 'Verifications', href: '/admin/verifications' },
    {
        title: `Request #${props.verification.id}`,
        href: `/admin/verifications/${props.verification.id}`,
    },
];

const confirmOpen = ref(false);
const confirmTitle = ref('Confirm Action');
const confirmDescription = ref('');
const confirmLabel = ref('Confirm');
const confirmDestructive = ref(false);
const pendingAction = ref(null);

const rejectDialogOpen = ref(false);
const rejectForm = reactive({
    decision_status: 'rejected',
    reason: '',
});

const invoiceDialogOpen = ref(false);
const invoiceForm = reactive({
    amount: props.verification.fee_amount,
    currency: props.verification.currency || 'BDT',
    due_at: '',
    expires_at: '',
    notes: '',
});

const markPaidDialogOpen = ref(false);
const markPaidForm = reactive({
    payment_gateway: 'manual',
    payment_method: 'manual',
    payment_reference: '',
    paid_at: new Date().toISOString().slice(0, 16),
    notes: '',
});

const canApprove = computed(() => props.verification.status === 'pending');
const canReject = computed(() =>
    ['pending', 'approved', 'invoiced'].includes(props.verification.status),
);
const canGenerateInvoice = computed(() => {
    if (!['pending', 'approved'].includes(props.verification.status)) {
        return false;
    }

    const status = props.verification.invoice?.status;

    return !status || ['void'].includes(status);
});
const canMarkPaid = computed(() => {
    const invoiceStatus = props.verification.invoice?.status;

    return (
        Boolean(props.verification.invoice?.id) &&
        ['unpaid', 'draft'].includes(invoiceStatus)
    );
});

const timeline = computed(() => {
    const events = [
        {
            key: 'submitted',
            title: 'Submitted',
            at: props.verification.submitted_at,
            visible: true,
        },
        {
            key: 'reviewed',
            title: 'Reviewed',
            at: props.verification.reviewed_at,
            visible: Boolean(props.verification.reviewed_at),
        },
        {
            key: 'invoice',
            title: 'Invoice Issued',
            at: props.verification.invoice?.issued_at,
            visible: Boolean(props.verification.invoice),
        },
        {
            key: 'paid',
            title: 'Invoice Paid',
            at: props.verification.invoice?.paid_at,
            visible: Boolean(props.verification.invoice?.paid_at),
        },
        {
            key: 'verified',
            title: 'User Verified',
            at: props.verification.user?.verified_at,
            visible: props.verification.status === 'verified',
        },
    ];

    return events.filter((event) => event.visible);
});

function openConfirm(action) {
    pendingAction.value = action;
    confirmTitle.value = 'Confirm Action';
    confirmDescription.value = '';
    confirmLabel.value = 'Confirm';
    confirmDestructive.value = false;

    if (action === 'approve') {
        confirmTitle.value = 'Approve Verification';
        confirmDescription.value =
            'Approve this request for invoice generation?';
        confirmLabel.value = 'Approve';
    }

    confirmOpen.value = true;
}

function runConfirmedAction() {
    if (pendingAction.value === 'approve') {
        router.patch(
            `/admin/verifications/${props.verification.id}/approve`,
            {},
            {
                preserveScroll: true,
            },
        );
    }

    pendingAction.value = null;
    confirmOpen.value = false;
}

function submitReject() {
    router.patch(
        `/admin/verifications/${props.verification.id}/reject`,
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

function submitInvoice() {
    router.post(
        `/admin/verifications/${props.verification.id}/invoice`,
        {
            amount: invoiceForm.amount || null,
            currency: invoiceForm.currency || null,
            due_at: invoiceForm.due_at || null,
            expires_at: invoiceForm.expires_at || null,
            notes: invoiceForm.notes || null,
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                invoiceDialogOpen.value = false;
            },
        },
    );
}

function submitMarkPaid() {
    if (!props.verification.invoice?.id) {
        return;
    }

    router.patch(
        `/admin/invoices/${props.verification.invoice.id}/mark-paid`,
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
    <Head :title="`Verification #${verification.id}`" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h1 class="text-2xl font-semibold">
                        Verification Request #{{ verification.id }}
                    </h1>
                    <p class="text-sm text-muted-foreground">
                        {{ verification.role }} verification review
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <Button
                        type="button"
                        variant="outline"
                        @click="router.visit('/admin/verifications')"
                        >Back</Button
                    >
                    <Button
                        v-if="canApprove"
                        type="button"
                        @click="openConfirm('approve')"
                        >Approve</Button
                    >
                    <Button
                        v-if="canReject"
                        type="button"
                        variant="destructive"
                        @click="rejectDialogOpen = true"
                        >Reject / Cancel</Button
                    >
                    <Button
                        v-if="canGenerateInvoice"
                        type="button"
                        variant="outline"
                        @click="invoiceDialogOpen = true"
                        >Generate Invoice</Button
                    >
                    <Button
                        v-if="canMarkPaid"
                        type="button"
                        variant="outline"
                        @click="markPaidDialogOpen = true"
                        >Mark Paid</Button
                    >
                </div>
            </div>

            <div
                v-if="$page.props.flash?.status"
                class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800"
            >
                {{ $page.props.flash.status }}
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

            <section
                class="grid gap-4 rounded-xl border bg-white p-5 lg:grid-cols-2"
            >
                <div class="space-y-2 text-sm">
                    <h2 class="text-lg font-semibold">Request Summary</h2>
                    <p>
                        <span class="font-medium">Role:</span>
                        {{ verification.role }}
                    </p>
                    <p>
                        <span class="font-medium">Status:</span>
                        <Badge
                            class="ml-2"
                            :variant="
                                verification.status === 'verified'
                                    ? 'default'
                                    : ['rejected', 'cancelled'].includes(
                                            verification.status,
                                        )
                                      ? 'destructive'
                                      : 'secondary'
                            "
                        >
                            {{ verification.status }}
                        </Badge>
                    </p>
                    <p>
                        <span class="font-medium">Fee:</span>
                        {{ verification.fee_amount }}
                        {{ verification.currency }}
                    </p>
                    <p>
                        <span class="font-medium">Submitted:</span>
                        {{
                            verification.submitted_at
                                ? new Date(
                                      verification.submitted_at,
                                  ).toLocaleString()
                                : '—'
                        }}
                    </p>
                    <p>
                        <span class="font-medium">Reviewed By:</span>
                        {{ verification.reviewed_by || '—' }}
                    </p>
                    <p>
                        <span class="font-medium">Reviewed At:</span>
                        {{
                            verification.reviewed_at
                                ? new Date(
                                      verification.reviewed_at,
                                  ).toLocaleString()
                                : '—'
                        }}
                    </p>
                    <p v-if="verification.decision_reason">
                        <span class="font-medium">Decision Reason:</span>
                        {{ verification.decision_reason }}
                    </p>
                </div>

                <div class="space-y-2 text-sm">
                    <h2 class="text-lg font-semibold">User Summary</h2>
                    <p>
                        <span class="font-medium">Name:</span>
                        {{ verification.user?.name || '—' }}
                    </p>
                    <p>
                        <span class="font-medium">Email:</span>
                        {{ verification.user?.email || '—' }}
                    </p>
                    <p>
                        <span class="font-medium">Phone:</span>
                        {{ verification.user?.phone || '—' }}
                    </p>
                    <p>
                        <span class="font-medium">Verification Status:</span>
                        {{ verification.user?.verification_status || '—' }}
                    </p>
                    <p>
                        <span class="font-medium">Verified At:</span>
                        {{
                            verification.user?.verified_at
                                ? new Date(
                                      verification.user.verified_at,
                                  ).toLocaleString()
                                : '—'
                        }}
                    </p>
                </div>
            </section>

            <section
                class="grid gap-4 rounded-xl border bg-white p-5 lg:grid-cols-2"
            >
                <div class="space-y-2 text-sm">
                    <h2 class="text-lg font-semibold">Invoice</h2>
                    <p
                        v-if="!verification.invoice"
                        class="text-muted-foreground"
                    >
                        No invoice generated.
                    </p>
                    <template v-else>
                        <p>
                            <span class="font-medium">Invoice No:</span>
                            {{ verification.invoice.invoice_no }}
                        </p>
                        <p>
                            <span class="font-medium">Status:</span>
                            {{ verification.invoice.status }}
                        </p>
                        <p>
                            <span class="font-medium">Amount:</span>
                            {{ verification.invoice.amount }}
                            {{ verification.invoice.currency }}
                        </p>
                        <p>
                            <span class="font-medium">Due At:</span>
                            {{
                                verification.invoice.due_at
                                    ? new Date(
                                          verification.invoice.due_at,
                                      ).toLocaleString()
                                    : '—'
                            }}
                        </p>
                        <p>
                            <span class="font-medium">Expires At:</span>
                            {{
                                verification.invoice.expires_at
                                    ? new Date(
                                          verification.invoice.expires_at,
                                      ).toLocaleString()
                                    : '—'
                            }}
                        </p>
                        <p>
                            <span class="font-medium">Paid At:</span>
                            {{
                                verification.invoice.paid_at
                                    ? new Date(
                                          verification.invoice.paid_at,
                                      ).toLocaleString()
                                    : '—'
                            }}
                        </p>
                        <p>
                            <span class="font-medium">Gateway:</span>
                            {{ verification.invoice.payment_gateway || '—' }}
                        </p>
                        <p>
                            <span class="font-medium">Reference:</span>
                            {{ verification.invoice.payment_reference || '—' }}
                        </p>
                        <p>
                            <span class="font-medium">Transaction ID:</span>
                            {{ verification.invoice.transaction_id || '—' }}
                        </p>
                    </template>
                </div>

                <div class="space-y-2 text-sm">
                    <h2 class="text-lg font-semibold">Status Timeline</h2>
                    <ul class="space-y-2">
                        <li
                            v-for="event in timeline"
                            :key="event.key"
                            class="rounded-md border px-3 py-2"
                        >
                            <p class="font-medium">{{ event.title }}</p>
                            <p class="text-xs text-muted-foreground">
                                {{
                                    event.at
                                        ? new Date(event.at).toLocaleString()
                                        : '—'
                                }}
                            </p>
                        </li>
                    </ul>
                </div>
            </section>

            <section class="rounded-xl border bg-white p-5">
                <h2 class="text-lg font-semibold">Profile Snapshot</h2>

                <div
                    v-if="!profileSnapshot"
                    class="mt-3 text-sm text-muted-foreground"
                >
                    No profile snapshot available.
                </div>

                <div v-else class="mt-3 space-y-4 text-sm">
                    <div class="grid gap-2 md:grid-cols-2">
                        <p
                            v-for="(value, key) in profileSnapshot"
                            :key="key"
                            class="break-words"
                        >
                            <span class="font-medium">{{ key }}:</span>
                            {{
                                Array.isArray(value)
                                    ? value.join(', ')
                                    : (value ?? '—')
                            }}
                        </p>
                    </div>

                    <div
                        v-if="
                            verification.role === 'tutor' &&
                            educationSnapshot.length
                        "
                        class="space-y-2"
                    >
                        <h3 class="font-semibold">Education Snapshot</h3>
                        <div
                            v-for="education in educationSnapshot"
                            :key="education.id"
                            class="rounded-md border px-3 py-2"
                        >
                            <p>
                                <span class="font-medium">Degree:</span>
                                {{ education.degree }}
                            </p>
                            <p>
                                <span class="font-medium">Institute:</span>
                                {{ education.institute }}
                            </p>
                            <p>
                                <span class="font-medium">Department:</span>
                                {{ education.department || '—' }}
                            </p>
                            <p>
                                <span class="font-medium">Year:</span>
                                {{ education.graduation_year || '—' }}
                            </p>
                            <p>
                                <span class="font-medium">Result:</span>
                                {{ education.result || '—' }}
                            </p>
                        </div>
                    </div>
                </div>
            </section>
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
                        <Label for="reason">Reason</Label>
                        <textarea
                            id="reason"
                            v-model="rejectForm.reason"
                            rows="4"
                            class="rounded-md border px-3 py-2 text-sm"
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
                        >Submit</Button
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
                        <Label for="notes">Notes</Label>
                        <textarea
                            id="notes"
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
                        <Label for="manual_notes">Notes</Label>
                        <textarea
                            id="manual_notes"
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
