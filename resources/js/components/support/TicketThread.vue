<script setup lang="ts">
import type { TicketMessage } from '@/types/support';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Separator } from '@/components/ui/separator';

defineProps<{
    messages: TicketMessage[];
    currentUserId: number;
}>();

function getInitials(name: string): string {
    return name
        .split(' ')
        .map((part) => part[0])
        .join('')
        .toUpperCase()
        .slice(0, 2);
}

function formatDate(dateString: string): string {
    return new Date(dateString).toLocaleString();
}
</script>

<template>
    <div class="space-y-6">
        <div v-for="(message, index) in messages" :key="message.id">
            <div
                class="flex gap-4"
                :class="{
                    'flex-row-reverse': message.user_id === currentUserId,
                }"
            >
                <Avatar class="mt-1 size-10 shrink-0">
                    <AvatarImage
                        v-if="message.user?.avatar"
                        :src="message.user.avatar"
                        :alt="message.user?.name ?? 'User'"
                    />
                    <AvatarFallback class="bg-slate-100 text-xs font-medium text-slate-600">
                        {{ getInitials(message.user?.name ?? 'U') }}
                    </AvatarFallback>
                </Avatar>

                <div
                    class="max-w-[80%] flex-1 space-y-2"
                    :class="{
                        'text-right': message.user_id === currentUserId,
                    }"
                >
                    <div
                        class="flex items-center gap-2"
                        :class="{
                            'justify-end': message.user_id === currentUserId,
                        }"
                    >
                        <span class="text-sm font-semibold text-slate-900">
                            {{ message.user?.name ?? 'Unknown' }}
                        </span>
                        <span
                            v-if="message.is_admin_reply"
                            class="inline-flex items-center rounded-full bg-blue-50 px-2 py-0.5 text-xs font-medium text-blue-700"
                        >
                            Staff
                        </span>
                        <span class="text-xs text-slate-500">
                            {{ formatDate(message.created_at) }}
                        </span>
                    </div>

                    <div
                        class="rounded-xl px-4 py-3 text-sm leading-relaxed"
                        :class="
                            message.user_id === currentUserId
                                ? 'bg-blue-50 text-slate-800'
                                : 'bg-slate-50 text-slate-700'
                        "
                    >
                        <p class="whitespace-pre-wrap">{{ message.body }}</p>
                    </div>

                    <div
                        v-if="message.attachments && message.attachments.length > 0"
                        class="mt-2 flex flex-wrap gap-2"
                        :class="{
                            'justify-end': message.user_id === currentUserId,
                        }"
                    >
                        <a
                            v-for="attachment in message.attachments"
                            :key="attachment.id"
                            :href="attachment.url"
                            target="_blank"
                            class="group relative overflow-hidden rounded-lg border border-slate-200 transition hover:border-slate-300"
                        >
                            <img
                                :src="attachment.url"
                                :alt="attachment.name"
                                class="h-20 w-20 object-cover transition group-hover:scale-105"
                            />
                        </a>
                    </div>
                </div>
            </div>

            <Separator v-if="index < messages.length - 1" class="mt-6" />
        </div>

        <div
            v-if="messages.length === 0"
            class="py-8 text-center text-sm text-slate-500"
        >
            No messages yet.
        </div>
    </div>
</template>
