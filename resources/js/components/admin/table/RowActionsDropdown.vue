<script setup lang="ts">
import { MoreHorizontal } from 'lucide-vue-next';
import { computed } from 'vue';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';

const props = defineProps({
    actions: {
        type: Array,
        default: () => [],
    },
    align: {
        type: String,
        default: 'end',
    },
});

const emit = defineEmits(['select']);

const visibleActions = computed(() =>
    props.actions.filter((action) => action && action.show !== false),
);

function selectAction(action) {
    if (action.disabled) {
        return;
    }

    emit('select', action.key);
}

function getIconComponent(iconName) {
    if (!iconName) return null;

    return iconName;
}
</script>

<template>
    <DropdownMenu>
        <DropdownMenuTrigger as-child>
            <Button variant="outline" size="sm" class="h-8 px-2">
                <MoreHorizontal class="h-4 w-4" />
                <span class="sr-only">Row actions</span>
            </Button>
        </DropdownMenuTrigger>
        <DropdownMenuContent :align="align" class="w-48">
            <DropdownMenuItem
                v-for="action in visibleActions"
                :key="action.key"
                :disabled="action.disabled"
                :variant="action.destructive ? 'destructive' : 'default'"
                @select.prevent="selectAction(action)"
            >
                <component
                    :is="getIconComponent(action.icon)"
                    v-if="action.icon"
                    class="mr-2 h-4 w-4"
                />
                {{ action.label }}
            </DropdownMenuItem>
        </DropdownMenuContent>
    </DropdownMenu>
</template>
