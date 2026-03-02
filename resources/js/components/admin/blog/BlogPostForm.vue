<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import { Loader2, Plus, Search, X } from 'lucide-vue-next';
import { computed, onBeforeUnmount, ref, toRef, watch } from 'vue';
import ConfirmDialog from '@/components/admin/dialogs/ConfirmDialog.vue';
import CreateCategoryDialog from '@/components/admin/blog/CreateCategoryDialog.vue';
import CreateTagDialog from '@/components/admin/blog/CreateTagDialog.vue';
import MetaBoxCard from '@/components/admin/blog/MetaBoxCard.vue';
import TiptapEditor from '@/components/admin/blog/TiptapEditor.vue';
import InputError from '@/components/InputError.vue';
import { slugify, useAutoSlug } from '@/composables/useAutoSlug';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
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
        default: '/admin/blog/posts',
    },
    categories: {
        type: Array,
        default: () => [],
    },
    tags: {
        type: Array,
        default: () => [],
    },
    initial: {
        type: Object,
        default: () => ({
            title: '',
            slug: '',
            summary: '',
            content: '<p></p>',
            status: 'draft',
            published_at: '',
            category_ids: [],
            tag_ids: [],
            meta_title: '',
            meta_description: '',
            cover_url: null,
        }),
    },
});

function normalizeIdArray(values) {
    return Array.from(
        new Set(
            (values ?? [])
                .map((value) => Number(value))
                .filter((value) => Number.isFinite(value) && value > 0),
        ),
    );
}

function normalizeOption(option) {
    return {
        id: Number(option?.id ?? 0),
        name: String(option?.name ?? ''),
        slug: String(option?.slug ?? ''),
        status: String(option?.status ?? 'active'),
        image_url:
            typeof option?.image_url === 'string' && option.image_url !== ''
                ? option.image_url
                : null,
    };
}

function normalizeOptions(options) {
    return (options ?? [])
        .map((option) => normalizeOption(option))
        .filter((option) => Number.isFinite(option.id) && option.id > 0)
        .sort((first, second) => first.name.localeCompare(second.name));
}

const form = useForm({
    title: props.initial.title ?? '',
    slug: props.initial.slug ?? '',
    summary: props.initial.summary ?? '',
    content: props.initial.content ?? '<p></p>',
    status: props.initial.status ?? 'draft',
    published_at: props.initial.published_at ?? '',
    category_ids: normalizeIdArray(props.initial.category_ids),
    tag_ids: normalizeIdArray(props.initial.tag_ids),
    meta_title: props.initial.meta_title ?? '',
    meta_description: props.initial.meta_description ?? '',
    cover: null,
    remove_cover: false,
});

const categoryOptions = ref(normalizeOptions(props.categories));
const tagOptions = ref(normalizeOptions(props.tags));
const tagSearchQuery = ref('');

watch(
    () => props.categories,
    (value) => {
        categoryOptions.value = normalizeOptions(value);
    },
);

watch(
    () => props.tags,
    (value) => {
        tagOptions.value = normalizeOptions(value);
    },
);

const summaryLength = computed(() => String(form.summary ?? '').trim().length);
const selectedCategoryCount = computed(() => form.category_ids.length);
const selectedTagCount = computed(() => form.tag_ids.length);

const filteredTagOptions = computed(() => {
    const query = tagSearchQuery.value.trim().toLowerCase();

    if (query === '') {
        return tagOptions.value;
    }

    return tagOptions.value.filter((tag) =>
        `${tag.name} ${tag.slug}`.toLowerCase().includes(query),
    );
});

const selectedTags = computed(() =>
    tagOptions.value.filter((tag) => isTagSelected(tag.id)),
);

const coverPreviewUrl = ref(props.initial.cover_url ?? null);
const temporaryCoverUrl = ref(null);
const isCreateCategoryDialogOpen = ref(false);
const isCreateTagDialogOpen = ref(false);
const isRemoveCoverConfirmOpen = ref(false);
const submitIntent = ref('primary');

