<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowUpRight, TrendingDown, TrendingUp } from 'lucide-vue-next';
import type { Component } from 'vue';
import { computed } from 'vue';
import { cn } from '@/lib/utils';
import type { StatCardColor } from '@/types/dashboard';

const props = withDefaults(
    defineProps<{
        label: string;
        value: number | string;
        subValue?: string;
        href: string;
        icon: Component;
        color?: StatCardColor;
        trend?: {
            direction: 'up' | 'down';
            value: string;
        };
        class?: string;
    }>(),
    { color: 'blue' },
);

const colorStyles = computed(() => {
    const variants: Record<
        StatCardColor,
        {
            iconGradient: string;
            iconColor: string;
            borderHover: string;
            gradientBar: string;
            labelColor: string;
            valueColor: string;
            glowShadow: string;
            bgAura: string;
            trendUp: string;
            trendDown: string;
        }
    > = {
        blue: {
            iconGradient: 'from-blue-500 to-indigo-600',
            iconColor: 'text-blue-600 dark:text-blue-400',
            borderHover: 'hover:border-blue-200 dark:hover:border-blue-800',
            gradientBar: 'from-blue-500 to-indigo-500',
            labelColor: 'text-blue-600 dark:text-blue-400',
            valueColor: 'text-blue-700 dark:text-blue-300',
            glowShadow: 'hover:shadow-blue-500/15',
            bgAura: 'bg-blue-500/[0.02]',
            trendUp: 'text-emerald-600 dark:text-emerald-400',
            trendDown: 'text-rose-600 dark:text-rose-400',
        },
        emerald: {
            iconGradient: 'from-emerald-500 to-teal-600',
            iconColor: 'text-emerald-600 dark:text-emerald-400',
            borderHover:
                'hover:border-emerald-200 dark:hover:border-emerald-800',
            gradientBar: 'from-emerald-500 to-teal-500',
            labelColor: 'text-emerald-600 dark:text-emerald-400',
            valueColor: 'text-emerald-700 dark:text-emerald-300',
            glowShadow: 'hover:shadow-emerald-500/15',
            bgAura: 'bg-emerald-500/[0.02]',
            trendUp: 'text-emerald-600 dark:text-emerald-400',
            trendDown: 'text-rose-600 dark:text-rose-400',
        },
        amber: {
            iconGradient: 'from-amber-500 to-orange-600',
            iconColor: 'text-amber-600 dark:text-amber-400',
            borderHover: 'hover:border-amber-200 dark:hover:border-amber-800',
            gradientBar: 'from-amber-500 to-orange-500',
            labelColor: 'text-amber-600 dark:text-amber-400',
            valueColor: 'text-amber-700 dark:text-amber-300',
            glowShadow: 'hover:shadow-amber-500/15',
            bgAura: 'bg-amber-500/[0.02]',
            trendUp: 'text-emerald-600 dark:text-emerald-400',
            trendDown: 'text-rose-600 dark:text-rose-400',
        },
        violet: {
            iconGradient: 'from-violet-500 to-purple-600',
            iconColor: 'text-violet-600 dark:text-violet-400',
            borderHover: 'hover:border-violet-200 dark:hover:border-violet-800',
            gradientBar: 'from-violet-500 to-purple-500',
            labelColor: 'text-violet-600 dark:text-violet-400',
            valueColor: 'text-violet-700 dark:text-violet-300',
            glowShadow: 'hover:shadow-violet-500/15',
            bgAura: 'bg-violet-500/[0.02]',
            trendUp: 'text-emerald-600 dark:text-emerald-400',
            trendDown: 'text-rose-600 dark:text-rose-400',
        },
        rose: {
            iconGradient: 'from-rose-500 to-pink-600',
            iconColor: 'text-rose-600 dark:text-rose-400',
            borderHover: 'hover:border-rose-200 dark:hover:border-rose-800',
            gradientBar: 'from-rose-500 to-pink-500',
            labelColor: 'text-rose-600 dark:text-rose-400',
            valueColor: 'text-rose-700 dark:text-rose-300',
            glowShadow: 'hover:shadow-rose-500/15',
            bgAura: 'bg-rose-500/[0.02]',
            trendUp: 'text-emerald-600 dark:text-emerald-400',
            trendDown: 'text-rose-600 dark:text-rose-400',
        },
        slate: {
            iconGradient: 'from-slate-500 to-slate-600',
            iconColor: 'text-slate-600 dark:text-slate-400',
            borderHover: 'hover:border-slate-300 dark:hover:border-slate-700',
            gradientBar: 'from-slate-400 to-slate-500',
            labelColor: 'text-slate-500 dark:text-slate-400',
            valueColor: 'text-slate-900 dark:text-slate-100',
            glowShadow: 'hover:shadow-slate-500/10',
            bgAura: 'bg-slate-500/[0.02]',
            trendUp: 'text-emerald-600 dark:text-emerald-400',
            trendDown: 'text-slate-600 dark:text-slate-400',
        },
        cyan: {
            iconGradient: 'from-cyan-500 to-sky-600',
            iconColor: 'text-cyan-600 dark:text-cyan-400',
            borderHover: 'hover:border-cyan-200 dark:hover:border-cyan-800',
            gradientBar: 'from-cyan-500 to-sky-500',
            labelColor: 'text-cyan-600 dark:text-cyan-400',
            valueColor: 'text-cyan-700 dark:text-cyan-300',
            glowShadow: 'hover:shadow-cyan-500/15',
            bgAura: 'bg-cyan-500/[0.02]',
            trendUp: 'text-emerald-600 dark:text-emerald-400',
            trendDown: 'text-rose-600 dark:text-rose-400',
        },
        orange: {
            iconGradient: 'from-orange-500 to-red-600',
            iconColor: 'text-orange-600 dark:text-orange-400',
            borderHover: 'hover:border-orange-200 dark:hover:border-orange-800',
            gradientBar: 'from-orange-500 to-red-500',
            labelColor: 'text-orange-600 dark:text-orange-400',
            valueColor: 'text-orange-700 dark:text-orange-300',
            glowShadow: 'hover:shadow-orange-500/15',
            bgAura: 'bg-orange-500/[0.02]',
            trendUp: 'text-emerald-600 dark:text-emerald-400',
            trendDown: 'text-rose-600 dark:text-rose-400',
        },
        indigo: {
            iconGradient: 'from-indigo-500 to-blue-600',
            iconColor: 'text-indigo-600 dark:text-indigo-400',
            borderHover: 'hover:border-indigo-200 dark:hover:border-indigo-800',
            gradientBar: 'from-indigo-500 to-blue-500',
            labelColor: 'text-indigo-600 dark:text-indigo-400',
            valueColor: 'text-indigo-700 dark:text-indigo-300',
            glowShadow: 'hover:shadow-indigo-500/15',
            bgAura: 'bg-indigo-500/[0.02]',
            trendUp: 'text-emerald-600 dark:text-emerald-400',
            trendDown: 'text-rose-600 dark:text-rose-400',
        },
        purple: {
            iconGradient: 'from-purple-500 to-violet-600',
            iconColor: 'text-purple-600 dark:text-purple-400',
            borderHover: 'hover:border-purple-200 dark:hover:border-purple-800',
            gradientBar: 'from-purple-500 to-violet-500',
            labelColor: 'text-purple-600 dark:text-purple-400',
            valueColor: 'text-purple-700 dark:text-purple-300',
            glowShadow: 'hover:shadow-purple-500/15',
            bgAura: 'bg-purple-500/[0.02]',
            trendUp: 'text-emerald-600 dark:text-emerald-400',
            trendDown: 'text-rose-600 dark:text-rose-400',
        },
        teal: {
            iconGradient: 'from-teal-500 to-emerald-600',
            iconColor: 'text-teal-600 dark:text-teal-400',
            borderHover: 'hover:border-teal-200 dark:hover:border-teal-800',
            gradientBar: 'from-teal-500 to-emerald-500',
            labelColor: 'text-teal-600 dark:text-teal-400',
            valueColor: 'text-teal-700 dark:text-teal-300',
            glowShadow: 'hover:shadow-teal-500/15',
            bgAura: 'bg-teal-500/[0.02]',
            trendUp: 'text-emerald-600 dark:text-emerald-400',
            trendDown: 'text-rose-600 dark:text-rose-400',
        },
        pink: {
            iconGradient: 'from-pink-500 to-rose-600',
            iconColor: 'text-pink-600 dark:text-pink-400',
            borderHover: 'hover:border-pink-200 dark:hover:border-pink-800',
            gradientBar: 'from-pink-500 to-rose-500',
            labelColor: 'text-pink-600 dark:text-pink-400',
            valueColor: 'text-pink-700 dark:text-pink-300',
            glowShadow: 'hover:shadow-pink-500/15',
            bgAura: 'bg-pink-500/[0.02]',
            trendUp: 'text-emerald-600 dark:text-emerald-400',
            trendDown: 'text-rose-600 dark:text-rose-400',
        },
    };

    return variants[props.color] ?? variants.blue;
});

