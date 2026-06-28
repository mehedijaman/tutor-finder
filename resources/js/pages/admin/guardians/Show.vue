<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
    ArrowLeft,
    Calendar,
    Mail,
    MapPin,
    Phone,
    Shield,
    ShieldAlert,
    ShieldCheck,
    User as UserIcon,
    ChevronRight,
    Clock,
    RotateCcw,
    Briefcase,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';
import ConfirmDialog from '@/components/admin/dialogs/ConfirmDialog.vue';
import InputError from '@/components/InputError.vue';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { cn } from '@/lib/utils';

const props = defineProps<{
    guardian: any;
    profile: any;
    verification: any;
}>();

const breadcrumbs = [
    { title: 'Guardians', href: '/admin/guardians' },
    { title: props.guardian.name, href: '#' },
];

const activeTab = ref('personal');
const isEditMode = ref(false);
const confirmOpen = ref(false);

const form = useForm({
    name: props.guardian.name,
    email: props.guardian.email,
    phone: props.guardian.phone,
    status: props.guardian.status,
    occupation: props.profile.occupation,
    address: props.profile.address,
    phone_alt: props.profile.phone_alt,
    notes: props.profile.notes,
});

const initials = computed(() => {
    return props.guardian.name
        .split(' ')
        .map((n: string) => n[0])
        .join('')
        .toUpperCase()
        .slice(0, 2);
});

const statusColor = computed(() => {
    return props.guardian.status === 'active'
        ? 'bg-emerald-50 text-emerald-700 border-emerald-200'
        : 'bg-red-50 text-red-700 border-red-200';
});

const profileCompletion = computed(() => {
    const fields = ['name', 'phone', 'occupation', 'address'];
    const filled = fields.filter((f) => !!form[f as keyof typeof form]).length;
    return Math.round((filled / fields.length) * 100);
});

function toggleStatus() {
    const nextStatus =
        props.guardian.status === 'active' ? 'suspended' : 'active';
    form.status = nextStatus;
    submit();
    confirmOpen.value = false;
}

const formatDate = (date: any) => {
    if (!date) return '—';
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
};

