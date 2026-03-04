<script setup lang="ts">
defineProps<{
    title: string;
    description?: string;
}>();
</script>

<template>
    <div
        class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6 dark:border-slate-700/80 dark:bg-slate-900"
    >
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="space-y-1">
                <h1
                    class="text-2xl font-semibold tracking-tight text-slate-900 dark:text-slate-100"
                >
                    {{ title }}
                </h1>
                <p
                    v-if="description"
                    class="text-sm text-slate-600 dark:text-slate-400"
                >
                    {{ description }}
                </p>
                <slot name="description" />
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <slot name="actions" />
            </div>
        </div>
    </div>
</template>
