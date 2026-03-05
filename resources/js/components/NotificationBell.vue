<script setup lang="ts">
import { Link, router, usePage } from '@inertiajs/vue3';
import { Bell, BellRing, Check, ExternalLink, Loader2 } from 'lucide-vue-next';
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { usePushNotifications } from '@/composables/usePushNotifications';

interface Notification {
    id: string;
    type: string;
    title: string;
    data: Record<string, unknown>;
    created_at: string;
}

interface NoticeCreatedEvent {
    notice_id: number;
    title: string;
    audience: string;
    created_at: string;
}

interface TicketBroadcastEvent {
    ticket_id: number;
    ticket_number: string;
    subject: string;
    user_name?: string;
    replier_name?: string;
    category?: string;
    priority?: string;
}

const page = usePage();

const {
    isSupported: pushSupported,
    isSubscribed: pushSubscribed,
    isLoading: pushLoading,
    permission: pushPermission,
    checkSupport: checkPushSupport,
    subscribe: subscribePush,
    unsubscribe: unsubscribePush,
} = usePushNotifications();

const localUnreadCount = ref(0);
const localNotifications = ref<Notification[]>([]);

const unreadCount = computed(() => localUnreadCount.value);
const recentNotifications = computed<Notification[]>(() => localNotifications.value);
const userRole = computed(() => page.props.auth?.user?.role as string);

function syncFromProps() {
    localUnreadCount.value = Number(page.props.notificationCounts?.unread ?? 0);
    localNotifications.value = (page.props.notificationCounts?.recent as Notification[]) ?? [];
}

function handleNoticeCreated(event: NoticeCreatedEvent) {
    localUnreadCount.value++;

    const newNotification: Notification = {
        id: `notice-${event.notice_id}-${Date.now()}`,
        type: 'notice',
        title: event.title,
        data: {
            notice_id: event.notice_id,
            audience: event.audience,
        },
        created_at: event.created_at ?? new Date().toISOString(),
    };

    localNotifications.value = [newNotification, ...localNotifications.value].slice(0, 5);
}

function handleTicketEvent(event: TicketBroadcastEvent) {
    localUnreadCount.value++;

    const title = event.replier_name
        ? `Reply on ${event.ticket_number}`
        : `New Ticket ${event.ticket_number}`;

    const message = event.replier_name
        ? `${event.replier_name} replied to "${event.subject}"`
        : `${event.user_name} opened "${event.subject}"`;

    const newNotification: Notification = {
        id: `ticket-${event.ticket_id}-${Date.now()}`,
        type: 'ticket',
        title,
        data: {
            ticket_id: event.ticket_id,
            ticket_number: event.ticket_number,
            message,
        },
        created_at: new Date().toISOString(),
    };

    localNotifications.value = [newNotification, ...localNotifications.value].slice(0, 5);
}

onMounted(() => {
    syncFromProps();
    checkPushSupport();

    const role = userRole.value;
    if (role && window.Echo && (role === 'tutor' || role === 'guardian')) {
        window.Echo.private(`role.${role}`)
            .listen('.notice.created', handleNoticeCreated);
    }

    if (role === 'admin' && window.Echo) {
        window.Echo.private('role.admin')
            .listen('.ticket.created', handleTicketEvent)
            .listen('.ticket.replied', handleTicketEvent);
    }
});

async function togglePushSubscription() {
    if (pushSubscribed.value) {
        await unsubscribePush();
    } else {
        await subscribePush();
    }
}

const pushToggleDisabled = computed(() => {
    return !pushSupported.value || pushPermission.value === 'denied' || pushLoading.value;
});

const pushToggleLabel = computed(() => {
    if (!pushSupported.value) {
        return 'Not supported';
    }
    if (pushPermission.value === 'denied') {
        return 'Permission denied';
    }
    return 'Push notifications';
});

onUnmounted(() => {
    const role = userRole.value;
    if (role && window.Echo && (role === 'tutor' || role === 'guardian')) {
        window.Echo.leave(`role.${role}`);
    }
    if (role === 'admin' && window.Echo) {
        window.Echo.leave('role.admin');
    }
});

const notificationsUrl = computed(() => {
    if (userRole.value === 'tutor') {
        return '/tutor/notifications';
    }

    if (userRole.value === 'guardian') {
        return '/guardian/notifications';
    }

    if (userRole.value === 'admin') {
        return '/admin/notifications';
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

    if (userRole.value === 'admin') {
        return '/admin/notifications/read-all';
    }

    return '';
});

function markAllAsRead() {
    if (!markAsReadUrl.value) {
        return;
    }

    localUnreadCount.value = 0;
    router.patch(markAsReadUrl.value, {}, {
        preserveScroll: true,
        onSuccess: () => {
            syncFromProps();
        },
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
            <div
                class="flex items-center justify-between px-3 py-2"
                :class="{ 'opacity-50': pushToggleDisabled }"
            >
                <div class="flex items-center gap-2">
                    <BellRing
                        v-if="pushSubscribed"
                        class="h-4 w-4 text-emerald-500"
                    />
                    <Bell v-else class="h-4 w-4 text-slate-400" />
                    <Label
                        for="push-toggle"
                        class="text-sm font-medium text-slate-700 dark:text-slate-300"
                        :class="{ 'cursor-not-allowed': pushToggleDisabled, 'cursor-pointer': !pushToggleDisabled }"
                    >
                        {{ pushToggleLabel }}
                    </Label>
                </div>
                <div class="flex items-center">
                    <Loader2 v-if="pushLoading" class="h-4 w-4 animate-spin text-slate-400" />
                    <Switch
                        v-else
                        id="push-toggle"
                        :model-value="pushSubscribed"
                        :disabled="pushToggleDisabled"
                        @update:model-value="togglePushSubscription"
                    />
                </div>
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
