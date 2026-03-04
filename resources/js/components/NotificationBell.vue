<script setup lang="ts">
import { Link, router, usePage } from '@inertiajs/vue3';
import { Bell, Check, ExternalLink } from 'lucide-vue-next';
import { computed } from 'vue';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';

interface Notification {
    id: string;
    type: string;
    title: string;
    data: Record<string, unknown>;
    created_at: string;
}

const page = usePage();

const unreadCount = computed(() =>
    Number(page.props.notificationCounts?.unread ?? 0),
);
const recentNotifications = computed<Notification[]>(() =>
    (page.props.notificationCounts?.recent as Notification[]) ?? [],
);
const userRole = computed(() => page.props.auth?.user?.role as string);

const notificationsUrl = computed(() => {
    if (userRole.value === 'tutor') {
        return '/tutor/notifications';
    }

    if (userRole.value === 'guardian') {
        return '/guardian/notifications';
    }

    return '';
});

const markAsReadUrl = computed(() => {
    if (userRole.value === 'tutor') {
        return '/tutor/notifications/read-all';
    }

    if (userRole.value === 'guardian') {
        return '/guardian/notifications/read-all';
    }

    return '';
});

function markAllAsRead() {
    if (!markAsReadUrl.value) {
        return;
    }

    router.patch(markAsReadUrl.value, {}, {
        preserveScroll: true,
    });
}

function formatTimeAgo(dateString: string): string {
    const date = new Date(dateString);
    const now = new Date();
    const diffMs = now.getTime() - date.getTime();
    const diffMins = Math.floor(diffMs / 60000);
    const diffHours = Math.floor(diffMs / 3600000);
    const diffDays = Math.floor(diffMs / 86400000);

    if (diffMins < 1) {
        return 'Just now';
    }

    if (diffMins < 60) {
        return `${diffMins}m ago`;
    }

    if (diffHours < 24) {
        return `${diffHours}h ago`;
    }

    if (diffDays < 7) {
        return `${diffDays}d ago`;
    }

    return date.toLocaleDateString();
}
</script>

<template>
    <DropdownMenu v-if="notificationsUrl">
        <DropdownMenuTrigger :as-child="true">
            <Button
                variant="ghost"
                size="icon"
                class="relative h-9 w-9 rounded-xl text-slate-500 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-white"
            >
                <Bell class="h-4 w-4" />
                <span
                    v-if="unreadCount > 0"
                    class="absolute -top-0.5 -right-0.5 flex h-4 min-w-4 items-center justify-center rounded-full bg-red-500 px-1 text-[10px] font-bold text-white shadow-sm"
                >
                    {{ unreadCount > 99 ? '99+' : unreadCount }}
                </span>
                <span class="sr-only">Notifications</span>
            </Button>
        </DropdownMenuTrigger>
        <DropdownMenuContent align="end" class="w-80">
            <div class="flex items-center justify-between px-3 py-2">
                <span class="text-sm font-semibold text-slate-900 dark:text-white">
                    Notifications
                </span>
                <Button
                    v-if="unreadCount > 0"
                    variant="ghost"
                    size="sm"
                    class="h-7 text-xs"
                    @click="markAllAsRead"
                >
                    <Check class="mr-1 h-3 w-3" />
                    Mark all read
                </Button>
            </div>
            <DropdownMenuSeparator />

            <div v-if="recentNotifications.length === 0" class="px-3 py-6 text-center">
                <Bell class="mx-auto h-8 w-8 text-slate-300 dark:text-slate-600" />
                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                    No new notifications
                </p>
            </div>

            <div v-else class="max-h-72 overflow-y-auto">
                <DropdownMenuItem
                    v-for="notification in recentNotifications"
                    :key="notification.id"
                    class="cursor-pointer flex-col items-start gap-1 px-3 py-2.5"
                >
                    <div class="flex w-full items-start justify-between gap-2">
                        <span class="font-medium text-slate-900 line-clamp-1 dark:text-white">
                            {{ notification.title }}
                        </span>
                        <span class="shrink-0 text-xs text-slate-400">
                            {{ formatTimeAgo(notification.created_at) }}
                        </span>
                    </div>
                    <p
                        v-if="notification.data.message"
                        class="text-xs text-slate-500 line-clamp-2 dark:text-slate-400"
                    >
                        {{ notification.data.message }}
                    </p>
                </DropdownMenuItem>
            </div>

            <DropdownMenuSeparator />
            <div class="p-1">
                <Link
                    :href="notificationsUrl"
                    class="flex w-full items-center justify-center gap-1.5 rounded-md px-3 py-2 text-sm font-medium text-blue-600 transition hover:bg-blue-50 dark:text-blue-400 dark:hover:bg-blue-950/50"
                >
                    View all notifications
                    <ExternalLink class="h-3.5 w-3.5" />
                </Link>
            </div>
        </DropdownMenuContent>
    </DropdownMenu>
</template>
