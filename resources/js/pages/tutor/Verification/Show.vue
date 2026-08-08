<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import ConfirmDialog from '@/components/admin/dialogs/ConfirmDialog.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import TutorLayout from '@/layouts/TutorLayout.vue';

const props = defineProps({
    verification: {
        type: Object,
        default: null,
    },
    verificationStatus: {
        type: String,
        default: 'unverified',
    },
    verifiedAt: {
        type: String,
        default: null,
    },
});

const breadcrumbs = [{ title: 'Verification', href: '/tutor/verification' }];

const requestDialogOpen = ref(false);

const normalizedStatus = computed(
    () => props.verificationStatus || 'unverified',
);

const statusLabel = computed(
    () =>
        ({
            unverified: 'Unverified',
            pending: 'Pending Review',
            approved: 'Approved',
            invoiced: 'Invoice Issued',
            verified: 'Verified',
            rejected: 'Rejected',
            cancelled: 'Cancelled',
        })[normalizedStatus.value] ?? normalizedStatus.value,
);

const statusVariant = computed(() => {
    if (normalizedStatus.value === 'verified') {
        return 'default';
    }

    if (['rejected', 'cancelled'].includes(normalizedStatus.value)) {
        return 'destructive';
    }

    return 'secondary';
});

const canRequestVerification = computed(() => {
    return ['unverified', 'rejected', 'cancelled'].includes(
        normalizedStatus.value,
    );
});

const invoice = computed(() => props.verification?.invoice ?? null);
const canPayInvoice = computed(
    () => invoice.value && invoice.value.status === 'unpaid',
);

function requestVerification() {
    router.post(
        '/tutor/verification/request',
        {},
        {
            preserveScroll: true,
            onFinish: () => {
                requestDialogOpen.value = false;
            },
        },
    );
}

function startPayment(gateway) {
    if (!invoice.value || !canPayInvoice.value) {
        return;
    }

    const endpoint =
        gateway === 'bkash'
            ? `/payment/bkash/${invoice.value.id}`
            : `/payment/sslcommerz/${invoice.value.id}`;

    router.post(
        endpoint,
        {},
        {
            preserveScroll: true,
        },
    );
}
</script>

<template>
    <Head title="Tutor Verification" />

    <TutorLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-4 sm:p-6 lg:p-8">
            <div
                class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6"
            >
                <h1 class="text-2xl font-semibold tracking-tight sm:text-3xl">
                    Tutor Verification
                </h1>
                <p class="mt-1 text-sm text-muted-foreground">
                    Request profile verification and complete invoice payment
                    securely.
                </p>
            </div>

            <div
                v-if="$page.props.flash?.status"
                class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800"
            >
                {{ $page.props.flash.status }}
            </div>

            <div
                v-if="
                    $page.props.errors?.payment ||
                    $page.props.errors?.verification
                "
                class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800"
            >
                {{
                    $page.props.errors.payment ||
                    $page.props.errors.verification
                }}
            </div>

            <section
                class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm"
            >
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div class="space-y-1">
                        <p class="text-sm text-muted-foreground">
                            Current Status
                        </p>
                        <Badge :variant="statusVariant">{{
                            statusLabel
                        }}</Badge>
                    </div>

                    <Button
                        v-if="canRequestVerification"
                        type="button"
                        @click="requestDialogOpen = true"
                    >
                        Request Verification (BDT 500)
                    </Button>
                </div>

                <p
                    v-if="normalizedStatus === 'verified' && verifiedAt"
                    class="mt-4 text-sm text-muted-foreground"
                >
                    Verified on {{ new Date(verifiedAt).toLocaleString() }}.
                </p>

                <p
                    v-if="verification?.decision_reason"
                    class="mt-4 rounded-md border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-900"
                >
                    {{ verification.decision_reason }}
                </p>
            </section>

            <section
                class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm"
            >
                <h2 class="text-lg font-semibold">Invoice</h2>

                <p v-if="!invoice" class="mt-3 text-sm text-muted-foreground">
                    Invoice will be generated after admin approval.
                </p>

                <div v-else class="mt-4 space-y-4">
                    <div
                        class="grid gap-3 rounded-lg border p-4 text-sm md:grid-cols-2"
                    >
                        <p>
                            <span class="font-medium">Invoice No:</span>
                            {{ invoice.invoice_no }}
                        </p>
                        <p>
                            <span class="font-medium">Amount:</span>
                            {{ invoice.amount }} {{ invoice.currency }}
                        </p>
                        <p>
                            <span class="font-medium">Status:</span>
                            {{ invoice.status }}
                        </p>
                        <p>
                            <span class="font-medium">Due At:</span>
                            {{
                                invoice.due_at
                                    ? new Date(invoice.due_at).toLocaleString()
                                    : '—'
                            }}
                        </p>
                        <p>
                            <span class="font-medium">Expires At:</span>
                            {{
                                invoice.expires_at
                                    ? new Date(
                                          invoice.expires_at,
                                      ).toLocaleString()
                                    : '—'
                            }}
                        </p>
                        <p>
                            <span class="font-medium">Paid At:</span>
                            {{
                                invoice.paid_at
                                    ? new Date(invoice.paid_at).toLocaleString()
                                    : '—'
                            }}
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <Button
                            type="button"
                            :disabled="!canPayInvoice"
                            @click="startPayment('bkash')"
                        >
                            Pay with bKash
                        </Button>
                        <Button
                            type="button"
                            variant="outline"
                            :disabled="!canPayInvoice"
                            @click="startPayment('sslcommerz')"
                        >
                            Pay with SSLCommerz
                        </Button>
                    </div>

                    <p
                        v-if="invoice.status !== 'unpaid'"
                        class="text-xs text-muted-foreground"
                    >
                        Payment buttons are available only when invoice is
                        unpaid.
                    </p>
                </div>
            </section>
        </div>

        <ConfirmDialog
            v-model:open="requestDialogOpen"
            title="Submit Verification Request"
            description="A one-time non-refundable verification fee of BDT 500 will apply. Continue?"
            confirm-label="Submit Request"
            @confirm="requestVerification"
        />
    </TutorLayout>
</template>