const isEditMode = computed(() => props.method.toLowerCase() === 'put');
const primaryActionLabel = computed(() => props.submitLabel || 'Publish');
const isPrimarySubmitting = computed(
    () => form.processing && submitIntent.value === 'primary',
);
const isDraftSubmitting = computed(
    () => form.processing && submitIntent.value === 'draft',
);

const isInitiallyAuto = (() => {
    const sourceTitle = String(props.initial.title ?? '');
    const currentSlug = String(props.initial.slug ?? '');

    if (currentSlug === '') {
        return true;
    }

    return slugify(sourceTitle) === currentSlug;
})();

const { autoSlug, onManualSlugInput, toggleAutoSlug } = useAutoSlug(
    toRef(form, 'title'),
    toRef(form, 'slug'),
    { initiallyAuto: isInitiallyAuto },
);

function clearTemporaryCoverUrl() {
    if (temporaryCoverUrl.value !== null) {
        URL.revokeObjectURL(temporaryCoverUrl.value);
        temporaryCoverUrl.value = null;
    }
}

function onCoverChange(event) {
    const target = event.target;
    const file = target.files?.[0] ?? null;

    clearTemporaryCoverUrl();

    form.cover = file;
    form.remove_cover = false;

    if (file !== null) {
        temporaryCoverUrl.value = URL.createObjectURL(file);
        coverPreviewUrl.value = temporaryCoverUrl.value;

        return;
    }

    coverPreviewUrl.value = props.initial.cover_url ?? null;
}

function requestCoverRemoval() {
    isRemoveCoverConfirmOpen.value = true;
}

function confirmCoverRemoval() {
    form.remove_cover = true;
    form.cover = null;
    coverPreviewUrl.value = null;
    clearTemporaryCoverUrl();
    isRemoveCoverConfirmOpen.value = false;
}

function keepCover() {
    form.remove_cover = false;

    if (temporaryCoverUrl.value !== null) {
        coverPreviewUrl.value = temporaryCoverUrl.value;

        return;
    }

    coverPreviewUrl.value = props.initial.cover_url ?? null;
}

function isCategorySelected(categoryId) {
    const normalizedId = Number(categoryId);

    return form.category_ids.some((value) => Number(value) === normalizedId);
}

function toggleCategorySelection(categoryId) {
    const normalizedId = Number(categoryId);

    if (!Number.isFinite(normalizedId) || normalizedId <= 0) {
        return;
    }

    if (isCategorySelected(normalizedId)) {
        form.category_ids = form.category_ids.filter(
            (value) => Number(value) !== normalizedId,
        );

        return;
    }

    form.category_ids = [...form.category_ids, normalizedId];
}

function isTagSelected(tagId) {
    const normalizedId = Number(tagId);

    return form.tag_ids.some((value) => Number(value) === normalizedId);
}

function toggleTagSelection(tagId) {
    const normalizedId = Number(tagId);

    if (!Number.isFinite(normalizedId) || normalizedId <= 0) {
        return;
    }

    if (isTagSelected(normalizedId)) {
        form.tag_ids = form.tag_ids.filter((value) => Number(value) !== normalizedId);

        return;
    }

    form.tag_ids = [...form.tag_ids, normalizedId];
}

function upsertCategoryOption(payload) {
    const normalized = normalizeOption(payload);

    if (normalized.id <= 0) {
        return;
    }

    const existing = categoryOptions.value.some(
        (category) => category.id === normalized.id,
    );

    categoryOptions.value = existing
        ? categoryOptions.value.map((category) =>
              category.id === normalized.id ? normalized : category,
          )
        : [...categoryOptions.value, normalized].sort((first, second) =>
              first.name.localeCompare(second.name),
          );

    if (!isCategorySelected(normalized.id)) {
        form.category_ids = [...form.category_ids, normalized.id];
    }
}

function upsertTagOption(payload) {
    const normalized = normalizeOption(payload);

    if (normalized.id <= 0) {
        return;
    }

    const existing = tagOptions.value.some((tag) => tag.id === normalized.id);

    tagOptions.value = existing
        ? tagOptions.value.map((tag) =>
              tag.id === normalized.id ? normalized : tag,
          )
        : [...tagOptions.value, normalized].sort((first, second) =>
              first.name.localeCompare(second.name),
          );

    if (!isTagSelected(normalized.id)) {
        form.tag_ids = [...form.tag_ids, normalized.id];
    }
}

