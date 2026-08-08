<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
import { computed, toRef } from 'vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { slugify, useAutoSlug } from '@/composables/useAutoSlug';

const props = defineProps({
    action: { type: String, required: true },
    method: { type: String, default: 'post' },
    submitLabel: { type: String, required: true },
    cancelHref: { type: String, default: '/admin/tuition/taxonomies/areas' },
    cities: { type: Array, default: () => [] },
    statusOptions: { type: Array, default: () => [] },
    initial: {
        type: Object,
        default: () => ({
            city_id: null,
            name: '',
            slug: '',
            status: 'active',
        }),
    },
});

const defaultCityId = props.initial.city_id ?? props.cities[0]?.id ?? null;

const form = useForm({
    city_id: defaultCityId,
    name: props.initial.name ?? '',
    slug: props.initial.slug ?? '',
    status: props.initial.status ?? 'active',
});

const cityLabel = computed(() => {
    const selected = props.cities.find(
        (item) => item.id === Number(form.city_id),
    );

    if (!selected) {
        return '';
    }

    return selected.country_name
        ? `${selected.name} (${selected.country_name})`
        : selected.name;
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
    {
        initiallyAuto: isInitiallyAuto,
    },
);

function submit() {
    if (props.method.toLowerCase() === 'put') {
        form.transform((data) => ({
            ...data,
            _method: 'put',
        })).post(props.action, { preserveScroll: true });

        return;
    }

    form.post(props.action, { preserveScroll: true });
}
</script>

<template>
    <form class="space-y-6" @submit.prevent="submit">
        <section
            class="grid gap-4 rounded-2xl border border-slate-200/80 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm sm:p-6"
        >
            <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Area Details</h2>

            <div class="grid gap-2">
                <Label for="area-city" class="dark:text-slate-200">City</Label>
                <select
                    id="area-city"
                    v-model="form.city_id"
                    class="h-10 rounded-md border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 px-3 text-sm text-slate-900 dark:text-slate-100"
                    required
                >
                    <option
                        v-for="city in cities"
                        :key="city.id"
                        :value="city.id"
                    >
                        {{
                            city.country_name
                                ? `${city.name} (${city.country_name})`
                                : city.name
                        }}
                    </option>
                </select>
                <p v-if="cityLabel" class="text-xs text-muted-foreground">
                    Selected: {{ cityLabel }}
                </p>
                <InputError :message="form.errors.city_id" />
            </div>

            <div class="grid gap-2">
                <Label for="area-name" class="dark:text-slate-200">Name</Label>
                <Input
                    id="area-name"
                    v-model="form.name"
                    type="text"
                    required
                    class="dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100"
                />
                <InputError :message="form.errors.name" />
            </div>

            <div class="grid gap-2">
                <div class="flex items-center justify-between gap-3">
                    <Label for="area-slug" class="dark:text-slate-200">Slug</Label>
                    <Button
                        type="button"
                        size="sm"
                        variant="outline"
                        class="dark:border-slate-700 dark:text-slate-300"
                        @click="toggleAutoSlug"
                    >
                        Auto: {{ autoSlug ? 'On' : 'Off' }}
                    </Button>
                </div>
                <Input
                    id="area-slug"
                    :model-value="form.slug"
                    type="text"
                    class="dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100"
                    @update:model-value="onManualSlugInput"
                />
                <InputError :message="form.errors.slug" />
            </div>

            <div class="grid gap-2">
                <Label for="area-status" class="dark:text-slate-200">Status</Label>
                <select
                    id="area-status"
                    v-model="form.status"
                    class="h-10 rounded-md border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 px-3 text-sm text-slate-900 dark:text-slate-100"
                >
                    <option
                        v-for="option in statusOptions"
                        :key="option.value"
                        :value="option.value"
                    >
                        {{ option.label }}
                    </option>
                </select>
                <InputError :message="form.errors.status" />
            </div>
        </section>

        <div class="flex flex-wrap items-center gap-3">
            <Button type="submit" :disabled="form.processing">{{
                submitLabel
            }}</Button>
            <Link
                :href="cancelHref"
                class="inline-flex h-9 items-center rounded-md border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 px-3 text-sm font-medium text-slate-700 dark:text-slate-200 transition hover:bg-slate-50 dark:hover:bg-slate-700"
                >Cancel</Link
            >
        </div>
    </form>
</template>
