<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import InputError from '@/components/InputError.vue';
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
import { Textarea } from '@/components/ui/textarea';
import GuardianLayout from '@/layouts/GuardianLayout.vue';
import type { SelectOption } from '@/types';

const props = defineProps<{
    categoryOptions: SelectOption[];
    priorityOptions: SelectOption[];
}>();

const breadcrumbs = [
    { title: 'Support Tickets', href: '/guardian/support-tickets' },
    { title: 'New Ticket', href: '/guardian/support-tickets/create' },
];

const form = useForm({
    subject: '',
    category: 'general',
    priority: 'medium',
    message: '',
    attachments: [] as File[],
});

const fileInput = ref<HTMLInputElement | null>(null);
const previews = ref<string[]>([]);

function handleFileSelect(event: Event): void {
    const target = event.target as HTMLInputElement;
    const files = Array.from(target.files || []);
    const combined = [...form.attachments, ...files].slice(0, 3);
    form.attachments = combined;
    updatePreviews();
}

function removeAttachment(index: number): void {
    form.attachments.splice(index, 1);
    updatePreviews();
}

function updatePreviews(): void {
    previews.value.forEach((url) => URL.revokeObjectURL(url));
    previews.value = form.attachments.map((file) => URL.createObjectURL(file));
}

function submit(): void {
    form.post('/guardian/support-tickets', {
        preserveScroll: true,
        forceFormData: true,
    });
}
</script>

<template>
    <Head title="New Support Ticket" />

    <GuardianLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-4 sm:p-6 lg:p-8">
            <div
                class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6"
            >
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div class="space-y-1">
                        <h1 class="text-2xl sm:text-3xl font-semibold tracking-tight">
                            Create Support Ticket
                        </h1>
                        <p class="text-sm text-muted-foreground">
                            Describe your issue and our team will get back to
                            you.
                        </p>
                    </div>
                    <Link
                        href="/guardian/support-tickets"
                        class="inline-flex h-9 items-center rounded-md border border-slate-200 px-3 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
                    >
                        Back
                    </Link>
                </div>
            </div>

            <form
                @submit.prevent="submit"
                class="space-y-6 rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6"
            >
                <div class="space-y-2">
                    <Label for="subject">Subject</Label>
                    <Input
                        id="subject"
                        v-model="form.subject"
                        type="text"
                        placeholder="Brief description of your issue"
                        :aria-invalid="!!form.errors.subject"
                    />
                    <InputError :message="form.errors.subject" />
                </div>

                <div class="grid gap-6 sm:grid-cols-2">
                    <div class="space-y-2">
                        <Label>Category</Label>
                        <Select v-model="form.category">
                            <SelectTrigger>
                                <SelectValue placeholder="Select category" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem
                                    v-for="cat in categoryOptions"
                                    :key="cat.value"
                                    :value="cat.value"
                                >
                                    {{ cat.label }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <InputError :message="form.errors.category" />
                    </div>

                    <div class="space-y-2">
                        <Label>Priority</Label>
                        <Select v-model="form.priority">
                            <SelectTrigger>
                                <SelectValue placeholder="Select priority" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem
                                    v-for="pri in priorityOptions"
                                    :key="pri.value"
                                    :value="pri.value"
                                >
                                    {{ pri.label }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <InputError :message="form.errors.priority" />
                    </div>
                </div>

                <div class="space-y-2">
                    <Label for="message">Message</Label>
                    <Textarea
                        id="message"
                        v-model="form.message"
                        placeholder="Describe your issue in detail..."
                        rows="6"
                        :aria-invalid="!!form.errors.message"
                    />
                    <InputError :message="form.errors.message" />
                </div>

                <div class="space-y-2">
                    <Label>
                        Attachments
                        <span class="text-xs text-slate-500">
                            (optional, max 3 images)
                        </span>
                    </Label>

                    <div
                        v-if="previews.length > 0"
                        class="flex flex-wrap gap-2"
                    >
                        <div
                            v-for="(preview, index) in previews"
                            :key="index"
                            class="group relative overflow-hidden rounded-lg border border-slate-200"
                        >
                            <img
                                :src="preview"
                                :alt="`Attachment ${index + 1}`"
                                class="h-20 w-20 object-cover"
                            />
                            <button
                                type="button"
                                class="absolute inset-0 flex items-center justify-center bg-black/50 opacity-0 transition group-hover:opacity-100"
                                @click="removeAttachment(index)"
                            >
                                <span class="text-xs font-medium text-white">
                                    Remove
                                </span>
                            </button>
                        </div>
                    </div>

                    <input
                        ref="fileInput"
                        type="file"
                        accept="image/jpeg,image/png,image/gif"
                        multiple
                        class="block w-full text-sm text-slate-500 file:mr-4 file:rounded-md file:border-0 file:bg-slate-50 file:px-4 file:py-2 file:text-sm file:font-medium file:text-slate-700 hover:file:bg-slate-100"
                        @change="handleFileSelect"
                    />
                    <InputError
                        :message="
                            (form.errors as Record<string, string>)[
                                'attachments'
                            ]
                        "
                    />
                    <InputError
                        :message="
                            (form.errors as Record<string, string>)[
                                'attachments.0'
                            ]
                        "
                    />
                </div>

                <div class="flex justify-end">
                    <Button type="submit" :disabled="form.processing">
                        <span v-if="form.processing">Submitting...</span>
                        <span v-else>Submit Ticket</span>
                    </Button>
                </div>
            </form>
        </div>
    </GuardianLayout>
</template>
