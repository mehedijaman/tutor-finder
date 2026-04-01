<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { 
    ArrowLeft, 
    Briefcase,
    CheckCircle2,
    Contact,
    FileText,
    Lock,
    Mail,
    MapPin,
    Phone,
    Shield,
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

const breadcrumbs = [
    { title: 'Guardian Directory', href: '/admin/guardians' },
    { title: 'Register Guardian', href: '/admin/guardians/create' },
];

const form = useForm({
    name: '',
    guardian_name: '',
    email: '',
    phone: '',
    password: '',
    password_confirmation: '',
    status: 'active',
    occupation: '',
    address: '',
    phone_alt: '',
    notes: '',
});

const submit = () => {
    form.post('/admin/guardians', {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <Head title="Register Guardian" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-8 p-4 sm:p-6 lg:p-10 max-w-5xl mx-auto">
            <!-- Header Section -->
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between mb-2">
                <div class="space-y-1">
                    <h1 class="text-3xl font-bold tracking-tight text-slate-900 flex items-center gap-3">
                        <div class="h-10 w-10 rounded-xl bg-indigo-600 flex items-center justify-center shadow-lg shadow-indigo-200 shrink-0">
                            <UserPlus class="h-6 w-6 text-white" />
                        </div>
                        Register Complete Guardian
                    </h1>
                    <p class="text-sm font-medium text-slate-500">
                        Create account and set profile details in one step.
                    </p>
                </div>
                
                <Button variant="ghost" as-child class="rounded-xl hover:bg-slate-100 group transition-all">
                    <Link href="/admin/guardians" class="flex items-center">
                        <ArrowLeft class="mr-2 h-4 w-4 transition-transform group-hover:-translate-x-1" />
                        Back to Directory
                    </Link>
                </Button>
            </div>

            <form @submit.prevent="submit" class="space-y-8 pb-10">
                <div v-if="Object.keys(form.errors).length > 0" class="p-4 bg-red-50 border border-red-100 rounded-2xl flex items-start gap-3">
                    <Shield class="h-5 w-5 text-red-500 shrink-0 mt-0.5" />
                    <div class="space-y-1">
                        <p class="text-sm font-bold text-red-800">Please correct the following errors:</p>
                        <ul class="text-xs text-red-600 list-disc list-inside">
                            <li v-for="(err, key) in form.errors" :key="key">{{ err }}</li>
                        </ul>
                    </div>
                </div>

                <!-- Section 1: Security & Status -->
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
                            <Label for="name" class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">Account Holder Name</Label>
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
                                <Contact class="h-4 w-4 text-emerald-600" />
                            </div>
                            <div>
                                <CardTitle class="text-lg font-bold text-slate-900">Guardian Details</CardTitle>
                                <CardDescription class="text-xs font-medium uppercase tracking-wider text-emerald-600/80">Identity & Professional</CardDescription>
                            </div>
                        </div>
                    </CardHeader>
                    <CardContent class="grid gap-6 p-6 md:grid-cols-2">
                        <div class="space-y-2">
                            <Label for="guardian_name" class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">Public Profile Name</Label>
                            <div class="relative">
                                <User class="absolute left-3.5 top-3 h-4 w-4 text-slate-400" />
                                <Input v-model="form.guardian_name" placeholder="Name to display on profile" class="pl-10 h-11 rounded-xl" />
                            </div>
                            <InputError :message="form.errors.guardian_name" />
                        </div>

                        <div class="space-y-2">
                            <Label for="occupation" class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">Occupation</Label>
                            <div class="relative">
                                <Briefcase class="absolute left-3.5 top-3 h-4 w-4 text-slate-400" />
                                <Input v-model="form.occupation" placeholder="Professional title" class="pl-10 h-11 rounded-xl" />
                            </div>
                            <InputError :message="form.errors.occupation" />
                        </div>

                        <div class="space-y-2 md:col-span-2">
                            <Label for="phone_alt" class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">Alternative Contact Number</Label>
                            <div class="relative">
                                <Phone class="absolute left-3.5 top-3 h-4 w-4 text-slate-400" />
                                <Input v-model="form.phone_alt" placeholder="Secondary contact (optional)" class="pl-10 h-11 rounded-xl" />
                            </div>
                            <InputError :message="form.errors.phone_alt" />
                        </div>
                    </CardContent>
                </Card>

                <!-- Section 3: Location & Admin Notes -->
                <Card class="rounded-2xl border-slate-200/60 shadow-sm overflow-hidden">
                    <CardHeader class="bg-amber-50/30 border-b border-amber-100/50 p-6">
                        <div class="flex items-center gap-3">
                            <div class="h-8 w-8 rounded-lg bg-amber-100 flex items-center justify-center shrink-0">
                                <MapPin class="h-4 w-4 text-amber-600" />
                            </div>
                            <div>
                                <CardTitle class="text-lg font-bold text-slate-900">Background & Location</CardTitle>
                                <CardDescription class="text-xs font-medium uppercase tracking-wider text-amber-600/80">Residential & Workspace</CardDescription>
                            </div>
                        </div>
                    </CardHeader>
                    <CardContent class="grid gap-6 p-6 md:grid-cols-2">
                        <div class="space-y-2">
                            <Label for="address" class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">Physical Address</Label>
                            <div class="relative">
                                <MapPin class="absolute left-3.5 top-3 h-4 w-4 text-slate-400 shrink-0" />
                                <Textarea v-model="form.address" rows="3" placeholder="Full residential or workspace address..." class="pl-10 rounded-2xl resize-none py-3" />
                            </div>
                            <InputError :message="form.errors.address" />
                        </div>

                        <div class="space-y-2">
                            <Label for="notes" class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">Administrative Notes</Label>
                            <div class="relative">
                                <FileText class="absolute left-3.5 top-3 h-4 w-4 text-slate-400 shrink-0" />
                                <Textarea v-model="form.notes" rows="3" placeholder="Internal observations, referral source, etc..." class="pl-10 rounded-2xl resize-none py-3" />
                            </div>
                            <InputError :message="form.errors.notes" />
                        </div>
                    </CardContent>
                </Card>

                <!-- Form Actions -->
                <div class="flex items-center justify-end gap-3 pt-4">
                    <Button type="button" variant="ghost" as-child class="rounded-xl px-8 h-11 font-bold text-slate-400">
                        <Link href="/admin/guardians">Cancel</Link>
                    </Button>
                    <Button 
                        type="submit" 
                        class="rounded-xl px-12 h-11 font-bold bg-indigo-600 hover:bg-indigo-700 shadow-lg shadow-indigo-200 transition-all hover:scale-[1.02] active:scale-95" 
                        :disabled="form.processing"
                    >
                        <span v-if="form.processing" class="mr-2 h-4 w-4 border-2 border-white/20 border-t-white rounded-full animate-spin"></span>
                        <CheckCircle2 v-else class="mr-2 h-4 w-4" />
                        {{ form.processing ? 'Registering...' : 'Save Profile' }}
                    </Button>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>

<style scoped>
/* Entry Animation */
form > * {
    animation: slideUp 0.5s ease-out forwards;
    opacity: 0;
}
form > *:nth-child(1) { animation-delay: 0.1s; }
form > *:nth-child(2) { animation-delay: 0.2s; }
form > *:nth-child(3) { animation-delay: 0.3s; }
form > *:nth-child(4) { animation-delay: 0.4s; }

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
