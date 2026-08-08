<script setup lang="ts">
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';

const props = withDefaults(
    defineProps<{
        icon?: any;
        title: string;
        description?: string;
        accent?: 'indigo' | 'emerald' | 'amber' | 'blue' | 'violet';
    }>(),
    {
        icon: undefined,
        description: undefined,
        accent: 'indigo',
    },
);

const accentStyles: Record<
    string,
    {
        border: string;
        bg: string;
        iconBg: string;
        iconColor: string;
        descColor: string;
    }
> = {
    indigo: {
        border: 'border-indigo-100/50',
        bg: 'bg-indigo-50/30',
        iconBg: 'bg-indigo-100',
        iconColor: 'text-indigo-600',
        descColor: 'text-indigo-600/80',
    },
    emerald: {
        border: 'border-emerald-100/50',
        bg: 'bg-emerald-50/30',
        iconBg: 'bg-emerald-100',
        iconColor: 'text-emerald-600',
        descColor: 'text-emerald-600/80',
    },
    amber: {
        border: 'border-amber-100/50',
        bg: 'bg-amber-50/30',
        iconBg: 'bg-amber-100',
        iconColor: 'text-amber-600',
        descColor: 'text-amber-600/80',
    },
    blue: {
        border: 'border-blue-100/50',
        bg: 'bg-blue-50/30',
        iconBg: 'bg-blue-100',
        iconColor: 'text-blue-600',
        descColor: 'text-blue-600/80',
    },
    violet: {
        border: 'border-violet-100/50',
        bg: 'bg-violet-50/30',
        iconBg: 'bg-violet-100',
        iconColor: 'text-violet-600',
        descColor: 'text-violet-600/80',
    },
};

const styles = $computed(
    () => accentStyles[props.accent] ?? accentStyles.indigo,
);
</script>

<template>
    <Card class="overflow-hidden rounded-2xl border-border/60 shadow-sm">
        <CardHeader
            class="flex flex-row items-center justify-between border-b p-6"
            :class="[styles.bg, styles.border]"
        >
            <div class="flex items-center gap-3">
                <div
                    class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg"
                    :class="styles.iconBg"
                >
                    <component
                        :is="icon"
                        v-if="icon"
                        class="h-4 w-4"
                        :class="styles.iconColor"
                    />
                </div>
                <div>
                    <CardTitle class="text-lg font-bold text-card-foreground">
                        {{ title }}
                    </CardTitle>
                    <CardDescription
                        v-if="description"
                        class="text-xs font-medium tracking-wider uppercase"
                        :class="styles.descColor"
                    >
                        {{ description }}
                    </CardDescription>
                </div>
            </div>
            <slot name="header-actions" />
        </CardHeader>
        <CardContent class="p-6">
            <slot />
        </CardContent>
    </Card>
</template>
