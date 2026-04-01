<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { Home, Moon, Sun } from 'lucide-vue-next';
import { computed } from 'vue';
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import NotificationBell from '@/components/NotificationBell.vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { SidebarTrigger } from '@/components/ui/sidebar';
import UserMenuContent from '@/components/UserMenuContent.vue';
import { useAppearance } from '@/composables/useAppearance';
import { useInitials } from '@/composables/useInitials';
import type { BreadcrumbItem } from '@/types';

withDefaults(
    defineProps<{
        breadcrumbs?: BreadcrumbItem[];
    }>(),
    {
        breadcrumbs: () => [],
    },
);

const { resolvedAppearance, updateAppearance } = useAppearance();
const { getInitials } = useInitials();
const page = usePage();
const user = computed(() => page.props.auth?.user);
const panelLabel = computed(() => {
    const role = user.value?.role;

    if (role === 'admin') {
        return 'Admin Dashboard';
    }

    if (role === 'tutor') {
        return 'Tutor Panel';
    }

    return 'Guardian Panel';
});

function toggleAppearance(): void {
    updateAppearance(resolvedAppearance.value === 'dark' ? 'light' : 'dark');
}
</script>

<template>
    <header
        class="sticky top-0 z-20 flex shrink-0 items-center gap-3 border-b border-slate-200/80 bg-white/95 px-4 py-3.5 backdrop-blur-lg transition-[width,height] ease-linear md:px-6 dark:border-slate-800/80 dark:bg-slate-950/95"
    >
        <div class="flex min-w-0 flex-1 items-center gap-3">
            <SidebarTrigger class="-ml-1 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800" />

            <div class="min-w-0">
                <p class="truncate text-sm font-semibold tracking-tight text-slate-900 dark:text-white">
                    {{ panelLabel }}
                </p>

                <div
                    v-if="breadcrumbs && breadcrumbs.length > 0"
                    class="mt-0.5 text-xs text-slate-500 dark:text-slate-400"
                >
                    <Breadcrumbs :breadcrumbs="breadcrumbs" />
                </div>
            </div>
        </div>

        <div class="ml-auto flex items-center gap-1.5">
            <NotificationBell />

            <Button
                variant="ghost"
                size="icon"
                class="h-9 w-9 rounded-xl text-slate-500 hover:text-slate-900 hover:bg-slate-100 dark:text-slate-400 dark:hover:text-white dark:hover:bg-slate-800"
                type="button"
                @click="toggleAppearance"
            >
                <Sun v-if="resolvedAppearance === 'dark'" class="h-4 w-4" />
                <Moon v-else class="h-4 w-4" />
                <span class="sr-only">Toggle theme</span>
            </Button>

            <Button
                variant="ghost"
                size="icon"
                class="h-9 w-9 rounded-xl text-slate-500 hover:text-slate-900 hover:bg-slate-100 dark:text-slate-400 dark:hover:text-white dark:hover:bg-slate-800"
                as-child
            >
                <Link href="/">
                    <Home class="h-4 w-4" />
                    <span class="sr-only">Home</span>
                </Link>
            </Button>

            <DropdownMenu v-if="user">
                <DropdownMenuTrigger :as-child="true">
                    <Button
                        variant="ghost"
                        size="icon"
                        class="relative size-10 w-auto rounded-full p-1 hover:bg-slate-100 focus-within:ring-2 focus-within:ring-primary/50 dark:hover:bg-slate-800"
                    >
                        <Avatar class="size-8 overflow-hidden rounded-full ring-2 ring-white dark:ring-slate-800">
                            <AvatarImage
                                v-if="user.avatar"
                                :src="user.avatar"
                                :alt="user.name"
                            />
                            <AvatarFallback
                                class="rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 font-semibold text-white"
                            >
                                {{ getInitials(user.name) }}
                            </AvatarFallback>
                        </Avatar>
                    </Button>
                </DropdownMenuTrigger>
                <DropdownMenuContent align="end" class="w-56">
                    <UserMenuContent :user="user" />
                </DropdownMenuContent>
            </DropdownMenu>
        </div>
    </header>
</template>
