<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
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
import { computed } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';
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

const commissionRate = computed(() =>
    Number(props.finance.commission_rate || 0.6),
);
const commissionAmount = computed(
    () => Number(form.salary_base_amount || 0) * commissionRate.value,
);

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
                    <h1
                        class="text-2xl font-bold tracking-tight text-slate-900"
                    >
                        Finalize Settlement
                    </h1>
                    <p class="text-sm text-slate-500">
                        Admin override for direct recruitment of
                        {{ tutor.name }} for "{{ job.title }}"
                    </p>
                </div>
            </div>

            <div
                v-if="Object.keys(form.errors).length > 0"
                class="animate-in rounded-xl border border-red-100 bg-red-50 p-4 text-sm text-red-800 fade-in slide-in-from-top-2"
            >
                <div class="flex items-start gap-3">
                    <Info class="mt-0.5 h-4 w-4 shrink-0 text-red-600" />
                    <div>
                        <p class="font-semibold">Settlement Failed</p>
                        <ul class="mt-1 list-inside list-disc space-y-1">
                            <li v-for="(error, key) in form.errors" :key="key">
                                {{ error }}
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <Card
                class="overflow-hidden border-slate-200/80 shadow-lg shadow-slate-200/50"
            >
                <CardHeader
                    class="border-b border-slate-100 bg-slate-50/50 pb-6"
                >
                    <div class="flex items-center justify-between">
                        <div class="space-y-1">
                            <CardTitle class="flex items-center gap-2 text-lg">
                                <Calculator class="h-5 w-5 text-blue-500" />
                                Financial Terms & Commission
                            </CardTitle>
                            <CardDescription>
                                Set the base salary and review the platform
                                service fee calculation.
                            </CardDescription>
                        </div>
                        <Badge
                            variant="outline"
                            class="border-blue-100 bg-blue-50/50 px-3 py-1 font-bold text-blue-700"
                        >
                            {{ (commissionRate * 100).toFixed(0) }}% Fee Rate
                        </Badge>
                    </div>
                </CardHeader>
                <CardContent class="p-0">
                    <div
                        class="grid divide-y divide-slate-100 md:grid-cols-5 md:divide-x md:divide-y-0"
                    >
                        <div class="space-y-8 p-8 md:col-span-3">
                            <div class="space-y-4">
                                <Label
                                    for="salary_base_amount"
                                    class="text-sm font-bold tracking-wider text-slate-500 uppercase"
                                >
                                    Base Salary for Calculation
                                </Label>
                                <div class="relative">
                                    <div
                                        class="absolute top-1/2 left-4 flex -translate-y-1/2 items-center gap-2 border-r border-slate-200 pr-3 font-semibold text-slate-400"
                                    >
                                        {{ job.salary_currency || 'BDT' }}
                                    </div>
                                    <Input
                                        id="salary_base_amount"
                                        v-model="form.salary_base_amount"
                                        type="number"
                                        class="h-14 rounded-xl border-slate-200 pl-24 text-xl font-bold transition-all focus:border-blue-500 focus:ring-blue-500/10"
                                        placeholder="0.00"
                                    />
                                </div>
                                <p class="text-xs text-slate-400">
                                    Change this value to adjust the commission.
                                    The original job salary was
                                    <strong
                                        >{{ job.salary_amount }}
                                        {{ job.salary_currency }}</strong
                                    >.
                                </p>
                            </div>

                            <Separator />

                            <div class="space-y-4">
                                <h3
                                    class="text-sm font-bold tracking-wider text-slate-500 uppercase"
                                >
                                    Service Fee Summary
                                </h3>
                                <div
                                    class="space-y-4 rounded-2xl border border-slate-100 bg-slate-50/80 p-5"
                                >
                                    <div
                                        class="flex items-center justify-between text-sm"
                                    >
                                        <span class="text-slate-600"
                                            >Base Amount</span
                                        >
                                        <span
                                            class="font-semibold text-slate-900"
                                            >{{
                                                Number(
                                                    form.salary_base_amount,
                                                ).toLocaleString()
                                            }}
                                            {{ job.salary_currency }}</span
                                        >
                                    </div>
                                    <div
                                        class="flex items-center justify-between text-sm"
                                    >
                                        <span class="text-slate-600"
                                            >Platform Share ({{
                                                (commissionRate * 100).toFixed(
                                                    0,
                                                )
                                            }}%)</span
                                        >
                                        <span
                                            class="text-lg font-semibold text-blue-600"
                                            >+{{
                                                commissionAmount.toLocaleString()
                                            }}
                                            {{ job.salary_currency }}</span
                                        >
                                    </div>
                                    <Separator />
                                    <div
                                        class="flex items-center justify-between pt-2"
                                    >
                                        <span
                                            class="text-sm font-bold text-slate-900"
                                            >Total Invoice Amount</span
                                        >
                                        <span
                                            class="text-2xl font-black text-slate-900"
                                        >
                                            {{
                                                commissionAmount.toLocaleString()
                                            }}
                                            {{ job.salary_currency }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-8 bg-slate-50/30 p-8 md:col-span-2">
                            <div class="space-y-6">
                                <h3
                                    class="text-sm font-bold tracking-wider text-slate-500 uppercase"
                                >
                                    Assignment Details
                                </h3>

                                <div class="space-y-5">
                                    <div class="flex items-start gap-3">
                                        <div
                                            class="mt-1 flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-400 shadow-sm"
                                        >
                                            <User class="h-4 w-4" />
                                        </div>
                                        <div>
                                            <p
                                                class="text-[10px] font-bold tracking-tight text-slate-400 uppercase"
                                            >
                                                Target Tutor
                                            </p>
                                            <p
                                                class="text-sm font-bold text-slate-800"
                                            >
                                                {{ tutor.name }}
                                            </p>
                                        </div>
                                    </div>

                                    <div class="flex items-start gap-3">
                                        <div
                                            class="mt-1 flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-400 shadow-sm"
                                        >
                                            <Briefcase class="h-4 w-4" />
                                        </div>
                                        <div>
                                            <p
                                                class="text-[10px] font-bold tracking-tight text-slate-400 uppercase"
                                            >
                                                Direct Request Job
                                            </p>
                                            <p
                                                class="line-clamp-1 text-sm font-bold text-slate-800"
                                            >
                                                {{ job.title }}
                                            </p>
                                        </div>
                                    </div>

                                    <div class="flex items-start gap-3">
                                        <div
                                            class="mt-1 flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-400 shadow-sm"
                                        >
                                            <BadgeCheck class="h-4 w-4" />
                                        </div>
                                        <div>
                                            <p
                                                class="text-[10px] font-bold tracking-tight text-slate-400 uppercase"
                                            >
                                                Process State
                                            </p>
                                            <p
                                                class="text-sm font-bold text-emerald-600"
                                            >
                                                Ready for Assignment
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div
                                class="space-y-3 rounded-xl border border-blue-100 bg-blue-50 p-4"
                            >
                                <div
                                    class="flex items-center gap-2 text-blue-700"
                                >
                                    <Info class="h-4 w-4" />
                                    <span
                                        class="text-xs font-bold tracking-wider uppercase"
                                        >Workflow Info</span
                                    >
                                </div>
                                <p
                                    class="text-xs leading-relaxed text-blue-800/80"
                                >
                                    Settling this request will move the job to
                                    <span class="font-bold">Confirmed</span>
                                    status and issue an unpaid invoice to the
                                    tutor for the platform service fee.
                                </p>
                            </div>
                        </div>
                    </div>
                </CardContent>
                <CardFooter
                    class="flex items-center justify-between border-t bg-slate-50/50 px-8 py-6"
                >
                    <Button
                        variant="ghost"
                        as-child
                        class="rounded-xl hover:bg-slate-100"
                    >
                        <Link href="/admin/jobs">Cancel & Return</Link>
                    </Button>
                    <Button
                        class="h-12 gap-2 rounded-xl bg-blue-600 px-10 shadow-lg shadow-blue-500/20 transition-all hover:scale-[1.02] hover:bg-blue-700 active:scale-[0.98]"
                        :disabled="form.processing"
                        @click="submit"
                    >
                        <CheckCircle2 v-if="!form.processing" class="h-5 w-5" />
                        <span
                            v-else
                            class="h-5 w-5 animate-spin rounded-full border-2 border-white/30 border-t-white"
                        />
                        Confirm & Process Settlement
                    </Button>
                </CardFooter>
            </Card>
        </div>
    </AdminLayout>
</template>
