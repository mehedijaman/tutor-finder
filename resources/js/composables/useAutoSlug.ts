import { onBeforeUnmount, ref, watch } from 'vue';
import type { Ref } from 'vue';

type AutoSlugOptions = {
    delay?: number;
    initiallyAuto?: boolean;
};

export function slugify(value: string): string {
    return String(value)
        .toLowerCase()
        .trim()
        .replace(/[^a-z0-9\s-]/g, '')
        .replace(/\s+/g, '-')
        .replace(/-+/g, '-')
        .replace(/^-|-$/g, '');
}

export function useAutoSlug(
    source: Ref<string>,
    slug: Ref<string>,
    options: AutoSlugOptions = {},
) {
    const autoSlug = ref(options.initiallyAuto ?? true);
    let timer: ReturnType<typeof setTimeout> | null = null;
    const delay = options.delay ?? 250;

    const clearTimer = () => {
        if (timer) {
            clearTimeout(timer);
            timer = null;
        }
    };

    const regenerateSlug = () => {
        slug.value = slugify(source.value);
    };

    watch(
        source,
        () => {
            if (!autoSlug.value) {
                return;
            }

            clearTimer();
            timer = setTimeout(() => {
                regenerateSlug();
            }, delay);
        },
        { immediate: true },
    );

    onBeforeUnmount(() => {
        clearTimer();
    });

    const onManualSlugInput = (value: string) => {
        slug.value = slugify(value);
        autoSlug.value = false;
    };

    const toggleAutoSlug = () => {
        autoSlug.value = !autoSlug.value;

        if (autoSlug.value) {
            regenerateSlug();
        }
    };

    return {
        autoSlug,
        onManualSlugInput,
        regenerateSlug,
        toggleAutoSlug,
    };
}
