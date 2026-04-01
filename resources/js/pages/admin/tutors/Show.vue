<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import {
    ArrowLeft,
    CheckCircle2,
    Calendar,
    GraduationCap,
    LayoutGrid,
    Mail,
    MapPin,
    Phone,
    Shield,
    ShieldAlert,
    ShieldCheck,
    Star,
    User as UserIcon,
    Camera,
    Plus,
    X,
    Info,
    ChevronRight,
    Search,
    MessageSquareText,
    Clock,
    AlertCircle,
    RotateCcw
} from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import ConfirmDialog from '@/components/admin/dialogs/ConfirmDialog.vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import InputError from '@/components/InputError.vue';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { cn } from '@/lib/utils';
import { update } from '@/actions/App/Http/Controllers/Admin/TutorManagementController';
import { index as verificationIndex } from '@/actions/App/Http/Controllers/Admin/VerificationRequestController';

interface Education {
    id: number | null;
    degree: string;
    institute: string;
    department: string;
    graduation_year: number;
    result: string;
    is_current: boolean;
    sort_order?: number;
}

const props = defineProps<{
    tutor: any;
    profile: any;
    tuitionTypes: any[];
    categories: any[];
    schoolClasses: any[];
    subjects: any[];
    locations: any[];
    dayOptions: any[];
    genderOptions: any[];
    verification: any;
    verificationStatus: string | null;
    verifiedAt: string | null;
}>();

const breadcrumbs = [
    { title: 'Tutors', href: '/admin/tutors' },
    { title: props.tutor.name, href: '#' },
];

const activeTab = ref('personal');
const isEditMode = ref(false);
const confirmOpen = ref(false);

const form = useForm({
    name: props.profile.name,
    email: props.profile.email,
    phone: props.profile.phone,
    gender: props.profile.gender || 'none',
    date_of_birth: props.profile.date_of_birth,
    present_address: props.profile.present_address,
    permanent_address: props.profile.permanent_address,
    nid_no: props.profile.nid_no,
    bio: props.profile.bio,
    preferred_tuition_types: [...props.profile.preferred_tuition_types],
    preferred_categories: [...props.profile.preferred_categories],
    preferred_classes: [...props.profile.preferred_classes],
    preferred_subjects: [...props.profile.preferred_subjects],
    preferred_locations: [...props.profile.preferred_locations],
    expected_salary_min: props.profile.expected_salary_min,
    expected_salary_max: props.profile.expected_salary_max,
    available_days: [...props.profile.available_days],
    available_time: props.profile.available_time,
    status: props.tutor.status,
    profile_status: props.profile.profile_status,
    educations: props.profile.educations.map((edu: any) => ({ ...edu })),
});

const initials = computed(() => {
    return props.tutor.name
        .split(' ')
        .map((n: string) => n[0])
        .join('')
        .toUpperCase()
        .slice(0, 2);
});

const statusColor = computed(() => {
    return props.tutor.status === 'active'
        ? 'bg-emerald-50 text-emerald-700 border-emerald-200'
        : 'bg-red-50 text-red-700 border-red-200';
});

const verificationStatusColor = computed(() => {
    switch (props.tutor.verification_status) {
        case 'verified':
            return 'bg-blue-50 text-blue-700 border-blue-200';
        case 'pending':
            return 'bg-amber-50 text-amber-700 border-amber-200';
        default:
            return 'bg-slate-50 text-slate-700 border-slate-200';
    }
});

const profileCompletion = computed(() => {
    const fields = [
        'name', 'phone', 'gender', 'date_of_birth', 'present_address',
        'nid_no', 'bio', 'expected_salary_min', 'available_time'
    ];
    const filled = fields.filter(f => !!props.profile[f]).length;
    const hasEducation = props.profile.educations.length > 0 ? 1 : 0;
    const totalFields = fields.length + 1;
    return Math.round(((filled + hasEducation) / totalFields) * 100);
});

function toggleStatus() {
    const nextStatus = props.tutor.status === 'active' ? 'suspended' : 'active';
    form.status = nextStatus;
    submit();
    confirmOpen.value = false;
}

const formatDate = (date: any) => {
    if (!date) return 'Not provided';
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
};

const formatCurrency = (amount: any) => {
    if (amount === undefined || amount === null) return '—';
    const val = typeof amount === 'string' ? parseFloat(amount) : amount;
    return new Intl.NumberFormat('en-BD', {
        style: 'currency',
        currency: 'BDT',
        minimumFractionDigits: 0,
    }).format(val);
};

const submit = () => {
    form.put(update.url(props.tutor.id), {
        preserveScroll: true,
        onSuccess: () => {
            isEditMode.value = false;
        },
    });
};

// Education helpers
const addEducation = () => {
    form.educations.push({
        id: null,
        degree: '',
        institute: '',
        department: '',
        graduation_year: new Date().getFullYear(),
        result: '',
        is_current: false,
    });
};

const removeEducation = (index: number) => {
    form.educations.splice(index, 1);
};

const moveEducation = (idx: number | string, direction: number) => {
    const index = typeof idx === 'string' ? parseInt(idx) : idx;
    const newIndex = index + direction;
    if (newIndex >= 0 && newIndex < form.educations.length) {
        const item = form.educations.splice(index, 1)[0];
        form.educations.splice(newIndex, 0, item);
    }
};

// Selection helpers
const toggleMultiSelect = (field: string, value: any) => {
    const index = (form as any)[field].indexOf(value);
    if (index === -1) {
        (form as any)[field].push(value);
    } else {
        (form as any)[field].splice(index, 1);
    }
};

const hasSelected = (field: string, value: any) => {
    return (form as any)[field].includes(value);
};

