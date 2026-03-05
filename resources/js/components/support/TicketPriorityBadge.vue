<script setup lang="ts">
import { Badge } from '@/components/ui/badge';

const props = defineProps<{
    priority: string;
}>();

const priorityMap: Record<string, { label: string; variant: 'secondary' | 'default' | 'warning' | 'destructive' }> = {
    low: { label: 'Low', variant: 'secondary' },
    medium: { label: 'Medium', variant: 'default' },
    high: { label: 'High', variant: 'warning' },
    urgent: { label: 'Urgent', variant: 'destructive' },
};

const config = priorityMap[props.priority] ?? { label: props.priority, variant: 'secondary' as const };
</script>

<template>
    <Badge :variant="config.variant">
        {{ config.label }}
    </Badge>
</template>
