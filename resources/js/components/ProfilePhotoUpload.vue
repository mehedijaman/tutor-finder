<script setup lang="ts">
import { useForm, usePage, router } from '@inertiajs/vue3';
import { Camera, Trash2, Loader2 } from 'lucide-vue-next';
import { ref, computed } from 'vue';
import {
    update,
    destroy,
} from '@/actions/App/Http/Controllers/ProfilePhotoController';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import type { Auth } from '@/types';

const page = usePage<Auth>();

const photoInput = ref<HTMLInputElement | null>(null);
const photoPreview = ref<string | null>(null);

const form = useForm({
    _method: 'POST',
    photo: null as File | null,
});

const selectNewPhoto = () => {
    photoInput.value?.click();
};

const updatePhotoPreview = () => {
    const photo = photoInput.value?.files?.[0];

    if (!photo) return;

    form.photo = photo;

    const reader = new FileReader();

    reader.onload = (e) => {
        photoPreview.value = e.target?.result as string;
        // Automatically submit when photo is selected
        submit();
    };

    reader.readAsDataURL(photo);
};

const submit = () => {
    form.post(update.url(), {
        preserveScroll: true,
        onSuccess: () => {
            clearPhotoFileInput();
            photoPreview.value = null;
        },
    });
};

const deletePhoto = () => {
    router.delete(destroy.url(), {
        preserveScroll: true,
        onSuccess: () => {
            photoPreview.value = null;
            clearPhotoFileInput();
        },
    });
};

const clearPhotoFileInput = () => {
    if (photoInput.value?.value) {
        photoInput.value.value = '';
    }
};

const initials = computed(() => {
    return page.props.auth.user.name
        .split(' ')
        .map((n: string) => n[0])
        .join('')
        .toUpperCase()
        .slice(0, 2);
});
</script>

<template>
    <div class="flex flex-col items-center gap-6 py-4">
        <input
            type="file"
            class="hidden"
            ref="photoInput"
            accept="image/*"
            @change="updatePhotoPreview"
        />

        <div class="group relative">
            <Avatar
                class="h-32 w-32 border-4 border-white shadow-xl ring-1 ring-slate-100 transition-transform group-hover:scale-[1.02]"
            >
                <AvatarImage
                    :src="photoPreview || $page.props.auth.user.photo_url"
                    :alt="$page.props.auth.user.name"
                />
                <AvatarFallback
                    class="bg-slate-100 text-3xl font-bold text-slate-400"
                >
                    {{ initials }}
                </AvatarFallback>
            </Avatar>

            <button
                type="button"
                @click="selectNewPhoto"
                class="absolute right-0 bottom-0 transform rounded-full border-2 border-white bg-indigo-600 p-2.5 text-white shadow-lg transition-all hover:scale-110 hover:bg-indigo-700 active:scale-95"
                title="Change Photo"
            >
                <Camera v-if="!form.processing" class="h-4 w-4" />
                <Loader2 v-else class="h-4 w-4 animate-spin" />
            </button>
        </div>

        <div class="flex items-center gap-3">
            <Button
                v-if="
                    $page.props.auth.user.photo_url &&
                    !$page.props.auth.user.photo_url.includes('gravatar')
                "
                type="button"
                variant="outline"
                size="sm"
                @click="deletePhoto"
                class="h-9 rounded-xl border-rose-100 px-4 font-bold text-rose-500 transition-colors hover:bg-rose-50 hover:text-rose-600"
            >
                <Trash2 class="mr-2 h-3.5 w-3.5" />
                Remove Photo
            </Button>

            <p
                v-if="form.errors.photo"
                class="rounded-lg border border-red-100 bg-red-50 px-3 py-1.5 text-xs font-bold text-red-500 italic"
            >
                {{ form.errors.photo }}
            </p>
        </div>

        <p
            class="max-w-[200px] px-4 text-center text-[10px] leading-relaxed font-bold tracking-widest text-slate-400 uppercase italic"
        >
            PNG, JPG or WEBP. <br />
            Max size of 2MB allowed.
        </p>
    </div>
</template>
