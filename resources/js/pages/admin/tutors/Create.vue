<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
    BookOpen,
    Briefcase,
    Calendar,
    CheckCircle2,
    ChevronLeft,
    Clock,
    Contact,
    DollarSign,
    GraduationCap,
    Lock,
    Mail,
    Phone,
    Plus,
    Shield,
    Trash2,
    User,
    UserPlus,
} from 'lucide-vue-next';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
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

defineProps<{
    tuitionTypes: any[];
    categories: any[];
    schoolClasses: any[];
    subjects: any[];
    locations: any[];
    dayOptions: any[];
}>();

const breadcrumbs = [
    { title: 'Tutor Directory', href: '/admin/tutors' },
    { title: 'Create Complete Profile', href: '/admin/tutors/create' },
];

const form = useForm({
    name: '',
    email: '',
    phone: '',
    status: 'active',
    password: '',
    password_confirmation: '',
    // Basic Profile
    gender: 'male',
    date_of_birth: '',
    present_address: '',
    permanent_address: '',
    nid_no: '',
    bio: '',
    // Preferences
    preferred_tuition_types: [] as number[],
    preferred_categories: [] as number[],
    preferred_classes: [] as number[],
    preferred_subjects: [] as number[],
    preferred_locations: [] as number[],
    expected_salary_min: '',
    expected_salary_max: '',
    available_days: [] as string[],
    available_time: '',
    // Education
    educations: [] as any[],
});

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

const toggleItem = (list: number[], id: number) => {
    const index = list.indexOf(id);
    if (index === -1) list.push(id);
    else list.splice(index, 1);
};

const toggleDay = (day: string) => {
    const index = form.available_days.indexOf(day);
    if (index === -1) form.available_days.push(day);
    else form.available_days.splice(index, 1);
};

