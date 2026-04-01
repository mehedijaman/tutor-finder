<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import {
    ArrowLeft,
    CheckCircle2,
    DollarSign,
    Info,
    User,
    Calculator,
    Briefcase,
    BadgeCheck,
} from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Badge } from '@/components/ui/badge';
import AdminLayout from '@/layouts/AdminLayout.vue';

const props = defineProps({
    job: { type: Object, required: true },
    tutor: { type: Object, required: true },
    application: { type: Object, required: true },
    finance: { type: Object, required: true },
});

const breadcrumbs = [
    { title: 'Jobs', href: '/admin/jobs' },
    { title: 'Settle Request', href: '#' },
];

const form = useForm({
    salary_base_amount: Number(props.job.salary_amount || 0),
});

const commissionRate = computed(() => Number(props.finance.commission_rate || 0.6));
const commissionAmount = computed(() => Number(form.salary_base_amount || 0) * commissionRate.value);

function submit() {
    form.post(`/admin/jobs/${props.job.id}/settle`);
}
</script>

<template>
    <Head title="Settle Direct Request" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto max-w-4xl space-y-6 p-4 sm:p-6 lg:p-8">
            <div class="flex items-center gap-4">
                <Link
                    href="/admin/jobs"
                    class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-white shadow-sm transition-all hover:bg-slate-50 hover:shadow-md"
                >
                    <ArrowLeft class="h-5 w-5 text-slate-600" />
                </Link>
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-slate-900">
                        Finalize Settlement
                    </h1>
                    <p class="text-sm text-slate-500">
                        Admin override for direct recruitment of {{ tutor.name }} for
                        "{{ job.title }}"
                    </p>
                </div>
            </div>

            <div v-if="Object.keys(form.errors).length > 0" class="rounded-xl border border-red-100 bg-red-50 p-4 text-sm text-red-800 animate-in fade-in slide-in-from-top-2">
                <div class="flex items-start gap-3">
                    <Info class="mt-0.5 h-4 w-4 shrink-0 text-red-600" />
                    <div>
                        <p class="font-semibold">Settlement Failed</p>
                        <ul class="mt-1 list-disc list-inside space-y-1">
                            <li v-for="(error, key) in form.errors" :key="key">
                                {{ error }}
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <Card class="overflow-hidden border-slate-200/80 shadow-lg shadow-slate-200/50">
                <CardHeader class="bg-slate-50/50 pb-6 border-b border-slate-100">
                    <div class="flex items-center justify-between">
                        <div class="space-y-1">
                            <CardTitle class="flex items-center gap-2 text-lg">
                                <Calculator class="h-5 w-5 text-blue-500" />
                                Financial Terms & Commission
                            </CardTitle>
                            <CardDescription>
                                Set the base salary and review the platform service fee calculation.
                            </CardDescription>
                        </div>
                        <Badge variant="outline" class="bg-blue-50/50 text-blue-700 border-blue-100 font-bold px-3 py-1">
                            {{ (commissionRate * 100).toFixed(0) }}% Fee Rate
                        </Badge>
                    </div>
                </CardHeader>
                <CardContent class="p-0">
                    <div class="grid md:grid-cols-5 divide-y md:divide-y-0 md:divide-x divide-slate-100">
                        <div class="md:col-span-3 p-8 space-y-8">
                            <div class="space-y-4">
                                <Label for="salary_base_amount" class="text-sm font-bold uppercase tracking-wider text-slate-500">
                                    Base Salary for Calculation
                                </Label>
                                <div class="relative">
                                    <div class="absolute left-4 top-1/2 -translate-y-1/2 flex items-center gap-2 text-slate-400 font-semibold border-r pr-3 border-slate-200">
                                        {{ job.salary_currency || 'BDT' }}
                                    </div>
                                    <Input
                                        id="salary_base_amount"
                                        v-model="form.salary_base_amount"
                                        type="number"
                                        class="pl-24 h-14 text-xl font-bold rounded-xl border-slate-200 focus:border-blue-500 focus:ring-blue-500/10 transition-all"
                                        placeholder="0.00"
                                    />
                                </div>
                                <p class="text-xs text-slate-400">
                                    Change this value to adjust the commission. The original job salary was 
                                    <strong>{{ job.salary_amount }} {{ job.salary_currency }}</strong>.
                                </p>
                            </div>

                            <Separator />

                            <div class="space-y-4">
                                <h3 class="text-sm font-bold uppercase tracking-wider text-slate-500">Service Fee Summary</h3>
                                <div class="rounded-2xl bg-slate-50/80 p-5 space-y-4 border border-slate-100">
                                    <div class="flex justify-between items-center text-sm">
                                        <span class="text-slate-600">Base Amount</span>
                                        <span class="font-semibold text-slate-900">{{ Number(form.salary_base_amount).toLocaleString() }} {{ job.salary_currency }}</span>
                                    </div>
                                    <div class="flex justify-between items-center text-sm">
                                        <span class="text-slate-600">Platform Share ({{ (commissionRate * 100).toFixed(0) }}%)</span>
                                        <span class="font-semibold text-blue-600 text-lg">+{{ commissionAmount.toLocaleString() }} {{ job.salary_currency }}</span>
                                    </div>
                                    <Separator />
                                    <div class="flex justify-between items-center pt-2">
                                        <span class="text-sm font-bold text-slate-900">Total Invoice Amount</span>
                                        <span class="text-2xl font-black text-slate-900">
                                            {{ commissionAmount.toLocaleString() }} {{ job.salary_currency }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="md:col-span-2 p-8 bg-slate-50/30 space-y-8">
                            <div class="space-y-6">
                                <h3 class="text-sm font-bold uppercase tracking-wider text-slate-500">Assignment Details</h3>
                                
                                <div class="space-y-5">
                                    <div class="flex items-start gap-3">
                                        <div class="mt-1 h-8 w-8 rounded-lg bg-white border border-slate-200 flex items-center justify-center text-slate-400 shadow-sm">
                                            <User class="h-4 w-4" />
                                        </div>
                                        <div>
                                            <p class="text-[10px] font-bold uppercase tracking-tight text-slate-400">Target Tutor</p>
                                            <p class="text-sm font-bold text-slate-800">{{ tutor.name }}</p>
                                        </div>
                                    </div>

                                    <div class="flex items-start gap-3">
                                        <div class="mt-1 h-8 w-8 rounded-lg bg-white border border-slate-200 flex items-center justify-center text-slate-400 shadow-sm">
                                            <Briefcase class="h-4 w-4" />
                                        </div>
                                        <div>
                                            <p class="text-[10px] font-bold uppercase tracking-tight text-slate-400">Direct Request Job</p>
                                            <p class="text-sm font-bold text-slate-800 line-clamp-1">{{ job.title }}</p>
                                        </div>
                                    </div>

                                    <div class="flex items-start gap-3">
                                        <div class="mt-1 h-8 w-8 rounded-lg bg-white border border-slate-200 flex items-center justify-center text-slate-400 shadow-sm">
                                            <BadgeCheck class="h-4 w-4" />
                                        </div>
                                        <div>
                                            <p class="text-[10px] font-bold uppercase tracking-tight text-slate-400">Process State</p>
                                            <p class="text-sm font-bold text-emerald-600">Ready for Assignment</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="p-4 rounded-xl bg-blue-50 border border-blue-100 space-y-3">
                                <div class="flex items-center gap-2 text-blue-700">
                                    <Info class="h-4 w-4" />
                                    <span class="text-xs font-bold uppercase tracking-wider">Workflow Info</span>
                                </div>
                                <p class="text-xs text-blue-800/80 leading-relaxed">
                                    Settling this request will move the job to <span class="font-bold">Confirmed</span> status and issue an unpaid invoice to the tutor for the platform service fee.
                                </p>
                            </div>
                        </div>
                    </div>
                </CardContent>
                <CardFooter class="flex items-center justify-between border-t bg-slate-50/50 px-8 py-6">
                    <Button
                        variant="ghost"
                        as-child
                        class="hover:bg-slate-100 rounded-xl"
                    >
                        <Link href="/admin/jobs">Cancel & Return</Link>
                    </Button>
                    <Button
                        class="gap-2 px-10 h-12 rounded-xl bg-blue-600 hover:bg-blue-700 shadow-lg shadow-blue-500/20 transition-all hover:scale-[1.02] active:scale-[0.98]"
                        :disabled="form.processing"
                        @click="submit"
                    >
                        <CheckCircle2 v-if="!form.processing" class="h-5 w-5" />
                        <span v-else class="h-5 w-5 animate-spin rounded-full border-2 border-white/30 border-t-white" />
                        Confirm & Process Settlement
                    </Button>
                </CardFooter>
            </Card>
        </div>
    </AdminLayout>
</template>
