<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import { ref, toRef } from 'vue';
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
        default: '/admin/blog/categories',
    },
    initial: {
        type: Object,
        default: () => ({
            name: '',
            slug: '',
            description: '',
            status: 'active',
            meta_title: '',
            meta_description: '',
            image_url: null,
        }),
    },
});

const form = useForm({
    name: props.initial.name ?? '',
    slug: props.initial.slug ?? '',
    description: props.initial.description ?? '',
    status: props.initial.status ?? 'active',
    meta_title: props.initial.meta_title ?? '',
    meta_description: props.initial.meta_description ?? '',
    image: null,
    remove_image: false,
});

const imagePreviewUrl = ref(props.initial.image_url ?? null);

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

const onImageChange = (event) => {
    const file = event.target.files?.[0] ?? null;
    form.image = file;
    form.remove_image = false;

    if (file) {
        imagePreviewUrl.value = URL.createObjectURL(file);
    }
};

const toggleRemoveImage = () => {
    form.remove_image = !form.remove_image;

    if (form.remove_image) {
        form.image = null;
        imagePreviewUrl.value = null;
    } else {
        imagePreviewUrl.value = props.initial.image_url ?? null;
    }
};

const submit = () => {
    if (props.method.toLowerCase() === 'put') {
        form.transform((data) => ({
            ...data,
            _method: 'put',
        })).post(props.action, {
            preserveScroll: true,
            forceFormData: true,
        });

        return;
    }

    form.post(props.action, {
        preserveScroll: true,
        forceFormData: true,
    });
};
</script>

<template>
    <form class="space-y-6" @submit.prevent="submit">
        <section class="grid gap-4 rounded-xl border bg-white p-4">
            <h2 class="text-lg font-semibold">Basic Information</h2>

            <div class="grid gap-2">
                <Label for="category-name">Name</Label>
                <Input
                    id="category-name"
                    v-model="form.name"
                    type="text"
                    required
                />
                <InputError :message="form.errors.name" />
            </div>

            <div class="grid gap-2">
                <div class="flex items-center justify-between gap-3">
                    <Label for="category-slug">Slug</Label>
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
                    id="category-slug"
                    :model-value="form.slug"
                    type="text"
                    @update:model-value="onManualSlugInput"
                />
                <InputError :message="form.errors.slug" />
            </div>

            <div class="grid gap-2">
                <Label for="category-description">Description</Label>
                <textarea
                    id="category-description"
                    v-model="form.description"
                    rows="4"
                    class="rounded-md border px-3 py-2 text-sm"
                ></textarea>
                <InputError :message="form.errors.description" />
            </div>

            <div class="grid gap-2">
                <Label for="category-status">Status</Label>
                <select
                    id="category-status"
                    v-model="form.status"
                    class="h-10 rounded-md border px-3 text-sm"
                >
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
                <InputError :message="form.errors.status" />
            </div>
        </section>

        <section class="grid gap-4 rounded-xl border bg-white p-4">
            <h2 class="text-lg font-semibold">Image</h2>

            <div class="flex flex-wrap items-center gap-4">
                <img
                    v-if="imagePreviewUrl"
                    :src="imagePreviewUrl"
                    alt="Category Image"
                    class="h-14 w-14 rounded-md border object-cover"
                />
                <div
                    v-else
                    class="flex h-14 w-14 items-center justify-center rounded-md border text-xs text-muted-foreground"
                >
                    No Image
                </div>

                <Input type="file" accept="image/*" @change="onImageChange" />

                <Button
                    v-if="initial.image_url"
                    type="button"
                    variant="outline"
                    @click="toggleRemoveImage"
                >
                    {{
                        form.remove_image
                            ? 'Keep Existing Image'
                            : 'Remove Image'
                    }}
                </Button>
            </div>

            <InputError :message="form.errors.image" />
            <InputError :message="form.errors.remove_image" />
        </section>

        <section class="grid gap-4 rounded-xl border bg-white p-4">
            <h2 class="text-lg font-semibold">SEO</h2>
            <p class="text-sm text-muted-foreground">
                If left empty, public pages will use fallback values.
            </p>

            <div class="grid gap-2">
                <Label for="category-meta-title">Meta Title</Label>
                <Input
                    id="category-meta-title"
                    v-model="form.meta_title"
                    type="text"
                />
                <InputError :message="form.errors.meta_title" />
            </div>

            <div class="grid gap-2">
                <Label for="category-meta-description">Meta Description</Label>
                <textarea
                    id="category-meta-description"
                    v-model="form.meta_description"
                    rows="3"
                    class="rounded-md border px-3 py-2 text-sm"
                ></textarea>
                <InputError :message="form.errors.meta_description" />
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
