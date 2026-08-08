<script setup lang="ts">
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import {
    ArrowUp,
    ArrowDown,
    BookOpen,
    Calendar,
    CheckCircle2,
    ClipboardList,
    Clock,
    CreditCard,
    Download,
    ExternalLink,
    FileText,
    GraduationCap,
    Mail,
    MapPin,
    PenSquare,
    Phone,
    Plus,
    Receipt,
    Shield,
    ShieldAlert,
    ShieldCheck,
    Trash2,
    User as UserIcon,
    X,
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
const flashStatus = computed<string | null>(
    () => (page.props.flash as { status?: string } | undefined)?.status ?? null,
);
const downloadCvUrl = '/tutor/profile/download-cv';
const viewAsGuardianUrl = computed(() => {
    if (!authUser.value?.id) {
        return '/tutors';
    }
    return `/tutors/${authUser.value.id}`;
});

const breadcrumbs = [{ title: 'Tutor Profile', href: '/tutor/profile' }];

const tabs = [
    {
        key: 'personal',
        label: 'Personal Info',
        sublabel: 'Basic details & address',
        icon: UserIcon,
    },
    {
        key: 'education',
        label: 'Educational Info',
        sublabel: 'Degrees & institutions',
        icon: GraduationCap,
    },
    {
        key: 'preferences',
        label: 'Tuition Preferences',
        sublabel: 'Subjects, areas & salary',
        icon: ClipboardList,
    },
    {
        key: 'verification',
        label: 'Verification Status',
        sublabel: 'Badge & account status',
        icon: ShieldCheck,
    },
];

const activeTab = ref('personal');
const editingTab = ref<string | null>(null);
const isEditingActiveTab = computed(() => editingTab.value === activeTab.value);

const originalProfile = { ...props.profile };

function resetFormToOriginal(): void {
    form.name = originalProfile.name ?? '';
    form.phone = originalProfile.phone ?? '';
    form.gender = originalProfile.gender ?? 'none';
    form.date_of_birth = originalProfile.date_of_birth ?? '';
    form.present_address = originalProfile.present_address ?? '';
    form.permanent_address = originalProfile.permanent_address ?? '';
    form.nid_no = originalProfile.nid_no ?? '';
    form.bio = originalProfile.bio ?? '';
    form.preferred_tuition_types = Array.isArray(
        originalProfile.preferred_tuition_types,
    )
        ? [...originalProfile.preferred_tuition_types]
        : [];
    form.preferred_categories = Array.isArray(
        originalProfile.preferred_categories,
    )
        ? [...originalProfile.preferred_categories]
        : [];
    form.preferred_classes = Array.isArray(originalProfile.preferred_classes)
        ? [...originalProfile.preferred_classes]
        : [];
    form.preferred_subjects = Array.isArray(originalProfile.preferred_subjects)
        ? [...originalProfile.preferred_subjects]
        : [];
    form.preferred_locations = Array.isArray(
        originalProfile.preferred_locations,
    )
        ? [...originalProfile.preferred_locations]
        : [];
    form.expected_salary_min = originalProfile.expected_salary_min ?? '';
    form.expected_salary_max = originalProfile.expected_salary_max ?? '';
    form.available_days = Array.isArray(originalProfile.available_days)
        ? [...originalProfile.available_days]
        : [];
    form.available_time = originalProfile.available_time ?? '';
    form.status = originalProfile.status ?? 'active';
    form.educations = Array.isArray(originalProfile.educations)
        ? originalProfile.educations.map((education, index) => ({
              id: education.id ?? null,
              degree: education.degree ?? '',
              institute: education.institute ?? '',
              department: education.department ?? '',
              graduation_year: education.graduation_year ?? '',
              result: education.result ?? '',
              is_current: Boolean(education.is_current),
              sort_order: education.sort_order ?? index,
          }))
        : [];
}

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
    return props.schoolClasses.filter((schoolClass: any) =>
        selectedCategories.has(schoolClass.category_id),
    );
});

