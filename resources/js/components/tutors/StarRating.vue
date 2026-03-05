<script setup lang="ts">
import { Star } from 'lucide-vue-next';
import { computed } from 'vue';

const props = withDefaults(
    defineProps<{
        rating: number;
        size?: 'sm' | 'md' | 'lg';
        showValue?: boolean;
        reviewCount?: number;
        interactive?: boolean;
    }>(),
    {
        size: 'sm',
        showValue: false,
        reviewCount: undefined,
        interactive: false,
    },
);

const emit = defineEmits<{
    select: [rating: number];
}>();

const sizeClasses = computed(() => {
    const map = {
        sm: 'h-3.5 w-3.5',
        md: 'h-4.5 w-4.5',
        lg: 'h-5.5 w-5.5',
    };
    return map[props.size];
});

const textSizeClass = computed(() => {
    const map = {
        sm: 'text-xs',
        md: 'text-sm',
        lg: 'text-base',
    };
    return map[props.size];
});

function handleClick(star: number): void {
    if (props.interactive) {
        emit('select', star);
    }
}
</script>

<template>
    <div class="inline-flex items-center gap-1">
        <div class="flex items-center gap-0.5">
            <button
                v-for="star in 5"
                :key="star"
                type="button"
                :class="[
                    'transition-colors',
                    interactive
                        ? 'cursor-pointer hover:scale-110'
                        : 'cursor-default',
                ]"
                :disabled="!interactive"
                @click="handleClick(star)"
            >
                <Star
                    :class="[
                        sizeClasses,
                        star <= Math.round(rating)
                            ? 'fill-amber-400 text-amber-400'
                            : 'fill-slate-200 text-slate-200 dark:fill-slate-600 dark:text-slate-600',
                    ]"
                />
            </button>
        </div>
        <span
            v-if="showValue"
            :class="[textSizeClass, 'font-semibold text-slate-700']"
        >
            {{ rating > 0 ? rating.toFixed(1) : '0.0' }}
        </span>
        <span
            v-if="reviewCount !== undefined"
            :class="[textSizeClass, 'text-slate-500']"
        >
            ({{ reviewCount }})
        </span>
    </div>
</template>
