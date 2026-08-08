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
import TutorLayout from '@/layouts/TutorLayout.vue';
import type { SelectOption } from '@/types';

const props = defineProps<{
    categoryOptions: SelectOption[];
    priorityOptions: SelectOption[];
}>();

const breadcrumbs = [
    { title: 'Support Tickets', href: '/tutor/support-tickets' },
    { title: 'New Ticket', href: '/tutor/support-tickets/create' },
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
    form.post('/tutor/support-tickets', {
        preserveScroll: true,
        forceFormData: true,
    });
}
</script>

<template>
    <Head title="New Support Ticket" />

    <TutorLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-4 sm:p-6 lg:p-8">
            <div
                class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6 dark:border-slate-800 dark:bg-slate-900"
            >
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div class="space-y-1">
                        <h1
                            class="text-2xl font-semibold tracking-tight text-slate-900 sm:text-3xl dark:text-slate-100"
                        >
                            Create Support Ticket
                        </h1>
                        <p class="text-sm text-muted-foreground">
                            Describe your issue and our team will get back to
                            you.
                        </p>
                    </div>
                    <Link
                        href="/tutor/support-tickets"
                        class="inline-flex h-9 items-center rounded-md border border-slate-200 bg-white px-3 text-sm font-medium text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700"
                    >
                        Back
                    </Link>
                </div>
            </div>

            <form
                @submit.prevent="submit"
                class="space-y-6 rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6 dark:border-slate-800 dark:bg-slate-900"
            >
                <div class="space-y-2">
                    <Label
                        for="subject"
                        class="text-slate-800 dark:text-slate-200"
                        >Subject</Label
                    >
                    <Input
                        id="subject"
                        v-model="form.subject"
                        type="text"
                        placeholder="Brief description of your issue"
                        :aria-invalid="!!form.errors.subject"
                        class="dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100"
                    />
                    <InputError :message="form.errors.subject" />
                </div>

                <div class="grid gap-6 sm:grid-cols-2">
                    <div class="space-y-2">
                        <Label class="text-slate-800 dark:text-slate-200"
                            >Category</Label
                        >
                        <Select v-model="form.category">
                            <SelectTrigger
                                class="dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100"
                            >
                                <SelectValue placeholder="Select category" />
                            </SelectTrigger>
                            <SelectContent
                                class="dark:border-slate-700 dark:bg-slate-900"
                            >
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
                        <Label class="text-slate-800 dark:text-slate-200"
                            >Priority</Label
                        >
                        <Select v-model="form.priority">
                            <SelectTrigger
                                class="dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100"
                            >
                                <SelectValue placeholder="Select priority" />
                            </SelectTrigger>
                            <SelectContent
                                class="dark:border-slate-700 dark:bg-slate-900"
                            >
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
                    <Label
                        for="message"
                        class="text-slate-800 dark:text-slate-200"
                        >Message</Label
                    >
                    <Textarea
                        id="message"
                        v-model="form.message"
                        placeholder="Detailed explanation of your issue..."
                        rows="5"
                        :aria-invalid="!!form.errors.message"
                        class="dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100"
                    />
                    <InputError :message="form.errors.message" />
                </div>

                <div class="space-y-2">
                    <Label class="text-slate-800 dark:text-slate-200">
                        Attachments
                        <span class="text-xs text-slate-500 dark:text-slate-400"
                            >(optional, max 3 images)</span
                        >
                    </Label>

                    <div
                        v-if="previews.length > 0"
                        class="flex flex-wrap gap-2"
                    >
                        <div
                            v-for="(preview, index) in previews"
                            :key="index"
                            class="group relative overflow-hidden rounded-lg border border-slate-200 dark:border-slate-700"
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
                                <span class="text-xs font-medium text-white"
                                    >Remove</span
                                >
                            </button>
                        </div>
                    </div>

                    <input
                        ref="fileInput"
                        type="file"
                        accept="image/jpeg,image/png,image/gif"
                        multiple
                        class="block w-full text-sm text-slate-500 file:mr-4 file:rounded-md file:border-0 file:bg-slate-50 file:px-4 file:py-2 file:text-sm file:font-medium file:text-slate-700 hover:file:bg-slate-100 dark:text-slate-400 dark:file:bg-slate-800 dark:file:text-slate-200 dark:hover:file:bg-slate-700"
                        @change="handleFileSelect"
                    />
                    <InputError
                        :message="
                            (form.errors as Record<string, string>)[
                                'attachments'
                            ]
                        "
                    />
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <Link href="/tutor/support-tickets">
                        <Button
                            type="button"
                            variant="outline"
                            class="dark:border-slate-700 dark:text-slate-300"
                        >
                            Cancel
                        </Button>
                    </Link>
                    <Button type="submit" :disabled="form.processing">
                        <span v-if="form.processing">Submitting...</span>
                        <span v-else>Submit Ticket</span>
                    </Button>
                </div>
            </form>
        </div>
    </TutorLayout>
</template>
