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
                        Register Complete Guardian
                    </h1>
                    <p class="text-sm font-medium text-slate-500">
                        Create account and set profile details in one step.
                    </p>
                </div>

                <Button
                    variant="ghost"
                    as-child
                    class="group rounded-xl transition-all hover:bg-slate-100"
                >
                    <Link href="/admin/guardians" class="flex items-center">
                        <ArrowLeft
                            class="mr-2 h-4 w-4 transition-transform group-hover:-translate-x-1"
                        />
                        Back to Directory
                    </Link>
                </Button>
            </div>

            <form @submit.prevent="submit" class="space-y-8 pb-10">
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

                <!-- Section 1: Security & Status -->
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
                                >Account Holder Name</Label
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
                                <Contact class="h-4 w-4 text-emerald-600" />
                            </div>
                            <div>
                                <CardTitle
                                    class="text-lg font-bold text-slate-900"
                                    >Guardian Details</CardTitle
                                >
                                <CardDescription
                                    class="text-xs font-medium tracking-wider text-emerald-600/80 uppercase"
                                    >Identity & Professional</CardDescription
                                >
                            </div>
                        </div>
                    </CardHeader>
                    <CardContent class="grid gap-6 p-6 md:grid-cols-2">
                        <div class="space-y-2">
                            <Label
                                for="guardian_name"
                                class="ml-1 text-[10px] font-black tracking-widest text-slate-400 uppercase"
                                >Public Profile Name</Label
                            >
                            <div class="relative">
                                <User
                                    class="absolute top-3 left-3.5 h-4 w-4 text-slate-400"
                                />
                                <Input
                                    v-model="form.guardian_name"
                                    placeholder="Name to display on profile"
                                    class="h-11 rounded-xl pl-10"
                                />
                            </div>
                            <InputError :message="form.errors.guardian_name" />
                        </div>

                        <div class="space-y-2">
                            <Label
                                for="occupation"
                                class="ml-1 text-[10px] font-black tracking-widest text-slate-400 uppercase"
                                >Occupation</Label
                            >
                            <div class="relative">
                                <Briefcase
                                    class="absolute top-3 left-3.5 h-4 w-4 text-slate-400"
                                />
                                <Input
                                    v-model="form.occupation"
                                    placeholder="Professional title"
                                    class="h-11 rounded-xl pl-10"
                                />
                            </div>
                            <InputError :message="form.errors.occupation" />
                        </div>

                        <div class="space-y-2 md:col-span-2">
                            <Label
                                for="phone_alt"
                                class="ml-1 text-[10px] font-black tracking-widest text-slate-400 uppercase"
                                >Alternative Contact Number</Label
                            >
                            <div class="relative">
                                <Phone
                                    class="absolute top-3 left-3.5 h-4 w-4 text-slate-400"
                                />
                                <Input
                                    v-model="form.phone_alt"
                                    placeholder="Secondary contact (optional)"
                                    class="h-11 rounded-xl pl-10"
                                />
                            </div>
                            <InputError :message="form.errors.phone_alt" />
                        </div>
                    </CardContent>
                </Card>

                <!-- Section 3: Location & Admin Notes -->
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
                                <MapPin class="h-4 w-4 text-amber-600" />
                            </div>
                            <div>
                                <CardTitle
                                    class="text-lg font-bold text-slate-900"
                                    >Background & Location</CardTitle
                                >
                                <CardDescription
                                    class="text-xs font-medium tracking-wider text-amber-600/80 uppercase"
                                    >Residential & Workspace</CardDescription
                                >
                            </div>
                        </div>
                    </CardHeader>
                    <CardContent class="grid gap-6 p-6 md:grid-cols-2">
                        <div class="space-y-2">
                            <Label
                                for="address"
                                class="ml-1 text-[10px] font-black tracking-widest text-slate-400 uppercase"
                                >Physical Address</Label
                            >
                            <div class="relative">
                                <MapPin
                                    class="absolute top-3 left-3.5 h-4 w-4 shrink-0 text-slate-400"
                                />
                                <Textarea
                                    v-model="form.address"
                                    rows="3"
                                    placeholder="Full residential or workspace address..."
                                    class="resize-none rounded-2xl py-3 pl-10"
                                />
                            </div>
                            <InputError :message="form.errors.address" />
                        </div>

                        <div class="space-y-2">
                            <Label
                                for="notes"
                                class="ml-1 text-[10px] font-black tracking-widest text-slate-400 uppercase"
                                >Administrative Notes</Label
                            >
                            <div class="relative">
                                <FileText
                                    class="absolute top-3 left-3.5 h-4 w-4 shrink-0 text-slate-400"
                                />
                                <Textarea
                                    v-model="form.notes"
                                    rows="3"
                                    placeholder="Internal observations, referral source, etc..."
                                    class="resize-none rounded-2xl py-3 pl-10"
                                />
                            </div>
                            <InputError :message="form.errors.notes" />
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
                        <Link href="/admin/guardians">Cancel</Link>
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
                            form.processing ? 'Registering...' : 'Save Profile'
                        }}
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
