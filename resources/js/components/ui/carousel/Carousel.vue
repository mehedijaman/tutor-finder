<script setup lang="ts">
import { useProvideCarousel } from './useCarousel';
import type { CarouselEmits, CarouselProps } from './interface';
import { cn } from '@/lib/utils';

const props = withDefaults(defineProps<CarouselProps>(), {
    orientation: 'horizontal',
});

const emits = defineEmits<CarouselEmits>();

const { canScrollNext, canScrollPrev, carouselApi, carouselRef, orientation } =
    useProvideCarousel(props, emits);

defineExpose({
    canScrollNext,
    canScrollPrev,
    carouselApi,
});
</script>

<template>
    <div
        :class="
            cn(
                'relative',
                props.class,
            )
        "
        role="region"
        aria-roledescription="carousel"
    >
        <slot
            :can-scroll-next="canScrollNext"
            :can-scroll-prev="canScrollPrev"
            :carousel-api="carouselApi"
            :carousel-ref="carouselRef"
            :orientation="orientation"
        />
    </div>
</template>
