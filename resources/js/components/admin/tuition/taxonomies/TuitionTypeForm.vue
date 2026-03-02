<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import { toRef } from 'vue';
import InputError from '@/components/InputError.vue';
import { slugify, useAutoSlug } from '@/composables/useAutoSlug';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

const props = defineProps({
    action: { type: String, required: true },
    method: { type: String, default: 'post' },
    submitLabel: { type: String, required: true },
    cancelHref: { type: String, default: '/admin/tuition/taxonomies/tuition-types' },
    statusOptions: { type: Array, default: () => [] },
    initial: {
        type: Object,
        default: () => ({
            name: '',
            slug: '',
            description: '',
            status: 'active',
            sort_order: 0,
        }),
    },
});

const form = useForm({
    name: props.initial.name ?? '',
    slug: props.initial.slug ?? '',
    description: props.initial.description ?? '',
    status: props.initial.status ?? 'active',
    sort_order: props.initial.sort_order ?? 0,
});

const isInitiallyAuto = (() => {
    const sourceName = String(props.initial.name ?? '');
    const currentSlug = String(props.initial.slug ?? '');

    if (currentSlug === '') {
        return true;
    }

    return slugify(sourceName) === currentSlug;
})();

const { autoSlug, onManualSlugInput, toggleAutoSlug } = useAutoSlug(toRef(form, 'name'), toRef(form, 'slug'), {
    initiallyAuto: isInitiallyAuto,
});

function submit() {
    if (props.method.toLowerCase() === 'put') {
        form
            .transform((data) => ({
                ...data,
                _method: 'put',
            }))
            .post(props.action, { preserveScroll: true });

        return;
    }

    form.post(props.action, { preserveScroll: true });
}
</script>

<template>
    <form class="space-y-6" @submit.prevent="submit">
        <section class="grid gap-4 rounded-xl border bg-white p-4">
            <h2 class="text-lg font-semibold">Tuition Type Details</h2>

            <div class="grid gap-2">
                <Label for="tuition-type-name">Name</Label>
                <Input id="tuition-type-name" v-model="form.name" type="text" required />
                <InputError :message="form.errors.name" />
            </div>

            <div class="grid gap-2">
                <div class="flex items-center justify-between gap-3">
                    <Label for="tuition-type-slug">Slug</Label>
                    <Button type="button" size="sm" variant="outline" @click="toggleAutoSlug">
                        Auto: {{ autoSlug ? 'On' : 'Off' }}
                    </Button>
                </div>
                <Input
                    id="tuition-type-slug"
                    :model-value="form.slug"
                    type="text"
                    @update:model-value="onManualSlugInput"
                />
                <InputError :message="form.errors.slug" />
            </div>

            <div class="grid gap-2">
                <Label for="tuition-type-description">Description</Label>
                <textarea id="tuition-type-description" v-model="form.description" rows="4" class="rounded-md border px-3 py-2 text-sm"></textarea>
                <InputError :message="form.errors.description" />
            </div>

            <div class="grid gap-2 sm:grid-cols-2">
                <div class="grid gap-2">
                    <Label for="tuition-type-status">Status</Label>
                    <select id="tuition-type-status" v-model="form.status" class="h-10 rounded-md border px-3 text-sm">
                        <option v-for="option in statusOptions" :key="option.value" :value="option.value">
                            {{ option.label }}
                        </option>
                    </select>
                    <InputError :message="form.errors.status" />
                </div>

                <div class="grid gap-2">
                    <Label for="tuition-type-sort-order">Sort Order</Label>
                    <Input id="tuition-type-sort-order" v-model.number="form.sort_order" type="number" min="0" />
                    <InputError :message="form.errors.sort_order" />
                </div>
            </div>
        </section>

        <div class="flex flex-wrap items-center gap-3">
            <Button type="submit" :disabled="form.processing">{{ submitLabel }}</Button>
            <Link :href="cancelHref" class="text-sm text-muted-foreground underline">Cancel</Link>
        </div>
    </form>
</template>
