<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import PublicLayout from '@/layouts/PublicLayout.vue';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <PublicLayout>
        <div
            class="flex min-h-[calc(100vh-200px)] flex-col items-center justify-center bg-slate-50 px-4 py-12 sm:px-6 lg:px-8"
        >
            <div class="w-full max-w-md space-y-6">
                <Card class="rounded-xl border-slate-200 bg-white shadow-lg">
                    <CardHeader class="space-y-1 pb-2 text-center">
                        <CardTitle
                            class="text-2xl font-bold tracking-tight text-slate-900"
                        >
                            {{ title }}
                        </CardTitle>
                        <CardDescription
                            v-if="description"
                            class="text-base text-slate-500"
                        >
                            {{ description }}
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="grid gap-4 pt-4">
                        <slot />
                    </CardContent>
                </Card>
            </div>
        </div>
    </PublicLayout>
</template>
