<script setup lang="ts">
import { Badge } from '@/components/ui/badge';

const props = defineProps<{
    status: string;
}>();

const statusMap: Record<
    string,
    { label: string; variant: 'default' | 'warning' | 'secondary' }
> = {
    open: { label: 'Open', variant: 'default' },
    in_progress: { label: 'In Progress', variant: 'warning' },
    closed: { label: 'Closed', variant: 'secondary' },
};

const config = statusMap[props.status] ?? {
    label: props.status,
    variant: 'secondary' as const,
};
</script>

<template>
    <Badge :variant="config.variant">
        {{ config.label }}
    </Badge>
</template>
