<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

type Props = {
    className?: HTMLAttributes['class'];
};

defineProps<Props>();
</script>

<template>
    <svg
        xmlns="http://www.w3.org/2000/svg"
        viewBox="0 0 100 120"
        :class="className"
        v-bind="$attrs"
    >
        <!-- Location Pin Outer Path -->
        <path
            d="M 50 5
               C 22 5, 5 22, 5 50
               C 5 78, 35 105, 50 115
               C 65 105, 95 78, 95 50
               C 95 22, 78 5, 50 5 Z"
            fill="none"
            stroke="#0eb0e6"
            stroke-width="7"
            stroke-linejoin="round"
        />
        <!-- Inner Circle Fill Background -->
        <circle cx="50" cy="50" r="36" fill="#0eb0e6" opacity="0.12" />
        <circle
            cx="50"
            cy="50"
            r="36"
            fill="none"
            stroke="#0eb0e6"
            stroke-width="4"
        />

        <!-- Graduation Cap (Mortarboard Top) -->
        <polygon points="50,28 76,40 50,52 24,40" fill="#1b2880" />

        <!-- Graduation Cap Skullcap -->
        <path
            d="M 33 45.5 V 56 C 33 62, 67 62, 67 56 V 45.5"
            fill="none"
            stroke="#1b2880"
            stroke-width="5"
            stroke-linecap="round"
        />

        <!-- Mortarboard Tassel -->
        <path
            d="M 68 40 V 58"
            fill="none"
            stroke="#0eb0e6"
            stroke-width="3"
            stroke-linecap="round"
        />
        <circle cx="68" cy="60" r="3" fill="#0eb0e6" />
    </svg>
</template>
