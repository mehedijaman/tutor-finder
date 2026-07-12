<script setup lang="ts">
defineProps<{
    title: string;
    description?: string;
}>();
</script>

<template>
    <div
        class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between"
    >
        <div class="min-w-0 flex-1 space-y-1">
            <h1
                class="text-2xl font-bold tracking-tight text-foreground sm:text-3xl"
            >
                {{ title }}
            </h1>
            <p
                v-if="description"
                class="text-sm text-muted-foreground"
            >
                {{ description }}
            </p>
            <slot name="description" />
        </div>
        <div
            v-if="$slots.actions"
            class="flex shrink-0 flex-wrap items-center gap-2"
        >
            <slot name="actions" />
        </div>
    </div>
</template>
