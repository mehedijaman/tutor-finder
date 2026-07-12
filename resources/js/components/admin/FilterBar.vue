<script setup lang="ts">
import { Search } from 'lucide-vue-next';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';

defineProps<{
    search: string;
    filters?: Array<{
        modelValue: string;
        placeholder: string;
        icon?: any;
        options: Array<{ value: string; label: string }>;
    }>;
}>();

const emit = defineEmits<{
    'update:search': [value: string];
    'update:filter': [index: number, value: string];
}>();
</script>

<template>
    <div
        class="flex flex-col gap-4 sm:flex-row sm:items-center"
    >
        <div class="relative flex-1">
            <Search
                class="absolute top-1/2 left-3.5 h-4 w-4 -translate-y-1/2 text-muted-foreground"
            />
            <Input
                :model-value="search"
                placeholder="Search..."
                class="h-11 rounded-xl border-border bg-muted/30 pl-10 focus-visible:ring-primary"
                @update:model-value="emit('update:search', $event)"
            />
        </div>

        <template v-for="(filter, index) in filters" :key="index">
            <Select
                :model-value="filter.modelValue"
                @update:model-value="emit('update:filter', index, $event)"
            >
                <SelectTrigger
                    class="h-11 min-w-[180px] rounded-xl border-border"
                >
                    <component
                        :is="filter.icon"
                        v-if="filter.icon"
                        class="mr-2 h-4 w-4 text-muted-foreground shrink-0"
                    />
                    <SelectValue :placeholder="filter.placeholder" />
                </SelectTrigger>
                <SelectContent class="rounded-xl">
                    <SelectItem
                        v-for="option in filter.options"
                        :key="option.value"
                        :value="option.value"
                    >
                        {{ option.label }}
                    </SelectItem>
                </SelectContent>
            </Select>
        </template>
    </div>
