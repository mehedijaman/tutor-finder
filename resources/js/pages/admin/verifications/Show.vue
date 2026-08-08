<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import {
    AlertCircle,
    ArrowLeft,
    Building2,
    Calendar,
    Check,
    CheckCircle2,
    Clock,
    Copy,
    CreditCard,
    DollarSign,
    ExternalLink,
    FileCheck,
    FileText,
    GraduationCap,
    Hash,
    HelpCircle,
    Mail,
    MapPin,
    Phone,
    Receipt,
    ShieldAlert,
    ShieldCheck,
    User as UserIcon,
    XCircle,
} from 'lucide-vue-next';
import { computed, reactive, ref } from 'vue';
import ConfirmDialog from '@/components/admin/dialogs/ConfirmDialog.vue';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
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
import { Separator } from '@/components/ui/separator';
import { Textarea } from '@/components/ui/textarea';
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

type EducationSnapshotItem = {
    id: number;
    degree?: string | null;
    institute?: string | null;
    department?: string | null;
    graduation_year?: string | number | null;
    result?: string | null;
};

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
const pendingAction = ref<'approve' | null>(null);

const copiedText = ref<string | null>(null);

const page = usePage();
const flashStatus = computed<string | null>(
    () => (page.props.flash as { status?: string } | undefined)?.status ?? null,
);

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

const userInitials = computed(() => {
    const name = props.verification.user?.name || 'User';
    return name
        .split(' ')
        .map((part: string) => part[0])
        .slice(0, 2)
        .join('')
        .toUpperCase();
});

const timelineSteps = computed(() => {
    const status = props.verification.status;
    const isRejectedOrCancelled = ['rejected', 'cancelled'].includes(status);

    return [
        {
            key: 'submitted',
            label: 'Submitted',
            description: 'Created',
            icon: Clock,
            at: props.verification.submitted_at,
            completed: Boolean(props.verification.submitted_at),
            active: status === 'pending',
        },
        {
            key: 'reviewed',
            label: isRejectedOrCancelled
                ? status === 'rejected'
                    ? 'Rejected'
                    : 'Cancelled'
                : 'Approved',
            description: props.verification.reviewed_by
                ? `By ${props.verification.reviewed_by}`
                : 'Review',
            icon: isRejectedOrCancelled ? ShieldAlert : ShieldCheck,
            at: props.verification.reviewed_at,
            completed: Boolean(props.verification.reviewed_at),
            active: ['approved', 'rejected', 'cancelled'].includes(status),
            isDestructive: isRejectedOrCancelled,
        },
        {
            key: 'invoice',
            label: 'Invoice Issued',
            description: props.verification.invoice?.invoice_no || 'Pending',
            icon: Receipt,
            at: props.verification.invoice?.issued_at,
            completed: Boolean(props.verification.invoice),
            active:
                status === 'invoiced' &&
                props.verification.invoice?.status === 'unpaid',
        },
        {
            key: 'paid',
            label: 'Invoice Paid',
            description:
                props.verification.invoice?.payment_gateway || 'Payment',
            icon: CreditCard,
            at: props.verification.invoice?.paid_at,
            completed: Boolean(props.verification.invoice?.paid_at),
            active: props.verification.invoice?.status === 'paid',
        },
        {
            key: 'verified',
            label: 'Verified',
            description: 'Completed',
            icon: CheckCircle2,
            at: props.verification.user?.verified_at,
            completed:
                status === 'verified' ||
                Boolean(props.verification.user?.verified_at),
            active: status === 'verified',
        },
    ];
});

function formatDateTime(dateStr: string | null | undefined): string {
    if (!dateStr) {
        return '—';
    }
    try {
        const d = new Date(dateStr);
        if (isNaN(d.getTime())) return String(dateStr);
        return d.toLocaleString(undefined, {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
        });
    } catch {
        return String(dateStr);
    }
}

function formatDateOnly(dateStr: string | null | undefined): string {
    if (!dateStr) {
        return '—';
    }
    try {
        const d = new Date(dateStr);
        if (isNaN(d.getTime())) return String(dateStr);
        return d.toLocaleDateString(undefined, {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
        });
    } catch {
        return String(dateStr);
    }
}

function isDateField(key: string): boolean {
    const k = key.toLowerCase();
    return (
        k.endsWith('_at') ||
        k === 'date_of_birth' ||
        k === 'created_at' ||
        k === 'updated_at'
    );
}

function formatSnapshotValue(
    key: string,
    value: any,
): { isArray: boolean; formatted: any } {
    if (value === null || value === undefined || value === '') {
        return { isArray: false, formatted: '—' };
    }

    if (Array.isArray(value)) {
        return { isArray: true, formatted: value };
    }

    if (typeof value === 'string' && isDateField(key)) {
        return { isArray: false, formatted: formatDateTime(value) };
    }

    return { isArray: false, formatted: String(value) };
}