const submit = () => {
    form.post('/admin/tutors', {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <Head title="Register Tutor" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto max-w-5xl space-y-8 p-4 sm:p-6 lg:p-10">
            <!-- Header Section -->
            <div
                class="mb-2 flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
            >
                <div class="space-y-1">
                    <h1
                        class="flex items-center gap-3 text-3xl font-bold tracking-tight text-slate-900"
                    >
                        <div
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-indigo-600 shadow-lg shadow-indigo-200"
                        >
                            <UserPlus class="h-6 w-6 text-white" />
                        </div>
                        Register Complete Tutor
                    </h1>
                    <p class="text-sm font-medium text-slate-500">
                        Create account, set preferences and add education
                        history in one step.
                    </p>
                </div>

                <Button
                    variant="ghost"
                    as-child
                    class="group rounded-xl transition-all hover:bg-slate-100"
                >
                    <Link href="/admin/tutors" class="flex items-center">
                        <ChevronLeft
                            class="mr-2 h-4 w-4 transition-transform group-hover:-translate-x-1"
                        />
                        Back to Directory
                    </Link>
                </Button>
            </div>

            <form @submit.prevent="submit" class="space-y-8 pb-20">
                <div
                    v-if="Object.keys(form.errors).length > 0"
                    class="flex items-start gap-3 rounded-2xl border border-red-100 bg-red-50 p-4"
                >
                    <Shield class="mt-0.5 h-5 w-5 shrink-0 text-red-500" />
                    <div class="space-y-1">
                        <p class="text-sm font-bold text-red-800">
                            Please correct the following errors:
                        </p>
                        <ul class="list-inside list-disc text-xs text-red-600">
                            <li v-for="(err, key) in form.errors" :key="key">
                                {{ err }}
                            </li>
                        </ul>
                    </div>
                </div>
                <!-- Section 1: Account Credentials -->
                <Card
                    class="overflow-hidden rounded-2xl border-slate-200/60 shadow-sm"
                >
                    <CardHeader
                        class="border-b border-indigo-100/50 bg-indigo-50/30 p-6"
                    >
                        <div class="flex items-center gap-3">
                            <div
                                class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-indigo-100"
                            >
                                <Lock class="h-4 w-4 text-indigo-600" />
                            </div>
                            <div>
                                <CardTitle
                                    class="text-lg font-bold text-slate-900"
                                    >Security & Status</CardTitle
                                >
                                <CardDescription
                                    class="text-xs font-medium tracking-wider text-indigo-600/80 uppercase"
                                    >Account Authentication</CardDescription
                                >
                            </div>
                        </div>
                    </CardHeader>
                    <CardContent class="grid gap-6 p-6 md:grid-cols-2">
                        <div class="space-y-2">
                            <Label
                                for="name"
                                class="ml-1 text-[10px] font-black tracking-widest text-slate-400 uppercase"
                                >Full Name</Label
                            >
                            <div class="relative">
                                <User
                                    class="absolute top-3 left-3.5 h-4 w-4 text-slate-400"
                                />
                                <Input
                                    v-model="form.name"
                                    placeholder="Full legal name"
                                    class="h-11 rounded-xl pl-10"
                                    required
                                />
                            </div>
                            <InputError :message="form.errors.name" />
                        </div>

                        <div class="space-y-2">
                            <Label
                                for="status"
                                class="ml-1 text-[10px] font-black tracking-widest text-slate-400 uppercase"
                                >Initial Status</Label
                            >
                            <Select v-model="form.status">
                                <SelectTrigger class="h-11 rounded-xl">
                                    <Shield
                                        class="mr-2 h-4 w-4 text-slate-400"
                                    />
                                    <SelectValue placeholder="Select status" />
                                </SelectTrigger>
                                <SelectContent class="rounded-xl">
                                    <SelectItem value="active"
                                        >Active</SelectItem
                                    >
                                    <SelectItem value="suspended"
                                        >Suspended</SelectItem
                                    >
                                    <SelectItem value="pending_verification"
                                        >Pending Verification</SelectItem
                                    >
                                </SelectContent>
                            </Select>
                            <InputError :message="form.errors.status" />
                        </div>

                        <div class="space-y-2">
                            <Label
                                for="email"
                                class="ml-1 text-[10px] font-black tracking-widest text-slate-400 uppercase"
                                >Email Address</Label
                            >
                            <div class="relative">
                                <Mail
                                    class="absolute top-3 left-3.5 h-4 w-4 text-slate-400"
                                />
                                <Input
                                    v-model="form.email"
                                    type="email"
                                    placeholder="email@example.com"
                                    class="h-11 rounded-xl pl-10"
                                />
                            </div>
                            <InputError :message="form.errors.email" />
                        </div>

                        <div class="space-y-2">
                            <Label
                                for="phone"
                                class="ml-1 text-[10px] font-black tracking-widest text-slate-400 uppercase"
                                >Phone Number</Label
                            >
                            <div class="relative">
                                <Phone
                                    class="absolute top-3 left-3.5 h-4 w-4 text-slate-400"
                                />
                                <Input
                                    v-model="form.phone"
                                    placeholder="01XXXXXXXXX"
                                    class="h-11 rounded-xl pl-10"
                                    required
                                />
                            </div>
                            <InputError :message="form.errors.phone" />
                        </div>

                        <div class="space-y-2">
                            <Label
                                for="password"
                                class="ml-1 text-[10px] font-black tracking-widest text-slate-400 uppercase"
                                >Temporary Password</Label
                            >
                            <div class="relative">
                                <Lock
                                    class="absolute top-3 left-3.5 h-4 w-4 text-slate-400"
                                />
                                <Input
                                    v-model="form.password"
                                    type="password"
                                    class="h-11 rounded-xl pl-10"
                                    required
                                />
                            </div>
                            <InputError :message="form.errors.password" />
                        </div>

                        <div class="space-y-2">
                            <Label
                                for="password_confirmation"
                                class="ml-1 text-[10px] font-black tracking-widest text-slate-400 uppercase"
                                >Confirm Password</Label
                            >
                            <div class="relative">
                                <CheckCircle2
                                    class="absolute top-3 left-3.5 h-4 w-4 text-slate-400"
                                />
                                <Input
                                    v-model="form.password_confirmation"
                                    type="password"
                                    class="h-11 rounded-xl pl-10"
                                    required
                                />
                            </div>
                            <InputError
                                :message="form.errors.password_confirmation"
                            />
                        </div>
                    </CardContent>
                </Card>

                <!-- Section 2: Personal Profile Details -->
                <Card
                    class="overflow-hidden rounded-2xl border-slate-200/60 shadow-sm"
                >
                    <CardHeader
                        class="border-b border-emerald-100/50 bg-emerald-50/30 p-6"
                    >
                        <div class="flex items-center gap-3">
                            <div
                                class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-emerald-100"
                            >
                                <User class="h-4 w-4 text-emerald-600" />
                            </div>
                            <div>
                                <CardTitle
                                    class="text-lg font-bold text-slate-900"
                                    >Personal Information</CardTitle
                                >
                                <CardDescription
                                    class="text-xs font-medium tracking-wider text-emerald-600/80 uppercase"
                                    >Bio & Identity</CardDescription
                                >
                            </div>
                        </div>
                    </CardHeader>
                    <CardContent class="grid gap-6 p-6 md:grid-cols-2">
                        <div class="space-y-2">
                            <Label
                                class="ml-1 text-[10px] font-black tracking-widest text-slate-400 uppercase"
                                >Gender</Label
                            >
                            <Select v-model="form.gender">
                                <SelectTrigger class="h-11 rounded-xl">
                                    <SelectValue placeholder="Select gender" />
                                </SelectTrigger>
                                <SelectContent class="rounded-xl">
                                    <SelectItem value="male">Male</SelectItem>
                                    <SelectItem value="female"
                                        >Female</SelectItem
                                    >
                                    <SelectItem value="other">Other</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <div class="space-y-2">
                            <Label
                                for="date_of_birth"
                                class="ml-1 text-[10px] font-black tracking-widest text-slate-400 uppercase"
                                >Date of Birth</Label
                            >
                            <div class="relative">
                                <Calendar
                                    class="absolute top-3 left-3.5 h-4 w-4 text-slate-400"
                                />
                                <Input
                                    v-model="form.date_of_birth"
                                    type="date"
                                    class="h-11 rounded-xl pl-10"
                                />
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label
                                for="nid_no"
                                class="ml-1 text-[10px] font-black tracking-widest text-slate-400 uppercase"
                                >Identity (NID/Passport)</Label
                            >
                            <div class="relative">
                                <Contact
                                    class="absolute top-3 left-3.5 h-4 w-4 text-slate-400"
                                />
                                <Input
                                    v-model="form.nid_no"
                                    placeholder="Identification number"
                                    class="h-11 rounded-xl pl-10"
                                />
                            </div>
                        </div>

                        <div class="space-y-2 md:col-span-2">
                            <Label
                                for="bio"
                                class="ml-1 text-[10px] font-black tracking-widest text-slate-400 uppercase"
                                >Professional Bio</Label
                            >
                            <Textarea
                                v-model="form.bio"
                                rows="3"
                                placeholder="Summary of tutoring experience..."
                                class="resize-none rounded-2xl"
                            />
                        </div>

                        <div class="space-y-2">
                            <Label
                                for="present_address"
                                class="ml-1 text-[10px] font-black tracking-widest text-slate-400 uppercase"
                                >Present Address</Label
                            >
                            <Textarea
                                v-model="form.present_address"
                                rows="2"
                                class="resize-none rounded-xl py-3"
                            />
                        </div>

                        <div class="space-y-2">
                            <Label
                                for="permanent_address"
                                class="ml-1 text-[10px] font-black tracking-widest text-slate-400 uppercase"
                                >Permanent Address</Label
                            >
                            <Textarea
                                v-model="form.permanent_address"
                                rows="2"
                                class="resize-none rounded-xl py-3"
                            />
                        </div>
                    </CardContent>
                </Card>

                <!-- Section 3: Tuition Preferences -->
                <Card
                    class="overflow-hidden rounded-2xl border-slate-200/60 shadow-sm"
                >
                    <CardHeader
                        class="border-b border-amber-100/50 bg-amber-50/30 p-6"
                    >
                        <div class="flex items-center gap-3">
                            <div
                                class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-amber-100"
                            >
                                <BookOpen class="h-4 w-4 text-amber-600" />
                            </div>
                            <div>
                                <CardTitle
                                    class="text-lg font-bold text-slate-900"
                                    >Tuition Preferences</CardTitle
                                >
                                <CardDescription
                                    class="text-xs font-medium tracking-wider text-amber-600/80 uppercase"
                                    >Subjects, Locations &
                                    Rates</CardDescription
                                >
                            </div>
                        </div>
                    </CardHeader>
                    <CardContent class="grid gap-8 p-6">
                        <!-- Multi Select Groups -->
                        <div class="grid gap-6 md:grid-cols-2">
                            <div class="space-y-3">
                                <Label
                                    class="ml-1 text-[10px] font-black tracking-widest text-slate-400 uppercase"
                                    >Preferred Methods</Label
                                >
                                <div
                                    class="grid grid-cols-2 gap-3 rounded-2xl border border-slate-100 bg-slate-50/50 p-4"
                                >
                                    <div
                                        v-for="type in tuitionTypes"
                                        :key="type.id"
                                        class="flex items-center space-x-2"
                                    >
                                        <Checkbox
                                            :id="'type-' + type.id"
                                            :checked="
                                                form.preferred_tuition_types.includes(
                                                    type.id,
                                                )
                                            "
                                            @update:checked="
                                                toggleItem(
                                                    form.preferred_tuition_types,
                                                    type.id,
                                                )
                                            "
                                        />
                                        <label
                                            :for="'type-' + type.id"
                                            class="text-sm leading-none font-medium peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
                                            >{{ type.name }}</label
                                        >
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-3">
                                <Label
                                    class="ml-1 text-[10px] font-black tracking-widest text-slate-400 uppercase"
                                    >Available Days</Label
                                >
                                <div class="flex flex-wrap gap-2">
                                    <Button
                                        v-for="day in dayOptions"
                                        :key="day.value"
                                        type="button"
                                        variant="outline"
                                        size="sm"
                                        :class="
                                            cn(
                                                'h-8 rounded-lg px-3 text-[11px] font-semibold transition-all',
                                                form.available_days.includes(
                                                    day.value,
                                                )
                                                    ? 'border-transparent bg-indigo-600 text-white shadow-md shadow-indigo-100/50'
                                                    : 'bg-white text-slate-600 hover:bg-slate-50',
                                            )
                                        "
                                        @click="toggleDay(day.value)"
                                    >
                                        {{ day.label.slice(0, 3) }}
                                    </Button>
                                </div>
                            </div>
                        </div>

                        <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                            <div class="space-y-3">
                                <Label
                                    class="ml-1 text-[10px] font-black tracking-widest text-slate-400 uppercase"
                                    >Categories</Label
                                >
                                <div
                                    class="max-h-[160px] scrollbar-thin scrollbar-thumb-slate-200 space-y-2 overflow-y-auto pr-2"
                                >
                                    <div
                                        v-for="item in categories"
                                        :key="item.id"
                                        class="flex items-center space-x-2 rounded-lg p-1.5 transition-colors hover:bg-slate-50"
                                    >
                                        <Checkbox
                                            :id="'cat-' + item.id"
                                            :checked="
                                                form.preferred_categories.includes(
                                                    item.id,
                                                )
                                            "
                                            @update:checked="
                                                toggleItem(
                                                    form.preferred_categories,
                                                    item.id,
                                                )
                                            "
                                        />
                                        <label
                                            :for="'cat-' + item.id"
                                            class="text-xs font-medium"
                                            >{{ item.name }}</label
                                        >
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-3">
                                <Label
                                    class="ml-1 text-[10px] font-black tracking-widest text-slate-400 uppercase"
                                    >Classes</Label
                                >
                                <div
                                    class="max-h-[160px] scrollbar-thin scrollbar-thumb-slate-200 space-y-2 overflow-y-auto pr-2"
                                >
                                    <div
                                        v-for="item in schoolClasses"
                                        :key="item.id"
                                        class="flex items-center space-x-2 rounded-lg p-1.5 transition-colors hover:bg-slate-50"
                                    >
                                        <Checkbox
                                            :id="'class-' + item.id"
                                            :checked="
                                                form.preferred_classes.includes(
                                                    item.id,
                                                )
                                            "
                                            @update:checked="
                                                toggleItem(
                                                    form.preferred_classes,
                                                    item.id,
                                                )
                                            "
                                        />
                                        <label
                                            :for="'class-' + item.id"
                                            class="text-xs font-medium"
                                            >{{ item.name }}</label
                                        >
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-3">
                                <Label
                                    class="ml-1 text-[10px] font-black tracking-widest text-slate-400 uppercase"
                                    >Locations (Areas)</Label
                                >
                                <div
                                    class="max-h-[160px] scrollbar-thin scrollbar-thumb-slate-200 space-y-2 overflow-y-auto pr-2"
                                >
                                    <div
                                        v-for="item in locations"
                                        :key="item.id"
                                        class="flex items-center space-x-2 rounded-lg p-1.5 transition-colors hover:bg-slate-50"
                                    >
                                        <Checkbox
                                            :id="'loc-' + item.id"
                                            :checked="
                                                form.preferred_locations.includes(
                                                    item.id,
                                                )
                                            "
                                            @update:checked="
                                                toggleItem(
                                                    form.preferred_locations,
                                                    item.id,
                                                )
                                            "
                                        />
                                        <label
                                            :for="'loc-' + item.id"
                                            class="text-xs font-medium"
                                            >{{ item.name }}</label
                                        >
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-3 border-t border-slate-100 pt-6">
                            <Label
                                class="ml-1 text-[10px] font-black tracking-widest text-slate-400 uppercase"
                                >Preferred Subjects</Label
                            >
                            <div
                                class="grid max-h-[200px] grid-cols-2 gap-x-6 gap-y-3 overflow-y-auto rounded-2xl border border-slate-100 bg-slate-50/30 p-4 md:grid-cols-3 lg:grid-cols-4"
                            >
                                <div
                                    v-for="item in subjects"
                                    :key="item.id"
                                    class="flex items-center space-x-2"
                                >
                                    <Checkbox
                                        :id="'sub-' + item.id"
                                        :checked="
                                            form.preferred_subjects.includes(
                                                item.id,
                                            )
                                        "
                                        @update:checked="
                                            toggleItem(
                                                form.preferred_subjects,
                                                item.id,
                                            )
                                        "
                                    />
                                    <label
                                        :for="'sub-' + item.id"
                                        class="truncate text-xs font-medium"
                                        >{{ item.name }}</label
                                    >
                                </div>
                            </div>
                        </div>

                        <div class="grid gap-6 pt-4 md:grid-cols-3">
                            <div class="space-y-2">
                                <Label
                                    class="ml-1 text-[10px] font-black tracking-widest text-slate-400 uppercase"
                                    >Expected Salary (Min)</Label
                                >
                                <div class="relative">
                                    <DollarSign
                                        class="absolute top-3 left-3.5 h-4 w-4 text-slate-400"
                                    />
                                    <Input
                                        v-model="form.expected_salary_min"
                                        type="number"
                                        placeholder="Min"
                                        class="h-11 rounded-xl pl-10"
                                    />
                                </div>
                            </div>
                            <div class="space-y-2">
                                <Label
                                    class="ml-1 text-[10px] font-black tracking-widest text-slate-400 uppercase"
                                    >Expected Salary (Max)</Label
                                >
                                <div class="relative">
                                    <DollarSign
                                        class="absolute top-3 left-3.5 h-4 w-4 text-slate-400"
                                    />
                                    <Input
                                        v-model="form.expected_salary_max"
                                        type="number"
                                        placeholder="Max"
                                        class="h-11 rounded-xl pl-10"
                                    />
                                </div>
                            </div>
                            <div class="space-y-2">
                                <Label
                                    class="ml-1 text-[10px] font-black tracking-widest text-slate-400 uppercase"
                                    >Available Time</Label
                                >
                                <div class="relative">
                                    <Clock
                                        class="absolute top-3 left-3.5 h-4 w-4 text-slate-400"
                                    />
                                    <Input
                                        v-model="form.available_time"
                                        placeholder="e.g. Afternoon"
                                        class="h-11 rounded-xl pl-10"
                                    />
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Section 4: Education History -->
                <Card
                    class="overflow-hidden rounded-2xl border-slate-200/60 shadow-sm"
                >
                    <CardHeader
                        class="flex flex-row items-center justify-between border-b border-blue-100/50 bg-blue-50/30 p-6"
                    >
                        <div class="flex items-center gap-3">
                            <div
                                class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-blue-100"
                            >
                                <GraduationCap class="h-4 w-4 text-blue-600" />
                            </div>
                            <div>
                                <CardTitle
                                    class="text-lg font-bold text-slate-900"
                                    >Education History</CardTitle
                                >
                                <CardDescription
                                    class="text-xs font-medium tracking-wider text-blue-600/80 uppercase"
                                    >Academic Qualifications</CardDescription
                                >
                            </div>
                        </div>
                        <Button
                            type="button"
                            @click="addEducation"
                            variant="outline"
                            size="sm"
                            class="h-9 rounded-xl border-blue-200 font-bold text-blue-700 hover:bg-blue-50"
                        >
                            <Plus class="mr-2 h-4 w-4" /> Add Record
                        </Button>
                    </CardHeader>
                    <CardContent class="divide-y divide-slate-100 p-0">
                        <div
                            v-for="(edu, index) in form.educations"
                            :key="index"
                            class="group relative p-6"
                        >
                            <div
                                class="absolute top-6 right-6 opacity-0 transition-opacity group-hover:opacity-100"
                            >
                                <Button
                                    type="button"
                                    variant="ghost"
                                    size="icon"
                                    @click="removeEducation(index)"
                                    class="h-8 w-8 rounded-lg text-rose-500 hover:bg-rose-50"
                                >
                                    <Trash2 class="h-4 w-4" />
                                </Button>
                            </div>

                            <div class="grid gap-6 md:grid-cols-2">
                                <div class="space-y-2">
                                    <Label
                                        class="ml-1 text-[10px] font-black tracking-widest text-slate-400 uppercase"
                                        >Degree Title</Label
                                    >
                                    <Input
                                        v-model="edu.degree"
                                        placeholder="e.g. B.Sc in CSE"
                                        class="h-10 rounded-xl"
                                        required
                                    />
                                </div>
                                <div class="space-y-2">
                                    <Label
                                        class="ml-1 text-[10px] font-black tracking-widest text-slate-400 uppercase"
                                        >Institute / University</Label
                                    >
                                    <Input
                                        v-model="edu.institute"
                                        placeholder="University name"
                                        class="h-10 rounded-xl"
                                        required
                                    />
                                </div>
                                <div class="space-y-2 lg:col-span-1">
                                    <Label
                                        class="ml-1 text-[10px] font-black tracking-widest text-slate-400 uppercase"
                                        >Department / Group</Label
                                    >
                                    <Input
                                        v-model="edu.department"
                                        placeholder="Department"
                                        class="h-10 rounded-xl"
                                    />
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="space-y-2">
                                        <Label
                                            class="ml-1 text-[10px] font-black tracking-widest text-slate-400 uppercase"
                                            >Graduation Year</Label
                                        >
                                        <Input
                                            v-model="edu.graduation_year"
                                            type="number"
                                            placeholder="202X"
                                            class="h-10 rounded-xl"
                                        />
                                    </div>
                                    <div class="space-y-2">
                                        <Label
                                            class="ml-1 text-[10px] font-black tracking-widest text-slate-400 uppercase"
                                            >Result / CGPA</Label
                                        >
                                        <Input
                                            v-model="edu.result"
                                            placeholder="e.g. 3.80"
                                            class="h-10 rounded-xl"
                                        />
                                        <InputError
                                            :message="
                                                (form.errors as any)[
                                                    `educations.${index}.result`
                                                ]
                                            "
                                        />
                                    </div>
                                </div>
                            </div>

                            <InputError
                                :message="
                                    (form.errors as any)[
                                        `educations.${index}.degree`
                                    ]
                                "
                            />
                            <InputError
                                :message="
                                    (form.errors as any)[
                                        `educations.${index}.institute`
                                    ]
                                "
                            />

                            <div class="mt-4 flex items-center space-x-2">
                                <Checkbox
                                    :id="'current-' + index"
                                    v-model:checked="edu.is_current"
                                />
                                <label
                                    :for="'current-' + index"
                                    class="text-xs font-bold tracking-tight text-slate-600 uppercase"
                                    >Currently Studying Here</label
                                >
                            </div>
                        </div>

                        <div
                            v-if="form.educations.length === 0"
                            class="p-12 text-center"
                        >
                            <div
                                class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-slate-50"
                            >
                                <GraduationCap class="h-6 w-6 text-slate-300" />
                            </div>
                            <p class="text-sm font-medium text-slate-400">
                                No education records added yet.
                            </p>
                            <Button
                                type="button"
                                @click="addEducation"
                                variant="link"
                                class="mt-2 font-bold text-indigo-600"
                                >Add your first record</Button
                            >
                        </div>
                    </CardContent>
                </Card>

                <!-- Form Actions -->
                <div class="flex items-center justify-end gap-3 pt-4">
                    <Button
                        type="button"
                        variant="ghost"
                        as-child
                        class="h-11 rounded-xl px-8 font-bold text-slate-400"
                    >
                        <Link href="/admin/tutors">Cancel</Link>
                    </Button>
                    <Button
                        type="submit"
                        class="h-11 rounded-xl bg-indigo-600 px-12 font-bold shadow-lg shadow-indigo-200 transition-all hover:scale-[1.02] hover:bg-indigo-700 active:scale-95"
                        :disabled="form.processing"
                    >
                        <span
                            v-if="form.processing"
                            class="mr-2 h-4 w-4 animate-spin rounded-full border-2 border-white/20 border-t-white"
                        ></span>
                        <CheckCircle2 v-else class="mr-2 h-4 w-4" />
                        {{
                            form.processing
                                ? 'Registering Tutor...'
                                : 'Save Profile'
                        }}
                    </Button>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>

<style scoped>
/* Scrollbar cleanup */
.scrollbar-thin::-webkit-scrollbar {
    width: 4px;
}
.scrollbar-thin::-webkit-scrollbar-track {
    background: transparent;
}
.scrollbar-thin::-webkit-scrollbar-thumb {
    background: #e2e8f0;
    border-radius: 20px;
}
.scrollbar-thin::-webkit-scrollbar-thumb:hover {
    background: #cbd5e1;
}

/* Entry Animation */
form > * {
    animation: slideUp 0.5s ease-out forwards;
    opacity: 0;
}
form > *:nth-child(1) {
    animation-delay: 0.1s;
}
form > *:nth-child(2) {
    animation-delay: 0.2s;
}
form > *:nth-child(3) {
    animation-delay: 0.3s;
}
form > *:nth-child(4) {
    animation-delay: 0.4s;
}
form > *:nth-child(5) {
    animation-delay: 0.5s;
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(15px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>
