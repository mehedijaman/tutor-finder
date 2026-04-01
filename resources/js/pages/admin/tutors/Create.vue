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
    UserPlus
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
    educations: [] as any[]
});

const addEducation = () => {
    form.educations.push({
        id: null,
        degree: '',
        institute: '',
        department: '',
        graduation_year: new Date().getFullYear(),
        result: '',
        is_current: false
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
        <div class="space-y-8 p-4 sm:p-6 lg:p-10 max-w-5xl mx-auto">
            <!-- Header Section -->
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between mb-2">
                <div class="space-y-1">
                    <h1 class="text-3xl font-bold tracking-tight text-slate-900 flex items-center gap-3">
                        <div class="h-10 w-10 rounded-xl bg-indigo-600 flex items-center justify-center shadow-lg shadow-indigo-200 shrink-0">
                            <UserPlus class="h-6 w-6 text-white" />
                        </div>
                        Register Complete Tutor
                    </h1>
                    <p class="text-sm font-medium text-slate-500">
                        Create account, set preferences and add education history in one step.
                    </p>
                </div>
                
                <Button variant="ghost" as-child class="rounded-xl hover:bg-slate-100 group transition-all">
                    <Link href="/admin/tutors" class="flex items-center">
                        <ChevronLeft class="mr-2 h-4 w-4 transition-transform group-hover:-translate-x-1" />
                        Back to Directory
                    </Link>
                </Button>
            </div>

            <form @submit.prevent="submit" class="space-y-8 pb-20">
                <div v-if="Object.keys(form.errors).length > 0" class="p-4 bg-red-50 border border-red-100 rounded-2xl flex items-start gap-3">
                    <Shield class="h-5 w-5 text-red-500 shrink-0 mt-0.5" />
                    <div class="space-y-1">
                        <p class="text-sm font-bold text-red-800">Please correct the following errors:</p>
                        <ul class="text-xs text-red-600 list-disc list-inside">
                            <li v-for="(err, key) in form.errors" :key="key">{{ err }}</li>
                        </ul>
                    </div>
                </div>
                <!-- Section 1: Account Credentials -->
                <Card class="rounded-2xl border-slate-200/60 shadow-sm overflow-hidden">
                    <CardHeader class="bg-indigo-50/30 border-b border-indigo-100/50 p-6">
                        <div class="flex items-center gap-3">
                            <div class="h-8 w-8 rounded-lg bg-indigo-100 flex items-center justify-center shrink-0">
                                <Lock class="h-4 w-4 text-indigo-600" />
                            </div>
                            <div>
                                <CardTitle class="text-lg font-bold text-slate-900">Security & Status</CardTitle>
                                <CardDescription class="text-xs font-medium uppercase tracking-wider text-indigo-600/80">Account Authentication</CardDescription>
                            </div>
                        </div>
                    </CardHeader>
                    <CardContent class="grid gap-6 p-6 md:grid-cols-2">
                        <div class="space-y-2">
                            <Label for="name" class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">Full Name</Label>
                            <div class="relative">
                                <User class="absolute left-3.5 top-3 h-4 w-4 text-slate-400" />
                                <Input v-model="form.name" placeholder="Full legal name" class="pl-10 h-11 rounded-xl" required />
                            </div>
                            <InputError :message="form.errors.name" />
                        </div>

                        <div class="space-y-2">
                            <Label for="status" class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">Initial Status</Label>
                            <Select v-model="form.status">
                                <SelectTrigger class="h-11 rounded-xl">
                                    <Shield class="mr-2 h-4 w-4 text-slate-400" />
                                    <SelectValue placeholder="Select status" />
                                </SelectTrigger>
                                <SelectContent class="rounded-xl">
                                    <SelectItem value="active">Active</SelectItem>
                                    <SelectItem value="suspended">Suspended</SelectItem>
                                    <SelectItem value="pending_verification">Pending Verification</SelectItem>
                                </SelectContent>
                            </Select>
                            <InputError :message="form.errors.status" />
                        </div>

                        <div class="space-y-2">
                            <Label for="email" class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">Email Address</Label>
                            <div class="relative">
                                <Mail class="absolute left-3.5 top-3 h-4 w-4 text-slate-400" />
                                <Input v-model="form.email" type="email" placeholder="email@example.com" class="pl-10 h-11 rounded-xl" />
                            </div>
                            <InputError :message="form.errors.email" />
                        </div>

                        <div class="space-y-2">
                            <Label for="phone" class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">Phone Number</Label>
                            <div class="relative">
                                <Phone class="absolute left-3.5 top-3 h-4 w-4 text-slate-400" />
                                <Input v-model="form.phone" placeholder="01XXXXXXXXX" class="pl-10 h-11 rounded-xl" required />
                            </div>
                            <InputError :message="form.errors.phone" />
                        </div>

                        <div class="space-y-2">
                            <Label for="password" class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">Temporary Password</Label>
                            <div class="relative">
                                <Lock class="absolute left-3.5 top-3 h-4 w-4 text-slate-400" />
                                <Input v-model="form.password" type="password" class="pl-10 h-11 rounded-xl" required />
                            </div>
                            <InputError :message="form.errors.password" />
                        </div>

                        <div class="space-y-2">
                            <Label for="password_confirmation" class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">Confirm Password</Label>
                            <div class="relative">
                                <CheckCircle2 class="absolute left-3.5 top-3 h-4 w-4 text-slate-400" />
                                <Input v-model="form.password_confirmation" type="password" class="pl-10 h-11 rounded-xl" required />
                            </div>
                            <InputError :message="form.errors.password_confirmation" />
                        </div>
                    </CardContent>
                </Card>

                <!-- Section 2: Personal Profile Details -->
                <Card class="rounded-2xl border-slate-200/60 shadow-sm overflow-hidden">
                    <CardHeader class="bg-emerald-50/30 border-b border-emerald-100/50 p-6">
                        <div class="flex items-center gap-3">
                            <div class="h-8 w-8 rounded-lg bg-emerald-100 flex items-center justify-center shrink-0">
                                <User class="h-4 w-4 text-emerald-600" />
                            </div>
                            <div>
                                <CardTitle class="text-lg font-bold text-slate-900">Personal Information</CardTitle>
                                <CardDescription class="text-xs font-medium uppercase tracking-wider text-emerald-600/80">Bio & Identity</CardDescription>
                            </div>
                        </div>
                    </CardHeader>
                    <CardContent class="grid gap-6 p-6 md:grid-cols-2">
                        <div class="space-y-2">
                            <Label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">Gender</Label>
                            <Select v-model="form.gender">
                                <SelectTrigger class="h-11 rounded-xl">
                                    <SelectValue placeholder="Select gender" />
                                </SelectTrigger>
                                <SelectContent class="rounded-xl">
                                    <SelectItem value="male">Male</SelectItem>
                                    <SelectItem value="female">Female</SelectItem>
                                    <SelectItem value="other">Other</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <div class="space-y-2">
                            <Label for="date_of_birth" class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">Date of Birth</Label>
                            <div class="relative">
                                <Calendar class="absolute left-3.5 top-3 h-4 w-4 text-slate-400" />
                                <Input v-model="form.date_of_birth" type="date" class="pl-10 h-11 rounded-xl" />
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label for="nid_no" class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">Identity (NID/Passport)</Label>
                            <div class="relative">
                                <Contact class="absolute left-3.5 top-3 h-4 w-4 text-slate-400" />
                                <Input v-model="form.nid_no" placeholder="Identification number" class="pl-10 h-11 rounded-xl" />
                            </div>
                        </div>

                        <div class="space-y-2 md:col-span-2">
                            <Label for="bio" class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">Professional Bio</Label>
                            <Textarea v-model="form.bio" rows="3" placeholder="Summary of tutoring experience..." class="rounded-2xl resize-none" />
                        </div>

                        <div class="space-y-2">
                            <Label for="present_address" class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">Present Address</Label>
                            <Textarea v-model="form.present_address" rows="2" class="rounded-xl resize-none py-3" />
                        </div>

                        <div class="space-y-2">
                            <Label for="permanent_address" class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">Permanent Address</Label>
                            <Textarea v-model="form.permanent_address" rows="2" class="rounded-xl resize-none py-3" />
                        </div>
                    </CardContent>
                </Card>

                <!-- Section 3: Tuition Preferences -->
                <Card class="rounded-2xl border-slate-200/60 shadow-sm overflow-hidden">
                    <CardHeader class="bg-amber-50/30 border-b border-amber-100/50 p-6">
                        <div class="flex items-center gap-3">
                            <div class="h-8 w-8 rounded-lg bg-amber-100 flex items-center justify-center shrink-0">
                                <BookOpen class="h-4 w-4 text-amber-600" />
                            </div>
                            <div>
                                <CardTitle class="text-lg font-bold text-slate-900">Tuition Preferences</CardTitle>
                                <CardDescription class="text-xs font-medium uppercase tracking-wider text-amber-600/80">Subjects, Locations & Rates</CardDescription>
                            </div>
                        </div>
                    </CardHeader>
                    <CardContent class="grid gap-8 p-6">
                        <!-- Multi Select Groups -->
                        <div class="grid gap-6 md:grid-cols-2">
                            <div class="space-y-3">
                                <Label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">Preferred Methods</Label>
                                <div class="grid grid-cols-2 gap-3 bg-slate-50/50 p-4 rounded-2xl border border-slate-100">
                                    <div v-for="type in tuitionTypes" :key="type.id" class="flex items-center space-x-2">
                                        <Checkbox :id="'type-'+type.id" :checked="form.preferred_tuition_types.includes(type.id)" @update:checked="toggleItem(form.preferred_tuition_types, type.id)" />
                                        <label :for="'type-'+type.id" class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">{{ type.name }}</label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="space-y-3">
                                <Label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">Available Days</Label>
                                <div class="flex flex-wrap gap-2">
                                    <Button 
                                        v-for="day in dayOptions" 
                                        :key="day.value" 
                                        type="button"
                                        variant="outline" 
                                        size="sm"
                                        :class="cn('rounded-lg transition-all px-3 font-semibold h-8 text-[11px]', form.available_days.includes(day.value) ? 'bg-indigo-600 text-white border-transparent shadow-md shadow-indigo-100/50' : 'bg-white text-slate-600 hover:bg-slate-50')"
                                        @click="toggleDay(day.value)"
                                    >
                                        {{ day.label.slice(0, 3) }}
                                    </Button>
                                </div>
                            </div>
                        </div>

                        <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                            <div class="space-y-3">
                                <Label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">Categories</Label>
                                <div class="max-h-[160px] overflow-y-auto pr-2 space-y-2 scrollbar-thin scrollbar-thumb-slate-200">
                                    <div v-for="item in categories" :key="item.id" class="flex items-center space-x-2 p-1.5 hover:bg-slate-50 rounded-lg transition-colors">
                                        <Checkbox :id="'cat-'+item.id" :checked="form.preferred_categories.includes(item.id)" @update:checked="toggleItem(form.preferred_categories, item.id)" />
                                        <label :for="'cat-'+item.id" class="text-xs font-medium">{{ item.name }}</label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="space-y-3">
                                <Label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">Classes</Label>
                                <div class="max-h-[160px] overflow-y-auto pr-2 space-y-2 scrollbar-thin scrollbar-thumb-slate-200">
                                    <div v-for="item in schoolClasses" :key="item.id" class="flex items-center space-x-2 p-1.5 hover:bg-slate-50 rounded-lg transition-colors">
                                        <Checkbox :id="'class-'+item.id" :checked="form.preferred_classes.includes(item.id)" @update:checked="toggleItem(form.preferred_classes, item.id)" />
                                        <label :for="'class-'+item.id" class="text-xs font-medium">{{ item.name }}</label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="space-y-3">
                                <Label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">Locations (Areas)</Label>
                                <div class="max-h-[160px] overflow-y-auto pr-2 space-y-2 scrollbar-thin scrollbar-thumb-slate-200">
                                    <div v-for="item in locations" :key="item.id" class="flex items-center space-x-2 p-1.5 hover:bg-slate-50 rounded-lg transition-colors">
                                        <Checkbox :id="'loc-'+item.id" :checked="form.preferred_locations.includes(item.id)" @update:checked="toggleItem(form.preferred_locations, item.id)" />
                                        <label :for="'loc-'+item.id" class="text-xs font-medium">{{ item.name }}</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-3 border-t border-slate-100 pt-6">
                            <Label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">Preferred Subjects</Label>
                            <div class="max-h-[200px] overflow-y-auto bg-slate-50/30 p-4 rounded-2xl border border-slate-100 grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-x-6 gap-y-3">
                                <div v-for="item in subjects" :key="item.id" class="flex items-center space-x-2">
                                    <Checkbox :id="'sub-'+item.id" :checked="form.preferred_subjects.includes(item.id)" @update:checked="toggleItem(form.preferred_subjects, item.id)" />
                                    <label :for="'sub-'+item.id" class="text-xs font-medium truncate">{{ item.name }}</label>
                                </div>
                            </div>
                        </div>

                        <div class="grid gap-6 md:grid-cols-3 pt-4">
                            <div class="space-y-2">
                                <Label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">Expected Salary (Min)</Label>
                                <div class="relative">
                                    <DollarSign class="absolute left-3.5 top-3 h-4 w-4 text-slate-400" />
                                    <Input v-model="form.expected_salary_min" type="number" placeholder="Min" class="pl-10 h-11 rounded-xl" />
                                </div>
                            </div>
                            <div class="space-y-2">
                                <Label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">Expected Salary (Max)</Label>
                                <div class="relative">
                                    <DollarSign class="absolute left-3.5 top-3 h-4 w-4 text-slate-400" />
                                    <Input v-model="form.expected_salary_max" type="number" placeholder="Max" class="pl-10 h-11 rounded-xl" />
                                </div>
                            </div>
                            <div class="space-y-2">
                                <Label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">Available Time</Label>
                                <div class="relative">
                                    <Clock class="absolute left-3.5 top-3 h-4 w-4 text-slate-400" />
                                    <Input v-model="form.available_time" placeholder="e.g. Afternoon" class="pl-10 h-11 rounded-xl" />
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Section 4: Education History -->
                <Card class="rounded-2xl border-slate-200/60 shadow-sm overflow-hidden">
                    <CardHeader class="bg-blue-50/30 border-b border-blue-100/50 p-6 flex flex-row items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="h-8 w-8 rounded-lg bg-blue-100 flex items-center justify-center shrink-0">
                                <GraduationCap class="h-4 w-4 text-blue-600" />
                            </div>
                            <div>
                                <CardTitle class="text-lg font-bold text-slate-900">Education History</CardTitle>
                                <CardDescription class="text-xs font-medium uppercase tracking-wider text-blue-600/80">Academic Qualifications</CardDescription>
                            </div>
                        </div>
                        <Button type="button" @click="addEducation" variant="outline" size="sm" class="rounded-xl border-blue-200 text-blue-700 hover:bg-blue-50 h-9 font-bold">
                            <Plus class="mr-2 h-4 w-4" /> Add Record
                        </Button>
                    </CardHeader>
                    <CardContent class="p-0 divide-y divide-slate-100">
                        <div v-for="(edu, index) in form.educations" :key="index" class="p-6 relative group">
                            <div class="absolute top-6 right-6 opacity-0 group-hover:opacity-100 transition-opacity">
                                <Button type="button" variant="ghost" size="icon" @click="removeEducation(index)" class="h-8 w-8 text-rose-500 hover:bg-rose-50 rounded-lg">
                                    <Trash2 class="h-4 w-4" />
                                </Button>
                            </div>
                            
                            <div class="grid gap-6 md:grid-cols-2">
                                <div class="space-y-2">
                                    <Label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">Degree Title</Label>
                                    <Input v-model="edu.degree" placeholder="e.g. B.Sc in CSE" class="h-10 rounded-xl" required />
                                </div>
                                <div class="space-y-2">
                                    <Label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">Institute / University</Label>
                                    <Input v-model="edu.institute" placeholder="University name" class="h-10 rounded-xl" required />
                                </div>
                                <div class="space-y-2 lg:col-span-1">
                                    <Label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">Department / Group</Label>
                                    <Input v-model="edu.department" placeholder="Department" class="h-10 rounded-xl" />
                                </div>
                                <div class="grid gap-4 grid-cols-2">
                                    <div class="space-y-2">
                                        <Label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">Graduation Year</Label>
                                        <Input v-model="edu.graduation_year" type="number" placeholder="202X" class="h-10 rounded-xl" />
                                    </div>
                                    <div class="space-y-2">
                                        <Label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">Result / CGPA</Label>
                                        <Input v-model="edu.result" placeholder="e.g. 3.80" class="h-10 rounded-xl" />
                                        <InputError :message="(form.errors as any)[`educations.${index}.result`]" />
                                    </div>
                                </div>
                            </div>
                            
                            <InputError :message="(form.errors as any)[`educations.${index}.degree`]" />
                            <InputError :message="(form.errors as any)[`educations.${index}.institute`]" />
                            
                            <div class="mt-4 flex items-center space-x-2">
                                <Checkbox :id="'current-'+index" v-model:checked="edu.is_current" />
                                <label :for="'current-'+index" class="text-xs font-bold text-slate-600 uppercase tracking-tight">Currently Studying Here</label>
                            </div>
                        </div>
                        
                        <div v-if="form.educations.length === 0" class="p-12 text-center">
                            <div class="mx-auto w-12 h-12 rounded-full bg-slate-50 flex items-center justify-center mb-3">
                                <GraduationCap class="h-6 w-6 text-slate-300" />
                            </div>
                            <p class="text-sm font-medium text-slate-400">No education records added yet.</p>
                            <Button type="button" @click="addEducation" variant="link" class="mt-2 text-indigo-600 font-bold">Add your first record</Button>
                        </div>
                    </CardContent>
                </Card>

                <!-- Form Actions -->
                <div class="flex items-center justify-end gap-3 pt-4">
                    <Button type="button" variant="ghost" as-child class="rounded-xl px-8 h-11 font-bold text-slate-400">
                        <Link href="/admin/tutors">Cancel</Link>
                    </Button>
                    <Button 
                        type="submit" 
                        class="rounded-xl px-12 h-11 font-bold bg-indigo-600 hover:bg-indigo-700 shadow-lg shadow-indigo-200 transition-all hover:scale-[1.02] active:scale-95" 
                        :disabled="form.processing"
                    >
                        <span v-if="form.processing" class="mr-2 h-4 w-4 border-2 border-white/20 border-t-white rounded-full animate-spin"></span>
                        <CheckCircle2 v-else class="mr-2 h-4 w-4" />
                        {{ form.processing ? 'Registering Tutor...' : 'Save Profile' }}
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
form > *:nth-child(1) { animation-delay: 0.1s; }
form > *:nth-child(2) { animation-delay: 0.2s; }
form > *:nth-child(3) { animation-delay: 0.3s; }
form > *:nth-child(4) { animation-delay: 0.4s; }
form > *:nth-child(5) { animation-delay: 0.5s; }

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
