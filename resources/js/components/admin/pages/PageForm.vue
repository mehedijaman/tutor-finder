<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, ref, watch } from 'vue';
import TiptapEditor from '@/components/admin/blog/TiptapEditor.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

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
        default: '/admin/pages',
    },
    statusOptions: {
        type: Array,
        default: () => [],
    },
    initial: {
        type: Object,
        default: () => ({
            title: '',
            slug: '',
            content: '<p></p>',
            status: 'active',
            meta_title: '',
            meta_description: '',
            featured_image_url: null,
        }),
    },
    isSystem: {
        type: Boolean,
        default: false,
    },
});

const form = useForm({
    title: props.initial.title ?? '',
    slug: props.initial.slug ?? '',
    content: props.initial.content ?? '<p></p>',
    status: props.initial.status ?? 'active',
    meta_title: props.initial.meta_title ?? '',
    meta_description: props.initial.meta_description ?? '',
    featured_image: null as File | null,
    remove_featured_image: false,
});

const slugDisabled = computed(() => props.isSystem);

const imagePreviewUrl = ref<string | null>(
    props.initial.featured_image_url ?? null,
);
const temporaryImageUrl = ref<string | null>(null);

function clearTemporaryImageUrl(): void {
    if (temporaryImageUrl.value) {
        URL.revokeObjectURL(temporaryImageUrl.value);
        temporaryImageUrl.value = null;
    }
}

onBeforeUnmount(() => {
    clearTemporaryImageUrl();
});

function onImageChange(event: Event): void {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0] ?? null;

    clearTemporaryImageUrl();
    form.featured_image = file;
    form.remove_featured_image = false;

    if (file) {
        temporaryImageUrl.value = URL.createObjectURL(file);
        imagePreviewUrl.value = temporaryImageUrl.value;
        return;
    }

    imagePreviewUrl.value = props.initial.featured_image_url ?? null;
}

function removeImage(): void {
    clearTemporaryImageUrl();
    form.featured_image = null;
    form.remove_featured_image = true;
    imagePreviewUrl.value = null;

    const fileInput = document.getElementById(
        'page-featured-image',
    ) as HTMLInputElement | null;
    if (fileInput) {
        fileInput.value = '';
    }
}

function slugify(text: string): string {
    return text
        .toLowerCase()
        .replace(/[^\w\s-]/g, '')
        .replace(/[\s_]+/g, '-')
        .replace(/-+/g, '-')
        .replace(/^-|-$/g, '');
}

watch(
    () => form.title,
    (title) => {
        if (!props.isSystem && props.method === 'post') {
            form.slug = slugify(title);
        }
    },
);

function submit() {
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
}
</script>

<template>
    <form class="space-y-6" @submit.prevent="submit">
        <div class="grid gap-6 sm:grid-cols-12">
            <section
                class="grid gap-4 rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6 sm:col-span-8"
            >
                <h2 class="text-lg font-semibold">Page Content</h2>

                <div class="grid gap-2">
                    <Label for="page-title">Title</Label>
                    <Input
                        id="page-title"
                        v-model="form.title"
                        type="text"
                        required
                    />
                    <InputError :message="form.errors.title" />
                </div>

                <div class="grid gap-2">
                    <Label for="page-slug">Slug</Label>
                    <Input
                        id="page-slug"
                        v-model="form.slug"
                        type="text"
                        required
                        :disabled="slugDisabled"
                        :class="{
                            'cursor-not-allowed bg-slate-100': slugDisabled,
                        }"
                    />
                    <p
                        v-if="slugDisabled"
                        class="text-xs text-muted-foreground"
                    >
                        System page slugs cannot be changed.
                    </p>
                    <InputError :message="form.errors.slug" />
                </div>

                <div class="grid gap-2">
                    <Label>Content</Label>
                    <TiptapEditor
                        v-model="form.content"
                        placeholder="Write the page content with rich formatting"
                    />
                    <InputError :message="form.errors.content" />
                </div>
            </section>

            <aside class="space-y-4 sm:col-span-4">
                <section
                    class="grid gap-4 rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6"
                >
                    <h2 class="text-lg font-semibold">Settings</h2>

                    <div class="grid gap-2">
                        <Label for="page-status">Status</Label>
                        <select
                            id="page-status"
                            v-model="form.status"
                            class="h-10 w-full rounded-md border px-3 text-sm"
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

                <section
                    class="grid gap-4 rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6"
                >
                    <h2 class="text-lg font-semibold">Featured Image</h2>

                    <div class="grid gap-3">
                        <div
                            v-if="imagePreviewUrl"
                            class="overflow-hidden rounded-lg border border-slate-200"
                        >
                            <img
                                :src="imagePreviewUrl"
                                alt="Featured Image"
                                class="h-40 w-full object-cover"
                            />
                        </div>
                        <div
                            v-else
                            class="flex h-32 items-center justify-center rounded-lg border border-dashed border-slate-300 bg-slate-50 text-sm text-slate-500"
                        >
                            No featured image selected.
                        </div>

                        <Input
                            id="page-featured-image"
                            type="file"
                            accept="image/*"
                            @change="onImageChange"
                        />
                        <InputError :message="form.errors.featured_image" />

                        <button
                            v-if="
                                imagePreviewUrl && !form.remove_featured_image
                            "
                            type="button"
                            class="text-sm font-medium text-red-600 hover:text-red-800"
                            @click="removeImage"
                        >
                            Remove image
                        </button>

                        <div
                            v-if="form.remove_featured_image"
                            class="rounded-md border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-700"
                        >
                            Image will be removed on save.
                        </div>
                    </div>
                </section>

                <section
                    class="grid gap-4 rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6"
                >
                    <h2 class="text-lg font-semibold">SEO</h2>

                    <div class="grid gap-2">
                        <Label for="page-meta-title">Meta Title</Label>
                        <Input
                            id="page-meta-title"
                            v-model="form.meta_title"
                            type="text"
                        />
                        <InputError :message="form.errors.meta_title" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="page-meta-description"
                            >Meta Description</Label
                        >
                        <textarea
                            id="page-meta-description"
                            v-model="form.meta_description"
                            rows="3"
                            class="rounded-md border px-3 py-2 text-sm"
                        />
                        <InputError :message="form.errors.meta_description" />
                    </div>
                </section>

                <section
                    class="grid gap-3 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm"
                >
                    <Button type="submit" :disabled="form.processing">
                        {{ submitLabel }}
                    </Button>
                    <Link
                        :href="cancelHref"
                        class="inline-flex h-9 items-center justify-center rounded-md border border-slate-200 px-3 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
                    >
                        Cancel
                    </Link>
                </section>
            </aside>
        </div>
    </form>
</template>
