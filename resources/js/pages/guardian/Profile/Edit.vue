<script setup lang="ts">
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import {
    Building2,
    Calendar,
    CheckCircle2,
    Clock,
    CreditCard,
    ExternalLink,
    FileText,
    Mail,
    MapPin,
    PenSquare,
    Phone,
    Receipt,
    Shield,
    ShieldAlert,
    ShieldCheck,
    User as UserIcon,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';
import ConfirmDialog from '@/components/admin/dialogs/ConfirmDialog.vue';
import InputError from '@/components/InputError.vue';
import ProfilePhotoUpload from '@/components/ProfilePhotoUpload.vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
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
    form.emergency_contact = originalProfile.emergency_contact ?? '';
    form.guardian_name = originalProfile.guardian_name ?? '';
    form.relationship_to_student =
        originalProfile.relationship_to_student ?? '';
    form.address = originalProfile.address ?? '';
    form.city = originalProfile.city ?? '';
    form.area = originalProfile.area ?? '';
    form.occupation = originalProfile.occupation ?? '';
    form.notes = originalProfile.notes ?? '';
    form.preferred_contact_time = originalProfile.preferred_contact_time ?? '';
    form.status = originalProfile.status ?? 'active';
}

const tabs = [
    {
        key: 'personal',
        label: 'Personal Info',
        sublabel: 'Name & Student relationship',
        icon: UserIcon,
    },
    {
        key: 'contact',
        label: 'Contact & Location',
        sublabel: 'Phone, Address & City',
        icon: Phone,
    },
    {
        key: 'verification',
        label: 'Verification Status',
        sublabel: 'Badge & account status',
        icon: ShieldCheck,
    },
];

const form = useForm({
    name: props.profile.name ?? '',
    phone: props.profile.phone ?? '',
    phone_alt: props.profile.phone_alt ?? '',
    emergency_contact: props.profile.emergency_contact ?? '',
    guardian_name: props.profile.guardian_name ?? '',
    relationship_to_student: props.profile.relationship_to_student ?? '',
    address: props.profile.address ?? '',
    city: props.profile.city ?? '',
    area: props.profile.area ?? '',
    occupation: props.profile.occupation ?? '',
    notes: props.profile.notes ?? '',
    preferred_contact_time: props.profile.preferred_contact_time ?? '',
    status: props.profile.status ?? 'active',
});

