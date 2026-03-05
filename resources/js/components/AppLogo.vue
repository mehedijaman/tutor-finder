<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { useSiteSettings } from '@/composables/useSiteSettings';

defineProps<{
    showSlogan?: boolean;
}>();

const { siteName, slogan, logoUrl } = useSiteSettings();
</script>

<template>
    <div
        class="flex aspect-square size-8 items-center justify-center overflow-hidden rounded-md bg-sidebar-primary text-sidebar-primary-foreground"
    >
        <img
            v-if="logoUrl"
            :src="logoUrl"
            :alt="siteName"
            class="h-full w-full object-cover"
        />
        <AppLogoIcon
            v-else
            class="size-5 fill-current text-white dark:text-black"
        />
    </div>
    <div class="ml-1 grid flex-1 text-left text-sm">
        <span class="mb-0.5 truncate leading-tight font-semibold">{{
            siteName
        }}</span>
        <span
            v-if="showSlogan && slogan"
            class="truncate text-xs leading-tight text-sidebar-foreground/70"
            >{{ slogan }}</span
        >
    </div>
</template>
