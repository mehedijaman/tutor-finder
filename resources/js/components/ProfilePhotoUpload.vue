<script setup lang="ts">
import { ref, computed } from 'vue';
import { useForm, usePage, router } from '@inertiajs/vue3';
import { Camera, Trash2, Loader2 } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { update, destroy } from '@/actions/App/Http/Controllers/ProfilePhotoController';
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

        <div class="relative group">
            <Avatar class="h-32 w-32 border-4 border-white shadow-xl ring-1 ring-slate-100 transition-transform group-hover:scale-[1.02]">
                <AvatarImage :src="photoPreview || $page.props.auth.user.photo_url" :alt="$page.props.auth.user.name" />
                <AvatarFallback class="bg-slate-100 text-3xl font-bold text-slate-400">
                    {{ initials }}
                </AvatarFallback>
            </Avatar>

            <button 
                type="button"
                @click="selectNewPhoto"
                class="absolute bottom-0 right-0 p-2.5 bg-indigo-600 text-white rounded-full shadow-lg border-2 border-white hover:bg-indigo-700 transition-all transform hover:scale-110 active:scale-95"
                title="Change Photo"
            >
                <Camera v-if="!form.processing" class="h-4 w-4" />
                <Loader2 v-else class="h-4 w-4 animate-spin" />
            </button>
        </div>

        <div class="flex items-center gap-3">
            <Button 
                v-if="$page.props.auth.user.photo_url && !$page.props.auth.user.photo_url.includes('gravatar')"
                type="button" 
                variant="outline" 
                size="sm" 
                @click="deletePhoto"
                class="rounded-xl border-rose-100 text-rose-500 hover:bg-rose-50 hover:text-rose-600 transition-colors h-9 px-4 font-bold"
            >
                <Trash2 class="mr-2 h-3.5 w-3.5" />
                Remove Photo
            </Button>
            
            <p v-if="form.errors.photo" class="text-xs font-bold text-red-500 bg-red-50 px-3 py-1.5 rounded-lg border border-red-100 italic">
                {{ form.errors.photo }}
            </p>
        </div>

        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest text-center px-4 leading-relaxed italic max-w-[200px]">
            PNG, JPG or WEBP. <br> Max size of 2MB allowed.
        </p>
    </div>
</template>