// Filtered lookups
const filteredClasses = computed(() => {
    if (form.preferred_categories.length === 0) return [];
    return props.schoolClasses.filter(c => form.preferred_categories.includes(c.category_id));
});

const filteredSubjects = computed(() => {
    if (form.preferred_classes.length === 0) return [];
    return props.subjects.filter(s => form.preferred_classes.includes(s.class_id));
});

// Watch for category changes to clear incompatible classes
watch(() => form.preferred_categories, (newVal) => {
    form.preferred_classes = form.preferred_classes.filter(classId => 
        props.schoolClasses.some(c => c.id === classId && newVal.includes(c.category_id))
    );
}, { deep: true });

// Watch for class changes to clear incompatible subjects
watch(() => form.preferred_classes, (newVal) => {
    form.preferred_subjects = form.preferred_subjects.filter(subjectId => 
        props.subjects.some(s => s.id === subjectId && newVal.includes(s.class_id))
    );
}, { deep: true });

const tabs = [
    { id: 'personal', label: 'Personal', icon: UserIcon },
    { id: 'education', label: 'Educational', icon: GraduationCap },
    { id: 'preferences', label: 'Preferences', icon: LayoutGrid },
    { id: 'reviews', label: 'Reviews', icon: MessageSquareText },
    { id: 'verification', label: 'Verification', icon: ShieldCheck },
];

const normalizedStatus = computed(() => props.verificationStatus?.toLowerCase());
const verification = computed(() => props.verification);
const verificationInvoice = computed(() => verification.value?.invoice);

const statusLabel = computed(() => {
    switch (normalizedStatus.value) {
        case 'verified': return 'Verified Account';
        case 'pending': return 'Verification Pending';
        case 'rejected': return 'Verification Rejected';
        default: return 'Not Verified';
    }
});

const statusVariant = computed(() => {
    switch (normalizedStatus.value) {
        case 'verified': return 'default';
        case 'pending': return 'secondary';
        case 'rejected': return 'destructive';
        default: return 'outline';
    }
});

const reviews = computed(() => props.tutor.tutor_reviews || []);
const averageRating = computed(() => {
    if (reviews.value.length === 0) return 0;
    const total = reviews.value.reduce((acc: number, r: any) => acc + (r.rating || 0), 0);
    return (total / reviews.value.length).toFixed(1);
});

</script>

