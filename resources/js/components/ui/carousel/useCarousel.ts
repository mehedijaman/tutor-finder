import type { UnwrapRef } from 'vue';
import type { CarouselEmits, CarouselProps } from './interface';
import emblaCarouselVue from 'embla-carousel-vue';
import { computed, inject, provide, ref, watch } from 'vue';

const CAROUSEL_INJECTION_KEY = Symbol('carousel');

interface CarouselContext {
    carouselRef: ReturnType<typeof emblaCarouselVue>[0];
    carouselApi: ReturnType<typeof emblaCarouselVue>[1];
    canScrollPrev: ReturnType<typeof ref<boolean>>;
    canScrollNext: ReturnType<typeof ref<boolean>>;
    scrollPrev: () => void;
    scrollNext: () => void;
    orientation: 'horizontal' | 'vertical';
}

export function useProvideCarousel(
    props: CarouselProps,
    emits: CarouselEmits,
) {
    const [carouselRef, carouselApi] = emblaCarouselVue(
        {
            ...props.opts,
            axis: props.orientation === 'horizontal' ? 'x' : 'y',
        },
        props.plugins,
    );

    function scrollPrev() {
        carouselApi.value?.scrollPrev();
    }

    function scrollNext() {
        carouselApi.value?.scrollNext();
    }

    const canScrollNext = ref(false);
    const canScrollPrev = ref(false);

    function onSelect(api: NonNullable<UnwrapRef<typeof carouselApi>>) {
        canScrollNext.value = api.canScrollNext();
        canScrollPrev.value = api.canScrollPrev();
    }

    watch(carouselApi, (api) => {
        if (!api) {
            return;
        }

        onSelect(api);
        api.on('reInit', () => onSelect(api));
        api.on('select', () => onSelect(api));

        emits('init-api', api);
    });

    const orientation = computed(() => props.orientation || 'horizontal');

    provide<CarouselContext>(CAROUSEL_INJECTION_KEY, {
        carouselRef,
        carouselApi,
        canScrollPrev,
        canScrollNext,
        scrollPrev,
        scrollNext,
        orientation: orientation.value,
    });

    return {
        carouselRef,
        carouselApi,
        canScrollPrev,
        canScrollNext,
        scrollPrev,
        scrollNext,
        orientation,
    };
}

export function useCarousel() {
    const context = inject<CarouselContext>(CAROUSEL_INJECTION_KEY);

    if (!context) {
        throw new Error('useCarousel must be used within a <Carousel />');
    }

    return context;
}
