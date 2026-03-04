import type { EmblaCarouselVueType } from 'embla-carousel-vue';
import type { HTMLAttributes, UnwrapRef } from 'vue';

export interface CarouselProps {
    opts?: Parameters<EmblaCarouselVueType>[1];
    plugins?: Parameters<EmblaCarouselVueType>[2];
    orientation?: 'horizontal' | 'vertical';
    class?: HTMLAttributes['class'];
}

export interface CarouselEmits {
    (e: 'init-api', payload: UnwrapRef<ReturnType<EmblaCarouselVueType>[1]>): void;
}

export interface CarouselApi {
    canScrollNext: () => boolean;
    canScrollPrev: () => boolean;
    scrollNext: () => void;
    scrollPrev: () => void;
    scrollTo: (index: number) => void;
}
