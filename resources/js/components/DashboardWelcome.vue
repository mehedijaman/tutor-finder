<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { CalendarDays } from 'lucide-vue-next';
import { computed } from 'vue';
import { useInitials } from '@/composables/useInitials';

const page = usePage();
const user = computed(() => page.props.auth?.user);
const { getInitials } = useInitials();

const greeting = computed(() => {
    const hour = new Date().getHours();
    if (hour < 12) return 'Good morning';
    if (hour < 17) return 'Good afternoon';
    return 'Good evening';
});

const roleLabel = computed(() => {
    const role = user.value?.role;
    if (role === 'admin') return 'Administrator';
    if (role === 'tutor') return 'Tutor';
    if (role === 'guardian') return 'Guardian';
    return 'User';
});

const roleBadgeClass = computed(() => {
    const role = user.value?.role;
    if (role === 'admin')
        return 'bg-violet-100 text-violet-700 dark:bg-violet-900/40 dark:text-violet-300';
    if (role === 'tutor')
        return 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300';
    if (role === 'guardian')
        return 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300';
    return 'bg-primary/10 text-primary';
});

const formattedDate = computed(() =>
    new Date().toLocaleDateString('en-US', {
        weekday: 'long',
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    }),
);
</script>

<template>
    <div
        class="relative overflow-hidden rounded-2xl border border-border/60 bg-linear-to-br from-primary/5 via-background to-background p-6 shadow-sm sm:p-8"
    >
        <!-- Decorative dot pattern -->
        <div
            class="pointer-events-none absolute inset-0 opacity-[0.02]"
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

        <!-- Background blur orbs -->
        <div
            class="pointer-events-none absolute -top-24 -right-24 h-64 w-64 rounded-full bg-primary/5 blur-3xl"
            aria-hidden="true"
        />
        <div
            class="pointer-events-none absolute -bottom-16 -left-16 h-48 w-48 rounded-full bg-primary/5 blur-3xl"
            aria-hidden="true"
        />

        <!-- Secondary accent orb -->
        <div
            class="pointer-events-none absolute top-1/2 right-1/3 h-32 w-32 rounded-full bg-primary/[0.02] blur-2xl"
            aria-hidden="true"
        />

        <div class="relative flex items-start justify-between gap-4">
            <div class="min-w-0 flex-1">
                <p class="text-sm font-medium text-muted-foreground">
                    {{ greeting }}
                </p>
                <h1
                    class="mt-1 text-2xl font-bold tracking-tight text-foreground sm:text-3xl"
                >
                    {{ user?.name ?? 'Welcome' }}
                </h1>
                <div
                    class="mt-3 flex flex-wrap items-center gap-3 text-sm text-muted-foreground"
                >
                    <span
                        class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-semibold"
                        :class="roleBadgeClass"
                    >
                        {{ roleLabel }}
                    </span>
                    <span class="inline-flex items-center gap-1.5">
                        <CalendarDays class="h-3.5 w-3.5" />
                        {{ formattedDate }}
                    </span>
                </div>
            </div>

            <div
                v-if="user"
                class="relative flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-linear-to-br from-primary to-primary/80 text-lg font-bold text-primary-foreground shadow-lg ring-2 shadow-primary/25 ring-primary/10"
            >
                {{ getInitials(user.name) }}
            </div>
        </div>
    </div>
</template>
