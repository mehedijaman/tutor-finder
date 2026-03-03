<script setup lang="ts">
import PublicFooter from '@/components/public/PublicFooter.vue';
import PublicNavbar from '@/components/public/PublicNavbar.vue';

defineProps<{
    title?: string;
}>();
</script>

<template>
    <div class="flex min-h-screen flex-col">
        <PublicNavbar />
        <main class="flex-1">
            <slot />
        </main>
        <PublicFooter />
    </div>
</template>
