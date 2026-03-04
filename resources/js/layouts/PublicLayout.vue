<script setup lang="ts">
import SiteFooter from '@/components/SiteFooter.vue';
import SiteHeader from '@/components/SiteHeader.vue';

defineProps<{
    title?: string;
}>();
</script>

<template>
    <div
        class="flex min-h-screen flex-col font-sans text-slate-900 antialiased"
    >
        <SiteHeader variant="full" />
        <main class="flex-1">
            <slot />
        </main>
        <SiteFooter variant="full" />
    </div>
</template>
