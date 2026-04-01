<script setup lang="ts">
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import {
    BookOpen,
    CheckCircle,
    ClipboardList,
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
import TutorLayout from '@/layouts/TutorLayout.vue';

const props = defineProps({
    profile: {
        type: Object,
        required: true,
    },
    tuitionTypes: {
        type: Array,
        default: () => [],
    },
    categories: {
        type: Array,
        default: () => [],
    },
    schoolClasses: {
        type: Array,
        default: () => [],
    },
    subjects: {
        type: Array,
        default: () => [],
    },
    locations: {
        type: Array,
        default: () => [],
    },
    dayOptions: {
        type: Array,
        default: () => [],
    },
    genderOptions: {
        type: Array as () => Array<{ value: string; label: string }>,
        default: () => [],
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

const page = usePage();
const authUser = computed(() => page.props.auth?.user);
const downloadCvUrl = '/tutor/profile/download-cv';
const viewAsGuardianUrl = computed(() => {
    if (!authUser.value?.id) {
        return '/tutors';
    }

    return `/tutors/${authUser.value.id}`;
});

const breadcrumbs = [{ title: 'Tutor Profile', href: '/tutor/profile' }];

const tabs = [
    { key: 'personal', label: 'Personal', sublabel: 'Information', icon: UserIcon },
    { key: 'education', label: 'Educational', sublabel: 'Information', icon: BookOpen },
    { key: 'preferences', label: 'Tuition Related', sublabel: 'Information', icon: ClipboardList },
    { key: 'verification', label: 'Verification', sublabel: 'Status', icon: Shield },
];

const activeTab = ref('personal');
const editingTab = ref<string | null>(null);
const isEditingActiveTab = computed(
    () => editingTab.value === activeTab.value,
);

const form = useForm({
    name: props.profile.name ?? '',
    phone: props.profile.phone ?? '',
    gender: props.profile.gender ?? 'none',
    date_of_birth: props.profile.date_of_birth ?? '',
    present_address: props.profile.present_address ?? '',
    permanent_address: props.profile.permanent_address ?? '',
    nid_no: props.profile.nid_no ?? '',
    bio: props.profile.bio ?? '',
    preferred_tuition_types: Array.isArray(
        props.profile.preferred_tuition_types,
    )
        ? [...props.profile.preferred_tuition_types]
        : [],
    preferred_categories: Array.isArray(props.profile.preferred_categories)
        ? [...props.profile.preferred_categories]
        : [],
    preferred_classes: Array.isArray(props.profile.preferred_classes)
        ? [...props.profile.preferred_classes]
        : [],
    preferred_subjects: Array.isArray(props.profile.preferred_subjects)
        ? [...props.profile.preferred_subjects]
        : [],
    preferred_locations: Array.isArray(props.profile.preferred_locations)
        ? [...props.profile.preferred_locations]
        : [],
    expected_salary_min: props.profile.expected_salary_min ?? '',
    expected_salary_max: props.profile.expected_salary_max ?? '',
    available_days: Array.isArray(props.profile.available_days)
        ? [...props.profile.available_days]
        : [],
    available_time: props.profile.available_time ?? '',
    status: props.profile.status ?? 'active',
    educations: Array.isArray(props.profile.educations)
        ? props.profile.educations.map((education, index) => ({
              id: education.id ?? null,
              degree: education.degree ?? '',
              institute: education.institute ?? '',
              department: education.department ?? '',
              graduation_year: education.graduation_year ?? '',
              result: education.result ?? '',
              is_current: Boolean(education.is_current),
              sort_order: education.sort_order ?? index,
          }))
        : [],
});

const profileCompletionFields = [
    'name',
    'phone',
    'gender',
    'date_of_birth',
    'present_address',
    'bio',
    'nid_no',
    'preferred_tuition_types',
    'preferred_categories',
    'preferred_classes',
    'preferred_subjects',
    'preferred_locations',
    'expected_salary_min',
    'available_days',
    'available_time',
];

const profileCompletion = computed(() => {
    let filled = 0;

    for (const field of profileCompletionFields) {
        const value = (form as any)[field];

        if (Array.isArray(value)) {
            if (value.length > 0) {
                filled++;
            }
        } else if (value && value !== 'none') {
            filled++;
        }
    }

    const educationFilled = form.educations.length > 0 ? 1 : 0;
    const total = profileCompletionFields.length + 1;

    return Math.round(((filled + educationFilled) / total) * 100);
});

const filteredClasses = computed(() => {
    const selectedCategories = new Set(form.preferred_categories);

    if (!selectedCategories.size) {
        return props.schoolClasses;
    }

    return props.schoolClasses.filter((schoolClass) =>
        selectedCategories.has(schoolClass.category_id),
    );
});

const filteredSubjects = computed(() => {
    const selectedClasses = new Set(form.preferred_classes);

    if (!selectedClasses.size) {
        return props.subjects;
    }

    return props.subjects.filter((subject) =>
        selectedClasses.has(subject.class_id),
    );
});

const genderLabel = computed(() => {
    const match = (props.genderOptions as any[]).find(
        (option: any) => option.value === form.gender,
    );

    return match?.label ?? 'Not specified';
});

const preferredTuitionTypeNames = computed(() =>
    (props.tuitionTypes as any[])
        .filter((option: any) => form.preferred_tuition_types.includes(option.id))
        .map((option: any) => option.name),
);

const preferredCategoryNames = computed(() =>
    (props.categories as any[])
        .filter((option: any) => form.preferred_categories.includes(option.id))
        .map((option: any) => option.name),
);

const preferredClassNames = computed(() =>
    (props.schoolClasses as any[])
        .filter((option: any) => form.preferred_classes.includes(option.id))
        .map((option: any) => option.name),
);

const preferredSubjectNames = computed(() =>
    (props.subjects as any[])
        .filter((option: any) => form.preferred_subjects.includes(option.id))
        .map((option: any) => option.name),
);

const preferredLocationNames = computed(() =>
    (props.locations as any[])
        .filter((option: any) => form.preferred_locations.includes(option.id))
        .map((option: any) => option.name),
);

const availableDayLabels = computed(() =>
    (props.dayOptions as any[])
        .filter((option: any) => form.available_days.includes(option.value))
        .map((option: any) => option.label),
);

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
    if (activeTab.value === 'education') {
        return form.educations.length > 0 ? 'Edit' : 'Add';
    }

    if (activeTab.value === 'preferences') {
        const hasPreferences = [
            form.preferred_tuition_types,
            form.preferred_categories,
            form.preferred_classes,
            form.preferred_subjects,
            form.preferred_locations,
            form.expected_salary_min,
            form.expected_salary_max,
            form.available_days,
            form.available_time,
        ].some((value) => hasValue(value));

        return hasPreferences ? 'Edit' : 'Add';
    }

    const hasPersonalInfo = [
        form.name,
        form.phone,
        form.gender !== 'none' ? form.gender : '',
        form.date_of_birth,
        form.present_address,
        form.permanent_address,
        form.nid_no,
        form.bio,
    ].some((value) => hasValue(value));

    return hasPersonalInfo ? 'Edit' : 'Add';
});

function switchTab(tabKey: string) {
    activeTab.value = tabKey;
    editingTab.value = null;
}

function openEditMode() {
    editingTab.value = activeTab.value;
}

function closeEditMode() {
    editingTab.value = null;
}

function submit() {
    form.transform((data) => ({
        ...data,
        gender: data.gender === 'none' ? null : data.gender,
        educations: data.educations.map((education, index) => ({
            ...education,
            sort_order: index,
        })),
    })).put('/tutor/profile', {
        preserveScroll: true,
        onSuccess: () => {
            editingTab.value = null;
        },
    });
}

function addEducation() {
    form.educations.push({
        id: null,
        degree: '',
        institute: '',
        department: '',
        graduation_year: '',
        result: '',
        is_current: false,
        sort_order: form.educations.length,
    });

    activeTab.value = 'education';
    editingTab.value = 'education';
}

function removeEducation(index) {
    form.educations.splice(index, 1);
}

function moveEducation(index, direction) {
    const targetIndex = index + direction;

    if (targetIndex < 0 || targetIndex >= form.educations.length) {
        return;
    }

    const [current] = form.educations.splice(index, 1);
    form.educations.splice(targetIndex, 0, current);
}

function toggleMultiSelect(field: string, value: any) {
    const current = new Set((form as any)[field]);

    if (current.has(value)) {
        current.delete(value);
    } else {
        current.add(value);
    }

    (form as any)[field] = Array.from(current);
}

function hasSelected(field: string, value: any) {
    return ((form as any)[field] as any[]).includes(value);
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

const verificationInvoice = computed(
    () => props.verification?.invoice ?? null,
);
const canPayInvoice = computed(
    () =>
        verificationInvoice.value &&
        verificationInvoice.value.status === 'unpaid',
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
    <Head title="Tutor Profile" />

    <TutorLayout :breadcrumbs="breadcrumbs">
        <div class="grid gap-6 p-4 xl:grid-cols-[320px_minmax(0,1fr)] sm:p-6">
            <aside
                class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm"
            >
                <div class="flex flex-col items-center text-center">
                    <ProfilePhotoUpload />

                    <h2 class="mt-2 text-2xl font-semibold tracking-tight">
                        {{ form.name || authUser?.name || 'Tutor' }}
                    </h2>
                    <p class="mt-1 text-sm text-muted-foreground">
                        Tutor Id : {{ authUser?.id ?? '—' }}
                    </p>

                    <div class="mt-4 flex items-center gap-2 text-sm text-muted-foreground">
                        <span>(0/5)</span>
                    </div>

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
                            <p class="font-medium">Present Address</p>
                            <p class="text-muted-foreground">
                                {{ form.present_address || '—' }}
                            </p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3">
                        <Shield class="mt-0.5 h-4 w-4 text-blue-500" />
                        <div>
                            <p class="font-medium">Verification</p>
                            <Badge :variant="statusVariant">{{ statusLabel }}</Badge>
                        </div>
                    </div>
                </div>

                <div class="mt-6 space-y-3">
                    <a
                        :href="downloadCvUrl"
                        class="inline-flex w-full items-center justify-center rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-blue-700"
                    >
                        Download CV
                    </a>
                    <a
                        :href="viewAsGuardianUrl"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex w-full items-center justify-center rounded-lg border border-blue-500 bg-white px-4 py-2.5 text-sm font-medium text-blue-600 transition hover:bg-blue-50"
                    >
                        View as Guardian
                    </a>
                </div>
            </aside>

            <div class="space-y-6">

            <div
                v-if="$page.props.flash?.status"
                class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800"
            >
                {{ $page.props.flash.status }}
            </div>

            <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
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
                            <p class="text-sm font-semibold">{{ tab.label }}</p>
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
                        {{ tabs.find((tab) => tab.key === activeTab)?.label }}
                        Information
                    </h2>
                    <Button type="button" variant="outline" @click="openEditMode">
                        {{ activeTabActionLabel }}
                    </Button>
                </div>

                <section
                    v-if="activeTab === 'personal'"
                    class="grid gap-3 text-sm md:grid-cols-2"
                >
                    <p><span class="font-medium">Full Name:</span> {{ form.name || '—' }}</p>
                    <p><span class="font-medium">Phone:</span> {{ form.phone || '—' }}</p>
                    <p><span class="font-medium">Gender:</span> {{ genderLabel }}</p>
                    <p><span class="font-medium">Date of Birth:</span> {{ form.date_of_birth || '—' }}</p>
                    <p class="md:col-span-2"><span class="font-medium">Present Address:</span> {{ form.present_address || '—' }}</p>
                    <p class="md:col-span-2"><span class="font-medium">Permanent Address:</span> {{ form.permanent_address || '—' }}</p>
                    <p><span class="font-medium">NID Number:</span> {{ form.nid_no || '—' }}</p>
                    <p class="md:col-span-2"><span class="font-medium">Bio:</span> {{ form.bio || '—' }}</p>
                </section>

                <section
                    v-if="activeTab === 'education'"
                    class="space-y-3 text-sm"
                >
                    <p v-if="!form.educations.length" class="text-muted-foreground">
                        No education records added yet.
                    </p>
                    <div
                        v-for="(education, index) in form.educations"
                        :key="education.id ?? `preview-${index}`"
                        class="rounded-lg border border-slate-200/80 p-3"
                    >
                        <p class="font-medium">Education #{{ index + 1 }}</p>
                        <p><span class="font-medium">Degree:</span> {{ education.degree || '—' }}</p>
                        <p><span class="font-medium">Institute:</span> {{ education.institute || '—' }}</p>
                        <p><span class="font-medium">Department:</span> {{ education.department || '—' }}</p>
                        <p><span class="font-medium">Graduation Year:</span> {{ education.graduation_year || '—' }}</p>
                        <p><span class="font-medium">Result:</span> {{ education.result || '—' }}</p>
                    </div>
                </section>

                <section
                    v-if="activeTab === 'preferences'"
                    class="grid gap-3 text-sm md:grid-cols-2"
                >
                    <p><span class="font-medium">Preferred Tuition Types:</span> {{ preferredTuitionTypeNames.join(', ') || '—' }}</p>
                    <p><span class="font-medium">Preferred Categories:</span> {{ preferredCategoryNames.join(', ') || '—' }}</p>
                    <p><span class="font-medium">Preferred Classes:</span> {{ preferredClassNames.join(', ') || '—' }}</p>
                    <p><span class="font-medium">Preferred Subjects:</span> {{ preferredSubjectNames.join(', ') || '—' }}</p>
                    <p class="md:col-span-2"><span class="font-medium">Preferred Locations:</span> {{ preferredLocationNames.join(', ') || '—' }}</p>
                    <p><span class="font-medium">Expected Salary Min:</span> {{ form.expected_salary_min || '—' }}</p>
                    <p><span class="font-medium">Expected Salary Max:</span> {{ form.expected_salary_max || '—' }}</p>
                    <p><span class="font-medium">Available Days:</span> {{ availableDayLabels.join(', ') || '—' }}</p>
                    <p><span class="font-medium">Available Time:</span> {{ form.available_time || '—' }}</p>
                    <p><span class="font-medium">Status:</span> {{ form.status || '—' }}</p>
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
                        <Label for="name">Full Name</Label>
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
                        <Label>Gender</Label>
                        <Select v-model="form.gender">
                            <SelectTrigger>
                                <SelectValue placeholder="Select gender" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="none"
                                    >Not specified</SelectItem
                                >
                                <SelectItem
                                    v-for="option in genderOptions"
                                    :key="option.value"
                                    :value="option.value"
                                >
                                    {{ option.label }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <InputError :message="form.errors.gender" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="date_of_birth">Date of Birth</Label>
                        <Input
                            id="date_of_birth"
                            v-model="form.date_of_birth"
                            type="date"
                        />
                        <InputError :message="form.errors.date_of_birth" />
                    </div>

                    <div class="grid gap-2 md:col-span-2">
                        <Label for="present_address">Present Address</Label>
                        <textarea
                            id="present_address"
                            v-model="form.present_address"
                            rows="3"
                            class="rounded-lg border border-slate-300 px-3 py-2 text-sm"
                        ></textarea>
                        <InputError :message="form.errors.present_address" />
                    </div>

                    <div class="grid gap-2 md:col-span-2">
                        <Label for="permanent_address">Permanent Address</Label>
                        <textarea
                            id="permanent_address"
                            v-model="form.permanent_address"
                            rows="3"
                            class="rounded-lg border border-slate-300 px-3 py-2 text-sm"
                        ></textarea>
                        <InputError :message="form.errors.permanent_address" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="nid_no">NID Number</Label>
                        <Input id="nid_no" v-model="form.nid_no" type="text" />
                        <InputError :message="form.errors.nid_no" />
                    </div>

                    <div class="grid gap-2 md:col-span-2">
                        <Label for="bio">Bio</Label>
                        <textarea
                            id="bio"
                            v-model="form.bio"
                            rows="4"
                            class="rounded-lg border border-slate-300 px-3 py-2 text-sm"
                        ></textarea>
                        <InputError :message="form.errors.bio" />
                    </div>
                </section>

                <section
                    v-if="activeTab === 'education'"
                    class="space-y-4 rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm"
                >
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-semibold">Education History</h2>
                        <Button
                            type="button"
                            variant="outline"
                            @click="addEducation"
                            >Add Row</Button
                        >
                    </div>

                    <p
                        v-if="!form.educations.length"
                        class="rounded-md border border-dashed px-4 py-5 text-sm text-muted-foreground"
                    >
                        No education records added yet.
                    </p>

                    <div
                        v-for="(education, index) in form.educations"
                        :key="education.id ?? `new-${index}`"
                        class="grid gap-3 rounded-xl border border-slate-200/80 p-4"
                    >
                        <div class="flex items-center justify-between">
                            <h3 class="text-sm font-semibold">
                                Education #{{ index + 1 }}
                            </h3>

                            <div class="flex items-center gap-2">
                                <Button
                                    type="button"
                                    variant="outline"
                                    size="sm"
                                    :disabled="index === 0"
                                    @click="moveEducation(index, -1)"
                                >
                                    Up
                                </Button>
                                <Button
                                    type="button"
                                    variant="outline"
                                    size="sm"
                                    :disabled="
                                        index === form.educations.length - 1
                                    "
                                    @click="moveEducation(index, 1)"
                                >
                                    Down
                                </Button>
                                <Button
                                    type="button"
                                    variant="destructive"
                                    size="sm"
                                    @click="removeEducation(index)"
                                >
                                    Remove
                                </Button>
                            </div>
                        </div>

                        <div class="grid gap-3 md:grid-cols-2">
                            <div class="grid gap-2">
                                <Label>Degree</Label>
                                <Input
                                    v-model="education.degree"
                                    type="text"
                                    placeholder="BSc in CSE"
                                />
                                <InputError
                                    :message="
                                        form.errors[
                                            `educations.${index}.degree`
                                        ]
                                    "
                                />
                            </div>

                            <div class="grid gap-2">
                                <Label>Institute</Label>
                                <Input
                                    v-model="education.institute"
                                    type="text"
                                    placeholder="University Name"
                                />
                                <InputError
                                    :message="
                                        form.errors[
                                            `educations.${index}.institute`
                                        ]
                                    "
                                />
                            </div>

                            <div class="grid gap-2">
                                <Label>Department</Label>
                                <Input
                                    v-model="education.department"
                                    type="text"
                                />
                                <InputError
                                    :message="
                                        form.errors[
                                            `educations.${index}.department`
                                        ]
                                    "
                                />
                            </div>

                            <div class="grid gap-2">
                                <Label>Graduation Year</Label>
                                <Input
                                    v-model="education.graduation_year"
                                    type="number"
                                    min="1900"
                                    max="2100"
                                />
                                <InputError
                                    :message="
                                        form.errors[
                                            `educations.${index}.graduation_year`
                                        ]
                                    "
                                />
                            </div>

                            <div class="grid gap-2 md:col-span-2">
                                <Label>Result</Label>
                                <Input
                                    v-model="education.result"
                                    type="text"
                                    placeholder="CGPA / Division"
                                />
                                <InputError
                                    :message="
                                        form.errors[
                                            `educations.${index}.result`
                                        ]
                                    "
                                />
                            </div>

                            <label
                                class="inline-flex items-center gap-2 text-sm"
                            >
                                <input
                                    v-model="education.is_current"
                                    type="checkbox"
                                    class="h-4 w-4 rounded border"
                                />
                                Currently studying
                            </label>
                        </div>
                    </div>

                    <InputError :message="form.errors.educations" />
                </section>

                <section
                    v-if="activeTab === 'preferences'"
                    class="space-y-5 rounded-xl border bg-white p-5"
                >
                    <h2 class="text-lg font-semibold">Tuition Preferences</h2>

                    <div class="grid gap-2">
                        <Label>Preferred Tuition Types</Label>
                        <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                            <label
                                v-for="option in tuitionTypes"
                                :key="`type-${option.id}`"
                                class="inline-flex items-center gap-2 rounded border px-3 py-2 text-sm"
                            >
                                <input
                                    type="checkbox"
                                    :checked="
                                        hasSelected(
                                            'preferred_tuition_types',
                                            option.id,
                                        )
                                    "
                                    @change="
                                        toggleMultiSelect(
                                            'preferred_tuition_types',
                                            option.id,
                                        )
                                    "
                                />
                                {{ option.name }}
                            </label>
                        </div>
                        <InputError
                            :message="form.errors.preferred_tuition_types"
                        />
                    </div>

                    <div class="grid gap-2">
                        <Label>Preferred Categories</Label>
                        <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                            <label
                                v-for="option in categories"
                                :key="`category-${option.id}`"
                                class="inline-flex items-center gap-2 rounded border px-3 py-2 text-sm"
                            >
                                <input
                                    type="checkbox"
                                    :checked="
                                        hasSelected(
                                            'preferred_categories',
                                            option.id,
                                        )
                                    "
                                    @change="
                                        toggleMultiSelect(
                                            'preferred_categories',
                                            option.id,
                                        )
                                    "
                                />
                                {{ option.name }}
                            </label>
                        </div>
                        <InputError
                            :message="form.errors.preferred_categories"
                        />
                    </div>

                    <div class="grid gap-2">
                        <Label>Preferred Classes</Label>
                        <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                            <label
                                v-for="option in filteredClasses"
                                :key="`class-${option.id}`"
                                class="inline-flex items-center gap-2 rounded border px-3 py-2 text-sm"
                            >
                                <input
                                    type="checkbox"
                                    :checked="
                                        hasSelected(
                                            'preferred_classes',
                                            option.id,
                                        )
                                    "
                                    @change="
                                        toggleMultiSelect(
                                            'preferred_classes',
                                            option.id,
                                        )
                                    "
                                />
                                {{ option.name }}
                            </label>
                        </div>
                        <InputError :message="form.errors.preferred_classes" />
                    </div>

                    <div class="grid gap-2">
                        <Label>Preferred Subjects</Label>
                        <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                            <label
                                v-for="option in filteredSubjects"
                                :key="`subject-${option.id}`"
                                class="inline-flex items-center gap-2 rounded border px-3 py-2 text-sm"
                            >
                                <input
                                    type="checkbox"
                                    :checked="
                                        hasSelected(
                                            'preferred_subjects',
                                            option.id,
                                        )
                                    "
                                    @change="
                                        toggleMultiSelect(
                                            'preferred_subjects',
                                            option.id,
                                        )
                                    "
                                />
                                {{ option.name }}
                            </label>
                        </div>
                        <InputError :message="form.errors.preferred_subjects" />
                    </div>

                    <div class="grid gap-2">
                        <Label>Preferred Locations</Label>
                        <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                            <label
                                v-for="option in locations"
                                :key="`location-${option.id}`"
                                class="inline-flex items-center gap-2 rounded border px-3 py-2 text-sm"
                            >
                                <input
                                    type="checkbox"
                                    :checked="
                                        hasSelected(
                                            'preferred_locations',
                                            option.id,
                                        )
                                    "
                                    @change="
                                        toggleMultiSelect(
                                            'preferred_locations',
                                            option.id,
                                        )
                                    "
                                />
                                {{ option.name }}
                            </label>
                        </div>
                        <InputError
                            :message="form.errors.preferred_locations"
                        />
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="grid gap-2">
                            <Label for="expected_salary_min"
                                >Expected Salary Min</Label
                            >
                            <Input
                                id="expected_salary_min"
                                v-model="form.expected_salary_min"
                                type="number"
                                min="0"
                                step="0.01"
                            />
                            <InputError
                                :message="form.errors.expected_salary_min"
                            />
                        </div>

                        <div class="grid gap-2">
                            <Label for="expected_salary_max"
                                >Expected Salary Max</Label
                            >
                            <Input
                                id="expected_salary_max"
                                v-model="form.expected_salary_max"
                                type="number"
                                min="0"
                                step="0.01"
                            />
                            <InputError
                                :message="form.errors.expected_salary_max"
                            />
                        </div>
                    </div>

                    <div class="grid gap-2">
                        <Label>Available Days</Label>
                        <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-4">
                            <label
                                v-for="option in dayOptions"
                                :key="`day-${option.value}`"
                                class="inline-flex items-center gap-2 rounded border px-3 py-2 text-sm"
                            >
                                <input
                                    type="checkbox"
                                    :checked="
                                        hasSelected(
                                            'available_days',
                                            option.value,
                                        )
                                    "
                                    @change="
                                        toggleMultiSelect(
                                            'available_days',
                                            option.value,
                                        )
                                    "
                                />
                                {{ option.label }}
                            </label>
                        </div>
                        <InputError :message="form.errors.available_days" />
                    </div>

                    <div class="grid gap-2 md:grid-cols-2">
                        <div class="grid gap-2">
                            <Label for="available_time">Available Time</Label>
                            <Input
                                id="available_time"
                                v-model="form.available_time"
                                type="text"
                                placeholder="e.g. 4 PM - 9 PM"
                            />
                            <InputError :message="form.errors.available_time" />
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
                    </div>
                </section>

                <div class="flex items-center gap-3">
                    <Button type="submit" :disabled="form.processing"
                        >Save Profile</Button
                    >
                    <Button type="button" variant="outline" @click="closeEditMode"
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
                            Payment buttons are available only when invoice is
                            unpaid.
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
    </TutorLayout>
</template>