const profileCompletionFields = [
    'name',
    'phone',
    'phone_alt',
    'emergency_contact',
    'guardian_name',
    'relationship_to_student',
    'address',
    'city',
    'area',
    'occupation',
    'notes',
    'preferred_contact_time',
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
        return hasContactInfo ? 'Edit Contact Info' : 'Add Contact Info';
    }

    const hasPersonalInfo = [
        form.name,
        form.phone,
        form.guardian_name,
        form.occupation,
        form.status,
    ].some((value) => hasValue(value));

    return hasPersonalInfo ? 'Edit Personal Info' : 'Add Personal Info';
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
        <div
            class="grid gap-5 p-4 text-slate-900 sm:p-5 lg:p-6 xl:grid-cols-[300px_minmax(0,1fr)] dark:text-slate-100"
        >
            <!-- Sidebar Profile Card -->
            <aside class="space-y-4">
                <Card
                    class="overflow-hidden border-slate-200/80 bg-white shadow-2xs dark:border-slate-800 dark:bg-slate-900"
                >
                    <CardContent class="space-y-4 p-5 text-center">
                        <div class="flex flex-col items-center">
                            <ProfilePhotoUpload />
                            <h2
                                class="mt-3 text-lg font-bold tracking-tight text-slate-900 dark:text-slate-100"
                            >
                                {{ form.name || authUser?.name || 'Guardian' }}
                            </h2>
                            <p
                                class="text-xs font-medium text-slate-500 dark:text-slate-400"
                            >
                                Guardian ID: #{{ authUser?.id ?? '—' }}
                            </p>

                            <div class="mt-2 inline-flex items-center gap-1.5">
                                <Badge
                                    :variant="statusVariant"
                                    class="px-2 py-0.5 text-[11px] font-semibold capitalize"
                                >
                                    {{ statusLabel }}
                                </Badge>
                                <Badge
                                    variant="outline"
                                    class="border-slate-300 px-2 py-0.5 text-[11px] capitalize dark:border-slate-700 dark:text-slate-300"
                                >
                                    {{ form.status || 'Active' }}
                                </Badge>
                            </div>
                        </div>

                        <!-- Progress Meter -->
                        <div
                            class="w-full space-y-1.5 border-t border-slate-100 pt-2 dark:border-slate-800"
                        >
                            <div
                                class="flex items-center justify-between text-xs font-semibold"
                            >
                                <span class="text-slate-500 dark:text-slate-400"
                                    >Profile Completion</span
                                >
                                <span
                                    class="font-bold text-blue-700 dark:text-blue-400"
                                    >{{ profileCompletion }}%</span
                                >
                            </div>
                            <div
                                class="h-2 w-full overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800"
                            >
                                <div
                                    class="h-full rounded-full bg-blue-600 transition-all duration-300 dark:bg-blue-500"
                                    :style="{ width: `${profileCompletion}%` }"
                                ></div>
                            </div>
                        </div>

                        <Separator class="dark:bg-slate-800" />

                        <!-- Contact Summary -->
                        <div class="space-y-2.5 text-left text-xs">
                            <div class="flex items-start gap-2.5">
                                <Mail
                                    class="mt-0.5 h-3.5 w-3.5 shrink-0 text-slate-400 dark:text-slate-500"
                                />
                                <div class="min-w-0">
                                    <span
                                        class="block text-[10px] font-semibold text-slate-400 uppercase dark:text-slate-500"
                                        >Email</span
                                    >
                                    <span
                                        class="block truncate font-medium text-slate-900 dark:text-slate-200"
                                        >{{ authUser?.email || '—' }}</span
                                    >
                                </div>
                            </div>

                            <div class="flex items-start gap-2.5">
                                <Phone
                                    class="mt-0.5 h-3.5 w-3.5 shrink-0 text-slate-400 dark:text-slate-500"
                                />
                                <div>
                                    <span
                                        class="block text-[10px] font-semibold text-slate-400 uppercase dark:text-slate-500"
                                        >Primary Phone</span
                                    >
                                    <span
                                        class="font-medium text-slate-900 dark:text-slate-200"
                                        >{{ form.phone || '—' }}</span
                                    >
                                </div>
                            </div>

                            <div class="flex items-start gap-2.5">
                                <MapPin
                                    class="mt-0.5 h-3.5 w-3.5 shrink-0 text-slate-400 dark:text-slate-500"
                                />
                                <div>
                                    <span
                                        class="block text-[10px] font-semibold text-slate-400 uppercase dark:text-slate-500"
                                        >City & Area</span
                                    >
                                    <span
                                        class="font-medium text-slate-900 dark:text-slate-200"
                                    >
                                        {{
                                            form.city || form.area
                                                ? `${form.city || ''} ${form.area ? ' (' + form.area + ')' : ''}`
                                                : '—'
                                        }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </aside>

            <!-- Main Content Section -->
            <div class="space-y-4">
                <!-- Flash Status Alert -->
                <div
                    v-if="flashStatus"
                    class="flex items-center gap-2 rounded-lg border border-emerald-200 bg-emerald-50/90 px-3.5 py-2.5 text-xs font-medium text-emerald-900 shadow-2xs dark:border-emerald-800/60 dark:bg-emerald-950/40 dark:text-emerald-200"
                >
                    <CheckCircle2
                        class="h-4 w-4 shrink-0 text-emerald-600 dark:text-emerald-400"
                    />
                    <div>{{ flashStatus }}</div>
                </div>

                <!-- Navigation Tabs Bar -->
                <div class="grid grid-cols-3 gap-2">
                    <button
                        v-for="tab in tabs"
                        :key="tab.key"
                        type="button"
                        class="flex items-center gap-2.5 rounded-xl border p-3 text-left transition-all"
                        :class="[
                            activeTab === tab.key
                                ? 'border-blue-600 bg-blue-600 text-white shadow-2xs dark:bg-blue-600'
                                : 'border-slate-200/80 bg-white text-slate-700 hover:border-slate-300 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300 dark:hover:border-slate-700',
                        ]"
                        @click="switchTab(tab.key)"
                    >
                        <div
                            class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg"
                            :class="[
                                activeTab === tab.key
                                    ? 'bg-white/20 text-white'
                                    : 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400',
                            ]"
                        >
                            <component :is="tab.icon" class="h-4 w-4" />
                        </div>
                        <div class="min-w-0">
                            <h4
                                class="truncate text-xs leading-tight font-bold"
                            >
                                {{ tab.label }}
                            </h4>
                            <p
                                class="truncate text-[10px]"
                                :class="[
                                    activeTab === tab.key
                                        ? 'text-blue-100'
                                        : 'text-slate-400 dark:text-slate-500',
                                ]"
                            >
                                {{ tab.sublabel }}
                            </p>
                        </div>
                    </button>
                </div>

                <!-- View Mode Display Card -->
                <Card
                    v-if="activeTab !== 'verification' && !isEditingActiveTab"
                    class="border-slate-200/80 bg-white shadow-2xs dark:border-slate-800 dark:bg-slate-900"
                >
                    <CardHeader
                        class="flex flex-row items-center justify-between border-b border-slate-100 px-4 py-3 dark:border-slate-800"
                    >
                        <CardTitle
                            class="flex items-center gap-1.5 text-xs font-bold tracking-wider text-slate-700 uppercase dark:text-slate-300"
                        >
                            <component
                                :is="
                                    tabs.find((t) => t.key === activeTab)?.icon
                                "
                                class="h-4 w-4 text-blue-600 dark:text-blue-400"
                            />
                            <span>{{
                                tabs.find((t) => t.key === activeTab)?.label
                            }}</span>
                        </CardTitle>
                        <Button
                            type="button"
                            variant="outline"
                            size="sm"
                            class="h-8 gap-1 text-xs dark:border-slate-700 dark:text-slate-300"
                            @click="openEditMode"
                        >
                            <PenSquare class="h-3.5 w-3.5" />
                            <span>{{ activeTabActionLabel }}</span>
                        </Button>
                    </CardHeader>

                    <CardContent class="p-4">
                        <!-- Personal Info Tab View -->
                        <section
                            v-if="activeTab === 'personal'"
                            class="grid gap-3 text-xs sm:grid-cols-2 lg:grid-cols-3"
                        >
                            <div
                                class="space-y-0.5 rounded-lg border border-slate-100 bg-slate-50/50 p-2.5 dark:border-slate-800 dark:bg-slate-900/50"
                            >
                                <span
                                    class="block text-[10px] font-semibold text-slate-400 uppercase dark:text-slate-500"
                                    >Full Name</span
                                >
                                <span
                                    class="block font-bold text-slate-900 dark:text-slate-100"
                                    >{{ form.name || '—' }}</span
                                >
                            </div>

                            <div
                                class="space-y-0.5 rounded-lg border border-slate-100 bg-slate-50/50 p-2.5 dark:border-slate-800 dark:bg-slate-900/50"
                            >
                                <span
                                    class="block text-[10px] font-semibold text-slate-400 uppercase dark:text-slate-500"
                                    >Guardian / Primary Contact</span
                                >
                                <span
                                    class="block font-bold text-slate-900 dark:text-slate-100"
                                    >{{ form.guardian_name || '—' }}</span
                                >
                            </div>

                            <div
                                class="space-y-0.5 rounded-lg border border-slate-100 bg-slate-50/50 p-2.5 dark:border-slate-800 dark:bg-slate-900/50"
                            >
                                <span
                                    class="block text-[10px] font-semibold text-slate-400 uppercase dark:text-slate-500"
                                    >Relationship to Student</span
                                >
                                <span
                                    class="block font-semibold text-slate-900 dark:text-slate-100"
                                    >{{
                                        form.relationship_to_student || '—'
                                    }}</span
                                >
                            </div>

                            <div
                                class="space-y-0.5 rounded-lg border border-slate-100 bg-slate-50/50 p-2.5 dark:border-slate-800 dark:bg-slate-900/50"
                            >
                                <span
                                    class="block text-[10px] font-semibold text-slate-400 uppercase dark:text-slate-500"
                                    >Occupation / Profession</span
                                >
                                <span
                                    class="block font-semibold text-slate-900 dark:text-slate-100"
                                    >{{ form.occupation || '—' }}</span
                                >
                            </div>

                            <div
                                class="space-y-0.5 rounded-lg border border-slate-100 bg-slate-50/50 p-2.5 dark:border-slate-800 dark:bg-slate-900/50"
                            >
                                <span
                                    class="block text-[10px] font-semibold text-slate-400 uppercase dark:text-slate-500"
                                    >Account Status</span
                                >
                                <Badge
                                    variant="outline"
                                    class="text-[11px] capitalize dark:border-slate-700 dark:text-slate-300"
                                >
                                    {{ form.status || 'Active' }}
                                </Badge>
                            </div>
                        </section>

                        <!-- Contact & Location Tab View -->
                        <section
                            v-if="activeTab === 'contact'"
                            class="grid gap-3 text-xs sm:grid-cols-2 lg:grid-cols-3"
                        >
                            <div
                                class="space-y-0.5 rounded-lg border border-slate-100 bg-slate-50/50 p-2.5 dark:border-slate-800 dark:bg-slate-900/50"
                            >
                                <span
                                    class="block text-[10px] font-semibold text-slate-400 uppercase dark:text-slate-500"
                                    >Primary Phone</span
                                >
                                <span
                                    class="block font-bold text-slate-900 dark:text-slate-100"
                                    >{{ form.phone || '—' }}</span
                                >
                            </div>

                            <div
                                class="space-y-0.5 rounded-lg border border-slate-100 bg-slate-50/50 p-2.5 dark:border-slate-800 dark:bg-slate-900/50"
                            >
                                <span
                                    class="block text-[10px] font-semibold text-slate-400 uppercase dark:text-slate-500"
                                    >Alternative Phone</span
                                >
                                <span
                                    class="block font-bold text-slate-900 dark:text-slate-100"
                                    >{{ form.phone_alt || '—' }}</span
                                >
                            </div>

                            <div
                                class="space-y-0.5 rounded-lg border border-slate-100 bg-slate-50/50 p-2.5 dark:border-slate-800 dark:bg-slate-900/50"
                            >
                                <span
                                    class="block text-[10px] font-semibold text-slate-400 uppercase dark:text-slate-500"
                                    >Emergency Contact</span
                                >
                                <span
                                    class="block font-bold text-slate-900 dark:text-slate-100"
                                    >{{ form.emergency_contact || '—' }}</span
                                >
                            </div>

                            <div
                                class="space-y-0.5 rounded-lg border border-slate-100 bg-slate-50/50 p-2.5 dark:border-slate-800 dark:bg-slate-900/50"
                            >
                                <span
                                    class="block text-[10px] font-semibold text-slate-400 uppercase dark:text-slate-500"
                                    >City</span
                                >
                                <span
                                    class="block font-semibold text-slate-900 dark:text-slate-100"
                                    >{{ form.city || '—' }}</span
                                >
                            </div>

                            <div
                                class="space-y-0.5 rounded-lg border border-slate-100 bg-slate-50/50 p-2.5 dark:border-slate-800 dark:bg-slate-900/50"
                            >
                                <span
                                    class="block text-[10px] font-semibold text-slate-400 uppercase dark:text-slate-500"
                                    >Area / Locality</span
                                >
                                <span
                                    class="block font-semibold text-slate-900 dark:text-slate-100"
                                    >{{ form.area || '—' }}</span
                                >
                            </div>

                            <div
                                class="space-y-0.5 rounded-lg border border-slate-100 bg-slate-50/50 p-2.5 dark:border-slate-800 dark:bg-slate-900/50"
                            >
                                <span
                                    class="block text-[10px] font-semibold text-slate-400 uppercase dark:text-slate-500"
                                    >Preferred Contact Time</span
                                >
                                <span
                                    class="block font-semibold text-slate-900 dark:text-slate-100"
                                    >{{
                                        form.preferred_contact_time ||
                                        'Flexible'
                                    }}</span
                                >
                            </div>

                            <div
                                class="space-y-0.5 rounded-lg border border-slate-100 bg-slate-50/50 p-2.5 sm:col-span-2 lg:col-span-3 dark:border-slate-800 dark:bg-slate-900/50"
                            >
                                <span
                                    class="block text-[10px] font-semibold text-slate-400 uppercase dark:text-slate-500"
                                    >Full Address</span
                                >
                                <span
                                    class="block font-medium text-slate-900 dark:text-slate-100"
                                    >{{ form.address || '—' }}</span
                                >
                            </div>

                            <div
                                class="space-y-0.5 rounded-lg border border-slate-100 bg-slate-50/50 p-2.5 sm:col-span-2 lg:col-span-3 dark:border-slate-800 dark:bg-slate-900/50"
                            >
                                <span
                                    class="block text-[10px] font-semibold text-slate-400 uppercase dark:text-slate-500"
                                    >Additional Notes / Instructions</span
                                >
                                <p
                                    class="leading-relaxed font-normal text-slate-800 dark:text-slate-200"
                                >
                                    {{
                                        form.notes ||
                                        'No additional notes provided.'
                                    }}
                                </p>
                            </div>
                        </section>
                    </CardContent>
                </Card>

                <!-- Edit Mode Form Section -->
                <form
                    v-if="activeTab !== 'verification' && isEditingActiveTab"
                    class="space-y-4"
                    @submit.prevent="submit"
                >
                    <!-- Personal Info Form -->
                    <Card
                        v-if="activeTab === 'personal'"
                        class="border-slate-200/80 bg-white shadow-2xs dark:border-slate-800 dark:bg-slate-900"
                    >
                        <CardHeader
                            class="border-b border-slate-100 px-4 py-3 dark:border-slate-800"
                        >
                            <CardTitle
                                class="text-xs font-bold tracking-wider text-slate-700 uppercase dark:text-slate-300"
                                >Edit Personal Information</CardTitle
                            >
                        </CardHeader>
                        <CardContent class="space-y-3 p-4 text-xs">
                            <div class="grid gap-3 sm:grid-cols-2">
                                <div class="grid gap-1">
                                    <Label
                                        for="name"
                                        class="text-xs font-semibold"
                                        >Account Name</Label
                                    >
                                    <Input
                                        id="name"
                                        v-model="form.name"
                                        type="text"
                                        class="h-8 text-xs"
                                        required
                                    />
                                    <InputError :message="form.errors.name" />
                                </div>

                                <div class="grid gap-1">
                                    <Label
                                        for="guardian_name"
                                        class="text-xs font-semibold"
                                        >Guardian / Contact Person Name</Label
                                    >
                                    <Input
                                        id="guardian_name"
                                        v-model="form.guardian_name"
                                        type="text"
                                        class="h-8 text-xs"
                                    />
                                    <InputError
                                        :message="form.errors.guardian_name"
                                    />
                                </div>

                                <div class="grid gap-1">
                                    <Label
                                        for="relationship_to_student"
                                        class="text-xs font-semibold"
                                        >Relationship to Student</Label
                                    >
                                    <Input
                                        id="relationship_to_student"
                                        v-model="form.relationship_to_student"
                                        type="text"
                                        placeholder="e.g. Father, Mother, Guardian"
                                        class="h-8 text-xs"
                                    />
                                    <InputError
                                        :message="
                                            form.errors.relationship_to_student
                                        "
                                    />
                                </div>

                                <div class="grid gap-1">
                                    <Label
                                        for="occupation"
                                        class="text-xs font-semibold"
                                        >Occupation / Profession</Label
                                    >
                                    <Input
                                        id="occupation"
                                        v-model="form.occupation"
                                        type="text"
                                        placeholder="e.g. Business / Engineer"
                                        class="h-8 text-xs"
                                    />
                                    <InputError
                                        :message="form.errors.occupation"
                                    />
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Contact & Location Form -->
                    <Card
                        v-if="activeTab === 'contact'"
                        class="border-slate-200/80 bg-white shadow-2xs dark:border-slate-800 dark:bg-slate-900"
                    >
                        <CardHeader
                            class="border-b border-slate-100 px-4 py-3 dark:border-slate-800"
                        >
                            <CardTitle
                                class="text-xs font-bold tracking-wider text-slate-700 uppercase dark:text-slate-300"
                                >Edit Contact & Address</CardTitle
                            >
                        </CardHeader>
                        <CardContent class="space-y-3 p-4 text-xs">
                            <div class="grid gap-3 sm:grid-cols-2">
                                <div class="grid gap-1">
                                    <Label
                                        for="phone"
                                        class="text-xs font-semibold"
                                        >Primary Phone</Label
                                    >
                                    <Input
                                        id="phone"
                                        v-model="form.phone"
                                        type="text"
                                        placeholder="01XXXXXXXXX"
                                        class="h-8 text-xs"
                                    />
                                    <InputError :message="form.errors.phone" />
                                </div>

                                <div class="grid gap-1">
                                    <Label
                                        for="phone_alt"
                                        class="text-xs font-semibold"
                                        >Alternative Phone</Label
                                    >
                                    <Input
                                        id="phone_alt"
                                        v-model="form.phone_alt"
                                        type="text"
                                        placeholder="01XXXXXXXXX"
                                        class="h-8 text-xs"
                                    />
                                    <InputError
                                        :message="form.errors.phone_alt"
                                    />
                                </div>

                                <div class="grid gap-1">
                                    <Label
                                        for="emergency_contact"
                                        class="text-xs font-semibold"
                                        >Emergency Contact</Label
                                    >
                                    <Input
                                        id="emergency_contact"
                                        v-model="form.emergency_contact"
                                        type="text"
                                        class="h-8 text-xs"
                                    />
                                    <InputError
                                        :message="form.errors.emergency_contact"
                                    />
                                </div>

                                <div class="grid gap-1">
                                    <Label
                                        for="preferred_contact_time"
                                        class="text-xs font-semibold"
                                        >Preferred Contact Time</Label
                                    >
                                    <Input
                                        id="preferred_contact_time"
                                        v-model="form.preferred_contact_time"
                                        type="text"
                                        placeholder="e.g. Evening / 6 PM - 9 PM"
                                        class="h-8 text-xs"
                                    />
                                    <InputError
                                        :message="
                                            form.errors.preferred_contact_time
                                        "
                                    />
                                </div>

                                <div class="grid gap-1">
                                    <Label
                                        for="city"
                                        class="text-xs font-semibold"
                                        >City</Label
                                    >
                                    <Input
                                        id="city"
                                        v-model="form.city"
                                        type="text"
                                        placeholder="e.g. Dhaka"
                                        class="h-8 text-xs"
                                    />
                                    <InputError :message="form.errors.city" />
                                </div>

                                <div class="grid gap-1">
                                    <Label
                                        for="area"
                                        class="text-xs font-semibold"
                                        >Area / Locality</Label
                                    >
                                    <Input
                                        id="area"
                                        v-model="form.area"
                                        type="text"
                                        placeholder="e.g. Mirpur, Gulshan"
                                        class="h-8 text-xs"
                                    />
                                    <InputError :message="form.errors.area" />
                                </div>

                                <div class="grid gap-1 sm:col-span-2">
                                    <Label
                                        for="address"
                                        class="text-xs font-semibold"
                                        >Full Address</Label
                                    >
                                    <Textarea
                                        id="address"
                                        v-model="form.address"
                                        rows="2"
                                        placeholder="House no, Road no, Locality..."
                                        class="text-xs"
                                    />
                                    <InputError
                                        :message="form.errors.address"
                                    />
                                </div>

                                <div class="grid gap-1 sm:col-span-2">
                                    <Label
                                        for="notes"
                                        class="text-xs font-semibold"
                                        >Additional Notes</Label
                                    >
                                    <Textarea
                                        id="notes"
                                        v-model="form.notes"
                                        rows="2"
                                        placeholder="Any specific requirements or notes..."
                                        class="text-xs"
                                    />
                                    <InputError :message="form.errors.notes" />
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Submit & Cancel Action Buttons -->
                    <div class="flex items-center justify-end gap-2 pt-2">
                        <Button
                            type="button"
                            variant="outline"
                            size="sm"
                            class="text-xs dark:border-slate-700 dark:text-slate-300"
                            @click="closeEditMode"
                        >
                            Cancel
                        </Button>
                        <Button
                            type="submit"
                            size="sm"
                            class="bg-blue-600 text-xs font-semibold text-white hover:bg-blue-700"
                            :disabled="form.processing"
                        >
                            Save Profile Changes
                        </Button>
                    </div>
                </form>

                <!-- Verification Status Tab -->
                <Card
                    v-if="activeTab === 'verification'"
                    class="border-slate-200/80 bg-white shadow-2xs dark:border-slate-800 dark:bg-slate-900"
                >
                    <CardHeader
                        class="border-b border-slate-100 px-4 py-3 dark:border-slate-800"
                    >
                        <CardTitle
                            class="flex items-center gap-1.5 text-xs font-bold tracking-wider text-slate-700 uppercase dark:text-slate-300"
                        >
                            <ShieldCheck
                                class="h-4 w-4 text-blue-600 dark:text-blue-400"
                            />
                            <span>Guardian Verification Status</span>
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4 p-4 text-xs">
                        <div
                            class="flex flex-col justify-between gap-3 rounded-xl border border-slate-100 bg-slate-50/60 p-4 sm:flex-row sm:items-center dark:border-slate-800 dark:bg-slate-900/60"
                        >
                            <div class="space-y-1">
                                <h4
                                    class="text-sm font-bold text-slate-900 dark:text-slate-100"
                                >
                                    Verification Status: {{ statusLabel }}
                                </h4>
                                <p
                                    class="text-xs text-slate-500 dark:text-slate-400"
                                >
                                    Verified guardians build trust with tutors
                                    and enjoy expedited tuition posting and
                                    matching.
                                </p>
                            </div>
                            <Button
                                v-if="canRequestVerification"
                                type="button"
                                size="sm"
                                class="shrink-0 bg-blue-600 text-xs text-white hover:bg-blue-700"
                                @click="requestDialogOpen = true"
                            >
                                Request Profile Verification
                            </Button>
                        </div>

                        <!-- Verification Invoice Payment Card -->
                        <div
                            v-if="canPayInvoice && verificationInvoice"
                            class="space-y-3 rounded-xl border border-blue-200 bg-blue-50/50 p-4 dark:border-blue-900/50 dark:bg-blue-950/30"
                        >
                            <div class="flex items-center justify-between">
                                <h4
                                    class="flex items-center gap-1.5 text-sm font-bold text-blue-900 dark:text-blue-200"
                                >
                                    <Receipt
                                        class="h-4 w-4 text-blue-600 dark:text-blue-400"
                                    />
                                    <span>Verification Fee Invoice</span>
                                </h4>
                                <Badge
                                    variant="outline"
                                    class="border-blue-300 bg-white text-blue-800 dark:border-blue-700 dark:bg-slate-900 dark:text-blue-200"
                                >
                                    {{ verificationInvoice.amount }}
                                    {{ verificationInvoice.currency }}
                                </Badge>
                            </div>
                            <p class="text-xs text-blue-950 dark:text-blue-300">
                                Invoice #{{
                                    verificationInvoice.invoice_no
                                }}
                                has been issued. Pay now to complete
                                verification.
                            </p>
                            <div class="flex flex-wrap gap-2 pt-1">
                                <Button
                                    type="button"
                                    size="sm"
                                    class="bg-pink-600 text-xs text-white hover:bg-pink-700"
                                    @click="startPayment('bkash')"
                                >
                                    Pay with bKash
                                </Button>
                                <Button
                                    type="button"
                                    size="sm"
                                    class="bg-emerald-600 text-xs text-white hover:bg-emerald-700"
                                    @click="startPayment('sslcommerz')"
                                >
                                    Pay with SSLCommerz / Cards
                                </Button>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>

        <!-- Request Verification Dialog Modal -->
        <Dialog
            :open="requestDialogOpen"
            @update:open="requestDialogOpen = $event"
        >
            <DialogContent
                class="sm:max-w-md dark:border-slate-800 dark:bg-slate-900"
            >
                <DialogHeader>
                    <DialogTitle
                        class="flex items-center gap-1.5 text-base text-blue-800 dark:text-blue-400"
                    >
                        <ShieldCheck
                            class="h-4 w-4 text-blue-600 dark:text-blue-400"
                        />
                        <span>Request Profile Verification</span>
                    </DialogTitle>
                    <DialogDescription
                        class="text-xs text-slate-500 dark:text-slate-400"
                    >
                        Submitting a verification request will submit your
                        guardian details to admins for review.
                    </DialogDescription>
                </DialogHeader>

                <p class="py-2 text-xs text-slate-600 dark:text-slate-300">
                    Once submitted, our team will review your information and
                    process your verification status.
                </p>

                <DialogFooter class="gap-2 sm:gap-0">
                    <Button
                        type="button"
                        variant="outline"
                        size="sm"
                        class="text-xs dark:border-slate-700 dark:text-slate-300"
                        @click="requestDialogOpen = false"
                        >Cancel</Button
                    >
                    <Button
                        type="button"
                        size="sm"
                        class="bg-blue-600 text-xs text-white hover:bg-blue-700"
                        @click="requestVerification"
                    >
                        Submit Verification Request
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </GuardianLayout>
</template>
