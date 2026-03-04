<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { Home, Moon, Sun } from 'lucide-vue-next';
import { computed } from 'vue';
import Breadcrumbs from '@/components/Breadcrumbs.vue';
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
        return 'Tutor Dashboard';
    }

    return 'Guardian Dashboard';
});

function toggleAppearance(): void {
    updateAppearance(resolvedAppearance.value === 'dark' ? 'light' : 'dark');
}
</script>

<template>
    <header
        class="sticky top-0 z-20 flex shrink-0 items-center gap-3 border-b border-sidebar-border/70 bg-background/95 px-4 py-3 backdrop-blur transition-[width,height] ease-linear md:px-6"
    >
        <div class="flex min-w-0 flex-1 items-center gap-2">
            <SidebarTrigger class="-ml-1" />

            <div class="min-w-0">
                <p class="truncate text-sm font-semibold tracking-tight">
                    {{ panelLabel }}
                </p>

                <div
                    v-if="breadcrumbs && breadcrumbs.length > 0"
                    class="mt-0.5 text-xs text-muted-foreground"
                >
                    <Breadcrumbs :breadcrumbs="breadcrumbs" />
                </div>
            </div>
        </div>

        <div class="ml-auto flex items-center gap-2">
            <Button
                variant="ghost"
                size="icon"
                class="h-9 w-9 rounded-xl"
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
                class="h-9 w-9 rounded-xl"
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
                        class="relative size-10 w-auto rounded-full p-1 focus-within:ring-2 focus-within:ring-primary"
                    >
                        <Avatar class="size-8 overflow-hidden rounded-full">
                            <AvatarImage
                                v-if="user.avatar"
                                :src="user.avatar"
                                :alt="user.name"
                            />
                            <AvatarFallback
                                class="rounded-full bg-neutral-200 font-semibold text-black dark:bg-neutral-700 dark:text-white"
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
