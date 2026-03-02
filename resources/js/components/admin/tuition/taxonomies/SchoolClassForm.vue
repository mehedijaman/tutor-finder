<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import { computed, toRef } from 'vue';
import InputError from '@/components/InputError.vue';
import { slugify, useAutoSlug } from '@/composables/useAutoSlug';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

const props = defineProps({
    action: { type: String, required: true },
    method: { type: String, default: 'post' },
    submitLabel: { type: String, required: true },
    cancelHref: { type: String, default: '/admin/tuition/taxonomies/classes' },
    categories: { type: Array, default: () => [] },
    statusOptions: { type: Array, default: () => [] },
    initial: {
        type: Object,
        default: () => ({
            category_id: null,
            name: '',
            slug: '',
            status: 'active',
            sort_order: 0,
        }),
    },
});

const defaultCategoryId = props.initial.category_id ?? props.categories[0]?.id ?? null;

const form = useForm({
    category_id: defaultCategoryId,
    name: props.initial.name ?? '',
    slug: props.initial.slug ?? '',
    status: props.initial.status ?? 'active',
    sort_order: props.initial.sort_order ?? 0,
});

const categoryLabel = computed(() => props.categories.find((item) => item.id === Number(form.category_id))?.name ?? '');

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
            <h2 class="text-lg font-semibold">Class Details</h2>

            <div class="grid gap-2">
                <Label for="class-category">Category</Label>
                <select id="class-category" v-model="form.category_id" class="h-10 rounded-md border px-3 text-sm" required>
                    <option v-for="category in categories" :key="category.id" :value="category.id">
                        {{ category.name }}
                    </option>
                </select>
                <p v-if="categoryLabel" class="text-xs text-muted-foreground">Selected: {{ categoryLabel }}</p>
                <InputError :message="form.errors.category_id" />
            </div>

            <div class="grid gap-2">
                <Label for="class-name">Name</Label>
                <Input id="class-name" v-model="form.name" type="text" required />
                <InputError :message="form.errors.name" />
            </div>

            <div class="grid gap-2">
                <div class="flex items-center justify-between gap-3">
                    <Label for="class-slug">Slug</Label>
                    <Button type="button" size="sm" variant="outline" @click="toggleAutoSlug">
                        Auto: {{ autoSlug ? 'On' : 'Off' }}
                    </Button>
                </div>
                <Input id="class-slug" :model-value="form.slug" type="text" @update:model-value="onManualSlugInput" />
                <InputError :message="form.errors.slug" />
            </div>

            <div class="grid gap-2 sm:grid-cols-2">
                <div class="grid gap-2">
                    <Label for="class-status">Status</Label>
                    <select id="class-status" v-model="form.status" class="h-10 rounded-md border px-3 text-sm">
                        <option v-for="option in statusOptions" :key="option.value" :value="option.value">
                            {{ option.label }}
                        </option>
                    </select>
                    <InputError :message="form.errors.status" />
                </div>

                <div class="grid gap-2">
                    <Label for="class-sort-order">Sort Order</Label>
                    <Input id="class-sort-order" v-model.number="form.sort_order" type="number" min="0" />
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
