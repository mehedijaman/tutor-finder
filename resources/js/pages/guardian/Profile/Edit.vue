<script setup lang="ts">
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import {
    CheckCircle,
    Mail,
    MapPin,
    Phone,
    Shield,
    User as UserIcon,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';
import ConfirmDialog from '@/components/admin/dialogs/ConfirmDialog.vue';
import InputError from '@/components/InputError.vue';
import ProfilePhotoUpload from '@/components/ProfilePhotoUpload.vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
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
import { getInitials } from '@/composables/useInitials';
import GuardianLayout from '@/layouts/GuardianLayout.vue';

const props = defineProps({
    profile: {
        type: Object,
        required: true,
    },
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

const breadcrumbs = [{ title: 'Guardian Profile', href: '/guardian/profile' }];
const page = usePage();
const authUser = computed(() => page.props.auth?.user);
const flashStatus = computed<string | null>(
    () => (page.props.flash as { status?: string } | undefined)?.status ?? null,
);

const activeTab = ref('personal');
const editingTab = ref<string | null>(null);
const isEditingActiveTab = computed(() => editingTab.value === activeTab.value);

const originalProfile = { ...props.profile };

function resetFormToOriginal(): void {
    form.name = originalProfile.name ?? '';
    form.phone = originalProfile.phone ?? '';
    form.phone_alt = originalProfile.phone_alt ?? '';
    form.guardian_name = originalProfile.guardian_name ?? '';
    form.address = originalProfile.address ?? '';
    form.occupation = originalProfile.occupation ?? '';
    form.notes = originalProfile.notes ?? '';
    form.status = originalProfile.status ?? 'active';
}
const tabs = [
    {
        key: 'personal',
        label: 'Personal',
        sublabel: 'Information',
        icon: UserIcon,
    },
    {
        key: 'contact',
        label: 'Contact',
        sublabel: 'Information',
        icon: Phone,
    },
    {
        key: 'verification',
        label: 'Verification',
        sublabel: 'Status',
        icon: Shield,
    },
];

const form = useForm({
    name: props.profile.name ?? '',
    phone: props.profile.phone ?? '',
    phone_alt: props.profile.phone_alt ?? '',
    guardian_name: props.profile.guardian_name ?? '',
    address: props.profile.address ?? '',
    occupation: props.profile.occupation ?? '',
    notes: props.profile.notes ?? '',
    status: props.profile.status ?? 'active',
});

const profileCompletionFields = [
    'name',
    'phone',
    'phone_alt',
    'guardian_name',
    'address',
    'occupation',
    'notes',
];

const profileCompletion = computed(() => {
    let filled = 0;

    for (const field of profileCompletionFields) {
        if ((form as any)[field]) {
            filled++;
        }
    }

    return Math.round((filled / profileCompletionFields.length) * 100);
});

function hasValue(value: unknown): boolean {
    if (Array.isArray(value)) {
        return value.length > 0;
    }

    if (typeof value === 'string') {
        return value.trim() !== '';
    }

    if (typeof value === 'number') {
        return true;
    }

    if (typeof value === 'boolean') {
        return value;
    }

    return value !== null && value !== undefined;
}

const activeTabActionLabel = computed(() => {
    if (activeTab.value === 'contact') {
        const hasContactInfo = [form.phone_alt, form.address, form.notes].some(
            (value) => hasValue(value),
        );

        return hasContactInfo ? 'Edit' : 'Add';
    }

    const hasPersonalInfo = [
        form.name,
        form.phone,
        form.guardian_name,
        form.occupation,
        form.status,
    ].some((value) => hasValue(value));

    return hasPersonalInfo ? 'Edit' : 'Add';
});

function switchTab(tabKey: string) {
    activeTab.value = tabKey;
    editingTab.value = null;
}

function openEditMode() {
    resetFormToOriginal();
    editingTab.value = activeTab.value;
}

function closeEditMode() {
    editingTab.value = null;
}

function submit() {
    form.put('/guardian/profile', {
        preserveScroll: true,
        onSuccess: () => {
            editingTab.value = null;
        },
    });
}

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

const verificationInvoice = computed(() => props.verification?.invoice ?? null);
const canPayInvoice = computed(
    () =>
        verificationInvoice.value &&
        verificationInvoice.value.status === 'unpaid',
);

function requestVerification() {
    router.post(
        '/guardian/verification/request',
        {},
        {
            preserveScroll: true,
            onFinish: () => {
                requestDialogOpen.value = false;
            },
        },
    );
}

function startPayment(gateway: 'bkash' | 'sslcommerz') {
    if (!verificationInvoice.value || !canPayInvoice.value) {
        return;
    }

    const endpoint =
        gateway === 'bkash'
            ? `/payment/bkash/${verificationInvoice.value.id}`
            : `/payment/sslcommerz/${verificationInvoice.value.id}`;

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
    <Head title="Guardian Profile" />

    <GuardianLayout :breadcrumbs="breadcrumbs">
        <div class="grid gap-6 p-4 sm:p-6 xl:grid-cols-[320px_minmax(0,1fr)]">
            <aside
                class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm"
            >
                <div class="flex flex-col items-center text-center">
                    <ProfilePhotoUpload />

                    <h2 class="mt-2 text-2xl sm:text-3xl font-semibold tracking-tight">
                        {{ form.name || authUser?.name || 'Guardian' }}
                    </h2>
                    <p class="mt-1 text-sm text-muted-foreground">
                        Guardian Id : {{ authUser?.id ?? '—' }}
                    </p>

                    <div class="mt-4 w-full">
                        <div class="h-2.5 w-full rounded-full bg-slate-200">
                            <div
                                class="h-2.5 rounded-full bg-emerald-500 transition-all"
                                :style="{ width: `${profileCompletion}%` }"
                            ></div>
                        </div>
                        <p class="mt-2 text-sm font-medium text-emerald-600">
                            {{ profileCompletion }}% Complete
                        </p>
                    </div>
                </div>

                <div class="mt-6 space-y-4 text-sm">
                    <div class="flex items-start gap-3">
                        <Mail class="mt-0.5 h-4 w-4 text-blue-500" />
                        <div>
                            <p class="font-medium">Email</p>
                            <p class="text-muted-foreground">
                                {{ authUser?.email || '—' }}
                            </p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3">
                        <Phone class="mt-0.5 h-4 w-4 text-blue-500" />
                        <div>
                            <p class="font-medium">Phone Number</p>
                            <p class="text-muted-foreground">
                                {{ form.phone || '—' }}
                            </p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3">
                        <MapPin class="mt-0.5 h-4 w-4 text-blue-500" />
                        <div>
                            <p class="font-medium">Address</p>
                            <p class="text-muted-foreground">
                                {{ form.address || '—' }}
                            </p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3">
                        <Shield class="mt-0.5 h-4 w-4 text-blue-500" />
                        <div>
                            <p class="font-medium">Verification</p>
                            <Badge :variant="statusVariant">{{
                                statusLabel
                            }}</Badge>
                        </div>
                    </div>
                </div>
            </aside>

            <div class="space-y-6">
                <div
                    v-if="flashStatus"
                    class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800"
                >
                    {{ flashStatus }}
                </div>

                <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                    <button
                        v-for="tab in tabs"
                        :key="tab.key"
                        type="button"
                        class="relative rounded-2xl border px-4 py-3 text-left transition-all"
                        :class="
                            activeTab === tab.key
                                ? 'border-blue-500 bg-linear-to-r from-blue-500 to-sky-500 text-white shadow-sm'
                                : 'border-slate-200/80 bg-white hover:border-slate-300'
                        "
                        @click="switchTab(tab.key)"
                    >
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-sm font-semibold">
                                    {{ tab.label }}
                                </p>
                                <p
                                    class="mt-0.5 text-xs"
                                    :class="
                                        activeTab === tab.key
                                            ? 'text-blue-100'
                                            : 'text-muted-foreground'
                                    "
                                >
                                    {{ tab.sublabel }}
                                </p>
                            </div>
                            <div
                                class="rounded-full p-2"
                                :class="
                                    activeTab === tab.key
                                        ? 'bg-white/15 text-white'
                                        : 'bg-slate-100 text-muted-foreground'
                                "
                            >
                                <component :is="tab.icon" class="h-4 w-4" />
                            </div>
                        </div>
                        <CheckCircle
                            v-if="activeTab === tab.key"
                            class="absolute -top-2 -right-2 h-6 w-6 rounded-full bg-white text-blue-500"
                        />
                    </button>
                </div>

                <div
                    v-if="activeTab !== 'verification' && !isEditingActiveTab"
                    class="space-y-4 rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm"
                >
                    <div class="flex items-center justify-between gap-3">
                        <h2 class="text-lg font-semibold">
                            {{
                                tabs.find((tab) => tab.key === activeTab)?.label
                            }}
                            Information
                        </h2>
                        <Button
                            type="button"
                            variant="outline"
                            @click="openEditMode"
                        >
                            {{ activeTabActionLabel }}
                        </Button>
                    </div>

                    <section
                        v-if="activeTab === 'personal'"
                        class="grid gap-3 text-sm md:grid-cols-2"
                    >
                        <p>
                            <span class="font-medium">Name:</span>
                            {{ form.name || '—' }}
                        </p>
                        <p>
                            <span class="font-medium">Phone:</span>
                            {{ form.phone || '—' }}
                        </p>
                        <p>
                            <span class="font-medium">Guardian Name:</span>
                            {{ form.guardian_name || '—' }}
                        </p>
                        <p>
                            <span class="font-medium">Occupation:</span>
                            {{ form.occupation || '—' }}
                        </p>
                        <p>
                            <span class="font-medium">Status:</span>
                            {{ form.status || '—' }}
                        </p>
                    </section>

                    <section
                        v-if="activeTab === 'contact'"
                        class="grid gap-3 text-sm md:grid-cols-2"
                    >
                        <p>
                            <span class="font-medium">Alternative Phone:</span>
                            {{ form.phone_alt || '—' }}
                        </p>
                        <p class="md:col-span-2">
                            <span class="font-medium">Address:</span>
                            {{ form.address || '—' }}
                        </p>
                        <p class="md:col-span-2">
                            <span class="font-medium">Notes:</span>
                            {{ form.notes || '—' }}
                        </p>
                    </section>
                </div>

                <form
                    v-if="activeTab !== 'verification' && isEditingActiveTab"
                    class="space-y-6"
                    @submit.prevent="submit"
                >
                    <section
                        v-if="activeTab === 'personal'"
                        class="grid gap-4 rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm md:grid-cols-2"
                    >
                        <div class="grid gap-2">
                            <Label for="name">Name</Label>
                            <Input
                                id="name"
                                v-model="form.name"
                                type="text"
                                required
                            />
                            <InputError :message="form.errors.name" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="phone">Phone</Label>
                            <Input
                                id="phone"
                                v-model="form.phone"
                                type="text"
                                placeholder="01XXXXXXXXX"
                            />
                            <InputError :message="form.errors.phone" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="guardian_name">Guardian Name</Label>
                            <Input
                                id="guardian_name"
                                v-model="form.guardian_name"
                                type="text"
                            />
                            <InputError :message="form.errors.guardian_name" />
                        </div>

                        <div class="grid gap-2">
                            <Label>Status</Label>
                            <Select v-model="form.status">
                                <SelectTrigger>
                                    <SelectValue placeholder="Select status" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="active"
                                        >Active</SelectItem
                                    >
                                    <SelectItem value="inactive"
                                        >Inactive</SelectItem
                                    >
                                </SelectContent>
                            </Select>
                            <InputError :message="form.errors.status" />
                        </div>

                        <div class="grid gap-2 md:col-span-2">
                            <Label for="occupation">Occupation</Label>
                            <Input
                                id="occupation"
                                v-model="form.occupation"
                                type="text"
                            />
                            <InputError :message="form.errors.occupation" />
                        </div>
                    </section>

                    <section
                        v-if="activeTab === 'contact'"
                        class="grid gap-4 rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm md:grid-cols-2"
                    >
                        <div class="grid gap-2">
                            <Label for="phone_alt">Alternative Phone</Label>
                            <Input
                                id="phone_alt"
                                v-model="form.phone_alt"
                                type="text"
                                placeholder="Optional"
                            />
                            <InputError :message="form.errors.phone_alt" />
                        </div>

                        <div class="grid gap-2 md:col-span-2">
                            <Label for="address">Address</Label>
                            <textarea
                                id="address"
                                v-model="form.address"
                                rows="4"
                                class="rounded-lg border border-slate-300 px-3 py-2 text-sm"
                                placeholder="House, road, area"
                            ></textarea>
                            <InputError :message="form.errors.address" />
                        </div>

                        <div class="grid gap-2 md:col-span-2">
                            <Label for="notes">Notes</Label>
                            <textarea
                                id="notes"
                                v-model="form.notes"
                                rows="4"
                                class="rounded-lg border border-slate-300 px-3 py-2 text-sm"
                                placeholder="Additional information"
                            ></textarea>
                            <InputError :message="form.errors.notes" />
                        </div>
                    </section>

                    <div class="flex items-center gap-3">
                        <Button type="submit" :disabled="form.processing"
                            >Save Profile</Button
                        >
                        <Button
                            type="button"
                            variant="outline"
                            @click="closeEditMode"
                            >Cancel</Button
                        >
                        <span
                            v-if="form.processing"
                            class="text-sm text-muted-foreground"
                            >Saving...</span
                        >
                    </div>
                </form>

                <div v-if="activeTab === 'verification'" class="space-y-6">
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
                        <div
                            class="flex flex-wrap items-center justify-between gap-3"
                        >
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
                            Verified on
                            {{ new Date(verifiedAt).toLocaleString() }}.
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

                        <p
                            v-if="!verificationInvoice"
                            class="mt-3 text-sm text-muted-foreground"
                        >
                            Invoice will be generated after admin approval.
                        </p>

                        <div v-else class="mt-4 space-y-4">
                            <div
                                class="grid gap-3 rounded-lg border p-4 text-sm md:grid-cols-2"
                            >
                                <p>
                                    <span class="font-medium">Invoice No:</span>
                                    {{ verificationInvoice.invoice_no }}
                                </p>
                                <p>
                                    <span class="font-medium">Amount:</span>
                                    {{ verificationInvoice.amount }}
                                    {{ verificationInvoice.currency }}
                                </p>
                                <p>
                                    <span class="font-medium">Status:</span>
                                    {{ verificationInvoice.status }}
                                </p>
                                <p>
                                    <span class="font-medium">Due At:</span>
                                    {{
                                        verificationInvoice.due_at
                                            ? new Date(
                                                  verificationInvoice.due_at,
                                              ).toLocaleString()
                                            : '—'
                                    }}
                                </p>
                                <p>
                                    <span class="font-medium">Expires At:</span>
                                    {{
                                        verificationInvoice.expires_at
                                            ? new Date(
                                                  verificationInvoice.expires_at,
                                              ).toLocaleString()
                                            : '—'
                                    }}
                                </p>
                                <p>
                                    <span class="font-medium">Paid At:</span>
                                    {{
                                        verificationInvoice.paid_at
                                            ? new Date(
                                                  verificationInvoice.paid_at,
                                              ).toLocaleString()
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
                                v-if="verificationInvoice.status !== 'unpaid'"
                                class="text-xs text-muted-foreground"
                            >
                                Payment buttons are available only when invoice
                                is unpaid.
                            </p>
                        </div>
                    </section>

                    <ConfirmDialog
                        v-model:open="requestDialogOpen"
                        title="Submit Verification Request"
                        description="A one-time non-refundable verification fee of BDT 500 will apply. Continue?"
                        confirm-label="Submit Request"
                        @confirm="requestVerification"
                    />
                </div>
            </div>
        </div>
    </GuardianLayout>
</template>