<template>
    <Head :title="`${tutor.name} | Tutor Profile`" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto max-w-7xl space-y-6 p-4 sm:p-6 lg:p-8">
            <div class="grid grid-cols-1 gap-6 xl:grid-cols-[320px_minmax(0,1fr)]">
                <!-- Sidebar -->
                <aside class="space-y-6">
                    <!-- Profile Summary Card -->
                    <Card class="overflow-hidden rounded-2xl border-slate-200/60 shadow-sm transition-all hover:shadow-md">
                        <div class="h-24 bg-gradient-to-br from-indigo-500 to-purple-600"></div>
                        <CardContent class="relative pt-0">
                            <div class="flex flex-col items-center -mt-12 text-center">
                                <Avatar class="h-24 w-24 border-4 border-white shadow-lg ring-1 ring-slate-100">
                                    <AvatarImage v-if="tutor.photo_url" :src="tutor.photo_url" :alt="tutor.name" />
                                    <AvatarFallback class="bg-slate-100 text-2xl font-bold text-slate-400 uppercase">
                                        {{ initials }}
                                    </AvatarFallback>
                                </Avatar>

                                <div class="mt-4 space-y-1">
                                    <h2 class="text-xl font-bold tracking-tight text-slate-900">{{ tutor.name }}</h2>
                                    <p class="text-sm font-medium text-slate-500">Tutor ID: #{{ tutor.id }}</p>
                                    <div class="flex items-center justify-center gap-2 pt-1 font-bold">
                                         <Badge variant="outline" :class="cn('rounded-full px-2.5 py-0.5 text-[10px] uppercase tracking-wider', statusColor)">
                                            {{ tutor.status }}
                                        </Badge>
                                        <Badge v-if="tutor.verified_at" class="rounded-full bg-blue-50 text-blue-700 border-blue-200 text-[10px] uppercase tracking-wider">
                                            Verified
                                        </Badge>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-8 space-y-6">
                                <div class="space-y-2">
                                    <div class="flex items-center justify-between text-xs">
                                        <span class="font-bold text-slate-400 uppercase tracking-wider">Profile Strength</span>
                                        <span class="font-bold text-indigo-600">{{ profileCompletion }}%</span>
                                    </div>
                                    <div class="h-2 w-full rounded-full bg-slate-100 overflow-hidden">
                                        <div 
                                            class="h-full bg-gradient-to-r from-indigo-500 to-purple-500 transition-all duration-1000 ease-out" 
                                            :style="{ width: `${profileCompletion}%` }"
                                        ></div>
                                    </div>
                                </div>

                                <div class="space-y-4">
                                    <div class="flex items-start gap-3">
                                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-slate-50 text-slate-400">
                                            <Mail class="h-4 w-4" />
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Email</p>
                                            <p class="truncate text-sm font-semibold text-slate-700">{{ tutor.email || '—' }}</p>
                                        </div>
                                    </div>
                                    <div class="flex items-start gap-3">
                                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-slate-50 text-slate-400">
                                            <Phone class="h-4 w-4" />
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Phone</p>
                                            <p class="text-sm font-semibold text-slate-700">{{ tutor.phone || '—' }}</p>
                                        </div>
                                    </div>
                                    <div class="flex items-start gap-3">
                                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-slate-50 text-slate-400">
                                            <Star class="h-4 w-4 text-amber-400" />
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Average Rating</p>
                                            <p class="text-sm font-semibold text-slate-700">{{ averageRating }} ({{ reviews.length }} reviews)</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-2 pt-2">
                                     <Button variant="outline" size="sm" as-child class="rounded-xl font-bold">
                                        <Link href="/admin/tutors">
                                            <ArrowLeft class="mr-2 h-3.5 w-3.5" />
                                            Back
                                        </Link>
                                    </Button>
                                    <Button 
                                        :variant="tutor.status === 'active' ? 'destructive' : 'default'" 
                                        size="sm" 
                                        class="rounded-xl font-bold"
                                        @click="confirmOpen = true"
                                    >
                                        <ShieldAlert v-if="tutor.status === 'active'" class="mr-2 h-3.5 w-3.5" />
                                        <ShieldCheck v-else class="mr-2 h-3.5 w-3.5" />
                                        {{ tutor.status === 'active' ? 'Suspend' : 'Activate' }}
                                    </Button>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Profile Menu -->
                    <Card class="overflow-hidden rounded-2xl border-slate-200/60 shadow-sm">
                        <nav class="p-2">
                            <button
                                v-for="tab in tabs"
                                :key="tab.id"
                                @click="activeTab = tab.id; isEditMode = false"
                                :class="cn(
                                    'flex w-full items-center gap-3 px-4 py-3 rounded-xl text-sm font-bold transition-all duration-200',
                                    activeTab === tab.id 
                                        ? 'bg-indigo-50 text-indigo-700 shadow-sm ring-1 ring-indigo-100' 
                                        : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900'
                                )"
                            >
                                <component :is="tab.icon" class="h-4 w-4" />
                                {{ tab.label }}
                                <ChevronRight v-if="activeTab === tab.id" class="ml-auto h-3.5 w-3.5 opacity-50" />
                            </button>
                        </nav>
                    </Card>
                </aside>

                <!-- Main Content -->
                <main class="space-y-6">
                    <!-- Tab Header -->
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <div class="space-y-1">
                            <h1 class="text-2xl font-bold tracking-tight text-slate-900">
                                {{ tabs.find(t => t.id === activeTab)?.label }} Information
                            </h1>
                            <p class="text-sm font-medium text-slate-500">
                                Manage and update tutor's {{ activeTab }} profile details.
                            </p>
                        </div>

                        <div v-if="activeTab !== 'verification' && activeTab !== 'reviews'" class="flex items-center gap-3">
                            <Button
                                v-if="!isEditMode"
                                variant="outline"
                                class="rounded-xl border-slate-200 transition-all hover:bg-white hover:shadow-md"
                                @click="isEditMode = true"
                            >
                                <UserIcon class="mr-2 h-4 w-4 text-indigo-500" />
                                Edit {{ tabs.find(t => t.id === activeTab)?.label }}
                            </Button>
                            <template v-else>
                                <Button 
                                    variant="outline" 
                                    class="rounded-xl"
                                    @click="isEditMode = false; form.reset()"
                                >
                                    Cancel
                                </Button>
                                <Button 
                                    class="rounded-xl shadow-lg shadow-indigo-100"
                                    :disabled="form.processing"
                                    @click="submit"
                                >
                                    <ShieldCheck v-if="!form.processing" class="mr-2 h-4 w-4" />
                                    <RotateCcw v-else class="mr-2 h-4 w-4 animate-spin" />
                                    Save Changes
                                </Button>
                            </template>
                        </div>
                    </div>

                    <!-- Personal Section -->
                    <div v-if="activeTab === 'personal'" class="space-y-6">
                        <Card v-if="!isEditMode" class="overflow-hidden rounded-2xl border-slate-200/60 shadow-sm">
                            <CardHeader class="border-b border-slate-50 bg-slate-50/30 px-6 py-4">
                                <CardTitle class="text-sm font-bold uppercase tracking-wider text-slate-500">Basic Background</CardTitle>
                            </CardHeader>
                            <CardContent class="p-0">
                                <div class="grid grid-cols-1 md:grid-cols-2">
                                    <div class="p-6 space-y-6">
                                        <div class="space-y-1">
                                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Full Name</p>
                                            <p class="font-bold text-slate-900">{{ tutor.name }}</p>
                                        </div>
                                        <div class="space-y-1">
                                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Identification (NID)</p>
                                            <p class="font-bold text-slate-900">{{ profile.nid_no || 'Missing' }}</p>
                                        </div>
                                        <div class="space-y-1">
                                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Present Location</p>
                                            <div class="flex items-start gap-2">
                                                <MapPin class="h-4 w-4 mt-0.5 text-slate-300" />
                                                <p class="text-sm font-medium text-slate-600 leading-relaxed">{{ profile.present_address || 'Not provided' }}</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="p-6 space-y-6 border-l border-slate-50">
                                        <div class="space-y-1">
                                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Gender Identity</p>
                                            <Badge variant="outline" class="font-bold uppercase tracking-tight text-[10px] bg-slate-50 text-slate-600 border-slate-200">
                                                {{ profile.gender || 'Not Specified' }}
                                            </Badge>
                                        </div>
                                        <div class="space-y-1">
                                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Date of Birth</p>
                                            <p class="font-bold text-slate-900">{{ formatDate(profile.date_of_birth) }}</p>
                                        </div>
                                        <div class="space-y-1">
                                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Account Status</p>
                                             <div class="flex items-center gap-2">
                                                <div :class="cn('h-2 w-2 rounded-full', tutor.status === 'active' ? 'bg-emerald-500' : 'bg-red-500')"></div>
                                                <p class="text-sm font-bold uppercase">{{ tutor.status }}</p>
                                             </div>
                                        </div>
                                    </div>
                                </div>
                                <Separator />
                                <div class="p-6 space-y-3">
                                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Professional Bio</p>
                                    <p class="text-sm text-slate-600 leading-relaxed whitespace-pre-wrap">{{ profile.bio || 'This tutor hasn\'t written a bio yet.' }}</p>
                                </div>
                            </CardContent>
                        </Card>

                        <form v-else @submit.prevent="submit" class="space-y-6">
                            <Card class="rounded-2xl border-slate-200/60 shadow-sm">
                                <CardContent class="grid gap-6 p-6 md:grid-cols-2">
                                    <div class="grid gap-2">
                                        <Label for="name">Full Name</Label>
                                        <Input id="name" v-model="form.name" placeholder="Full name" class="rounded-xl h-11" />
                                        <InputError :message="form.errors.name" />
                                    </div>
                                    <div class="grid gap-2">
                                        <Label for="email">Email Address</Label>
                                        <Input id="email" v-model="form.email" type="email" placeholder="Email address" class="rounded-xl h-11" />
                                        <InputError :message="form.errors.email" />
                                    </div>
                                    <div class="grid gap-2">
                                        <Label for="phone">Phone Number</Label>
                                        <Input id="phone" v-model="form.phone" placeholder="Phone number" class="rounded-xl h-11" />
                                        <InputError :message="form.errors.phone" />
                                    </div>
                                    <div class="grid gap-2">
                                        <Label for="gender">Gender</Label>
                                        <Select v-model="form.gender">
                                            <SelectTrigger id="gender" class="rounded-xl h-11">
                                                <SelectValue placeholder="Select gender" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem v-for="opt in genderOptions" :key="opt.value" :value="opt.value">
                                                    {{ opt.label }}
                                                </SelectItem>
                                            </SelectContent>
                                        </Select>
                                        <InputError :message="form.errors.gender" />
                                    </div>
                                    <div class="grid gap-2">
                                        <Label for="dob">Date of Birth</Label>
                                        <Input id="dob" v-model="form.date_of_birth" type="date" class="rounded-xl h-11" />
                                        <InputError :message="form.errors.date_of_birth" />
                                    </div>
                                    <div class="grid gap-2">
                                        <Label for="nid">NID / ID No</Label>
                                        <Input id="nid" v-model="form.nid_no" placeholder="NID or passport no" class="rounded-xl h-11" />
                                        <InputError :message="form.errors.nid_no" />
                                    </div>
                                    <div class="grid gap-2 md:col-span-2">
                                        <Label for="present_address">Present Address</Label>
                                        <Textarea id="present_address" v-model="form.present_address" rows="2" class="rounded-xl min-h-[80px]" />
                                        <InputError :message="form.errors.present_address" />
                                    </div>
                                    <div class="grid gap-2 md:col-span-2">
                                        <Label for="bio">Bio</Label>
                                        <Textarea id="bio" v-model="form.bio" rows="4" placeholder="Tell us about yourself..." class="rounded-xl min-h-[120px]" />
                                        <InputError :message="form.errors.bio" />
                                    </div>
                                    <div class="grid gap-2">
                                        <Label for="status">Account Status</Label>
                                        <Select v-model="form.status">
                                            <SelectTrigger id="status" class="rounded-xl h-11">
                                                <SelectValue placeholder="Account Status" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem value="active">Active</SelectItem>
                                                <SelectItem value="suspended">Suspended</SelectItem>
                                                <SelectItem value="pending_verification">Pending Verification</SelectItem>
                                            </SelectContent>
                                        </Select>
                                        <InputError :message="form.errors.status" />
                                    </div>
                                    <div class="grid gap-2">
                                        <Label for="profile_status">Visibility Status</Label>
                                        <Select v-model="form.profile_status">
                                            <SelectTrigger id="profile_status" class="rounded-xl h-11">
                                                <SelectValue placeholder="Visibility Status" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem value="active">Active (Visible)</SelectItem>
                                                <SelectItem value="inactive">Inactive (Hidden)</SelectItem>
                                            </SelectContent>
                                        </Select>
                                        <InputError :message="form.errors.profile_status" />
                                    </div>
                                </CardContent>
                            </Card>
                        </form>
                    </div>

                    <!-- Educational Section -->
                    <div v-if="activeTab === 'education'" class="space-y-6">
                        <div v-if="!isEditMode" class="space-y-4">
                            <Card v-if="profile.educations.length === 0" class="flex flex-col items-center justify-center p-12 text-center border-dashed rounded-2xl">
                                <GraduationCap class="h-12 w-12 text-slate-200 mb-4" />
                                <p class="text-sm font-medium text-slate-400">No education records found.</p>
                            </Card>
                            <Card v-for="(edu, idx) in profile.educations" :key="edu.id" class="overflow-hidden rounded-2xl border-slate-200/60 shadow-sm transition-all hover:bg-slate-50/30">
                                <CardContent class="grid grid-cols-1 md:grid-cols-[1fr_200px] gap-6 p-6">
                                    <div class="space-y-4">
                                        <div class="flex items-center gap-3">
                                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-purple-50 text-purple-600 font-bold">
                                                {{ Number(idx) + 1 }}
                                            </div>
                                            <div>
                                                <h3 class="font-bold text-slate-900 uppercase tracking-tight">{{ edu.degree }}</h3>
                                                <p class="text-sm font-medium text-slate-500 italic">{{ edu.institute }}</p>
                                            </div>
                                        </div>
                                        <div class="flex flex-wrap gap-6 pl-13">
                                            <div class="space-y-0.5">
                                                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Department</p>
                                                <p class="text-sm font-semibold text-slate-700">{{ edu.department || '—' }}</p>
                                            </div>
                                            <div class="space-y-0.5">
                                                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Graduation</p>
                                                <p class="text-sm font-semibold text-slate-700">{{ edu.graduation_year || '—' }}</p>
                                            </div>
                                            <div class="space-y-0.5">
                                                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Result</p>
                                                <p class="text-sm font-semibold text-indigo-600">{{ edu.result || '—' }}</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-end">
                                        <Badge v-if="edu.is_current" class="bg-emerald-50 text-emerald-700 border-emerald-100 font-bold px-3 py-1 uppercase text-[10px] tracking-widest">
                                            Current
                                        </Badge>
                                    </div>
                                </CardContent>
                            </Card>
                        </div>

                        <div v-else class="space-y-6">
                            <div class="flex items-center justify-between">
                                <h3 class="text-sm font-bold text-slate-500 uppercase tracking-widest">Education Records</h3>
                                <Button type="button" variant="outline" size="sm" @click="addEducation" class="rounded-xl border-dashed">
                                    <Plus class="mr-1.5 h-4 w-4" /> Add Row
                                </Button>
                            </div>

                            <div v-for="(edu, idx) in form.educations" :key="edu.id || `new-${idx}`" class="group relative rounded-2xl border border-slate-200 bg-white p-6 transition-all hover:border-indigo-200 hover:shadow-lg hover:shadow-indigo-50/50">
                                <div class="absolute -right-2 -top-2 opacity-0 transition-opacity group-hover:opacity-100">
                                    <Button type="button" variant="destructive" size="icon" class="h-8 w-8 rounded-full shadow-lg" @click="removeEducation(Number(idx))">
                                        <X class="h-4 w-4" />
                                    </Button>
                                </div>
                                
                                <div class="mb-6 flex items-center justify-between border-b border-slate-50 pb-4">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-50 text-xs font-bold text-indigo-600">
                                            #{{ Number(idx) + 1 }}
                                        </div>
                                        <h4 class="font-bold text-slate-900 tracking-tight">Record #{{ Number(idx) + 1 }}</h4>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <Button type="button" variant="outline" size="icon" class="h-8 w-8 rounded-lg" :disabled="Number(idx) === 0" @click="moveEducation(idx, -1)">
                                            <ChevronRight class="h-4 w-4 -rotate-90" />
                                        </Button>
                                        <Button type="button" variant="outline" size="icon" class="h-8 w-8 rounded-lg" :disabled="Number(idx) === form.educations.length - 1" @click="moveEducation(idx, 1)">
                                            <ChevronRight class="h-4 w-4 rotate-90" />
                                        </Button>
                                    </div>
                                </div>

                                <div class="grid gap-6 md:grid-cols-2">
                                    <div class="grid gap-2">
                                        <Label>Degree / Certificate</Label>
                                        <Input v-model="edu.degree" placeholder="BSc in CSE" class="rounded-xl h-11" />
                                    </div>
                                    <div class="grid gap-2">
                                        <Label>Institution</Label>
                                        <Input v-model="edu.institute" placeholder="University name" class="rounded-xl h-11" />
                                    </div>
                                    <div class="grid gap-2">
                                        <Label>Department</Label>
                                        <Input v-model="edu.department" placeholder="Subject name" class="rounded-xl h-11" />
                                    </div>
                                    <div class="grid gap-2">
                                        <Label>Graduation Year</Label>
                                        <Input v-model="edu.graduation_year" type="number" class="rounded-xl h-11" />
                                    </div>
                                    <div class="grid gap-2">
                                        <Label>Grade / CGPA</Label>
                                        <Input v-model="edu.result" placeholder="e.g. 3.80" class="rounded-xl h-11" />
                                    </div>
                                    <div class="flex items-center gap-2 pt-8">
                                        <input type="checkbox" :id="`is_current-${idx}`" v-model="edu.is_current" class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500" />
                                        <Label :for="`is_current-${idx}`" class="font-bold text-slate-600">Currently studying</Label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Preferences Section -->
                    <div v-if="activeTab === 'preferences'" class="space-y-6">
                         <div v-if="!isEditMode" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <Card class="rounded-2xl border-slate-200/60 shadow-sm overflow-hidden">
                                <CardHeader class="px-6 py-4 border-b border-slate-50 bg-slate-50/30">
                                    <CardTitle class="text-[10px] font-bold uppercase tracking-widest text-slate-500">Methodology & Audience</CardTitle>
                                </CardHeader>
                                <CardContent class="p-6 space-y-6">
                                    <div class="space-y-3">
                                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Tuition Methods</p>
                                        <div class="flex flex-wrap gap-2">
                                            <Badge v-for="typeId in profile.preferred_tuition_types" :key="typeId" variant="secondary" class="rounded-lg bg-indigo-50 text-indigo-700">
                                                {{ tuitionTypes.find(t => t.id === typeId)?.name || 'Unknown' }}
                                            </Badge>
                                            <p v-if="!profile.preferred_tuition_types.length" class="text-sm italic text-slate-400">None specified</p>
                                        </div>
                                    </div>
                                    <div class="space-y-3">
                                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Curriculums</p>
                                        <div class="flex flex-wrap gap-2">
                                            <Badge v-for="catId in profile.preferred_categories" :key="catId" variant="secondary" class="rounded-lg bg-emerald-50 text-emerald-700">
                                                {{ categories.find(c => c.id === catId)?.name || 'Unknown' }}
                                            </Badge>
                                            <p v-if="!profile.preferred_categories.length" class="text-sm italic text-slate-400">None specified</p>
                                        </div>
                                    </div>
                                    <div class="space-y-3">
                                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Target Classes</p>
                                        <div class="flex flex-wrap gap-2">
                                            <Badge v-for="clsId in profile.preferred_classes" :key="clsId" variant="secondary" class="rounded-lg bg-slate-100 text-slate-700">
                                                {{ schoolClasses.find(c => c.id === clsId)?.name || 'Unknown' }}
                                            </Badge>
                                            <p v-if="!profile.preferred_classes.length" class="text-sm italic text-slate-400">None specified</p>
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>

                            <Card class="rounded-2xl border-slate-200/60 shadow-sm overflow-hidden">
                                <CardHeader class="px-6 py-4 border-b border-slate-50 bg-slate-50/30">
                                    <CardTitle class="text-[10px] font-bold uppercase tracking-widest text-slate-500">Expertise & Availability</CardTitle>
                                </CardHeader>
                                <CardContent class="p-6 space-y-6">
                                    <div class="space-y-3">
                                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Key Subjects</p>
                                        <div class="flex flex-wrap gap-2">
                                            <Badge v-for="subId in profile.preferred_subjects" :key="subId" variant="secondary" class="rounded-lg bg-indigo-50 text-indigo-700">
                                                {{ subjects.find(s => s.id === subId)?.name || 'Unknown' }}
                                            </Badge>
                                            <p v-if="!profile.preferred_subjects.length" class="text-sm italic text-slate-400">None specified</p>
                                        </div>
                                    </div>
                                    <div class="space-y-3">
                                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Available Areas</p>
                                        <div class="flex flex-wrap gap-2">
                                            <Badge v-for="locId in profile.preferred_locations" :key="locId" variant="secondary" class="rounded-lg bg-slate-100 text-slate-700">
                                                <MapPin class="mr-1 h-3 w-3" />
                                                {{ locations.find(l => l.id === locId)?.name || 'Unknown' }}
                                            </Badge>
                                            <p v-if="!profile.preferred_locations.length" class="text-sm italic text-slate-400">None specified</p>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div class="space-y-1">
                                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Min Salary</p>
                                            <p class="font-bold text-slate-900">{{ formatCurrency(profile.expected_salary_min) }}</p>
                                        </div>
                                        <div class="space-y-1">
                                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Max Salary</p>
                                            <p class="font-bold text-slate-900">{{ formatCurrency(profile.expected_salary_max) }}</p>
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>

                            <Card class="md:col-span-2 rounded-2xl border-slate-200/60 shadow-sm">
                                <CardContent class="p-6">
                                    <div class="flex flex-wrap items-center justify-between gap-6">
                                        <div class="space-y-1">
                                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Time Commitment</p>
                                            <p class="text-sm font-bold text-slate-700 flex items-center gap-2">
                                                <Clock class="h-4 w-4 text-indigo-400" />
                                                {{ profile.available_time || 'No specific time given' }}
                                            </p>
                                        </div>
                                        <div class="flex flex-wrap gap-2">
                                            <div 
                                                v-for="day in dayOptions" 
                                                :key="day.value"
                                                :class="cn(
                                                    'px-3 py-1.5 rounded-lg text-[10px] font-bold uppercase tracking-widest border transition-all',
                                                    profile.available_days.includes(day.value)
                                                        ? 'bg-indigo-50 text-indigo-700 border-indigo-100 shadow-sm'
                                                        : 'bg-slate-50 text-slate-300 border-slate-100 opacity-50'
                                                )"
                                            >
                                                {{ day.label.slice(0, 3) }}
                                            </div>
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>
                         </div>

                        <div v-else class="space-y-6">
                            <Card class="rounded-2xl border-slate-200/60 shadow-sm">
                                <CardContent class="p-6 space-y-8">
                                    <!-- Tuition Type -->
                                    <div class="space-y-4">
                                        <Label class="text-sm font-bold text-slate-900">Preferred Tuition Types</Label>
                                        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
                                            <button
                                                v-for="type in tuitionTypes"
                                                :key="type.id"
                                                type="button"
                                                @click="toggleMultiSelect('preferred_tuition_types', type.id)"
                                                :class="cn(
                                                    'flex items-center gap-2.5 px-4 py-3 rounded-xl border-2 text-left transition-all duration-200',
                                                    hasSelected('preferred_tuition_types', type.id)
                                                        ? 'border-indigo-600 bg-indigo-50 text-indigo-700 ring-4 ring-indigo-50/50'
                                                        : 'border-slate-100 text-slate-500 hover:border-slate-200 hover:bg-slate-50'
                                                )"
                                            >
                                                <div :class="cn('h-2 w-2 rounded-full', hasSelected('preferred_tuition_types', type.id) ? 'bg-indigo-600' : 'bg-slate-200')"></div>
                                                <span class="text-xs font-bold leading-none">{{ type.name }}</span>
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Categories -->
                                    <div class="space-y-4">
                                        <Label class="text-sm font-bold text-slate-900">Preferred Curriculums / Categories</Label>
                                        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
                                            <button
                                                v-for="opt in categories"
                                                :key="opt.id"
                                                type="button"
                                                @click="toggleMultiSelect('preferred_categories', opt.id)"
                                                :class="cn(
                                                    'flex items-center gap-2.5 px-4 py-3 rounded-xl border-2 text-left transition-all duration-200',
                                                    hasSelected('preferred_categories', opt.id)
                                                        ? 'border-emerald-600 bg-emerald-50 text-emerald-700 ring-4 ring-emerald-50/50'
                                                        : 'border-slate-100 text-slate-500 hover:border-slate-200 hover:bg-slate-50'
                                                )"
                                            >
                                                <div :class="cn('h-2 w-2 rounded-full', hasSelected('preferred_categories', opt.id) ? 'bg-emerald-600' : 'bg-slate-200')"></div>
                                                <span class="text-xs font-bold leading-none">{{ opt.name }}</span>
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Classes -->
                                    <div class="space-y-4" v-if="form.preferred_categories.length > 0">
                                        <Label class="text-sm font-bold text-slate-900">Preferred Classes</Label>
                                        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
                                            <button
                                                v-for="opt in filteredClasses"
                                                :key="opt.id"
                                                type="button"
                                                @click="toggleMultiSelect('preferred_classes', opt.id)"
                                                :class="cn(
                                                    'flex items-center justify-between gap-2 px-4 py-3 rounded-xl border-2 text-left transition-all duration-200',
                                                    hasSelected('preferred_classes', opt.id)
                                                        ? 'border-slate-900 bg-slate-900 text-white ring-4 ring-slate-100'
                                                        : 'border-slate-100 text-slate-600 hover:border-slate-200'
                                                )"
                                            >
                                                <span class="text-xs font-bold leading-none">{{ opt.name }}</span>
                                                <Plus v-if="!hasSelected('preferred_classes', opt.id)" class="h-3.5 w-3.5 opacity-40" />
                                                <X v-else class="h-3.5 w-3.5" />
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Subjects -->
                                    <div class="space-y-4" v-if="form.preferred_classes.length > 0">
                                        <Label class="text-sm font-bold text-slate-900">Expert Subjects</Label>
                                        <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-6 gap-3">
                                            <button
                                                v-for="opt in filteredSubjects"
                                                :key="opt.id"
                                                type="button"
                                                @click="toggleMultiSelect('preferred_subjects', opt.id)"
                                                :class="cn(
                                                    'px-3 py-2 rounded-lg border-2 text-center transition-all text-[10px] font-bold uppercase tracking-tight',
                                                    hasSelected('preferred_subjects', opt.id)
                                                        ? 'border-indigo-600 bg-indigo-600 text-white'
                                                        : 'border-slate-100 text-slate-500 hover:border-slate-200'
                                                )"
                                            >
                                                {{ opt.name }}
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Locations -->
                                    <div class="space-y-4">
                                        <Label class="text-sm font-bold text-slate-900">Preferred Locations</Label>
                                        <div class="relative">
                                            <Search class="absolute left-3.5 top-3.5 h-4 w-4 text-slate-400" />
                                            <div class="flex flex-wrap gap-2 rounded-2xl border-2 border-slate-100 bg-slate-50/30 p-4 pt-10">
                                                <button
                                                    v-for="opt in locations"
                                                    :key="opt.id"
                                                    type="button"
                                                    @click="toggleMultiSelect('preferred_locations', opt.id)"
                                                    :class="cn(
                                                        'flex items-center gap-1.5 px-3 py-1.5 rounded-lg border text-xs font-bold transition-all',
                                                        hasSelected('preferred_locations', opt.id)
                                                            ? 'bg-white border-indigo-600 text-indigo-700 shadow-sm'
                                                            : 'bg-white border-slate-100 text-slate-400 opacity-60 hover:opacity-100'
                                                    )"
                                                >
                                                   <MapPin class="h-3 w-3" />
                                                   {{ opt.name }}
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Salary -->
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div class="grid gap-2">
                                            <Label>Min Expected (BDT)</Label>
                                            <Input v-model="form.expected_salary_min" type="number" class="rounded-xl h-11" />
                                        </div>
                                        <div class="grid gap-2">
                                            <Label>Max Expected (BDT)</Label>
                                            <Input v-model="form.expected_salary_max" type="number" class="rounded-xl h-11" />
                                        </div>
                                    </div>

                                    <!-- Days -->
                                    <div class="space-y-4">
                                        <Label class="text-sm font-bold text-slate-900">Available Days</Label>
                                        <div class="grid grid-cols-4 sm:grid-cols-7 gap-3">
                                            <button
                                                v-for="day in dayOptions"
                                                :key="day.value"
                                                type="button"
                                                @click="toggleMultiSelect('available_days', day.value)"
                                                :class="cn(
                                                    'flex flex-col items-center justify-center rounded-xl border-2 py-4 transition-all',
                                                    hasSelected('available_days', day.value)
                                                        ? 'border-slate-900 bg-slate-900 text-white'
                                                        : 'border-slate-100 text-slate-400 hover:border-slate-200'
                                                )"
                                            >
                                                <span class="text-[10px] font-bold uppercase tracking-widest">{{ day.label.slice(0, 3) }}</span>
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Availability details -->
                                    <div class="grid gap-2">
                                        <Label>Availability Note / Time</Label>
                                        <Input v-model="form.available_time" placeholder="e.g. 4 PM - 9 PM" class="rounded-xl h-11" />
                                    </div>
                                </CardContent>
                            </Card>
                        </div>
                    </div>

                    <!-- Reviews Section -->
                    <div v-if="activeTab === 'reviews'" class="space-y-6">
                         <Card v-if="reviews.length === 0" class="flex flex-col items-center justify-center p-12 text-center border-dashed rounded-2xl">
                                <MessageSquareText class="h-12 w-12 text-slate-200 mb-4" />
                                <p class="text-sm font-medium text-slate-400">No reviews earned yet.</p>
                        </Card>
                        <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <Card v-for="review in reviews" :key="review.id" class="rounded-2xl border-slate-200/60 shadow-sm transition-all hover:shadow-md">
                                <CardContent class="p-6 space-y-4">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-3">
                                            <Avatar class="h-10 w-10 border border-slate-100">
                                                <AvatarFallback class="bg-indigo-50 text-indigo-700 text-xs font-bold uppercase">
                                                    {{ (review.guardian?.name || 'G')[0] }}
                                                </AvatarFallback>
                                            </Avatar>
                                            <div>
                                                <h4 class="text-sm font-bold text-slate-900">{{ review.guardian?.name || 'Guardian' }}</h4>
                                                <p class="text-[10px] font-medium text-slate-400">{{ formatDate(review.created_at) }}</p>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-0.5">
                                            <Star v-for="i in 5" :key="i" :class="cn('h-3.5 w-3.5', i <= review.rating ? 'fill-amber-400 text-amber-400' : 'text-slate-100 fill-slate-50')" />
                                        </div>
                                    </div>
                                    <Separator class="bg-slate-50" />
                                    <p class="text-sm text-slate-600 leading-relaxed italic italic">"{{ review.comment }}"</p>
                                    <div class="flex items-center gap-2 pt-2">
                                        <Badge variant="outline" class="text-[9px] font-bold tracking-widest bg-slate-50 text-slate-500 border-slate-100 px-2 py-0.5 rounded-md uppercase">
                                            Case #{{ review.job_assignment_id }}
                                        </Badge>
                                    </div>
                                </CardContent>
                            </Card>
                        </div>
                    </div>

                    <!-- Verification Section -->
                    <div v-if="activeTab === 'verification'" class="space-y-6">
                         <Card class="overflow-hidden rounded-2xl border-slate-200/60 shadow-sm">
                            <CardHeader class="border-b border-slate-50 bg-slate-50/30 px-6 py-5">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div :class="cn('flex h-10 w-10 items-center justify-center rounded-xl font-bold', statusVariant === 'default' ? 'bg-blue-50 text-blue-600' : 'bg-amber-50 text-amber-600')">
                                            <ShieldCheck class="h-6 w-6" />
                                        </div>
                                        <div>
                                            <CardTitle class="text-md font-bold uppercase tracking-tight text-slate-900">{{ statusLabel }}</CardTitle>
                                            <CardDescription class="text-xs font-medium text-slate-400">
                                                Identity and credentials verification status
                                            </CardDescription>
                                        </div>
                                    </div>
                                    <Badge :variant="statusVariant" class="rounded-full px-3 py-1 font-bold">
                                        {{ normalizedStatus || 'UNVERIFIED' }}
                                    </Badge>
                                </div>
                            </CardHeader>
                            <CardContent class="p-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                    <div class="space-y-6">
                                        <div class="flex items-start gap-4">
                                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-100">
                                                <Calendar class="h-5 w-5 text-slate-500" />
                                            </div>
                                            <div class="space-y-1">
                                                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 text-slate-400">Verified At</p>
                                                <p class="text-sm font-bold text-slate-900">{{ verifiedAt ? formatDate(verifiedAt) : 'Not Verified' }}</p>
                                            </div>
                                        </div>

                                        <div v-if="verification?.decision_reason" class="p-4 rounded-xl border border-amber-100 bg-amber-50/50">
                                            <div class="flex items-start gap-3">
                                                <AlertCircle class="h-5 w-5 text-amber-500 mt-0.5" />
                                                <div class="space-y-1">
                                                    <p class="text-xs font-bold text-amber-900">Admin Remark</p>
                                                    <p class="text-sm text-amber-800 leading-relaxed">{{ verification.decision_reason }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div v-if="verificationInvoice" class="space-y-4">
                                         <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Payment Summary</p>
                                         <div class="p-5 rounded-2xl border border-slate-100 bg-slate-50/50 space-y-4">
                                            <div class="flex items-center justify-between">
                                                <span class="text-sm text-slate-500">Invoice No:</span>
                                                <span class="text-sm font-bold text-slate-900">#{{ verificationInvoice.invoice_no }}</span>
                                            </div>
                                             <div class="flex items-center justify-between">
                                                <span class="text-sm text-slate-500">Amount:</span>
                                                <span class="text-sm font-bold text-indigo-700">{{ formatCurrency(verificationInvoice.amount) }}</span>
                                            </div>
                                             <div class="flex items-center justify-between">
                                                <span class="text-sm text-slate-500">Payment Status:</span>
                                                <Badge variant="outline" :class="cn('rounded-full font-bold uppercase text-[9px] px-2', verificationInvoice.status === 'paid' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-red-50 text-red-700 border-red-200')">
                                                    {{ verificationInvoice.status }}
                                                </Badge>
                                            </div>
                                         </div>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        <Card class="rounded-2xl border-slate-200/60 shadow-sm bg-gradient-to-br from-slate-900 to-indigo-950 text-white overflow-hidden relative">
                             <div class="absolute top-0 right-0 h-40 w-40 bg-indigo-500 rounded-full filter blur-[80px] opacity-10 -mr-20 -mt-20"></div>
                             <CardContent class="p-8 relative z-10 flex flex-col md:flex-row items-center justify-between gap-6">
                                <div class="space-y-2 text-center md:text-left">
                                    <h3 class="text-lg font-bold">Verification Request Logs</h3>
                                    <p class="text-sm text-indigo-200/60 font-medium">History of verification submissions and decisions.</p>
                                </div>
                                <Button variant="secondary" class="rounded-xl font-bold px-6 h-12 shadow-lg shadow-black/20" as-child>
                                    <Link :href="verificationIndex.url({ query: { search: tutor.phone } })">
                                        View All Requests
                                    </Link>
                                </Button>
                             </CardContent>
                        </Card>
                    </div>
                </main>
            </div>
        </div>

        <ConfirmDialog
            v-model:open="confirmOpen"
            :title="tutor.status === 'active' ? 'Account Suspension' : 'Account Activation'"
            :description="tutor.status === 'active' 
                ? 'Are you sure you want to suspend this professional profile? All active services and visibility will be immediately revoked.' 
                : 'Restore this profile to active status. The tutor will regain access to their dashboard and job applications.'"
            :confirm-label="tutor.status === 'active' ? 'Suspend Tutor' : 'Activate Tutor'"
            :destructive="tutor.status === 'active'"
            :disabled="form.processing"
            @confirm="toggleStatus"
        />
    </AdminLayout>
</template>

<style scoped>
/* Custom animations for a premium feel */
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

main > * {
    animation: fadeIn 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

.pl-13 {
    padding-left: 3.25rem;
}

::selection {
    background-color: rgb(224 231 255);
    color: rgb(49 46 129);
}
</style>
