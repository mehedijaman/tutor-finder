<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import { toRef } from 'vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { slugify, useAutoSlug } from '@/composables/useAutoSlug';

const props = defineProps({
    action: {
        type: String,
        required: true,
    },
    method: {
        type: String,
        default: 'post',
    },
    submitLabel: {
        type: String,
        required: true,
    },
    cancelHref: {
        type: String,
        default: '/admin/blog/tags',
    },
    initial: {
        type: Object,
        default: () => ({
            name: '',
            slug: '',
            status: 'active',
        }),
    },
});

const form = useForm({
    name: props.initial.name ?? '',
    slug: props.initial.slug ?? '',
    status: props.initial.status ?? 'active',
});

const isInitiallyAuto = (() => {
    const sourceName = String(props.initial.name ?? '');
    const currentSlug = String(props.initial.slug ?? '');

    if (currentSlug === '') {
        return true;
    }

    return slugify(sourceName) === currentSlug;
})();

const { autoSlug, onManualSlugInput, toggleAutoSlug } = useAutoSlug(
    toRef(form, 'name'),
    toRef(form, 'slug'),
    { initiallyAuto: isInitiallyAuto },
);

const submit = () => {
    if (props.method.toLowerCase() === 'put') {
        form.transform((data) => ({
            ...data,
            _method: 'put',
        })).post(props.action, {
            preserveScroll: true,
        });

        return;
    }

    form.post(props.action, {
        preserveScroll: true,
    });
};
</script>

<template>
    <form class="space-y-6" @submit.prevent="submit">
        <section class="grid gap-4 rounded-xl border bg-white p-4">
            <h2 class="text-lg font-semibold">Tag Details</h2>

            <div class="grid gap-2">
                <Label for="tag-name">Name</Label>
                <Input id="tag-name" v-model="form.name" type="text" required />
                <InputError :message="form.errors.name" />
            </div>

            <div class="grid gap-2">
                <div class="flex items-center justify-between gap-3">
                    <Label for="tag-slug">Slug</Label>
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
                    id="tag-slug"
                    :model-value="form.slug"
                    type="text"
                    @update:model-value="onManualSlugInput"
                />
                <InputError :message="form.errors.slug" />
            </div>

            <div class="grid gap-2">
                <Label for="tag-status">Status</Label>
                <select
                    id="tag-status"
                    v-model="form.status"
                    class="h-10 rounded-md border px-3 text-sm"
                >
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
                <InputError :message="form.errors.status" />
            </div>
        </section>

        <div class="flex flex-wrap items-center gap-3">
            <Button type="submit" :disabled="form.processing">
                {{ submitLabel }}
            </Button>
            <Link
                :href="cancelHref"
                class="text-sm text-muted-foreground underline"
            >
                Cancel
            </Link>
        </div>
    </form>
</template>
