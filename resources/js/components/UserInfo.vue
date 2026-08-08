<script setup lang="ts">
import { computed } from 'vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { useInitials } from '@/composables/useInitials';
import type { User } from '@/types';

type Props = {
    user: User;
    showEmail?: boolean;
};

const props = withDefaults(defineProps<Props>(), {
    showEmail: false,
});

const { getInitials } = useInitials();

// No additional layout needed here
</script>

<template>
    <Avatar
        class="h-8 w-8 overflow-hidden rounded-xl ring-1 ring-sidebar-border/70"
    >
        <AvatarImage :src="user.avatar || user.photo_url" :alt="user.name" />
        <AvatarFallback
            class="rounded-xl bg-sidebar-accent text-black dark:text-white"
        >
            {{ getInitials(user.name) }}
        </AvatarFallback>
    </Avatar>

    <div class="grid flex-1 text-left text-sm leading-tight">
        <span class="truncate font-medium tracking-tight">{{ user.name }}</span>
        <span v-if="showEmail" class="truncate text-xs text-muted-foreground">{{
            user.email
        }}</span>
    </div>
</template>
