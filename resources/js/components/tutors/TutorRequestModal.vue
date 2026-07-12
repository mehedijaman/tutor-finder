<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import JobForm from '@/components/admin/jobs/JobForm.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';

interface Job {
    id: number;
    title: string;
}

const props = defineProps({
    tutor: { type: Object as () => any, required: true },
    filterOptions: { type: Object as () => any, required: true },
    guardianJobs: { type: Array as () => Job[], default: () => [] },
});

const isOpen = ref(false);
const activeTab = ref(props.guardianJobs.length > 0 ? 'existing' : 'new');

const initialData = {
    title: `Tuition for ${props.tutor.name}`,
    description: `I am looking for a tutor for my child. I found your profile and would like to request your services.`,
    requested_tutor_id: props.tutor.id,
    salary_amount: props.tutor.tutor_profile?.expected_salary_min || '',
    salary_currency: 'BDT',
    salary_negotiable: true,
};

const existingJobForm = useForm({
    job_id: '',
    tutor_id: props.tutor.id,
});

function handleSuccess() {
    isOpen.value = false;
}

function submitExisting() {
    if (!existingJobForm.job_id) return;

    existingJobForm.post(
        `/guardian/jobs/${existingJobForm.job_id}/request-tutor`,
        {
            onSuccess: () => {
                isOpen.value = false;
                existingJobForm.reset();
            },
        },
    );
}
</script>

<template>
    <Dialog v-model:open="isOpen">
        <Button
            class="h-12 w-full text-lg font-semibold shadow-lg shadow-primary/20 transition-all hover:scale-[1.02] active:scale-[0.98]"
            @click="isOpen = true"
        >
            Request This Tutor
        </Button>
        - -
        <DialogContent class="max-h-[95vh] max-w-7xl overflow-y-auto">
            <DialogHeader>
                <DialogTitle>Request {{ tutor.name }}</DialogTitle>
                <DialogDescription>
                    Choose how you want to request {{ tutor.name }}.
                </DialogDescription>
            </DialogHeader>

            <div
                v-if="guardianJobs.length > 0"
                class="mb-6 flex max-w-md rounded-lg bg-slate-100 p-1"
            >
                <button
                    @click="activeTab = 'existing'"
                    :class="[
                        'flex-1 rounded-md py-2 text-sm font-medium transition-all',
                        activeTab === 'existing'
                            ? 'bg-white text-blue-600 shadow-sm'
                            : 'text-slate-500 hover:text-slate-700',
                    ]"
                >
                    Use Existing Job
                </button>
                <button
                    @click="activeTab = 'new'"
                    :class="[
                        'flex-1 rounded-md py-2 text-sm font-medium transition-all',
                        activeTab === 'new'
                            ? 'bg-white text-blue-600 shadow-sm'
                            : 'text-slate-500 hover:text-slate-700',
                    ]"
                >
                    Create New Job
                </button>
            </div>

            <div v-if="activeTab === 'existing'" class="space-y-6 py-4">
                <div class="max-w-xl space-y-4">
                    <div class="space-y-2">
                        <Label>Select your live job</Label>
                        <Select v-model="existingJobForm.job_id">
                            <SelectTrigger>
                                <SelectValue placeholder="Select a job" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem
                                    v-for="job in guardianJobs"
                                    :key="job.id"
                                    :value="String(job.id)"
                                >
                                    {{ job.title }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <p class="text-xs text-slate-500">
                            Only your active live jobs are listed here.
                        </p>
                    </div>

                    <div class="flex gap-3 pt-4">
                        <Button variant="outline" @click="isOpen = false"
                            >Cancel</Button
                        >
                        <Button
                            @click="submitExisting"
                            :disabled="
                                !existingJobForm.job_id ||
                                existingJobForm.processing
                            "
                        >
                            {{
                                existingJobForm.processing
                                    ? 'Sending...'
                                    : 'Send Request'
                            }}
                        </Button>
                    </div>
                </div>
            </div>

            <div v-else class="mt-4">
                <JobForm
                    action="/guardian/jobs"
                    method="post"
                    submit-label="Send Request"
                    cancel-href="#"
                    :is-admin="false"
                    :hide-slug="true"
                    :hide-currency="true"
                    :initial="initialData"
                    :tuition-types="filterOptions.tuitionTypes"
                    :categories="filterOptions.categories"
                    :school-classes="filterOptions.classes"
                    :countries="filterOptions.countries"
                    :cities="filterOptions.cities"
                    :areas="filterOptions.areas"
                    :subjects="filterOptions.subjects"
                    :gender-options="filterOptions.genderOptions"
                    :day-options="filterOptions.days"
                    @success="handleSuccess"
                    @cancel="isOpen = false"
                />
            </div>
        </DialogContent>
    </Dialog>
</template>