function formatLabel(key: string): string {
    const labels: Record<string, string> = {
        id: 'ID',
        user_id: 'User ID',
        gender: 'Gender',
        date_of_birth: 'Date of Birth',
        present_address: 'Present Address',
        permanent_address: 'Permanent Address',
        nid_no: 'NID Number',
        bio: 'Bio',
        preferred_tuition_types: 'Preferred Tuition Types',
        preferred_categories: 'Preferred Categories',
        preferred_classes: 'Preferred Classes',
        preferred_subjects: 'Preferred Subjects',
        preferred_locations: 'Preferred Locations',
        expected_salary_min: 'Min Expected Salary',
        expected_salary_max: 'Max Expected Salary',
        available_days: 'Available Days',
        available_time: 'Available Time',
        admin_notes: 'Admin Notes',
        status: 'Profile Status',
        created_at: 'Profile Created At',
        updated_at: 'Profile Updated At',
        emergency_contact: 'Emergency Contact',
        relationship_to_student: 'Relationship to Student',
        preferred_contact_time: 'Preferred Contact Time',
        city: 'City',
        area: 'Area',
    };

    if (labels[key]) {
        return labels[key];
    }

    return String(key)
        .replace(/_/g, ' ')
        .replace(/\b\w/g, (char) => char.toUpperCase());
}

function copyText(text: string) {
    if (!text) {
        return;
    }
    navigator.clipboard.writeText(text);
    copiedText.value = text;
    setTimeout(() => {
        if (copiedText.value === text) {
            copiedText.value = null;
        }
    }, 2000);
}

function getStatusBadgeVariant(status: string) {
    switch (status) {
        case 'verified':
        case 'paid':
        case 'active':
            return 'default';
        case 'approved':
        case 'invoiced':
            return 'secondary';
        case 'pending':
        case 'unpaid':
        case 'unverified':
            return 'outline';
        case 'rejected':
        case 'cancelled':
        case 'void':
            return 'destructive';
        default:
            return 'secondary';
    }
}

