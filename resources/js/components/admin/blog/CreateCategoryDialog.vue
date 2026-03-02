<script setup>
import { reactive, ref, toRef } from 'vue';
import InputError from '@/components/InputError.vue';
import { useAutoSlug } from '@/composables/useAutoSlug';
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

const props = defineProps({
    open: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['update:open', 'created']);

const form = reactive({
    name: '',
    slug: '',
    description: '',
    status: 'active',
    image: null,
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
    form.description = '';
    form.status = 'active';
    form.image = null;
    errors.value = {};
    processing.value = false;
}

function onImageChange(event) {
    const target = event.target;
    form.image = target.files?.[0] ?? null;
}

async function submit() {
    processing.value = true;
    errors.value = {};

    try {
        const payload = new FormData();
        payload.append('name', form.name);
        payload.append('slug', form.slug);
        payload.append('description', form.description);
        payload.append('status', form.status);
        payload.append('remove_image', '0');

        if (form.image !== null) {
            payload.append('image', form.image);
        }

        const csrfToken = getCsrfToken();

        if (csrfToken !== null) {
            payload.append('_token', csrfToken);
        }

        const response = await fetch('/admin/blog/categories', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                ...(csrfToken !== null ? { 'X-CSRF-TOKEN': csrfToken } : {}),
            },
            body: payload,
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
                form: data?.message ?? 'Failed to create category.',
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
                <DialogTitle>Add Category</DialogTitle>
                <DialogDescription>
                    Create a new category and attach it to this post immediately.
                </DialogDescription>
            </DialogHeader>

            <form class="space-y-4" @submit.prevent="submit">
                <div class="grid gap-2">
                    <Label for="inline-category-name">Name</Label>
                    <Input
                        id="inline-category-name"
                        v-model="form.name"
                        type="text"
                        required
                    />
                    <InputError :message="errors.name" />
                </div>

                <div class="grid gap-2">
                    <div class="flex items-center justify-between gap-3">
                        <Label for="inline-category-slug">Slug</Label>
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
                        id="inline-category-slug"
                        :model-value="form.slug"
                        type="text"
                        @update:model-value="onManualSlugInput"
                    />
                    <InputError :message="errors.slug" />
                </div>

                <div class="grid gap-2">
                    <Label for="inline-category-description">Description</Label>
                    <textarea
                        id="inline-category-description"
                        v-model="form.description"
                        rows="3"
                        class="rounded-md border px-3 py-2 text-sm"
                    ></textarea>
                    <InputError :message="errors.description" />
                </div>

                <div class="grid gap-2">
                    <Label for="inline-category-status">Status</Label>
                    <select
                        id="inline-category-status"
                        v-model="form.status"
                        class="h-10 rounded-md border px-3 text-sm"
                    >
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                    <InputError :message="errors.status" />
                </div>

                <div class="grid gap-2">
                    <Label for="inline-category-image">Image (optional)</Label>
                    <Input
                        id="inline-category-image"
                        type="file"
                        accept="image/*"
                        @change="onImageChange"
                    />
                    <InputError :message="errors.image" />
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
                        Create Category
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
