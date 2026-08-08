<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { Upload, X } from 'lucide-vue-next';
import { ref, watch, computed, onBeforeUnmount } from 'vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Switch } from '@/components/ui/switch';
import { Textarea } from '@/components/ui/textarea';
import { slugify } from '@/composables/useAutoSlug';

type SelectOption = {
    value: string;
    label: string;
};

type TutorialData = {
    id?: number;
    title?: string;
    slug?: string;
    video_url?: string;
    audience?: { value: string } | string;
    description?: string | null;
    is_active?: boolean;
    sort_order?: number;
    thumbnail_url?: string | null;
};

const props = withDefaults(
    defineProps<{
        action: string;
        method?: 'post' | 'put';
        submitLabel: string;
        cancelHref?: string;
        audienceOptions: SelectOption[];
        initial?: TutorialData;
    }>(),
    {
        method: 'post',
        cancelHref: '/admin/tutorials',
    },
);

const isEdit = computed(() => props.method === 'put');

const resolveAudience = (
    audience: { value: string } | string | undefined,
): string => {
    if (!audience) {
        return 'all';
    }
    if (typeof audience === 'object' && 'value' in audience) {
        return audience.value;
    }
    return String(audience);
};

const form = useForm({
    title: props.initial?.title ?? '',
    slug: props.initial?.slug ?? '',
    video_url: props.initial?.video_url ?? '',
    audience: resolveAudience(props.initial?.audience),
    description: props.initial?.description ?? '',
    is_active: props.initial?.is_active ?? true,
    sort_order: props.initial?.sort_order ?? 0,
    thumbnail: null as File | null,
    remove_thumbnail: false,
});

const imagePreviewUrl = ref<string | null>(
    props.initial?.thumbnail_url ?? null,
);
const temporaryImageUrl = ref<string | null>(null);
const isDragging = ref(false);
const fileInputRef = ref<HTMLInputElement | null>(null);

function handleFileSelect(file: File | null): void {
    form.thumbnail = file;
    form.remove_thumbnail = false;

    clearTemporaryImageUrl();

    if (file) {
        temporaryImageUrl.value = URL.createObjectURL(file);
        imagePreviewUrl.value = temporaryImageUrl.value;
    } else {
        imagePreviewUrl.value = props.initial?.thumbnail_url ?? null;
    }
}

const onImageChange = (event: Event): void => {
    const input = event.target as HTMLInputElement;
    const file = input.files?.[0] ?? null;
    handleFileSelect(file);
};

function onDrop(event: DragEvent): void {
    isDragging.value = false;
    const file = event.dataTransfer?.files?.[0] ?? null;
    if (file && file.type.startsWith('image/')) {
        handleFileSelect(file);
    }
}

function onDragOver(): void {
    isDragging.value = true;
}

function onDragLeave(): void {
    isDragging.value = false;
}

function triggerFileInput(): void {
    fileInputRef.value?.click();
}

const removeImage = (): void => {
    form.thumbnail = null;
    form.remove_thumbnail = true;
    clearTemporaryImageUrl();
    imagePreviewUrl.value = null;
    if (fileInputRef.value) {
        fileInputRef.value.value = '';
    }
};

const clearTemporaryImageUrl = (): void => {
    if (temporaryImageUrl.value) {
        URL.revokeObjectURL(temporaryImageUrl.value);
        temporaryImageUrl.value = null;
    }
};

onBeforeUnmount(() => {
    clearTemporaryImageUrl();
});

watch(
    () => form.title,
    (newTitle) => {
        if (!isEdit.value) {
            form.slug = slugify(newTitle);
        }
    },
);

/**
 * Extract embed URL for video preview.
 */
function getVideoEmbedUrl(url: string): string | null {
    if (!url) {
        return null;
    }
    const ytMatch = url.match(
        /(?:youtube\.com\/(?:watch\?v=|embed\/|shorts\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})/,
    );
    if (ytMatch) {
        return `https://www.youtube.com/embed/${ytMatch[1]}?rel=0`;
    }
    const vimeoMatch = url.match(/vimeo\.com\/(\d+)/);
    if (vimeoMatch) {
        return `https://player.vimeo.com/video/${vimeoMatch[1]}`;
    }
    return null;
}