function formatValue(val: number | string): string {
    if (typeof val === 'string') return val;
    if (val >= 1000000) return parseFloat((val / 1000000).toFixed(1)) + 'M';
    if (val >= 1000) return parseFloat((val / 1000).toFixed(1)) + 'K';
    return val.toLocaleString();
}
</script>

<template>
    <Link
        :href="href"
        :class="
            cn(
                'group relative overflow-hidden rounded-2xl border bg-card p-5 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-lg',
                colorStyles.borderHover,
                colorStyles.glowShadow,
                props.class,
            )
        "
    >
        <!-- Gradient top bar -->
        <div
            class="absolute inset-x-0 top-0 h-0.5 bg-gradient-to-r opacity-80"
            :class="colorStyles.gradientBar"
        />

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

        <!-- Background aura blob -->
        <div
            class="pointer-events-none absolute -top-6 -right-6 h-24 w-24 rounded-full opacity-0 blur-2xl transition-opacity duration-500 group-hover:opacity-100"
            :class="colorStyles.bgAura"
            aria-hidden="true"
        />

        <div class="flex items-start justify-between gap-3">
            <div class="min-w-0 flex-1">
                <p
                    class="truncate text-[11px] font-semibold tracking-[0.12em] uppercase"
                    :class="colorStyles.labelColor"
                >
                    {{ label }}
                </p>
                <p
                    class="mt-2 text-2xl leading-none font-bold tracking-tight sm:text-3xl"
                    :class="colorStyles.valueColor"
                >
                    {{ formatValue(value) }}
                </p>
                <p
                    v-if="subValue"
                    class="mt-1.5 text-xs font-medium text-muted-foreground"
                >
                    {{ subValue }}
                </p>
            </div>
            <div
                class="relative flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-linear-to-br text-white shadow-sm ring-1 ring-white/10 transition-all duration-300 group-hover:scale-110 group-hover:shadow-md"
                :class="colorStyles.iconGradient"
            >
                <component :is="icon" class="h-5 w-5" />
            </div>
        </div>

        <div class="mt-4 flex items-center gap-2">
            <span
                class="text-[11px] font-medium text-muted-foreground/70 transition-colors group-hover:text-foreground/80"
            >
                View details
            </span>
            <ArrowUpRight
                class="h-3.5 w-3.5 text-muted-foreground/40 transition-all duration-300 group-hover:translate-x-0.5 group-hover:-translate-y-0.5"
            />
        </div>

        <div
            v-if="trend"
            class="absolute top-5 right-5 flex items-center gap-1 rounded-full bg-background/80 px-2 py-0.5 text-[11px] font-semibold shadow-xs ring-1 ring-border/20 backdrop-blur-sm"
            :class="[
                trend.direction === 'up'
                    ? colorStyles.trendUp
                    : colorStyles.trendDown,
            ]"
        >
            <TrendingUp v-if="trend.direction === 'up'" class="h-3 w-3" />
            <TrendingDown v-else class="h-3 w-3" />
            {{ trend.value }}
        </div>
    </Link>
</template>