function submitWithIntent(intent) {
    submitIntent.value = intent;

    if (intent === 'draft') {
        form.status = 'draft';
    }

    if (intent === 'primary' && !isEditMode.value) {
        form.status = 'published';
    }

    submit();
}

function submit() {
    const payload = {
        title: form.title,
        slug: form.slug,
        summary: form.summary,
        content: form.content,
        status: form.status,
        published_at: form.published_at || null,
        category_ids: normalizeIdArray(form.category_ids),
        tag_ids: normalizeIdArray(form.tag_ids),
        meta_title: form.meta_title,
        meta_description: form.meta_description,
        cover: form.cover,
        remove_cover: form.remove_cover,
    };

    const options = {
        preserveScroll: true,
        forceFormData: true,
        onFinish: () => {
            submitIntent.value = 'primary';
        },
    };

    if (isEditMode.value) {
        form
            .transform(() => ({
                ...payload,
                _method: 'put',
            }))
            .post(props.action, options);

        return;
    }

    form.transform(() => payload).post(props.action, options);
}

onBeforeUnmount(() => {
    clearTemporaryCoverUrl();
});
</script>

<template>
    <form class="space-y-6" @submit.prevent="submitWithIntent('primary')">
        <div class="grid grid-cols-12 gap-6">
            <div class="col-span-12 space-y-6 lg:col-span-8">
                <Card class="gap-0 py-0 shadow-sm">
                    <CardHeader class="border-b px-5 py-4">
                        <CardTitle class="text-lg">Post Details</CardTitle>
                        <CardDescription>
                            Start with a clear headline and short summary.
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-5 px-5 py-5">
                        <div class="grid gap-2">
                            <Label for="post-title">Title</Label>
                            <Input
                                id="post-title"
                                v-model="form.title"
                                type="text"
                                required
                                class="h-12 text-base"
                            />
                            <InputError :message="form.errors.title" />
                        </div>

                        <div class="grid gap-2">
                            <div class="flex items-center justify-between gap-3">
                                <Label for="post-slug">Slug</Label>
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
                                id="post-slug"
                                :model-value="form.slug"
                                type="text"
                                @update:model-value="onManualSlugInput"
                            />
                            <p class="text-xs text-muted-foreground">
                                SEO-friendly URL segment for this post.
                            </p>
                            <InputError :message="form.errors.slug" />
                        </div>

                        <div class="grid gap-2">
                            <div class="flex items-center justify-between gap-3">
                                <Label for="post-summary">Summary</Label>
                                <span class="text-xs text-muted-foreground">
                                    {{ summaryLength }}/1000
                                </span>
                            </div>
                            <textarea
                                id="post-summary"
                                v-model="form.summary"
                                rows="4"
                                class="rounded-md border px-3 py-2 text-sm"
                                placeholder="Short excerpt shown on blog cards"
                            ></textarea>
                            <InputError :message="form.errors.summary" />
                        </div>
                    </CardContent>
                </Card>

                <Card class="gap-0 py-0 shadow-sm">
                    <CardHeader class="border-b px-5 py-4">
                        <CardTitle class="text-lg">Content</CardTitle>
                        <CardDescription>
                            Write the full article body with rich formatting.
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-3 px-5 py-5">
                        <TiptapEditor v-model="form.content" />
                        <p class="text-xs text-muted-foreground">
                            Tip: paste or drag images directly into the editor to upload.
                        </p>
                        <InputError :message="form.errors.content" />
                    </CardContent>
                </Card>

                <div
                    class="flex flex-wrap items-center justify-between gap-3 rounded-xl border bg-white px-4 py-3 shadow-sm lg:sticky lg:bottom-4"
                >
                    <Link
                        :href="cancelHref"
                        class="text-sm font-medium text-muted-foreground underline-offset-4 hover:underline"
                    >
                        Cancel
                    </Link>

                    <div class="flex flex-wrap items-center gap-2">
                        <Button
                            type="button"
                            variant="outline"
                            :disabled="form.processing"
                            @click="submitWithIntent('draft')"
                        >
                            <Loader2
                                v-if="isDraftSubmitting"
                                class="mr-2 size-4 animate-spin"
                            />
                            Save Draft
                        </Button>
                        <Button type="submit" :disabled="form.processing">
                            <Loader2
                                v-if="isPrimarySubmitting"
                                class="mr-2 size-4 animate-spin"
                            />
                            {{ primaryActionLabel }}
                        </Button>
                    </div>
                </div>
            </div>

            <div class="col-span-12 space-y-4 lg:col-span-4">
                <MetaBoxCard title="Publication">
                    <div class="grid gap-2">
                        <Label for="post-status">Status</Label>
                        <select
                            id="post-status"
                            v-model="form.status"
                            class="h-10 rounded-md border px-3 text-sm"
                        >
                            <option value="draft">Draft</option>
                            <option value="published">Published</option>
                        </select>
                        <InputError :message="form.errors.status" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="post-published-at">Published At</Label>
                        <Input
                            id="post-published-at"
                            v-model="form.published_at"
                            type="datetime-local"
                        />
                        <InputError :message="form.errors.published_at" />
                    </div>

                    <div class="grid gap-2 sm:grid-cols-2">
                        <Button
                            type="button"
                            variant="outline"
                            class="w-full"
                            :disabled="form.processing"
                            @click="submitWithIntent('draft')"
                        >
                            Save Draft
                        </Button>
                        <Button
                            type="button"
                            class="w-full"
                            :disabled="form.processing"
                            @click="submitWithIntent('primary')"
                        >
                            {{ primaryActionLabel }}
                        </Button>
                    </div>
                </MetaBoxCard>

                <MetaBoxCard title="Categories" description="Choose one or more categories.">
                    <div class="flex items-center justify-between gap-2">
                        <Badge variant="secondary">
                            Selected {{ selectedCategoryCount }}
                        </Badge>
                        <Button
                            type="button"
                            size="sm"
                            variant="outline"
                            @click="isCreateCategoryDialogOpen = true"
                        >
                            <Plus class="mr-1 size-4" /> Add Category
                        </Button>
                    </div>

                    <div
                        v-if="categoryOptions.length"
                        class="max-h-52 space-y-2 overflow-y-auto rounded-md border p-3"
                    >
                        <label
                            v-for="category in categoryOptions"
                            :key="`post-category-${category.id}`"
                            class="flex items-center gap-2 text-sm"
                        >
                            <input
                                type="checkbox"
                                class="h-4 w-4 rounded border"
                                :checked="isCategorySelected(category.id)"
                                @change="toggleCategorySelection(category.id)"
                            >
                            <span>{{ category.name }}</span>
                        </label>
                    </div>
                    <div
                        v-else
                        class="rounded-md border border-dashed px-3 py-4 text-sm text-muted-foreground"
                    >
                        No categories yet. Use Add Category to create one.
                    </div>

                    <InputError :message="form.errors.category_ids" />
                    <InputError
                        v-for="(message, index) in form.errors"
                        :key="`category-error-${index}`"
                        :message="index.startsWith('category_ids.') ? message : ''"
                    />
                </MetaBoxCard>

                <MetaBoxCard title="Tags" description="Assign searchable labels for this post.">
                    <div class="flex items-center justify-between gap-2">
                        <Badge variant="secondary">
                            Selected {{ selectedTagCount }}
                        </Badge>
                        <Button
                            type="button"
                            size="sm"
                            variant="outline"
                            @click="isCreateTagDialogOpen = true"
                        >
                            <Plus class="mr-1 size-4" /> Add Tag
                        </Button>
                    </div>

                    <div v-if="selectedTags.length" class="flex flex-wrap gap-2">
                        <button
                            v-for="tag in selectedTags"
                            :key="`selected-tag-${tag.id}`"
                            type="button"
                            class="inline-flex items-center gap-1 rounded-full border bg-muted px-2.5 py-1 text-xs"
                            @click="toggleTagSelection(tag.id)"
                        >
                            {{ tag.name }}
                            <X class="size-3" />
                        </button>
                    </div>

                    <div class="relative">
                        <Search
                            class="pointer-events-none absolute top-1/2 left-2.5 size-4 -translate-y-1/2 text-muted-foreground"
                        />
                        <Input
                            v-model="tagSearchQuery"
                            type="text"
                            placeholder="Search tags"
                            class="pl-8"
                        />
                    </div>

                    <div
                        v-if="filteredTagOptions.length"
                        class="max-h-52 space-y-2 overflow-y-auto rounded-md border p-3"
                    >
                        <label
                            v-for="tag in filteredTagOptions"
                            :key="`post-tag-${tag.id}`"
                            class="flex items-center gap-2 text-sm"
                        >
                            <input
                                type="checkbox"
                                class="h-4 w-4 rounded border"
                                :checked="isTagSelected(tag.id)"
                                @change="toggleTagSelection(tag.id)"
                            >
                            <span>{{ tag.name }}</span>
                        </label>
                    </div>
                    <div
                        v-else
                        class="rounded-md border border-dashed px-3 py-4 text-sm text-muted-foreground"
                    >
                        No tags match your search.
                    </div>

                    <InputError :message="form.errors.tag_ids" />
                    <InputError
                        v-for="(message, index) in form.errors"
                        :key="`tag-error-${index}`"
                        :message="index.startsWith('tag_ids.') ? message : ''"
                    />
                </MetaBoxCard>

                <MetaBoxCard title="Cover Image" description="Shown on blog list and post header.">
                    <div
                        v-if="coverPreviewUrl"
                        class="overflow-hidden rounded-md border bg-muted/20"
                    >
                        <img
                            :src="coverPreviewUrl"
                            alt="Post Cover"
                            class="h-40 w-full object-cover"
                        >
                    </div>
                    <div
                        v-else
                        class="flex h-32 items-center justify-center rounded-md border border-dashed text-sm text-muted-foreground"
                    >
                        No cover image selected.
                    </div>

                    <Input type="file" accept="image/*" @change="onCoverChange" />
                    <p class="text-xs text-muted-foreground">
                        JPG, PNG, WEBP. Maximum 4MB.
                    </p>

                    <div v-if="form.remove_cover" class="space-y-2 rounded-md border border-amber-200 bg-amber-50 p-3">
                        <p class="text-sm text-amber-900">
                            Cover image will be removed when you save this post.
                        </p>
                        <Button type="button" variant="outline" @click="keepCover">
                            Keep Current Cover
                        </Button>
                    </div>
                    <Button
                        v-else-if="coverPreviewUrl || props.initial.cover_url"
                        type="button"
                        variant="destructive"
                        @click="requestCoverRemoval"
                    >
                        Remove Cover
                    </Button>

                    <InputError :message="form.errors.cover" />
                    <InputError :message="form.errors.remove_cover" />
                </MetaBoxCard>

                <MetaBoxCard title="SEO" description="Fallback uses title and summary when empty.">
                    <div class="grid gap-2">
                        <Label for="post-meta-title">Meta Title</Label>
                        <Input id="post-meta-title" v-model="form.meta_title" type="text" />
                        <InputError :message="form.errors.meta_title" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="post-meta-description">Meta Description</Label>
                        <textarea
                            id="post-meta-description"
                            v-model="form.meta_description"
                            rows="3"
                            class="rounded-md border px-3 py-2 text-sm"
                        ></textarea>
                        <InputError :message="form.errors.meta_description" />
                    </div>
                </MetaBoxCard>
            </div>
        </div>

        <CreateCategoryDialog
            :open="isCreateCategoryDialogOpen"
            @update:open="isCreateCategoryDialogOpen = $event"
            @created="upsertCategoryOption"
        />

        <CreateTagDialog
            :open="isCreateTagDialogOpen"
            @update:open="isCreateTagDialogOpen = $event"
            @created="upsertTagOption"
        />

        <ConfirmDialog
            v-model:open="isRemoveCoverConfirmOpen"
            title="Remove cover image?"
            description="The cover image will be removed when you save the post."
            confirm-label="Remove"
            cancel-label="Cancel"
            destructive
            @confirm="confirmCoverRemoval"
        />
    </form>
</template>
