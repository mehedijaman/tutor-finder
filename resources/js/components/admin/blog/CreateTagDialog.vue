<script setup lang="ts">
import { reactive, ref, toRef } from 'vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useAutoSlug } from '@/composables/useAutoSlug';

defineProps({
    open: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['update:open', 'created']);

const form = reactive({
    name: '',
    slug: '',
    status: 'active',
});

const errors = ref({});
const processing = ref(false);

const { autoSlug, onManualSlugInput, toggleAutoSlug } = useAutoSlug(
    toRef(form, 'name'),
    toRef(form, 'slug'),
    { initiallyAuto: true },
);

function handleOpenChange(value) {
    emit('update:open', value);

    if (!value) {
        resetForm();
    }
}

function resetForm() {
    form.name = '';
    form.slug = '';
    form.status = 'active';
    errors.value = {};
    processing.value = false;
}

async function submit() {
    processing.value = true;
    errors.value = {};

    try {
        const csrfToken = getCsrfToken();

        const response = await fetch('/admin/blog/tags', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                ...(csrfToken !== null ? { 'X-CSRF-TOKEN': csrfToken } : {}),
            },
            body: JSON.stringify({
                name: form.name,
                slug: form.slug,
                status: form.status,
            }),
        });

        const data = await response.json().catch(() => null);

        if (response.status === 422) {
            errors.value = Object.fromEntries(
                Object.entries(data?.errors ?? {}).map(([key, value]) => [
                    key,
                    Array.isArray(value) ? value[0] : value,
                ]),
            );

            return;
        }

        if (!response.ok || !data?.id) {
            errors.value = {
                form: data?.message ?? 'Failed to create tag.',
            };

            return;
        }

        emit('created', data);
        emit('update:open', false);
        resetForm();
    } finally {
        processing.value = false;
    }
}

function getCsrfToken() {
    const token = document
        .querySelector('meta[name="csrf-token"]')
        ?.getAttribute('content');

    if (typeof token !== 'string') {
        return null;
    }

    const trimmed = token.trim();

    return trimmed === '' ? null : trimmed;
}
</script>

<template>
    <Dialog :open="open" @update:open="handleOpenChange">
        <DialogContent>
            <DialogHeader class="space-y-2">
                <DialogTitle>Add Tag</DialogTitle>
                <DialogDescription>
                    Create a new tag and assign it to this post.
                </DialogDescription>
            </DialogHeader>

            <form class="space-y-4" @submit.prevent="submit">
                <div class="grid gap-2">
                    <Label for="inline-tag-name">Name</Label>
                    <Input
                        id="inline-tag-name"
                        v-model="form.name"
                        type="text"
                        required
                    />
                    <InputError :message="errors.name" />
                </div>

                <div class="grid gap-2">
                    <div class="flex items-center justify-between gap-3">
                        <Label for="inline-tag-slug">Slug</Label>
                        <Button
                            type="button"
                            size="sm"
                            variant="outline"
                            @click="toggleAutoSlug"
                        >
                            Auto: {{ autoSlug ? 'On' : 'Off' }}
                        </Button>
                    </div>
                    <Input
                        id="inline-tag-slug"
                        :model-value="form.slug"
                        type="text"
                        @update:model-value="onManualSlugInput"
                    />
                    <InputError :message="errors.slug" />
                </div>

                <div class="grid gap-2">
                    <Label for="inline-tag-status">Status</Label>
                    <select
                        id="inline-tag-status"
                        v-model="form.status"
                        class="h-10 rounded-md border px-3 text-sm"
                    >
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                    <InputError :message="errors.status" />
                </div>

                <InputError :message="errors.form" />

                <DialogFooter class="gap-2">
                    <Button
                        type="button"
                        variant="outline"
                        :disabled="processing"
                        @click="handleOpenChange(false)"
                    >
                        Cancel
                    </Button>
                    <Button type="submit" :disabled="processing">
                        Create Tag
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
