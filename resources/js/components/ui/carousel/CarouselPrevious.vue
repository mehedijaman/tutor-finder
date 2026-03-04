<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { ArrowLeft } from 'lucide-vue-next';
import { useCarousel } from './useCarousel';
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';

const props = defineProps<{
    class?: HTMLAttributes['class'];
}>();

const { orientation, canScrollPrev, scrollPrev } = useCarousel();
</script>

<template>
    <Button
        :disabled="!canScrollPrev"
        :class="
            cn(
                'absolute h-8 w-8 touch-manipulation rounded-full p-0',
                orientation === 'horizontal'
                    ? 'top-1/2 -left-12 -translate-y-1/2'
                    : '-top-12 left-1/2 -translate-x-1/2 rotate-90',
                props.class,
            )
        "
        variant="outline"
        @click="scrollPrev"
    >
        <ArrowLeft class="h-4 w-4" />
        <span class="sr-only">Previous slide</span>
    </Button>
</template>
