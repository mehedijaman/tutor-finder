<script setup>
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';

defineProps({
    title: {
        type: String,
        required: true,
    },
    description: {
        type: String,
        default: '',
    },
});
</script>

<template>
    <Card class="gap-0 overflow-hidden py-0 shadow-sm">
        <CardHeader class="border-b bg-muted/30 px-4 py-3">
            <CardTitle class="text-sm font-semibold">{{ title }}</CardTitle>
            <CardDescription v-if="description" class="text-xs">
                {{ description }}
            </CardDescription>
        </CardHeader>

        <CardContent class="space-y-4 px-4 py-4">
            <slot />
        </CardContent>
    </Card>
</template>
