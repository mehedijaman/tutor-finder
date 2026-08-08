<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowRight } from 'lucide-vue-next';
import type { Component } from 'vue';
import { computed } from 'vue';
import { cn } from '@/lib/utils';
import type { ActionCardColor } from '@/types/dashboard';

const props = withDefaults(
    defineProps<{
        title: string;
        description: string;
        href: string;
        icon: Component;
        color?: ActionCardColor;
        class?: string;
    }>(),
    { color: 'blue' },
);

const colorStyles = computed(() => {
    const variants: Record<
        ActionCardColor,
        {
            gradient: string;
            shadow: string;
            borderHover: string;
            arrowColor: string;
            glowShadow: string;
            accentBorder: string;
        }
    > = {
        blue: {
            gradient: 'from-blue-500 to-blue-600',
            shadow: 'shadow-blue-500/25',
            borderHover: 'hover:border-blue-200 dark:hover:border-blue-800',
            arrowColor: 'group-hover:text-blue-600',
            glowShadow: 'hover:shadow-blue-500/20',
            accentBorder: 'group-hover:border-l-blue-500',
        },
        emerald: {
            gradient: 'from-emerald-500 to-emerald-600',
            shadow: 'shadow-emerald-500/25',
            borderHover:
                'hover:border-emerald-200 dark:hover:border-emerald-800',
            arrowColor: 'group-hover:text-emerald-600',
            glowShadow: 'hover:shadow-emerald-500/20',
            accentBorder: 'group-hover:border-l-emerald-500',
        },
        amber: {
            gradient: 'from-amber-500 to-amber-600',
            shadow: 'shadow-amber-500/25',
            borderHover: 'hover:border-amber-200 dark:hover:border-amber-800',
            arrowColor: 'group-hover:text-amber-600',
            glowShadow: 'hover:shadow-amber-500/20',
            accentBorder: 'group-hover:border-l-amber-500',
        },
        violet: {
            gradient: 'from-violet-500 to-violet-600',
            shadow: 'shadow-violet-500/25',
            borderHover: 'hover:border-violet-200 dark:hover:border-violet-800',
            arrowColor: 'group-hover:text-violet-600',
            glowShadow: 'hover:shadow-violet-500/20',
            accentBorder: 'group-hover:border-l-violet-500',
        },
        rose: {
            gradient: 'from-rose-500 to-rose-600',
            shadow: 'shadow-rose-500/25',
            borderHover: 'hover:border-rose-200 dark:hover:border-rose-800',
            arrowColor: 'group-hover:text-rose-600',
            glowShadow: 'hover:shadow-rose-500/20',
            accentBorder: 'group-hover:border-l-rose-500',
        },
        cyan: {
            gradient: 'from-cyan-500 to-cyan-600',
            shadow: 'shadow-cyan-500/25',
            borderHover: 'hover:border-cyan-200 dark:hover:border-cyan-800',
            arrowColor: 'group-hover:text-cyan-600',
            glowShadow: 'hover:shadow-cyan-500/20',
            accentBorder: 'group-hover:border-l-cyan-500',
        },
        indigo: {
            gradient: 'from-indigo-500 to-indigo-600',
            shadow: 'shadow-indigo-500/25',
            borderHover: 'hover:border-indigo-200 dark:hover:border-indigo-800',
            arrowColor: 'group-hover:text-indigo-600',
            glowShadow: 'hover:shadow-indigo-500/20',
            accentBorder: 'group-hover:border-l-indigo-500',
        },
        slate: {
            gradient: 'from-slate-500 to-slate-600',
            shadow: 'shadow-slate-500/20',
            borderHover: 'hover:border-slate-300 dark:hover:border-slate-700',
            arrowColor: 'group-hover:text-slate-600',
            glowShadow: 'hover:shadow-slate-500/15',
            accentBorder: 'group-hover:border-l-slate-500',
        },
        orange: {
            gradient: 'from-orange-500 to-orange-600',
            shadow: 'shadow-orange-500/25',
            borderHover: 'hover:border-orange-200 dark:hover:border-orange-800',
            arrowColor: 'group-hover:text-orange-600',
            glowShadow: 'hover:shadow-orange-500/20',
            accentBorder: 'group-hover:border-l-orange-500',
        },
        purple: {
            gradient: 'from-purple-500 to-purple-600',
            shadow: 'shadow-purple-500/25',
            borderHover: 'hover:border-purple-200 dark:hover:border-purple-800',
            arrowColor: 'group-hover:text-purple-600',
            glowShadow: 'hover:shadow-purple-500/20',
            accentBorder: 'group-hover:border-l-purple-500',
        },
        teal: {
            gradient: 'from-teal-500 to-teal-600',
            shadow: 'shadow-teal-500/25',
            borderHover: 'hover:border-teal-200 dark:hover:border-teal-800',
            arrowColor: 'group-hover:text-teal-600',
            glowShadow: 'hover:shadow-teal-500/20',
            accentBorder: 'group-hover:border-l-teal-500',
        },
        pink: {
            gradient: 'from-pink-500 to-pink-600',
            shadow: 'shadow-pink-500/25',
            borderHover: 'hover:border-pink-200 dark:hover:border-pink-800',
            arrowColor: 'group-hover:text-pink-600',
            glowShadow: 'hover:shadow-pink-500/20',
            accentBorder: 'group-hover:border-l-pink-500',
        },
    };

    return variants[props.color] ?? variants.blue;
});
</script>

<template>
    <Link
        :href="href"
        :class="
            cn(
                'group relative overflow-hidden rounded-2xl border border-border/60 bg-card p-5 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-lg',
                colorStyles.borderHover,
                colorStyles.glowShadow,
                colorStyles.accentBorder,
                'border-l-[3px] border-l-transparent',
                props.class,
            )
        "
    >
        <!-- Decorative dot pattern -->
        <div
            class="pointer-events-none absolute inset-0 opacity-[0.015]"
            style="
                background-image: radial-gradient(
                    circle,
                    currentColor 1px,
                    transparent 1px
                );
                background-size: 16px 16px;
            "
            aria-hidden="true"
        />

        <div class="flex items-start gap-4">
            <div
                class="relative flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-linear-to-br text-white shadow-md ring-1 ring-white/10 transition-all duration-300 group-hover:scale-110 group-hover:shadow-lg"
                :class="[colorStyles.gradient, colorStyles.shadow]"
            >
                <component :is="icon" class="relative h-5 w-5" />
            </div>

            <div class="min-w-0 flex-1">
                <h3
                    class="text-sm leading-snug font-semibold tracking-tight text-card-foreground"
                >
                    {{ title }}
                </h3>

                <p class="mt-1 text-xs leading-relaxed text-muted-foreground">
                    {{ description }}
                </p>
            </div>

            <div
                class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-lg transition-all duration-300"
            >
                <ArrowRight
                    class="h-3.5 w-3.5 text-muted-foreground/50 transition-all duration-300 group-hover:translate-x-1"
                    :class="colorStyles.arrowColor"
                />
            </div>
        </div>
    </Link>
</template>