const videoPreviewUrl = computed(() => getVideoEmbedUrl(form.video_url));

const submit = (): void => {
    if (props.method === 'put') {
        form.transform((data) => ({
            ...data,
            _method: 'put',
        })).post(props.action, {
            forceFormData: true,
            preserveScroll: true,
        });
    } else {
        form.post(props.action, {
            forceFormData: true,
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <form @submit.prevent="submit" class="space-y-6">
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
            <!-- Main Content -->
            <div class="space-y-6 lg:col-span-8">
                <div
                    class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                >
                    <div class="space-y-4">
                        <div>
                            <Label
                                for="title"
                                class="text-slate-800 dark:text-slate-200"
                                >Title</Label
                            >
                            <Input
                                id="title"
                                v-model="form.title"
                                type="text"
                                class="mt-1 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100"
                                placeholder="Enter tutorial title"
                            />
                            <InputError
                                :message="form.errors.title"
                                class="mt-1"
                            />
                        </div>

                        <div>
                            <Label
                                for="slug"
                                class="text-slate-800 dark:text-slate-200"
                                >Slug</Label
                            >
                            <Input
                                id="slug"
                                v-model="form.slug"
                                type="text"
                                class="mt-1 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100"
                                placeholder="auto-generated-from-title"
                            />
                            <InputError
                                :message="form.errors.slug"
                                class="mt-1"
                            />
                        </div>

                        <div>
                            <Label
                                for="video_url"
                                class="text-slate-800 dark:text-slate-200"
                                >Video URL</Label
                            >
                            <Input
                                id="video_url"
                                v-model="form.video_url"
                                type="url"
                                class="mt-1 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100"
                                placeholder="https://www.youtube.com/watch?v=..."
                            />
                            <p
                                class="mt-1 text-xs text-slate-500 dark:text-slate-400"
                            >
                                Supports YouTube, Vimeo, or any embeddable video
                                URL.
                            </p>
                            <InputError
                                :message="form.errors.video_url"
                                class="mt-1"
                            />

                            <!-- Video Preview -->
                            <div
                                v-if="videoPreviewUrl"
                                class="mt-3 overflow-hidden rounded-lg border border-slate-200 dark:border-slate-800"
                            >
                                <div class="aspect-video w-full bg-black">
                                    <iframe
                                        :src="videoPreviewUrl"
                                        class="h-full w-full"
                                        frameborder="0"
                                        allow="
                                            accelerometer;
                                            clipboard-write;
                                            encrypted-media;
                                            gyroscope;
                                            picture-in-picture;
                                        "
                                        allowfullscreen
                                    />
                                </div>
                            </div>
                        </div>

                        <div>
                            <Label
                                for="description"
                                class="text-slate-800 dark:text-slate-200"
                                >Description (optional)</Label
                            >
                            <Textarea
                                id="description"
                                v-model="form.description"
                                class="mt-1 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100"
                                rows="3"
                                placeholder="Brief description of this tutorial..."
                            />
                            <InputError
                                :message="form.errors.description"
                                class="mt-1"
                            />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6 lg:col-span-4">
                <!-- Publishing -->
                <div
                    class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                >
                    <h3
                        class="mb-4 text-sm font-semibold text-slate-900 dark:text-slate-100"
                    >
                        Publishing
                    </h3>
                    <div class="space-y-4">
                        <div>
                            <Label
                                for="audience"
                                class="text-slate-800 dark:text-slate-200"
                                >Audience</Label
                            >
                            <Select v-model="form.audience">
                                <SelectTrigger
                                    id="audience"
                                    class="mt-1 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100"
                                >
                                    <SelectValue
                                        placeholder="Select audience"
                                    />
                                </SelectTrigger>
                                <SelectContent
                                    class="dark:border-slate-800 dark:bg-slate-900"
                                >
                                    <SelectItem
                                        v-for="option in audienceOptions"
                                        :key="option.value"
                                        :value="option.value"
                                    >
                                        {{ option.label }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <InputError
                                :message="form.errors.audience"
                                class="mt-1"
                            />
                        </div>

                        <div class="flex items-center justify-between">
                            <Label
                                for="is_active"
                                class="text-slate-800 dark:text-slate-200"
                                >Active</Label
                            >
                            <Switch
                                id="is_active"
                                :model-value="form.is_active"
                                @update:model-value="
                                    (val: boolean) => (form.is_active = val)
                                "
                            />
                        </div>
                    </div>
                </div>

                <!-- Thumbnail -->
                <div
                    class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                >
                    <h3
                        class="mb-4 text-sm font-semibold text-slate-900 dark:text-slate-100"
                    >
                        Thumbnail
                    </h3>
                    <div class="space-y-3">
                        <div
                            v-if="imagePreviewUrl"
                            class="relative overflow-hidden rounded-lg border border-slate-200 dark:border-slate-700"
                        >
                            <img
                                :src="imagePreviewUrl"
                                alt="Thumbnail preview"
                                class="h-40 w-full object-cover"
                            />
                            <button
                                type="button"
                                class="absolute top-2 right-2 rounded-full bg-red-500 p-1 text-white shadow-sm hover:bg-red-600"
                                @click="removeImage"
                            >
                                <X class="h-4 w-4" />
                            </button>
                        </div>

                        <div
                            v-if="form.remove_thumbnail && isEdit"
                            class="rounded-md border border-amber-200 bg-amber-50 px-3 py-2 dark:border-amber-900/60 dark:bg-amber-950/40"
                        >
                            <p
                                class="text-xs text-amber-700 dark:text-amber-300"
                            >
                                Thumbnail will be removed on save.
                            </p>
                        </div>

                        <!-- Dropzone -->
                        <div
                            class="flex cursor-pointer flex-col items-center justify-center rounded-lg border-2 border-dashed px-4 py-6 text-center transition-colors"
                            :class="
                                isDragging
                                    ? 'border-blue-500 bg-blue-50 dark:bg-blue-950/40'
                                    : 'border-slate-300 bg-slate-50 hover:border-blue-400 hover:bg-blue-50/50 dark:border-slate-700 dark:bg-slate-800 dark:hover:bg-slate-700/50'
                            "
                            @click="triggerFileInput"
                            @drop.prevent="onDrop"
                            @dragover.prevent="onDragOver"
                            @dragleave.prevent="onDragLeave"
                        >
                            <Upload
                                class="mb-2 h-8 w-8 text-slate-400 dark:text-slate-500"
                            />
                            <p
                                class="text-sm font-medium text-slate-600 dark:text-slate-300"
                            >
                                {{
                                    isDragging
                                        ? 'Drop image here'
                                        : 'Click or drag & drop'
                                }}
                            </p>
                            <p
                                class="mt-1 text-xs text-slate-500 dark:text-slate-400"
                            >
                                Recommended: 640×360px (16:9). Max 2MB.
                            </p>
                            <input
                                ref="fileInputRef"
                                type="file"
                                accept="image/*"
                                class="hidden"
                                @change="onImageChange"
                            />
                        </div>
                        <InputError
                            :message="form.errors.thumbnail"
                            class="mt-1"
                        />
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex items-center gap-3">
                    <Button
                        type="submit"
                        :disabled="form.processing"
                        class="flex-1"
                    >
                        {{ form.processing ? 'Saving...' : submitLabel }}
                    </Button>
                    <Button
                        type="button"
                        variant="outline"
                        class="dark:border-slate-700 dark:text-slate-300"
                        as="a"
                        :href="cancelHref"
                    >
                        Cancel
                    </Button>
                </div>
            </div>
        </div>
    </form>
</template>