const filteredSubjects = computed(() => {
    const selectedClasses = new Set(form.preferred_classes);
    if (!selectedClasses.size) {
        return props.subjects;
    }
    return props.subjects.filter((subject: any) =>
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
        .filter((option: any) =>
            form.preferred_tuition_types.includes(option.id),
        )
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
        return form.educations.length > 0 ? 'Edit Education' : 'Add Education';
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

        return hasPreferences ? 'Edit Preferences' : 'Add Preferences';
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

function removeEducation(index: number) {
    form.educations.splice(index, 1);
}

function moveEducation(index: number, direction: number) {
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

const verificationInvoice = computed(() => props.verification?.invoice ?? null);
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
        <div
            class="grid gap-5 p-4 text-slate-900 sm:p-5 lg:p-6 xl:grid-cols-[300px_minmax(0,1fr)]"
        >
            <!-- Sidebar Profile Card -->
            <aside class="space-y-4">
                <Card class="overflow-hidden border-slate-200/80 shadow-2xs">
                    <CardContent class="space-y-4 p-5 text-center">
                        <div class="flex flex-col items-center">
                            <ProfilePhotoUpload />
                            <h2
                                class="mt-3 text-lg font-bold tracking-tight text-slate-900"
                            >
                                {{ form.name || authUser?.name || 'Tutor' }}
                            </h2>
                            <p class="text-xs font-medium text-slate-500">
                                Tutor ID: #{{ authUser?.id ?? '—' }}
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
                                    class="border-slate-300 px-2 py-0.5 text-[11px] capitalize"
                                >
                                    {{ form.status || 'Active' }}
                                </Badge>
                            </div>
                        </div>

                        <!-- Progress Meter -->
                        <div
                            class="w-full space-y-1.5 border-t border-slate-100 pt-2"
                        >
                            <div
                                class="flex items-center justify-between text-xs font-semibold"
                            >
                                <span class="text-slate-500"
                                    >Profile Strength</span
                                >
                                <span class="font-bold text-emerald-700"
                                    >{{ profileCompletion }}%</span
                                >
                            </div>
                            <div
                                class="h-2 w-full overflow-hidden rounded-full bg-slate-100"
                            >
                                <div
                                    class="h-full rounded-full bg-emerald-500 transition-all duration-300"
                                    :style="{ width: `${profileCompletion}%` }"
                                ></div>
                            </div>
                        </div>

                        <Separator />

                        <!-- Contact & Verification Details -->
                        <div class="space-y-2.5 text-left text-xs">
                            <div class="flex items-start gap-2.5">
                                <Mail
                                    class="mt-0.5 h-3.5 w-3.5 shrink-0 text-slate-400"
                                />
                                <div class="min-w-0">
                                    <span
                                        class="block text-[10px] font-semibold text-slate-400 uppercase"
                                        >Email</span
                                    >
                                    <span
                                        class="block truncate font-medium text-slate-900"
                                        >{{ authUser?.email || '—' }}</span
                                    >
                                </div>
                            </div>

                            <div class="flex items-start gap-2.5">
                                <Phone
                                    class="mt-0.5 h-3.5 w-3.5 shrink-0 text-slate-400"
                                />
                                <div>
                                    <span
                                        class="block text-[10px] font-semibold text-slate-400 uppercase"
                                        >Phone</span
                                    >
                                    <span class="font-medium text-slate-900">{{
                                        form.phone || '—'
                                    }}</span>
                                </div>
                            </div>

                            <div class="flex items-start gap-2.5">
                                <MapPin
                                    class="mt-0.5 h-3.5 w-3.5 shrink-0 text-slate-400"
                                />
                                <div>
                                    <span
                                        class="block text-[10px] font-semibold text-slate-400 uppercase"
                                        >Present Address</span
                                    >
                                    <span class="font-medium text-slate-900">{{
                                        form.present_address || '—'
                                    }}</span>
                                </div>
                            </div>
                        </div>

                        <Separator />

                        <!-- Action Links -->
                        <div class="space-y-2 pt-1">
                            <a
                                :href="downloadCvUrl"
                                class="inline-flex w-full items-center justify-center gap-1.5 rounded-lg bg-emerald-600 py-2 text-xs font-semibold text-white shadow-2xs transition-colors hover:bg-emerald-700"
                            >
                                <Download class="h-3.5 w-3.5" />
                                <span>Download PDF CV</span>
                            </a>

                            <a
                                :href="viewAsGuardianUrl"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="inline-flex w-full items-center justify-center gap-1.5 rounded-lg border border-slate-200 bg-slate-50 py-2 text-xs font-semibold text-slate-700 transition-colors hover:bg-slate-100"
                            >
                                <span>Preview Public Profile</span>
                                <ExternalLink class="h-3.5 w-3.5" />
                            </a>
                        </div>
                    </CardContent>
                </Card>
            </aside>

            <!-- Main Content Section -->
            <div class="space-y-4">
                <!-- Flash Status Alert -->
                <div
                    v-if="flashStatus"
                    class="flex items-center gap-2 rounded-lg border border-emerald-200 bg-emerald-50/90 px-3.5 py-2.5 text-xs font-medium text-emerald-900 shadow-2xs"
                >
                    <CheckCircle2 class="h-4 w-4 shrink-0 text-emerald-600" />
                    <div>{{ flashStatus }}</div>
                </div>

                <!-- Navigation Tabs Bar -->
                <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
                    <button
                        v-for="tab in tabs"
                        :key="tab.key"
                        type="button"
                        class="flex items-center gap-2.5 rounded-xl border p-3 text-left transition-all"
                        :class="[
                            activeTab === tab.key
                                ? 'border-emerald-600 bg-emerald-600 text-white shadow-2xs'
                                : 'border-slate-200/80 bg-white text-slate-700 hover:border-slate-300',
                        ]"
                        @click="switchTab(tab.key)"
                    >
                        <div
                            class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg"
                            :class="[
                                activeTab === tab.key
                                    ? 'bg-white/20 text-white'
                                    : 'bg-slate-100 text-slate-500',
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
                                        ? 'text-emerald-100'
                                        : 'text-slate-400',
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
                    class="border-slate-200/80 shadow-2xs"
                >
                    <CardHeader
                        class="flex flex-row items-center justify-between border-b border-slate-100 px-4 py-3"
                    >
                        <CardTitle
                            class="flex items-center gap-1.5 text-xs font-bold tracking-wider text-slate-700 uppercase"
                        >
                            <component
                                :is="
                                    tabs.find((t) => t.key === activeTab)?.icon
                                "
                                class="h-4 w-4 text-emerald-600"
                            />
                            <span>{{
                                tabs.find((t) => t.key === activeTab)?.label
                            }}</span>
                        </CardTitle>
                        <Button
                            type="button"
                            variant="outline"
                            size="sm"
                            class="h-8 gap-1 text-xs"
                            @click="openEditMode"
                        >
                            <PenSquare class="h-3.5 w-3.5" />
                            <span>{{ activeTabActionLabel }}</span>
                        </Button>
                    </CardHeader>

                    <CardContent class="p-4">
                        <!-- Personal Info Tab -->
                        <section
                            v-if="activeTab === 'personal'"
                            class="grid gap-3 text-xs sm:grid-cols-2 lg:grid-cols-3"
                        >
                            <div
                                class="space-y-0.5 rounded-lg border border-slate-100 bg-slate-50/50 p-2.5"
                            >
                                <span
                                    class="block text-[10px] font-semibold text-slate-400 uppercase"
                                    >Full Name</span
                                >
                                <span class="block font-bold text-slate-900">{{
                                    form.name || '—'
                                }}</span>
                            </div>

                            <div
                                class="space-y-0.5 rounded-lg border border-slate-100 bg-slate-50/50 p-2.5"
                            >
                                <span
                                    class="block text-[10px] font-semibold text-slate-400 uppercase"
                                    >Phone</span
                                >
                                <span class="block font-bold text-slate-900">{{
                                    form.phone || '—'
                                }}</span>
                            </div>

                            <div
                                class="space-y-0.5 rounded-lg border border-slate-100 bg-slate-50/50 p-2.5"
                            >
                                <span
                                    class="block text-[10px] font-semibold text-slate-400 uppercase"
                                    >Gender</span
                                >
                                <span
                                    class="block font-semibold text-slate-900"
                                    >{{ genderLabel }}</span
                                >
                            </div>

                            <div
                                class="space-y-0.5 rounded-lg border border-slate-100 bg-slate-50/50 p-2.5"
                            >
                                <span
                                    class="block text-[10px] font-semibold text-slate-400 uppercase"
                                    >Date of Birth</span
                                >
                                <span
                                    class="block font-semibold text-slate-900"
                                    >{{ form.date_of_birth || '—' }}</span
                                >
                            </div>

                            <div
                                class="space-y-0.5 rounded-lg border border-slate-100 bg-slate-50/50 p-2.5"
                            >
                                <span
                                    class="block text-[10px] font-semibold text-slate-400 uppercase"
                                    >NID Number</span
                                >
                                <span
                                    class="block font-mono font-semibold text-slate-900"
                                    >{{ form.nid_no || '—' }}</span
                                >
                            </div>

                            <div
                                class="space-y-0.5 rounded-lg border border-slate-100 bg-slate-50/50 p-2.5 sm:col-span-2 lg:col-span-3"
                            >
                                <span
                                    class="block text-[10px] font-semibold text-slate-400 uppercase"
                                    >Present Address</span
                                >
                                <span
                                    class="block font-medium text-slate-900"
                                    >{{ form.present_address || '—' }}</span
                                >
                            </div>

                            <div
                                class="space-y-0.5 rounded-lg border border-slate-100 bg-slate-50/50 p-2.5 sm:col-span-2 lg:col-span-3"
                            >
                                <span
                                    class="block text-[10px] font-semibold text-slate-400 uppercase"
                                    >Permanent Address</span
                                >
                                <span
                                    class="block font-medium text-slate-900"
                                    >{{ form.permanent_address || '—' }}</span
                                >
                            </div>

                            <div
                                class="space-y-0.5 rounded-lg border border-slate-100 bg-slate-50/50 p-2.5 sm:col-span-2 lg:col-span-3"
                            >
                                <span
                                    class="block text-[10px] font-semibold text-slate-400 uppercase"
                                    >Bio</span
                                >
                                <p
                                    class="leading-relaxed font-normal text-slate-800"
                                >
                                    {{ form.bio || 'No bio provided yet.' }}
                                </p>
                            </div>
                        </section>

                        <!-- Educational Info Tab -->
                        <section
                            v-if="activeTab === 'education'"
                            class="space-y-3 text-xs"
                        >
                            <div
                                v-if="!form.educations.length"
                                class="py-6 text-center text-slate-400 italic"
                            >
                                No education history added yet. Click "Add
                                Education" to add your academic degrees.
                            </div>

                            <div class="grid gap-3 sm:grid-cols-2">
                                <div
                                    v-for="(
                                        education, index
                                    ) in form.educations"
                                    :key="education.id ?? `preview-${index}`"
                                    class="space-y-2 rounded-xl border border-slate-200 bg-white p-3.5 shadow-2xs"
                                >
                                    <div
                                        class="flex items-start justify-between gap-2"
                                    >
                                        <div>
                                            <h5
                                                class="text-xs font-bold text-slate-900"
                                            >
                                                {{
                                                    education.degree || 'Degree'
                                                }}
                                            </h5>
                                            <p
                                                class="text-[11px] font-semibold text-emerald-700"
                                            >
                                                {{ education.institute }}
                                            </p>
                                        </div>
                                        <Badge
                                            v-if="education.result"
                                            variant="secondary"
                                            class="text-[10px] font-bold"
                                        >
                                            Result: {{ education.result }}
                                        </Badge>
                                    </div>
                                    <Separator />
                                    <div
                                        class="grid grid-cols-2 gap-2 text-[11px] text-slate-600"
                                    >
                                        <div>
                                            <span
                                                class="block text-[9px] font-semibold text-slate-400 uppercase"
                                                >Department</span
                                            >
                                            <span
                                                class="font-medium text-slate-800"
                                                >{{
                                                    education.department || '—'
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
                                                    education.graduation_year ||
                                                    '—'
                                                }}</span
                                            >
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <!-- Preferences Tab -->
                        <section
                            v-if="activeTab === 'preferences'"
                            class="space-y-3 text-xs"
                        >
                            <div class="grid gap-3 sm:grid-cols-2">
                                <div
                                    class="space-y-1 rounded-lg border border-slate-100 bg-slate-50/50 p-2.5 sm:col-span-2"
                                >
                                    <span
                                        class="block text-[10px] font-semibold text-slate-400 uppercase"
                                        >Preferred Tuition Types</span
                                    >
                                    <div
                                        v-if="preferredTuitionTypeNames.length"
                                        class="flex flex-wrap gap-1.5"
                                    >
                                        <Badge
                                            v-for="(
                                                name, idx
                                            ) in preferredTuitionTypeNames"
                                            :key="idx"
                                            variant="secondary"
                                            class="border bg-white text-slate-800"
                                        >
                                            {{ name }}
                                        </Badge>
                                    </div>
                                    <span v-else class="text-slate-400 italic"
                                        >None selected</span
                                    >
                                </div>

                                <div
                                    class="space-y-1 rounded-lg border border-slate-100 bg-slate-50/50 p-2.5 sm:col-span-2"
                                >
                                    <span
                                        class="block text-[10px] font-semibold text-slate-400 uppercase"
                                        >Preferred Categories</span
                                    >
                                    <div
                                        v-if="preferredCategoryNames.length"
                                        class="flex flex-wrap gap-1.5"
                                    >
                                        <Badge
                                            v-for="(
                                                name, idx
                                            ) in preferredCategoryNames"
                                            :key="idx"
                                            variant="secondary"
                                            class="border bg-white text-slate-800"
                                        >
                                            {{ name }}
                                        </Badge>
                                    </div>
                                    <span v-else class="text-slate-400 italic"
                                        >None selected</span
                                    >
                                </div>

                                <div
                                    class="space-y-1 rounded-lg border border-slate-100 bg-slate-50/50 p-2.5 sm:col-span-2"
                                >
                                    <span
                                        class="block text-[10px] font-semibold text-slate-400 uppercase"
                                        >Preferred Classes</span
                                    >
                                    <div
                                        v-if="preferredClassNames.length"
                                        class="flex flex-wrap gap-1.5"
                                    >
                                        <Badge
                                            v-for="(
                                                name, idx
                                            ) in preferredClassNames"
                                            :key="idx"
                                            variant="secondary"
                                            class="border bg-white text-slate-800"
                                        >
                                            {{ name }}
                                        </Badge>
                                    </div>
                                    <span v-else class="text-slate-400 italic"
                                        >None selected</span
                                    >
                                </div>

                                <div
                                    class="space-y-1 rounded-lg border border-slate-100 bg-slate-50/50 p-2.5 sm:col-span-2"
                                >
                                    <span
                                        class="block text-[10px] font-semibold text-slate-400 uppercase"
                                        >Preferred Subjects</span
                                    >
                                    <div
                                        v-if="preferredSubjectNames.length"
                                        class="flex flex-wrap gap-1.5"
                                    >
                                        <Badge
                                            v-for="(
                                                name, idx
                                            ) in preferredSubjectNames"
                                            :key="idx"
                                            variant="secondary"
                                            class="border bg-white text-slate-800"
                                        >
                                            {{ name }}
                                        </Badge>
                                    </div>
                                    <span v-else class="text-slate-400 italic"
                                        >None selected</span
                                    >
                                </div>

                                <div
                                    class="space-y-1 rounded-lg border border-slate-100 bg-slate-50/50 p-2.5 sm:col-span-2"
                                >
                                    <span
                                        class="block text-[10px] font-semibold text-slate-400 uppercase"
                                        >Preferred Locations</span
                                    >
                                    <div
                                        v-if="preferredLocationNames.length"
                                        class="flex flex-wrap gap-1.5"
                                    >
                                        <Badge
                                            v-for="(
                                                name, idx
                                            ) in preferredLocationNames"
                                            :key="idx"
                                            variant="secondary"
                                            class="border bg-white text-slate-800"
                                        >
                                            {{ name }}
                                        </Badge>
                                    </div>
                                    <span v-else class="text-slate-400 italic"
                                        >None selected</span
                                    >
                                </div>

                                <div
                                    class="space-y-0.5 rounded-lg border border-slate-100 bg-slate-50/50 p-2.5"
                                >
                                    <span
                                        class="block text-[10px] font-semibold text-slate-400 uppercase"
                                        >Expected Salary Range</span
                                    >
                                    <span class="font-bold text-slate-900">
                                        {{ form.expected_salary_min || '0' }} -
                                        {{
                                            form.expected_salary_max || '0'
                                        }}
                                        BDT
                                    </span>
                                </div>

                                <div
                                    class="space-y-0.5 rounded-lg border border-slate-100 bg-slate-50/50 p-2.5"
                                >
                                    <span
                                        class="block text-[10px] font-semibold text-slate-400 uppercase"
                                        >Available Days & Time</span
                                    >
                                    <span
                                        class="block font-semibold text-slate-900"
                                    >
                                        {{
                                            availableDayLabels.join(', ') ||
                                            'Any day'
                                        }}
                                        ({{
                                            form.available_time || 'Flexible'
                                        }})
                                    </span>
                                </div>
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
                        class="border-slate-200/80 shadow-2xs"
                    >
                        <CardHeader class="border-b border-slate-100 px-4 py-3">
                            <CardTitle
                                class="text-xs font-bold tracking-wider text-slate-700 uppercase"
                                >Edit Personal Details</CardTitle
                            >
                        </CardHeader>
                        <CardContent class="space-y-3 p-4 text-xs">
                            <div class="grid gap-3 sm:grid-cols-2">
                                <div class="grid gap-1">
                                    <Label
                                        for="name"
                                        class="text-xs font-semibold"
                                        >Full Name</Label
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
                                        for="phone"
                                        class="text-xs font-semibold"
                                        >Phone Number</Label
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
                                    <Label class="text-xs font-semibold"
                                        >Gender</Label
                                    >
                                    <Select v-model="form.gender">
                                        <SelectTrigger class="h-8 text-xs">
                                            <SelectValue
                                                placeholder="Select gender"
                                            />
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

                                <div class="grid gap-1">
                                    <Label
                                        for="date_of_birth"
                                        class="text-xs font-semibold"
                                        >Date of Birth</Label
                                    >
                                    <Input
                                        id="date_of_birth"
                                        v-model="form.date_of_birth"
                                        type="date"
                                        class="h-8 text-xs"
                                    />
                                    <InputError
                                        :message="form.errors.date_of_birth"
                                    />
                                </div>

                                <div class="grid gap-1 sm:col-span-2">
                                    <Label
                                        for="nid_no"
                                        class="text-xs font-semibold"
                                        >NID Number</Label
                                    >
                                    <Input
                                        id="nid_no"
                                        v-model="form.nid_no"
                                        type="text"
                                        class="h-8 text-xs"
                                    />
                                    <InputError :message="form.errors.nid_no" />
                                </div>

                                <div class="grid gap-1 sm:col-span-2">
                                    <Label
                                        for="present_address"
                                        class="text-xs font-semibold"
                                        >Present Address</Label
                                    >
                                    <Textarea
                                        id="present_address"
                                        v-model="form.present_address"
                                        rows="2"
                                        class="text-xs"
                                    />
                                    <InputError
                                        :message="form.errors.present_address"
                                    />
                                </div>

                                <div class="grid gap-1 sm:col-span-2">
                                    <Label
                                        for="permanent_address"
                                        class="text-xs font-semibold"
                                        >Permanent Address</Label
                                    >
                                    <Textarea
                                        id="permanent_address"
                                        v-model="form.permanent_address"
                                        rows="2"
                                        class="text-xs"
                                    />
                                    <InputError
                                        :message="form.errors.permanent_address"
                                    />
                                </div>

                                <div class="grid gap-1 sm:col-span-2">
                                    <Label
                                        for="bio"
                                        class="text-xs font-semibold"
                                        >Bio / Summary</Label
                                    >
                                    <Textarea
                                        id="bio"
                                        v-model="form.bio"
                                        rows="3"
                                        placeholder="Brief summary about yourself..."
                                        class="text-xs"
                                    />
                                    <InputError :message="form.errors.bio" />
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Education Form -->
                    <Card
                        v-if="activeTab === 'education'"
                        class="border-slate-200/80 shadow-2xs"
                    >
                        <CardHeader
                            class="flex flex-row items-center justify-between border-b border-slate-100 px-4 py-3"
                        >
                            <CardTitle
                                class="text-xs font-bold tracking-wider text-slate-700 uppercase"
                                >Educational Background</CardTitle
                            >
                            <Button
                                type="button"
                                variant="outline"
                                size="sm"
                                class="h-7 gap-1 text-xs"
                                @click="addEducation"
                            >
                                <Plus class="h-3.5 w-3.5" /> Add Degree
                            </Button>
                        </CardHeader>

                        <CardContent class="space-y-3 p-4 text-xs">
                            <div
                                v-if="!form.educations.length"
                                class="py-6 text-center text-slate-400 italic"
                            >
                                No education added yet. Click "Add Degree" to
                                add an entry.
                            </div>

                            <div
                                v-for="(education, index) in form.educations"
                                :key="education.id ?? `new-${index}`"
                                class="space-y-2 rounded-lg border border-slate-200/90 bg-slate-50/40 p-3"
                            >
                                <div class="flex items-center justify-between">
                                    <span
                                        class="text-xs font-bold text-slate-800"
                                        >Degree #{{ index + 1 }}</span
                                    >
                                    <div class="flex items-center gap-1">
                                        <Button
                                            type="button"
                                            variant="ghost"
                                            size="sm"
                                            class="h-7 w-7 p-0"
                                            :disabled="index === 0"
                                            @click="moveEducation(index, -1)"
                                        >
                                            <ArrowUp class="h-3.5 w-3.5" />
                                        </Button>
                                        <Button
                                            type="button"
                                            variant="ghost"
                                            size="sm"
                                            class="h-7 w-7 p-0"
                                            :disabled="
                                                index ===
                                                form.educations.length - 1
                                            "
                                            @click="moveEducation(index, 1)"
                                        >
                                            <ArrowDown class="h-3.5 w-3.5" />
                                        </Button>
                                        <Button
                                            type="button"
                                            variant="ghost"
                                            size="sm"
                                            class="h-7 w-7 p-0 text-rose-600 hover:text-rose-700"
                                            @click="removeEducation(index)"
                                        >
                                            <Trash2 class="h-3.5 w-3.5" />
                                        </Button>
                                    </div>
                                </div>

                                <div class="grid gap-2 sm:grid-cols-2">
                                    <div class="grid gap-1">
                                        <Label class="text-[11px]"
                                            >Degree</Label
                                        >
                                        <Input
                                            v-model="education.degree"
                                            type="text"
                                            placeholder="e.g. BSc in Computer Science"
                                            class="h-8 text-xs"
                                        />
                                    </div>

                                    <div class="grid gap-1">
                                        <Label class="text-[11px]"
                                            >Institute</Label
                                        >
                                        <Input
                                            v-model="education.institute"
                                            type="text"
                                            placeholder="e.g. University Name"
                                            class="h-8 text-xs"
                                        />
                                    </div>

                                    <div class="grid gap-1">
                                        <Label class="text-[11px]"
                                            >Department</Label
                                        >
                                        <Input
                                            v-model="education.department"
                                            type="text"
                                            placeholder="e.g. CSE"
                                            class="h-8 text-xs"
                                        />
                                    </div>

                                    <div class="grid gap-1">
                                        <Label class="text-[11px]"
                                            >Graduation Year</Label
                                        >
                                        <Input
                                            v-model="education.graduation_year"
                                            type="number"
                                            min="1900"
                                            max="2100"
                                            class="h-8 text-xs"
                                        />
                                    </div>

                                    <div class="grid gap-1 sm:col-span-2">
                                        <Label class="text-[11px]"
                                            >Result / CGPA</Label
                                        >
                                        <Input
                                            v-model="education.result"
                                            type="text"
                                            placeholder="e.g. 3.80 out of 4.00"
                                            class="h-8 text-xs"
                                        />
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Tuition Preferences Form -->
                    <Card
                        v-if="activeTab === 'preferences'"
                        class="border-slate-200/80 shadow-2xs"
                    >
                        <CardHeader class="border-b border-slate-100 px-4 py-3">
                            <CardTitle
                                class="text-xs font-bold tracking-wider text-slate-700 uppercase"
                                >Tuition Preferences</CardTitle
                            >
                        </CardHeader>
                        <CardContent class="space-y-4 p-4 text-xs">
                            <!-- Preferred Tuition Types -->
                            <div class="space-y-1.5">
                                <Label class="text-xs font-semibold"
                                    >Preferred Tuition Types</Label
                                >
                                <div class="flex flex-wrap gap-1.5">
                                    <button
                                        v-for="typeItem in tuitionTypes as any[]"
                                        :key="typeItem.id"
                                        type="button"
                                        class="rounded-md border px-2.5 py-1 text-xs font-medium transition-all"
                                        :class="[
                                            hasSelected(
                                                'preferred_tuition_types',
                                                typeItem.id,
                                            )
                                                ? 'border-emerald-600 bg-emerald-50 font-bold text-emerald-900'
                                                : 'border-slate-200 bg-white text-slate-600 hover:border-slate-300',
                                        ]"
                                        @click="
                                            toggleMultiSelect(
                                                'preferred_tuition_types',
                                                typeItem.id,
                                            )
                                        "
                                    >
                                        {{ typeItem.name }}
                                    </button>
                                </div>
                            </div>

                            <!-- Preferred Categories -->
                            <div class="space-y-1.5">
                                <Label class="text-xs font-semibold"
                                    >Preferred Categories</Label
                                >
                                <div class="flex flex-wrap gap-1.5">
                                    <button
                                        v-for="cat in categories as any[]"
                                        :key="cat.id"
                                        type="button"
                                        class="rounded-md border px-2.5 py-1 text-xs font-medium transition-all"
                                        :class="[
                                            hasSelected(
                                                'preferred_categories',
                                                cat.id,
                                            )
                                                ? 'border-emerald-600 bg-emerald-50 font-bold text-emerald-900'
                                                : 'border-slate-200 bg-white text-slate-600 hover:border-slate-300',
                                        ]"
                                        @click="
                                            toggleMultiSelect(
                                                'preferred_categories',
                                                cat.id,
                                            )
                                        "
                                    >
                                        {{ cat.name }}
                                    </button>
                                </div>
                            </div>

                            <!-- Preferred Classes -->
                            <div class="space-y-1.5">
                                <Label class="text-xs font-semibold"
                                    >Preferred Classes</Label
                                >
                                <div class="flex flex-wrap gap-1.5">
                                    <button
                                        v-for="cls in filteredClasses as any[]"
                                        :key="cls.id"
                                        type="button"
                                        class="rounded-md border px-2.5 py-1 text-xs font-medium transition-all"
                                        :class="[
                                            hasSelected(
                                                'preferred_classes',
                                                cls.id,
                                            )
                                                ? 'border-emerald-600 bg-emerald-50 font-bold text-emerald-900'
                                                : 'border-slate-200 bg-white text-slate-600 hover:border-slate-300',
                                        ]"
                                        @click="
                                            toggleMultiSelect(
                                                'preferred_classes',
                                                cls.id,
                                            )
                                        "
                                    >
                                        {{ cls.name }}
                                    </button>
                                </div>
                            </div>

                            <!-- Preferred Subjects -->
                            <div class="space-y-1.5">
                                <Label class="text-xs font-semibold"
                                    >Preferred Subjects</Label
                                >
                                <div class="flex flex-wrap gap-1.5">
                                    <button
                                        v-for="subj in filteredSubjects as any[]"
                                        :key="subj.id"
                                        type="button"
                                        class="rounded-md border px-2 py-0.5 text-xs font-medium transition-all"
                                        :class="[
                                            hasSelected(
                                                'preferred_subjects',
                                                subj.id,
                                            )
                                                ? 'border-emerald-600 bg-emerald-50 font-bold text-emerald-900'
                                                : 'border-slate-200 bg-white text-slate-600 hover:border-slate-300',
                                        ]"
                                        @click="
                                            toggleMultiSelect(
                                                'preferred_subjects',
                                                subj.id,
                                            )
                                        "
                                    >
                                        {{ subj.name }}
                                    </button>
                                </div>
                            </div>

                            <!-- Preferred Locations -->
                            <div class="space-y-1.5">
                                <Label class="text-xs font-semibold"
                                    >Preferred Locations</Label
                                >
                                <div
                                    class="flex max-h-44 flex-wrap gap-1.5 overflow-y-auto rounded-md border p-1"
                                >
                                    <button
                                        v-for="loc in locations as any[]"
                                        :key="loc.id"
                                        type="button"
                                        class="rounded-md border px-2 py-0.5 text-xs font-medium transition-all"
                                        :class="[
                                            hasSelected(
                                                'preferred_locations',
                                                loc.id,
                                            )
                                                ? 'border-emerald-600 bg-emerald-50 font-bold text-emerald-900'
                                                : 'border-slate-200 bg-white text-slate-600 hover:border-slate-300',
                                        ]"
                                        @click="
                                            toggleMultiSelect(
                                                'preferred_locations',
                                                loc.id,
                                            )
                                        "
                                    >
                                        {{ loc.name }}
                                    </button>
                                </div>
                            </div>

                            <div class="grid gap-3 sm:grid-cols-2">
                                <div class="grid gap-1">
                                    <Label
                                        for="expected_salary_min"
                                        class="text-xs font-semibold"
                                        >Min Expected Salary (BDT)</Label
                                    >
                                    <Input
                                        id="expected_salary_min"
                                        v-model="form.expected_salary_min"
                                        type="number"
                                        min="0"
                                        class="h-8 text-xs"
                                    />
                                </div>

                                <div class="grid gap-1">
                                    <Label
                                        for="expected_salary_max"
                                        class="text-xs font-semibold"
                                        >Max Expected Salary (BDT)</Label
                                    >
                                    <Input
                                        id="expected_salary_max"
                                        v-model="form.expected_salary_max"
                                        type="number"
                                        min="0"
                                        class="h-8 text-xs"
                                    />
                                </div>

                                <div class="grid gap-1 sm:col-span-2">
                                    <Label
                                        for="available_time"
                                        class="text-xs font-semibold"
                                        >Available Time / Schedule</Label
                                    >
                                    <Input
                                        id="available_time"
                                        v-model="form.available_time"
                                        type="text"
                                        placeholder="e.g. Afternoon / 4 PM - 8 PM"
                                        class="h-8 text-xs"
                                    />
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Submit & Cancel Buttons Bar -->
                    <div class="flex items-center justify-end gap-2 pt-2">
                        <Button
                            type="button"
                            variant="outline"
                            size="sm"
                            class="text-xs"
                            @click="closeEditMode"
                        >
                            Cancel
                        </Button>
                        <Button
                            type="submit"
                            size="sm"
                            class="bg-emerald-600 text-xs font-semibold text-white hover:bg-emerald-700"
                            :disabled="form.processing"
                        >
                            Save Profile Changes
                        </Button>
                    </div>
                </form>

                <!-- Verification Status Tab -->
                <Card
                    v-if="activeTab === 'verification'"
                    class="border-slate-200/80 shadow-2xs"
                >
                    <CardHeader class="border-b border-slate-100 px-4 py-3">
                        <CardTitle
                            class="flex items-center gap-1.5 text-xs font-bold tracking-wider text-slate-700 uppercase"
                        >
                            <ShieldCheck class="h-4 w-4 text-emerald-600" />
                            <span>Tutor Account Verification</span>
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4 p-4 text-xs">
                        <div
                            class="flex flex-col justify-between gap-3 rounded-xl border border-slate-100 bg-slate-50/60 p-4 sm:flex-row sm:items-center"
                        >
                            <div class="space-y-1">
                                <h4 class="text-sm font-bold text-slate-900">
                                    Account Status: {{ statusLabel }}
                                </h4>
                                <p class="text-xs text-slate-500">
                                    Verified tutors receive priority job
                                    matching and a trusted verification badge.
                                </p>
                            </div>
                            <Button
                                v-if="canRequestVerification"
                                type="button"
                                size="sm"
                                class="shrink-0 bg-emerald-600 text-xs text-white hover:bg-emerald-700"
                                @click="requestDialogOpen = true"
                            >
                                Request Profile Verification
                            </Button>
                        </div>

                        <!-- Verification Fee Invoice Payment Card -->
                        <div
                            v-if="canPayInvoice && verificationInvoice"
                            class="space-y-3 rounded-xl border border-blue-200 bg-blue-50/50 p-4"
                        >
                            <div class="flex items-center justify-between">
                                <h4
                                    class="flex items-center gap-1.5 text-sm font-bold text-blue-900"
                                >
                                    <Receipt class="h-4 w-4 text-blue-600" />
                                    <span>Verification Invoice Issued</span>
                                </h4>
                                <Badge
                                    variant="outline"
                                    class="border-blue-300 bg-white text-blue-800"
                                >
                                    {{ verificationInvoice.amount }}
                                    {{ verificationInvoice.currency }}
                                </Badge>
                            </div>
                            <p class="text-xs text-blue-950">
                                Invoice #{{
                                    verificationInvoice.invoice_no
                                }}
                                has been issued. Pay now to complete your
                                verification process.
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
            <DialogContent class="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle
                        class="flex items-center gap-1.5 text-base text-emerald-800"
                    >
                        <ShieldCheck class="h-4 w-4 text-emerald-600" />
                        <span>Request Profile Verification</span>
                    </DialogTitle>
                    <DialogDescription class="text-xs">
                        Submitting a verification request will submit your
                        profile data to admins for review.
                    </DialogDescription>
                </DialogHeader>

                <p class="py-2 text-xs text-slate-600">
                    Once submitted, our admin team will review your credentials
                    and issue a verification fee invoice if approved.
                </p>

                <DialogFooter class="gap-2 sm:gap-0">
                    <Button
                        type="button"
                        variant="outline"
                        size="sm"
                        class="text-xs"
                        @click="requestDialogOpen = false"
                        >Cancel</Button
                    >
                    <Button
                        type="button"
                        size="sm"
                        class="bg-emerald-600 text-xs text-white hover:bg-emerald-700"
                        @click="requestVerification"
                    >
                        Submit Verification Request
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </TutorLayout>
</template>