function openConfirm(action: 'approve') {
    pendingAction.value = action;
    confirmTitle.value = 'Approve Verification Request';
    confirmDescription.value =
        'Are you sure you want to approve this verification request? This will mark it as approved and allow invoice generation.';
    confirmLabel.value = 'Approve Request';
    confirmDestructive.value = false;
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
        <div class="space-y-4 p-4 text-slate-900 sm:p-5 lg:p-6">
            <!-- Compact Header & Action Bar -->
            <div
                class="rounded-xl border border-slate-200/80 bg-white p-4 shadow-2xs"
            >
                <div
                    class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div class="flex items-center gap-3">
                        <Avatar
                            class="h-11 w-11 shrink-0 border border-emerald-500/20 bg-emerald-50 text-emerald-800"
                        >
                            <AvatarFallback class="text-sm font-bold">
                                {{ userInitials }}
                            </AvatarFallback>
                        </Avatar>

                        <div class="space-y-0.5">
                            <div class="flex flex-wrap items-center gap-2">
                                <h1
                                    class="text-lg font-bold tracking-tight text-slate-900 sm:text-xl"
                                >
                                    Verification Request #{{ verification.id }}
                                </h1>
                                <Badge
                                    :variant="
                                        getStatusBadgeVariant(
                                            verification.status,
                                        )
                                    "
                                    class="px-2 py-0 text-[11px] font-semibold capitalize"
                                >
                                    {{ verification.status }}
                                </Badge>
                                <Badge
                                    variant="outline"
                                    class="border-slate-300 bg-slate-50 px-2 py-0 text-[11px] font-medium text-slate-700 capitalize"
                                >
                                    {{ verification.role }}
                                </Badge>
                            </div>

                            <p
                                class="flex flex-wrap items-center gap-x-2 text-xs text-slate-500"
                            >
                                <span
                                    >Submitted:
                                    <strong
                                        class="font-medium text-slate-700"
                                        >{{
                                            formatDateTime(
                                                verification.submitted_at,
                                            )
                                        }}</strong
                                    ></span
                                >
                                <span>•</span>
                                <span
                                    >Fee:
                                    <strong class="font-bold text-slate-900"
                                        >{{ verification.fee_amount }}
                                        {{ verification.currency }}</strong
                                    ></span
                                >
                            </p>
                        </div>
                    </div>

                    <!-- Compact Action Buttons Toolbar -->
                    <div
                        class="flex flex-wrap items-center gap-2 border-t border-slate-100 pt-3 sm:border-t-0 sm:pt-0"
                    >
                        <Button
                            type="button"
                            variant="outline"
                            size="sm"
                            class="h-8 gap-1 text-xs shadow-none"
                            @click="router.visit('/admin/verifications')"
                        >
                            <ArrowLeft class="h-3.5 w-3.5" />
                            <span>Back</span>
                        </Button>

                        <Button
                            v-if="canApprove"
                            type="button"
                            size="sm"
                            class="h-8 gap-1 bg-emerald-600 text-xs text-white shadow-2xs hover:bg-emerald-700"
                            @click="openConfirm('approve')"
                        >
                            <CheckCircle2 class="h-3.5 w-3.5" />
                            <span>Approve</span>
                        </Button>

                        <Button
                            v-if="canReject"
                            type="button"
                            variant="destructive"
                            size="sm"
                            class="h-8 gap-1 text-xs shadow-2xs"
                            @click="rejectDialogOpen = true"
                        >
                            <XCircle class="h-3.5 w-3.5" />
                            <span>Reject / Cancel</span>
                        </Button>

                        <Button
                            v-if="canGenerateInvoice"
                            type="button"
                            variant="outline"
                            size="sm"
                            class="h-8 gap-1 border-emerald-300 bg-emerald-50/60 text-xs text-emerald-800 shadow-none hover:bg-emerald-100"
                            @click="invoiceDialogOpen = true"
                        >
                            <Receipt class="h-3.5 w-3.5 text-emerald-600" />
                            <span>Generate Invoice</span>
                        </Button>

                        <Button
                            v-if="canMarkPaid"
                            type="button"
                            variant="outline"
                            size="sm"
                            class="h-8 gap-1 border-blue-300 bg-blue-50/60 text-xs text-blue-800 shadow-none hover:bg-blue-100"
                            @click="markPaidDialogOpen = true"
                        >
                            <CreditCard class="h-3.5 w-3.5 text-blue-600" />
                            <span>Mark Paid</span>
                        </Button>
                    </div>
                </div>
            </div>

            <!-- Flash Alert Banners -->
            <div
                v-if="flashStatus"
                class="flex items-center gap-2.5 rounded-lg border border-emerald-200 bg-emerald-50/90 px-3.5 py-2.5 text-xs font-medium text-emerald-900 shadow-2xs"
            >
                <CheckCircle2 class="h-4 w-4 shrink-0 text-emerald-600" />
                <div>{{ flashStatus }}</div>
            </div>

            <div
                v-if="
                    $page.props.errors?.verification ||
                    $page.props.errors?.invoice
                "
                class="flex items-center gap-2.5 rounded-lg border border-rose-200 bg-rose-50/90 px-3.5 py-2.5 text-xs font-medium text-rose-900 shadow-2xs"
            >
                <AlertCircle class="h-4 w-4 shrink-0 text-rose-600" />
                <div>
                    {{
                        $page.props.errors.verification ||
                        $page.props.errors.invoice
                    }}
                </div>
            </div>

            <!-- Compact Workflow Stepper Timeline -->
            <Card class="border-slate-200/80 shadow-2xs">
                <CardHeader class="border-b border-slate-100 px-4 py-3">
                    <div class="flex items-center justify-between">
                        <CardTitle
                            class="flex items-center gap-1.5 text-xs font-bold tracking-wider text-slate-600 uppercase"
                        >
                            <Clock class="h-3.5 w-3.5 text-emerald-600" />
                            <span>Verification Workflow Stepper</span>
                        </CardTitle>
                    </div>
                </CardHeader>
                <CardContent class="p-3">
                    <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-5">
                        <div
                            v-for="(step, index) in timelineSteps"
                            :key="step.key"
                            class="flex items-center justify-between rounded-lg border p-2.5 text-xs transition-all"
                            :class="[
                                step.completed
                                    ? 'border-emerald-200 bg-emerald-50/30 text-emerald-950'
                                    : step.active
                                      ? 'border-blue-300 bg-blue-50/40 text-blue-950 ring-1 ring-blue-400/30'
                                      : step.isDestructive
                                        ? 'border-rose-200 bg-rose-50/30 text-rose-950'
                                        : 'border-slate-100 bg-slate-50/40 text-slate-500 opacity-60',
                            ]"
                        >
                            <div class="flex min-w-0 items-center gap-2.5">
                                <div
                                    class="flex h-7 w-7 shrink-0 items-center justify-center rounded-md text-xs font-bold"
                                    :class="[
                                        step.completed
                                            ? 'bg-emerald-600 text-white'
                                            : step.active
                                              ? 'bg-blue-600 text-white'
                                              : step.isDestructive
                                                ? 'bg-rose-600 text-white'
                                                : 'bg-slate-200 text-slate-600',
                                    ]"
                                >
                                    <component
                                        :is="step.icon"
                                        class="h-3.5 w-3.5"
                                    />
                                </div>

                                <div class="min-w-0">
                                    <h4
                                        class="truncate text-[11px] leading-tight font-bold text-slate-900"
                                    >
                                        {{ index + 1 }}. {{ step.label }}
                                    </h4>
                                    <p
                                        class="truncate text-[10px] text-slate-500"
                                    >
                                        {{ formatDateTime(step.at) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Main Content Layout Grid -->
            <div class="grid gap-4 lg:grid-cols-12">
                <!-- Left Main Column (8 cols) -->
                <div class="space-y-4 lg:col-span-8">
                    <!-- Request Summary Card -->
                    <Card class="border-slate-200/80 shadow-2xs">
                        <CardHeader class="border-b border-slate-100 px-4 py-3">
                            <CardTitle
                                class="flex items-center gap-1.5 text-xs font-bold tracking-wider text-slate-600 uppercase"
                            >
                                <ShieldCheck class="h-4 w-4 text-emerald-600" />
                                <span>Request Summary Details</span>
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-3 p-4">
                            <div class="grid gap-3 text-xs sm:grid-cols-3">
                                <div
                                    class="rounded-md border border-slate-100 bg-slate-50 p-2.5"
                                >
                                    <span
                                        class="block text-[10px] font-semibold tracking-wider text-slate-400 uppercase"
                                        >Role</span
                                    >
                                    <span
                                        class="text-sm font-bold text-slate-900 capitalize"
                                        >{{ verification.role }}</span
                                    >
                                </div>

                                <div
                                    class="rounded-md border border-slate-100 bg-slate-50 p-2.5"
                                >
                                    <span
                                        class="block text-[10px] font-semibold tracking-wider text-slate-400 uppercase"
                                        >Status</span
                                    >
                                    <Badge
                                        :variant="
                                            getStatusBadgeVariant(
                                                verification.status,
                                            )
                                        "
                                        class="mt-0.5 text-[11px] capitalize"
                                    >
                                        {{ verification.status }}
                                    </Badge>
                                </div>

                                <div
                                    class="rounded-md border border-slate-100 bg-slate-50 p-2.5"
                                >
                                    <span
                                        class="block text-[10px] font-semibold tracking-wider text-slate-400 uppercase"
                                        >Verification Fee</span
                                    >
                                    <span
                                        class="text-sm font-bold text-slate-900"
                                        >{{ verification.fee_amount }}
                                        {{ verification.currency }}</span
                                    >
                                </div>

                                <div
                                    class="rounded-md border border-slate-100 bg-slate-50 p-2.5"
                                >
                                    <span
                                        class="block text-[10px] font-semibold tracking-wider text-slate-400 uppercase"
                                        >Submitted At</span
                                    >
                                    <span
                                        class="font-semibold text-slate-800"
                                        >{{
                                            formatDateTime(
                                                verification.submitted_at,
                                            )
                                        }}</span
                                    >
                                </div>

                                <div
                                    class="rounded-md border border-slate-100 bg-slate-50 p-2.5"
                                >
                                    <span
                                        class="block text-[10px] font-semibold tracking-wider text-slate-400 uppercase"
                                        >Reviewed By</span
                                    >
                                    <span
                                        class="font-semibold text-slate-800"
                                        >{{
                                            verification.reviewed_by || '—'
                                        }}</span
                                    >
                                </div>

                                <div
                                    class="rounded-md border border-slate-100 bg-slate-50 p-2.5"
                                >
                                    <span
                                        class="block text-[10px] font-semibold tracking-wider text-slate-400 uppercase"
                                        >Reviewed At</span
                                    >
                                    <span
                                        class="font-semibold text-slate-800"
                                        >{{
                                            formatDateTime(
                                                verification.reviewed_at,
                                            )
                                        }}</span
                                    >
                                </div>
                            </div>

                            <div
                                v-if="verification.decision_reason"
                                class="rounded-lg border border-amber-200 bg-amber-50/80 p-3 text-xs"
                            >
                                <span
                                    class="mb-0.5 block font-bold text-amber-900"
                                    >Decision Reason / Admin Notes:</span
                                >
                                <p
                                    class="leading-snug font-normal text-amber-950"
                                >
                                    {{ verification.decision_reason }}
                                </p>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Profile Snapshot Details Card -->
                    <Card class="border-slate-200/80 shadow-2xs">
                        <CardHeader class="border-b border-slate-100 px-4 py-3">
                            <CardTitle
                                class="flex items-center justify-between text-xs font-bold tracking-wider text-slate-600 uppercase"
                            >
                                <span class="flex items-center gap-1.5">
                                    <UserIcon
                                        class="h-4 w-4 text-emerald-600"
                                    />
                                    <span>Profile Snapshot Details</span>
                                </span>
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4 p-4">
                            <div
                                v-if="!profileSnapshot"
                                class="py-4 text-center text-xs text-slate-400 italic"
                            >
                                No profile snapshot available.
                            </div>

                            <div v-else class="space-y-4 text-xs">
                                <div
                                    class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3"
                                >
                                    <template
                                        v-for="(value, key) in profileSnapshot"
                                        :key="key"
                                    >
                                        <!-- Render Array Fields (Subjects, Locations, Classes, Categories, Tuition Types) as Badges -->
                                        <div
                                            v-if="
                                                formatSnapshotValue(
                                                    String(key),
                                                    value,
                                                ).isArray
                                            "
                                            class="space-y-1.5 rounded-lg border border-slate-200/70 bg-slate-50/60 p-3 sm:col-span-2 lg:col-span-3"
                                        >
                                            <span
                                                class="block text-[11px] font-bold tracking-wider text-slate-600 uppercase"
                                            >
                                                {{ formatLabel(String(key)) }}
                                            </span>
                                            <div
                                                v-if="(value as any[]).length"
                                                class="flex flex-wrap gap-1.5"
                                            >
                                                <Badge
                                                    v-for="(
                                                        item, idx
                                                    ) in value as any[]"
                                                    :key="idx"
                                                    variant="secondary"
                                                    class="border border-slate-200 bg-white px-2 py-0.5 text-xs font-semibold text-slate-800 shadow-2xs"
                                                >
                                                    {{ item }}
                                                </Badge>
                                            </div>
                                            <span
                                                v-else
                                                class="text-slate-400 italic"
                                                >None specified</span
                                            >
                                        </div>

                                        <!-- Render Standard Key-Value Fields -->
                                        <div
                                            v-else
                                            class="space-y-0.5 rounded-lg border border-slate-100 bg-slate-50/50 p-2.5"
                                        >
                                            <span
                                                class="block text-[10px] font-semibold tracking-wider text-slate-400 uppercase"
                                            >
                                                {{ formatLabel(String(key)) }}
                                            </span>
                                            <span
                                                class="block font-semibold break-words text-slate-900"
                                            >
                                                {{
                                                    formatSnapshotValue(
                                                        String(key),
                                                        value,
                                                    ).formatted
                                                }}
                                            </span>
                                        </div>
                                    </template>
                                </div>

                                <!-- Education Snapshot Section -->
                                <div
                                    v-if="
                                        verification.role === 'tutor' &&
                                        educationSnapshot &&
                                        (
                                            educationSnapshot as EducationSnapshotItem[]
                                        ).length
                                    "
                                    class="space-y-3 border-t border-slate-100 pt-3"
                                >
                                    <h4
                                        class="flex items-center gap-1.5 text-xs font-bold tracking-wider text-slate-700 uppercase"
                                    >
                                        <GraduationCap
                                            class="h-4 w-4 text-emerald-600"
                                        />
                                        <span>Education Background</span>
                                    </h4>

                                    <div class="grid gap-2.5 sm:grid-cols-2">
                                        <div
                                            v-for="edu in educationSnapshot as EducationSnapshotItem[]"
                                            :key="edu.id"
                                            class="space-y-1.5 rounded-lg border border-slate-200 bg-white p-3 text-xs shadow-2xs"
                                        >
                                            <div
                                                class="flex items-start justify-between gap-2"
                                            >
                                                <div>
                                                    <h5
                                                        class="font-bold text-slate-900"
                                                    >
                                                        {{
                                                            edu.degree ||
                                                            'Degree'
                                                        }}
                                                    </h5>
                                                    <p
                                                        class="text-[11px] font-semibold text-emerald-700"
                                                    >
                                                        {{ edu.institute }}
                                                    </p>
                                                </div>
                                                <Badge
                                                    v-if="edu.result"
                                                    variant="outline"
                                                    class="border-emerald-200 bg-emerald-50 px-1.5 py-0 text-[10px] font-bold text-emerald-800"
                                                >
                                                    Result: {{ edu.result }}
                                                </Badge>
                                            </div>

                                            <div
                                                class="grid grid-cols-2 gap-2 border-t border-slate-100 pt-1 text-[11px] text-slate-600"
                                            >
                                                <div>
                                                    <span
                                                        class="block text-[9px] font-semibold text-slate-400 uppercase"
                                                        >Department</span
                                                    >
                                                    <span
                                                        class="font-medium text-slate-800"
                                                        >{{
                                                            edu.department ||
                                                            '—'
                                                        }}</span
                                                    >
                                                </div>
                                                <div>
                                                    <span
                                                        class="block text-[9px] font-semibold text-slate-400 uppercase"
                                                        >Passing Year</span
                                                    >
                                                    <span
                                                        class="font-medium text-slate-800"
                                                        >{{
                                                            edu.graduation_year ||
                                                            '—'
                                                        }}</span
                                                    >
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Right Sidebar Column (4 cols) -->
                <div class="space-y-4 lg:col-span-4">
                    <!-- User Account Summary -->
                    <Card class="border-slate-200/80 shadow-2xs">
                        <CardHeader class="border-b border-slate-100 px-4 py-3">
                            <CardTitle
                                class="flex items-center gap-1.5 text-xs font-bold tracking-wider text-slate-600 uppercase"
                            >
                                <UserIcon class="h-4 w-4 text-emerald-600" />
                                <span>User Account</span>
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-3 p-4 text-xs">
                            <div class="flex items-center gap-3">
                                <Avatar
                                    class="h-10 w-10 shrink-0 border bg-emerald-50 text-emerald-800"
                                >
                                    <AvatarFallback class="text-xs font-bold">
                                        {{ userInitials }}
                                    </AvatarFallback>
                                </Avatar>
                                <div class="min-w-0">
                                    <h4
                                        class="truncate font-bold text-slate-900"
                                    >
                                        {{ verification.user?.name || '—' }}
                                    </h4>
                                    <p
                                        class="font-mono text-[11px] text-slate-400"
                                    >
                                        User ID: #{{ verification.user?.id }}
                                    </p>
                                </div>
                            </div>

                            <Separator class="my-2" />

                            <div class="space-y-2">
                                <div class="flex items-center justify-between">
                                    <span
                                        class="flex items-center gap-1 text-slate-500"
                                    >
                                        <Mail class="h-3 w-3 text-slate-400" />
                                        Email
                                    </span>
                                    <span
                                        class="max-w-[160px] truncate font-semibold text-slate-900"
                                        :title="verification.user?.email"
                                    >
                                        {{ verification.user?.email || '—' }}
                                    </span>
                                </div>

                                <div class="flex items-center justify-between">
                                    <span
                                        class="flex items-center gap-1 text-slate-500"
                                    >
                                        <Phone class="h-3 w-3 text-slate-400" />
                                        Phone
                                    </span>
                                    <span class="font-semibold text-slate-900">
                                        {{ verification.user?.phone || '—' }}
                                    </span>
                                </div>

                                <div class="flex items-center justify-between">
                                    <span
                                        class="flex items-center gap-1 text-slate-500"
                                    >
                                        <ShieldCheck
                                            class="h-3 w-3 text-slate-400"
                                        />
                                        Verification
                                    </span>
                                    <Badge
                                        :variant="
                                            getStatusBadgeVariant(
                                                verification.user
                                                    ?.verification_status,
                                            )
                                        "
                                        class="px-1.5 py-0 text-[10px] capitalize"
                                    >
                                        {{
                                            verification.user
                                                ?.verification_status ||
                                            'unverified'
                                        }}
                                    </Badge>
                                </div>

                                <div class="flex items-center justify-between">
                                    <span
                                        class="flex items-center gap-1 text-slate-500"
                                    >
                                        <Calendar
                                            class="h-3 w-3 text-slate-400"
                                        />
                                        Verified At
                                    </span>
                                    <span class="font-medium text-slate-800">
                                        {{
                                            formatDateOnly(
                                                verification.user?.verified_at,
                                            )
                                        }}
                                    </span>
                                </div>
                            </div>

                            <div
                                v-if="
                                    verification.role === 'tutor' &&
                                    verification.user?.id
                                "
                                class="pt-1"
                            >
                                <Link
                                    :href="`/admin/tutors/${verification.user.id}`"
                                    class="inline-flex w-full items-center justify-center gap-1 rounded-md border border-slate-200 bg-slate-50 py-1.5 text-xs font-semibold text-slate-700 transition-colors hover:bg-slate-100 hover:text-slate-900"
                                >
                                    <span>Manage Tutor Profile</span>
                                    <ExternalLink class="h-3 w-3" />
                                </Link>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Invoice & Payment Details -->
                    <Card class="border-slate-200/80 shadow-2xs">
                        <CardHeader class="border-b border-slate-100 px-4 py-3">
                            <CardTitle
                                class="flex items-center justify-between text-xs font-bold tracking-wider text-slate-600 uppercase"
                            >
                                <span class="flex items-center gap-1.5">
                                    <Receipt class="h-4 w-4 text-emerald-600" />
                                    <span>Fee Invoice</span>
                                </span>
                                <Badge
                                    v-if="verification.invoice"
                                    :variant="
                                        getStatusBadgeVariant(
                                            verification.invoice.status,
                                        )
                                    "
                                    class="px-1.5 py-0 text-[10px] capitalize"
                                >
                                    {{ verification.invoice.status }}
                                </Badge>
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-3 p-4 text-xs">
                            <div
                                v-if="!verification.invoice"
                                class="space-y-2 py-4 text-center"
                            >
                                <Receipt
                                    class="mx-auto h-6 w-6 text-slate-300"
                                />
                                <p class="text-[11px] text-slate-500">
                                    No invoice generated yet.
                                </p>
                                <Button
                                    v-if="canGenerateInvoice"
                                    type="button"
                                    size="sm"
                                    variant="outline"
                                    class="h-7 border-emerald-300 text-xs text-emerald-700 hover:bg-emerald-50"
                                    @click="invoiceDialogOpen = true"
                                >
                                    Generate Invoice Now
                                </Button>
                            </div>

                            <div v-else class="space-y-2">
                                <div
                                    class="flex items-center justify-between rounded-md border border-slate-100 bg-slate-50 p-2"
                                >
                                    <span class="font-medium text-slate-500"
                                        >Invoice No:</span
                                    >
                                    <span
                                        class="flex items-center gap-1 font-mono text-[11px] font-bold text-slate-900"
                                    >
                                        {{ verification.invoice.invoice_no }}
                                        <button
                                            type="button"
                                            class="text-slate-400 hover:text-slate-700"
                                            title="Copy Invoice No"
                                            @click="
                                                copyText(
                                                    verification.invoice
                                                        .invoice_no,
                                                )
                                            "
                                        >
                                            <Check
                                                v-if="
                                                    copiedText ===
                                                    verification.invoice
                                                        .invoice_no
                                                "
                                                class="h-3 w-3 text-emerald-600"
                                            />
                                            <Copy v-else class="h-3 w-3" />
                                        </button>
                                    </span>
                                </div>

                                <div class="flex items-center justify-between">
                                    <span class="text-slate-500">Amount:</span>
                                    <span
                                        class="text-xs font-bold text-slate-900"
                                    >
                                        {{ verification.invoice.amount }}
                                        {{ verification.invoice.currency }}
                                    </span>
                                </div>

                                <div class="flex items-center justify-between">
                                    <span class="text-slate-500"
                                        >Issued At:</span
                                    >
                                    <span class="font-medium text-slate-800">
                                        {{
                                            formatDateTime(
                                                verification.invoice.issued_at,
                                            )
                                        }}
                                    </span>
                                </div>

                                <div class="flex items-center justify-between">
                                    <span class="text-slate-500">Due At:</span>
                                    <span class="font-medium text-slate-800">
                                        {{
                                            formatDateTime(
                                                verification.invoice.due_at,
                                            )
                                        }}
                                    </span>
                                </div>

                                <div class="flex items-center justify-between">
                                    <span class="text-slate-500">Paid At:</span>
                                    <span class="font-medium text-slate-800">
                                        {{
                                            formatDateTime(
                                                verification.invoice.paid_at,
                                            )
                                        }}
                                    </span>
                                </div>

                                <div class="flex items-center justify-between">
                                    <span class="text-slate-500">Gateway:</span>
                                    <span
                                        class="font-semibold text-slate-800 capitalize"
                                    >
                                        {{
                                            verification.invoice
                                                .payment_gateway || '—'
                                        }}
                                    </span>
                                </div>

                                <div class="flex items-center justify-between">
                                    <span class="text-slate-500"
                                        >Payment Ref:</span
                                    >
                                    <span class="font-mono text-slate-800">
                                        {{
                                            verification.invoice
                                                .payment_reference || '—'
                                        }}
                                    </span>
                                </div>

                                <div class="flex items-center justify-between">
                                    <span class="text-slate-500"
                                        >Transaction ID:</span
                                    >
                                    <span class="font-mono text-slate-800">
                                        {{
                                            verification.invoice
                                                .transaction_id || '—'
                                        }}
                                    </span>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>

        <!-- Confirm Approve Modal -->
        <ConfirmDialog
            v-model:open="confirmOpen"
            :title="confirmTitle"
            :description="confirmDescription"
            :confirm-label="confirmLabel"
            :destructive="confirmDestructive"
            @confirm="runConfirmedAction"
            @cancel="pendingAction = null"
        />

        <!-- Reject / Cancel Modal -->
        <Dialog
            :open="rejectDialogOpen"
            @update:open="rejectDialogOpen = $event"
        >
            <DialogContent class="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle
                        class="flex items-center gap-1.5 text-base text-rose-700"
                    >
                        <XCircle class="h-4 w-4" />
                        <span>Reject or Cancel Request</span>
                    </DialogTitle>
                    <DialogDescription class="text-xs">
                        Specify the decision status and reason.
                    </DialogDescription>
                </DialogHeader>

                <div class="grid gap-3 py-2 text-xs">
                    <div class="grid gap-1.5">
                        <Label class="text-xs font-semibold text-slate-700"
                            >Decision Outcome</Label
                        >
                        <Select v-model="rejectForm.decision_status">
                            <SelectTrigger>
                                <SelectValue placeholder="Select decision" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="rejected"
                                    >Reject Request</SelectItem
                                >
                                <SelectItem value="cancelled"
                                    >Cancel Request</SelectItem
                                >
                            </SelectContent>
                        </Select>
                    </div>

                    <div class="grid gap-1.5">
                        <Label
                            for="reason"
                            class="text-xs font-semibold text-slate-700"
                            >Reason / Explanation</Label
                        >
                        <Textarea
                            id="reason"
                            v-model="rejectForm.reason"
                            rows="3"
                            placeholder="Enter reason..."
                            class="text-xs"
                        />
                    </div>
                </div>

                <DialogFooter class="gap-2 sm:gap-0">
                    <Button
                        type="button"
                        variant="outline"
                        size="sm"
                        class="text-xs"
                        @click="rejectDialogOpen = false"
                        >Cancel</Button
                    >
                    <Button
                        type="button"
                        variant="destructive"
                        size="sm"
                        class="text-xs"
                        @click="submitReject"
                        >Confirm Decision</Button
                    >
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Generate Invoice Modal -->
        <Dialog
            :open="invoiceDialogOpen"
            @update:open="invoiceDialogOpen = $event"
        >
            <DialogContent class="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle
                        class="flex items-center gap-1.5 text-base text-emerald-800"
                    >
                        <Receipt class="h-4 w-4 text-emerald-600" />
                        <span>Generate Fee Invoice</span>
                    </DialogTitle>
                    <DialogDescription class="text-xs">
                        Set fee details and payment due dates.
                    </DialogDescription>
                </DialogHeader>

                <div class="grid gap-3 py-2 text-xs">
                    <div class="grid grid-cols-2 gap-3">
                        <div class="grid gap-1.5">
                            <Label
                                for="amount"
                                class="text-xs font-semibold text-slate-700"
                                >Fee Amount</Label
                            >
                            <Input
                                id="amount"
                                v-model="invoiceForm.amount"
                                type="number"
                                min="1"
                                step="0.01"
                                class="h-8 text-xs"
                            />
                        </div>

                        <div class="grid gap-1.5">
                            <Label
                                for="currency"
                                class="text-xs font-semibold text-slate-700"
                                >Currency</Label
                            >
                            <Input
                                id="currency"
                                v-model="invoiceForm.currency"
                                type="text"
                                class="h-8 text-xs"
                            />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div class="grid gap-1.5">
                            <Label
                                for="due_at"
                                class="text-xs font-semibold text-slate-700"
                                >Due Date</Label
                            >
                            <Input
                                id="due_at"
                                v-model="invoiceForm.due_at"
                                type="datetime-local"
                                class="h-8 text-xs"
                            />
                        </div>

                        <div class="grid gap-1.5">
                            <Label
                                for="expires_at"
                                class="text-xs font-semibold text-slate-700"
                                >Expiry Date</Label
                            >
                            <Input
                                id="expires_at"
                                v-model="invoiceForm.expires_at"
                                type="datetime-local"
                                class="h-8 text-xs"
                            />
                        </div>
                    </div>

                    <div class="grid gap-1.5">
                        <Label
                            for="notes"
                            class="text-xs font-semibold text-slate-700"
                            >Admin Notes</Label
                        >
                        <Textarea
                            id="notes"
                            v-model="invoiceForm.notes"
                            rows="2"
                            placeholder="Optional notes..."
                            class="text-xs"
                        />
                    </div>
                </div>

                <DialogFooter class="gap-2 sm:gap-0">
                    <Button
                        type="button"
                        variant="outline"
                        size="sm"
                        class="text-xs"
                        @click="invoiceDialogOpen = false"
                        >Cancel</Button
                    >
                    <Button
                        type="button"
                        size="sm"
                        class="bg-emerald-600 text-xs text-white hover:bg-emerald-700"
                        @click="submitInvoice"
                    >
                        Generate Invoice
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Mark Paid Modal -->
        <Dialog
            :open="markPaidDialogOpen"
            @update:open="markPaidDialogOpen = $event"
        >
            <DialogContent class="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle
                        class="flex items-center gap-1.5 text-base text-blue-800"
                    >
                        <CreditCard class="h-4 w-4 text-blue-600" />
                        <span>Manual Payment Confirmation</span>
                    </DialogTitle>
                    <DialogDescription class="text-xs">
                        Record a manual payment confirmation for this invoice.
                    </DialogDescription>
                </DialogHeader>

                <div class="grid gap-3 py-2 text-xs">
                    <div class="grid grid-cols-2 gap-3">
                        <div class="grid gap-1.5">
                            <Label class="text-xs font-semibold text-slate-700"
                                >Payment Gateway</Label
                            >
                            <Select v-model="markPaidForm.payment_gateway">
                                <SelectTrigger class="h-8 text-xs">
                                    <SelectValue placeholder="Select gateway" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="manual"
                                        >Manual / Cash</SelectItem
                                    >
                                    <SelectItem value="bkash">bKash</SelectItem>
                                    <SelectItem value="sslcommerz"
                                        >SSLCommerz</SelectItem
                                    >
                                </SelectContent>
                            </Select>
                        </div>

                        <div class="grid gap-1.5">
                            <Label
                                for="payment_method"
                                class="text-xs font-semibold text-slate-700"
                                >Method</Label
                            >
                            <Input
                                id="payment_method"
                                v-model="markPaidForm.payment_method"
                                type="text"
                                class="h-8 text-xs"
                            />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div class="grid gap-1.5">
                            <Label
                                for="payment_reference"
                                class="text-xs font-semibold text-slate-700"
                                >TRX Reference</Label
                            >
                            <Input
                                id="payment_reference"
                                v-model="markPaidForm.payment_reference"
                                type="text"
                                class="h-8 text-xs"
                            />
                        </div>

                        <div class="grid gap-1.5">
                            <Label
                                for="paid_at"
                                class="text-xs font-semibold text-slate-700"
                                >Paid Date</Label
                            >
                            <Input
                                id="paid_at"
                                v-model="markPaidForm.paid_at"
                                type="datetime-local"
                                class="h-8 text-xs"
                            />
                        </div>
                    </div>

                    <div class="grid gap-1.5">
                        <Label
                            for="manual_notes"
                            class="text-xs font-semibold text-slate-700"
                            >Payment Notes</Label
                        >
                        <Textarea
                            id="manual_notes"
                            v-model="markPaidForm.notes"
                            rows="2"
                            placeholder="Optional notes..."
                            class="text-xs"
                        />
                    </div>
                </div>

                <DialogFooter class="gap-2 sm:gap-0">
                    <Button
                        type="button"
                        variant="outline"
                        size="sm"
                        class="text-xs"
                        @click="markPaidDialogOpen = false"
                        >Cancel</Button
                    >
                    <Button
                        type="button"
                        size="sm"
                        class="bg-blue-600 text-xs text-white hover:bg-blue-700"
                        @click="submitMarkPaid"
                    >
                        Confirm Paid
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AdminLayout>
</template>
