<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';

const props = defineProps<{
    action: string;
    maxAttachments?: number;
}>();

const emit = defineEmits<{
    (e: 'submitted'): void;
}>();

const form = useForm({
    message: '',
    attachments: [] as File[],
});

const fileInput = ref<HTMLInputElement | null>(null);
const previews = ref<string[]>([]);

function handleFileSelect(event: Event): void {
    const target = event.target as HTMLInputElement;
    const files = Array.from(target.files || []);
    const max = props.maxAttachments ?? 3;

    const combined = [...form.attachments, ...files].slice(0, max);
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
    form.post(props.action, {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            form.reset();
            previews.value.forEach((url) => URL.revokeObjectURL(url));
            previews.value = [];
            if (fileInput.value) {
                fileInput.value.value = '';
            }
            emit('submitted');
        },
    });
}

watch(
    () => form.attachments,
    () => updatePreviews(),
    { deep: true },
);
</script>

<template>
    <form @submit.prevent="submit" class="space-y-4">
        <div class="space-y-2">
            <Label for="reply-message">Your Reply</Label>
            <Textarea
                id="reply-message"
                v-model="form.message"
                placeholder="Type your reply here..."
                rows="4"
                :aria-invalid="!!form.errors.message"
                class="dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100"
            />
            <InputError :message="form.errors.message" />
        </div>

        <div class="space-y-2">
            <Label>
                Attach Images
                <span class="text-xs text-slate-500 dark:text-slate-400"
                    >(optional, max {{ maxAttachments ?? 3 }} images)</span
                >
            </Label>

            <div v-if="previews.length > 0" class="flex flex-wrap gap-2">
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
                    (form.errors as Record<string, string>)['attachments']
                "
            />
            <InputError
                :message="
                    (form.errors as Record<string, string>)['attachments.0']
                "
            />
            <InputError
                :message="
                    (form.errors as Record<string, string>)['attachments.1']
                "
            />
            <InputError
                :message="
                    (form.errors as Record<string, string>)['attachments.2']
                "
            />
        </div>

        <div class="flex justify-end">
            <Button type="submit" :disabled="form.processing">
                <span v-if="form.processing">Sending...</span>
                <span v-else>Send Reply</span>
            </Button>
        </div>
    </form>
</template>