const submit = () => {
    form.put(`/admin/guardians/${props.guardian.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            isEditMode.value = false;
        },
    });
};

const tabs = [
    { id: 'personal', label: 'Personal Info', icon: UserIcon },
    { id: 'verification', label: 'Verification', icon: ShieldCheck },
];
</script>

<template>
    <Head :title="`${guardian.name} | Guardian Profile`" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto max-w-7xl space-y-6 p-4 sm:p-6 lg:p-8">
            <div
                class="grid grid-cols-1 gap-6 xl:grid-cols-[300px_minmax(0,1fr)]"
            >
                <!-- Sidebar -->
                <aside class="space-y-6">
                    <Card
                        class="overflow-hidden rounded-2xl border-slate-200/60 shadow-sm transition-all hover:shadow-md"
                    >
                        <div
                            class="h-24 bg-gradient-to-br from-indigo-500 to-indigo-700"
                        ></div>
                        <CardContent class="relative pt-0">
                            <div
                                class="-mt-12 flex flex-col items-center text-center"
                            >
                                <Avatar
                                    class="h-24 w-24 border-4 border-white shadow-lg ring-1 ring-slate-100"
                                >
                                    <AvatarImage
                                        v-if="guardian.photo_url"
                                        :src="guardian.photo_url"
                                        :alt="guardian.name"
                                    />
                                    <AvatarFallback
                                        class="bg-slate-100 text-2xl font-bold text-slate-400 uppercase"
                                    >
                                        {{ initials }}
                                    </AvatarFallback>
                                </Avatar>

                                <div class="mt-4 space-y-1">
                                    <h2
                                        class="text-xl leading-tight font-bold tracking-tight text-slate-900"
                                    >
                                        {{ guardian.name }}
                                    </h2>
                                    <p
                                        class="pt-1 text-[11px] font-bold tracking-widest text-slate-400 uppercase"
                                    >
                                        Guardian ID: #{{ guardian.id }}
                                    </p>
                                    <div
                                        class="flex items-center justify-center pt-2"
                                    >
                                        <Badge
                                            variant="outline"
                                            :class="
                                                cn(
                                                    'rounded-full px-2.5 py-0.5 text-[9px] font-bold tracking-widest uppercase',
                                                    statusColor,
                                                )
                                            "
                                        >
                                            {{
                                                guardian.status?.replace(
                                                    '_',
                                                    ' ',
                                                )
                                            }}
                                        </Badge>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-8 space-y-6">
                                <div class="space-y-2">
                                    <div
                                        class="flex items-center justify-between text-[10px]"
                                    >
                                        <span
                                            class="font-bold tracking-widest text-slate-400 uppercase"
                                            >Profile Progress</span
                                        >
                                        <span class="font-bold text-indigo-600"
                                            >{{ profileCompletion }}%</span
                                        >
                                    </div>
                                    <div
                                        class="h-1.5 w-full overflow-hidden rounded-full bg-slate-100"
                                    >
                                        <div
                                            class="h-full bg-indigo-500 transition-all duration-1000 ease-out"
                                            :style="{
                                                width: `${profileCompletion}%`,
                                            }"
                                        ></div>
                                    </div>
                                </div>

                                <div class="space-y-4">
                                    <div class="flex items-start gap-3">
                                        <div
                                            class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-slate-50 text-slate-400 ring-1 ring-slate-100"
                                        >
                                            <Mail class="h-4 w-4" />
                                        </div>
                                        <div class="min-w-0">
                                            <p
                                                class="text-[9px] font-bold tracking-widest text-slate-400 uppercase"
                                            >
                                                Account Email
                                            </p>
                                            <p
                                                class="truncate text-xs font-bold text-slate-700"
                                            >
                                                {{ guardian.email || '—' }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="flex items-start gap-3">
                                        <div
                                            class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-slate-50 text-slate-400 ring-1 ring-slate-100"
                                        >
                                            <Phone class="h-4 w-4" />
                                        </div>
                                        <div class="min-w-0">
                                            <p
                                                class="text-[9px] font-bold tracking-widest text-slate-400 uppercase"
                                            >
                                                Contact Number
                                            </p>
                                            <p
                                                class="text-xs font-bold text-slate-700"
                                            >
                                                {{ guardian.phone || '—' }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="flex items-start gap-3">
                                        <div
                                            class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-slate-50 text-slate-400 ring-1 ring-slate-100"
                                        >
                                            <Calendar class="h-4 w-4" />
                                        </div>
                                        <div class="min-w-0">
                                            <p
                                                class="text-[9px] font-bold tracking-widest text-slate-400 uppercase"
                                            >
                                                Joined On
                                            </p>
                                            <p
                                                class="text-xs font-bold text-slate-700"
                                            >
                                                {{
                                                    formatDate(
                                                        guardian.created_at,
                                                    )
                                                }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-2 pt-2">
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        as-child
                                        class="h-9 rounded-xl font-bold"
                                    >
                                        <Link href="/admin/guardians">
                                            <ArrowLeft
                                                class="mr-2 h-3.5 w-3.5"
                                            />
                                            Back
                                        </Link>
                                    </Button>
                                    <Button
                                        :variant="
                                            guardian.status === 'active'
                                                ? 'destructive'
                                                : 'default'
                                        "
                                        size="sm"
                                        class="h-9 rounded-xl font-bold"
                                        @click="confirmOpen = true"
                                    >
                                        <ShieldAlert
                                            v-if="guardian.status === 'active'"
                                            class="mr-2 h-3.5 w-3.5"
                                        />
                                        <ShieldCheck
                                            v-else
                                            class="mr-2 h-3.5 w-3.5"
                                        />
                                        {{
                                            guardian.status === 'active'
                                                ? 'Suspend'
                                                : 'Activate'
                                        }}
                                    </Button>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <Card
                        class="overflow-hidden rounded-2xl border-slate-200/60 shadow-sm"
                    >
                        <nav class="space-y-1 p-1.5">
                            <button
                                v-for="tab in tabs"
                                :key="tab.id"
                                @click="
                                    activeTab = tab.id;
                                    isEditMode = false;
                                "
                                :class="
                                    cn(
                                        'flex w-full items-center gap-3 rounded-xl px-4 py-2.5 text-xs font-bold transition-all duration-200',
                                        activeTab === tab.id
                                            ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-100'
                                            : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900',
                                    )
                                "
                            >
                                <component :is="tab.icon" class="h-3.5 w-3.5" />
                                {{ tab.label }}
                                <ChevronRight
                                    v-if="activeTab === tab.id"
                                    class="ml-auto h-3 w-3 opacity-60"
                                />
                            </button>
                        </nav>
                    </Card>
                </aside>

                <!-- Main Content -->
                <main class="space-y-6">
                    <div
                        class="flex flex-wrap items-center justify-between gap-4"
                    >
                        <div class="space-y-1">
                            <h1
                                class="text-2xl leading-tight font-bold tracking-tight text-slate-900"
                            >
                                {{
                                    tabs.find((t) => t.id === activeTab)?.label
                                }}
                                Information
                            </h1>
                            <p class="text-xs font-medium text-slate-500">
                                Detailed background and account configuration
                                for this guardian.
                            </p>
                        </div>

                        <div
                            v-if="activeTab === 'personal'"
                            class="flex items-center gap-3"
                        >
                            <Button
                                v-if="!isEditMode"
                                variant="outline"
                                class="h-9 rounded-xl border-slate-200 px-4 font-bold ring-1 ring-slate-100"
                                @click="isEditMode = true"
                            >
                                <UserIcon
                                    class="mr-2 h-3.5 w-3.5 text-indigo-500"
                                />
                                Edit Account
                            </Button>
                            <template v-else>
                                <Button
                                    variant="outline"
                                    class="h-9 rounded-xl px-4 font-bold"
                                    @click="
                                        isEditMode = false;
                                        form.reset();
                                    "
                                >
                                    Cancel
                                </Button>
                                <Button
                                    class="h-9 rounded-xl bg-indigo-600 px-4 font-bold shadow-lg shadow-indigo-100"
                                    @click="submit"
                                    :disabled="form.processing"
                                >
                                    <ShieldCheck
                                        v-if="!form.processing"
                                        class="mr-2 h-3.5 w-3.5"
                                    />
                                    <RotateCcw
                                        v-else
                                        class="mr-2 h-3.5 w-3.5 animate-spin"
                                    />
                                    Save Changes
                                </Button>
                            </template>
                        </div>
                    </div>

                    <div
                        v-if="activeTab === 'personal'"
                        class="animate-in duration-300 fade-in slide-in-from-bottom-2"
                    >
                        <Card
                            v-if="!isEditMode"
                            class="overflow-hidden rounded-2xl border-slate-200/60 shadow-sm transition-all hover:border-slate-300"
                        >
                            <div
                                class="grid grid-cols-1 divide-x divide-slate-100 md:grid-cols-2"
                            >
                                <div class="space-y-8 p-8">
                                    <div class="space-y-1.5">
                                        <p
                                            class="text-[10px] font-bold tracking-widest text-slate-400 uppercase"
                                        >
                                            Guardian Occupation
                                        </p>
                                        <div
                                            class="flex items-center gap-2 font-bold text-slate-800"
                                        >
                                            <Briefcase
                                                class="h-4 w-4 text-indigo-400"
                                            />
                                            {{
                                                profile.occupation ||
                                                'No occupation listed'
                                            }}
                                        </div>
                                    </div>
                                    <div class="space-y-1.5">
                                        <p
                                            class="text-[10px] font-bold tracking-widest text-slate-400 uppercase"
                                        >
                                            Permanent Address
                                        </p>
                                        <div
                                            class="flex items-start gap-2 rounded-xl border border-slate-100 bg-slate-50 p-4 text-sm leading-relaxed font-medium text-slate-600"
                                        >
                                            <MapPin
                                                class="mt-0.5 h-4 w-4 shrink-0 text-slate-300"
                                            />
                                            {{
                                                profile.address ||
                                                'Address information missing'
                                            }}
                                        </div>
                                    </div>
                                </div>
                                <div class="space-y-8 bg-slate-50/20 p-8">
                                    <div class="space-y-1.5">
                                        <p
                                            class="text-[10px] font-bold tracking-widest text-slate-400 uppercase"
                                        >
                                            Emergency / Alt Phone
                                        </p>
                                        <p
                                            class="flex items-center gap-2 font-bold text-slate-800"
                                        >
                                            <Phone
                                                class="h-4 w-4 text-emerald-400"
                                            />
                                            {{ profile.phone_alt || '—' }}
                                        </p>
                                    </div>
                                    <div class="space-y-1.5">
                                        <p
                                            class="text-[10px] font-bold tracking-widest text-slate-400 uppercase"
                                        >
                                            Verification State
                                        </p>
                                        <Badge
                                            variant="outline"
                                            :class="
                                                cn(
                                                    'rounded-full px-3 py-1 text-[10px] font-bold tracking-widest uppercase',
                                                    guardian.verification_status ===
                                                        'verified'
                                                        ? 'border-blue-200 bg-blue-50 text-blue-700'
                                                        : 'border-slate-200 bg-slate-50 text-slate-500',
                                                )
                                            "
                                        >
                                            {{
                                                guardian.verification_status ||
                                                'Unverified'
                                            }}
                                        </Badge>
                                    </div>
                                </div>
                                <div
                                    class="border-t border-slate-100 p-8 md:col-span-2"
                                >
                                    <p
                                        class="mb-3 text-[10px] font-bold tracking-widest text-slate-400 uppercase"
                                    >
                                        Internal Administrative Notes
                                    </p>
                                    <p
                                        class="text-sm leading-relaxed font-medium text-slate-600 italic"
                                    >
                                        {{
                                            profile.notes ||
                                            'No administrative notes have been recorded for this guardian.'
                                        }}
                                    </p>
                                </div>
                            </div>
                        </Card>

                        <form v-else @submit.prevent="submit" class="space-y-6">
                            <Card
                                class="rounded-2xl border-slate-200/60 shadow-sm"
                            >
                                <CardContent
                                    class="grid gap-6 p-8 md:grid-cols-2"
                                >
                                    <div class="grid gap-2">
                                        <Label for="name">Full Name</Label>
                                        <Input
                                            id="name"
                                            v-model="form.name"
                                            class="h-11 rounded-xl border-slate-200 focus-visible:ring-indigo-500"
                                        />
                                        <InputError
                                            :message="form.errors.name"
                                        />
                                    </div>
                                    <div class="grid gap-2">
                                        <Label for="email">Account Email</Label>
                                        <Input
                                            id="email"
                                            v-model="form.email"
                                            type="email"
                                            class="h-11 rounded-xl border-slate-200 focus-visible:ring-indigo-500"
                                        />
                                        <InputError
                                            :message="form.errors.email"
                                        />
                                    </div>
                                    <div class="grid gap-2">
                                        <Label for="phone">Primary Phone</Label>
                                        <Input
                                            id="phone"
                                            v-model="form.phone"
                                            class="h-11 rounded-xl border-slate-200 focus-visible:ring-indigo-500"
                                        />
                                        <InputError
                                            :message="form.errors.phone"
                                        />
                                    </div>
                                    <div class="grid gap-2">
                                        <Label for="phone_alt"
                                            >Alternative Phone</Label
                                        >
                                        <Input
                                            id="phone_alt"
                                            v-model="form.phone_alt"
                                            class="h-11 rounded-xl border-slate-200 focus-visible:ring-indigo-500"
                                        />
                                        <InputError
                                            :message="form.errors.phone_alt"
                                        />
                                    </div>
                                    <div class="grid gap-2">
                                        <Label for="occupation"
                                            >Occupation</Label
                                        >
                                        <Input
                                            id="occupation"
                                            v-model="form.occupation"
                                            class="h-11 rounded-xl border-slate-200 focus-visible:ring-indigo-500"
                                        />
                                        <InputError
                                            :message="form.errors.occupation"
                                        />
                                    </div>
                                    <div class="grid gap-2">
                                        <Label for="status"
                                            >Account Status</Label
                                        >
                                        <Select v-model="form.status">
                                            <SelectTrigger
                                                id="status"
                                                class="h-11 rounded-xl border-slate-200"
                                            >
                                                <SelectValue
                                                    placeholder="Access control"
                                                />
                                            </SelectTrigger>
                                            <SelectContent class="rounded-xl">
                                                <SelectItem value="active"
                                                    >Active</SelectItem
                                                >
                                                <SelectItem value="suspended"
                                                    >Suspended</SelectItem
                                                >
                                                <SelectItem
                                                    value="pending_verification"
                                                    >Pending
                                                    Approval</SelectItem
                                                >
                                            </SelectContent>
                                        </Select>
                                        <InputError
                                            :message="form.errors.status"
                                        />
                                    </div>
                                    <div class="grid gap-2 md:col-span-2">
                                        <Label for="address"
                                            >Full Address</Label
                                        >
                                        <Textarea
                                            id="address"
                                            v-model="form.address"
                                            rows="3"
                                            class="min-h-[100px] rounded-xl border-slate-200 focus-visible:ring-indigo-500"
                                        />
                                        <InputError
                                            :message="form.errors.address"
                                        />
                                    </div>
                                    <div class="grid gap-2 md:col-span-2">
                                        <Label for="notes">Admin Notes</Label>
                                        <Textarea
                                            id="notes"
                                            v-model="form.notes"
                                            rows="4"
                                            class="min-h-[120px] rounded-xl border-slate-200 focus-visible:ring-indigo-500"
                                        />
                                        <InputError
                                            :message="form.errors.notes"
                                        />
                                    </div>
                                </CardContent>
                            </Card>
                        </form>
                    </div>

                    <!-- Verification Section -->
                    <div
                        v-if="activeTab === 'verification'"
                        class="animate-in duration-300 fade-in slide-in-from-bottom-2"
                    >
                        <Card
                            v-if="!verification"
                            class="flex flex-col items-center justify-center rounded-2xl border-dashed border-slate-200 p-16 text-center"
                        >
                            <Shield class="mb-4 h-14 w-14 text-slate-100" />
                            <h3
                                class="mb-1 text-[10px] font-bold tracking-widest text-slate-400 uppercase"
                            >
                                Status: Unverified
                            </h3>
                            <p
                                class="max-w-sm text-sm font-medium text-slate-400"
                            >
                                This guardian has not submitted any verification
                                requests yet.
                            </p>
                        </Card>
                        <div v-else class="space-y-6">
                            <Card
                                class="overflow-hidden rounded-2xl border-slate-200/60 shadow-sm"
                            >
                                <CardHeader
                                    class="border-b border-slate-50 bg-indigo-50/30 p-6"
                                >
                                    <div
                                        class="flex items-center justify-between"
                                    >
                                        <div class="space-y-1">
                                            <CardTitle
                                                class="text-[10px] font-bold tracking-widest text-indigo-500 uppercase"
                                                >Active Request</CardTitle
                                            >
                                            <p
                                                class="text-xl font-bold tracking-tight text-slate-900"
                                            >
                                                Guardian Identity Review
                                            </p>
                                        </div>
                                        <Badge
                                            :class="
                                                cn(
                                                    'rounded-full px-4 py-1.5 text-[10px] font-bold tracking-widest uppercase',
                                                    verification.status ===
                                                        'approved'
                                                        ? 'bg-emerald-500 text-white'
                                                        : 'border-amber-200 bg-amber-100 text-amber-700',
                                                )
                                            "
                                        >
                                            {{ verification.status }}
                                        </Badge>
                                    </div>
                                </CardHeader>
                                <CardContent class="p-0">
                                    <div
                                        class="grid grid-cols-1 divide-x divide-slate-50 md:grid-cols-3"
                                    >
                                        <div class="space-y-1.5 p-8">
                                            <p
                                                class="text-[10px] font-bold tracking-widest text-slate-400 uppercase"
                                            >
                                                Submission Date
                                            </p>
                                            <div
                                                class="flex items-center gap-2 font-bold text-slate-800"
                                            >
                                                <Clock
                                                    class="h-4 w-4 text-slate-300"
                                                />
                                                {{
                                                    formatDate(
                                                        verification.submitted_at,
                                                    )
                                                }}
                                            </div>
                                        </div>
                                        <div class="space-y-1.5 p-8">
                                            <p
                                                class="text-[10px] font-bold tracking-widest text-slate-400 uppercase"
                                            >
                                                Review State
                                            </p>
                                            <div
                                                class="flex items-center gap-2 font-bold text-slate-800"
                                            >
                                                <RotateCcw
                                                    class="h-4 w-4 text-indigo-400"
                                                />
                                                {{
                                                    verification.reviewed_at
                                                        ? formatDate(
                                                              verification.reviewed_at,
                                                          )
                                                        : 'In progress...'
                                                }}
                                            </div>
                                        </div>
                                        <div class="space-y-1.5 p-8">
                                            <p
                                                class="text-[10px] font-bold tracking-widest text-slate-400 uppercase"
                                            >
                                                Payment Link
                                            </p>
                                            <Badge
                                                v-if="verification.invoice"
                                                variant="outline"
                                                :class="
                                                    cn(
                                                        'h-7 rounded-lg border-2 px-3 text-[10px] font-bold uppercase',
                                                        verification.invoice
                                                            .status === 'paid'
                                                            ? 'border-emerald-100 bg-emerald-50 text-emerald-700'
                                                            : 'border-amber-100 bg-amber-50 text-amber-600',
                                                    )
                                                "
                                            >
                                                Invoice:
                                                {{
                                                    verification.invoice.status
                                                }}
                                            </Badge>
                                            <p
                                                v-else
                                                class="text-xs font-medium text-slate-400"
                                            >
                                                No invoice generated
                                            </p>
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>
                        </div>
                    </div>
                </main>
            </div>
        </div>

        <ConfirmDialog
            v-model:open="confirmOpen"
            :title="
                guardian.status === 'active'
                    ? 'Suspend Guardian'
                    : 'Activate Guardian'
            "
            :description="
                guardian.status === 'active'
                    ? 'Suspending this user will immediately revoke dashboard access.'
                    : 'Account activation will restore full access for this guardian.'
            "
            :confirm-label="
                guardian.status === 'active'
                    ? 'Suspend Account'
                    : 'Activate Now'
            "
            :destructive="guardian.status === 'active'"
            @confirm="toggleStatus"
        />
    </AdminLayout>
</template>

<style scoped>
.animate-in {
    animation-fill-mode: forwards;
}

@keyframes fadeIn {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(12px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

main > div {
    animation: slideUp 0.4s ease-out forwards;
}
aside {
    animation: fadeIn 0.6s ease-out forwards;
}
</style>
