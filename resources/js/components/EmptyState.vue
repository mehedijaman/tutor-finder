<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Inbox } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';

defineProps<{
    title?: string;
    description?: string;
    actionLabel?: string;
    actionHref?: string;
}>();
</script>

<template>
    <div
        class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-border/60 bg-card/50 px-6 py-12 text-center"
    >
        <div
            class="flex h-14 w-14 items-center justify-center rounded-2xl bg-muted"
        >
            <slot name="icon">
                <Inbox class="h-7 w-7 text-muted-foreground/60" />
            </slot>
        </div>

        <h3
            v-if="title"
            class="mt-4 text-base font-semibold text-card-foreground"
        >
            {{ title }}
        </h3>

        <p
            v-if="description"
            class="mt-1.5 max-w-sm text-sm text-muted-foreground"
        >
            {{ description }}
        </p>

        <slot name="action">
            <Button
                v-if="actionLabel && actionHref"
                variant="outline"
                class="mt-5"
                as-child
            >
                <Link :href="actionHref">
                    {{ actionLabel }}
                </Link>
            </Button>
        </slot>

        <slot />
    </div>
</template>
