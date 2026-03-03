<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ChevronRight } from 'lucide-vue-next';
import {
    Collapsible,
    CollapsibleContent,
    CollapsibleTrigger,
} from '@/components/ui/collapsible';
import {
    SidebarGroup,
    SidebarGroupLabel,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    SidebarMenuSub,
    SidebarMenuSubButton,
    SidebarMenuSubItem,
} from '@/components/ui/sidebar';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import type { NavItem } from '@/types';

defineProps<{
    items: NavItem[];
}>();

const { isCurrentUrl } = useCurrentUrl();

const hasChildren = (item: NavItem): boolean =>
    (item.children?.length ?? 0) > 0;

const isChildActive = (item: NavItem): boolean =>
    item.children?.some((child) => isCurrentUrl(child.href)) ?? false;
</script>

<template>
    <SidebarGroup class="px-2 py-0">
        <SidebarGroupLabel>Platform</SidebarGroupLabel>
        <SidebarMenu>
            <template v-for="item in items" :key="item.title">
                <Collapsible
                    v-if="hasChildren(item)"
                    as-child
                    :default-open="isChildActive(item)"
                    class="group/collapsible"
                >
                    <SidebarMenuItem>
                        <CollapsibleTrigger as-child>
                            <SidebarMenuButton
                                :is-active="isChildActive(item)"
                                :tooltip="item.title"
                            >
                                <component :is="item.icon" />
                                <span>{{ item.title }}</span>
                                <span
                                    v-if="item.badge !== undefined"
                                    class="mr-1 inline-flex min-w-5 items-center justify-center rounded-full bg-primary px-1.5 py-0.5 text-[10px] font-semibold text-primary-foreground"
                                >
                                    {{ item.badge }}
                                </span>
                                <ChevronRight
                                    class="ml-auto transition-transform duration-200 group-data-[state=open]/collapsible:rotate-90"
                                />
                            </SidebarMenuButton>
                        </CollapsibleTrigger>

                        <CollapsibleContent>
                            <SidebarMenuSub>
                                <SidebarMenuSubItem
                                    v-for="child in item.children ?? []"
                                    :key="`${item.title}-${child.title}`"
                                >
                                    <SidebarMenuSubButton
                                        as-child
                                        :is-active="isCurrentUrl(child.href)"
                                    >
                                        <a
                                            v-if="child.fullPage"
                                            :href="child.href"
                                        >
                                            <component :is="child.icon" />
                                            <span>{{ child.title }}</span>
                                            <span
                                                v-if="child.badge !== undefined"
                                                class="ml-auto inline-flex min-w-5 items-center justify-center rounded-full bg-primary px-1.5 py-0.5 text-[10px] font-semibold text-primary-foreground"
                                            >
                                                {{ child.badge }}
                                            </span>
                                        </a>
                                        <Link v-else :href="child.href">
                                            <component :is="child.icon" />
                                            <span>{{ child.title }}</span>
                                            <span
                                                v-if="child.badge !== undefined"
                                                class="ml-auto inline-flex min-w-5 items-center justify-center rounded-full bg-primary px-1.5 py-0.5 text-[10px] font-semibold text-primary-foreground"
                                            >
                                                {{ child.badge }}
                                            </span>
                                        </Link>
                                    </SidebarMenuSubButton>
                                </SidebarMenuSubItem>
                            </SidebarMenuSub>
                        </CollapsibleContent>
                    </SidebarMenuItem>
                </Collapsible>

                <SidebarMenuItem v-else>
                    <SidebarMenuButton
                        as-child
                        :is-active="isCurrentUrl(item.href)"
                        :tooltip="item.title"
                    >
                        <a v-if="item.fullPage" :href="item.href">
                            <component :is="item.icon" />
                            <span>{{ item.title }}</span>
                            <span
                                v-if="item.badge !== undefined"
                                class="ml-auto inline-flex min-w-5 items-center justify-center rounded-full bg-primary px-1.5 py-0.5 text-[10px] font-semibold text-primary-foreground"
                            >
                                {{ item.badge }}
                            </span>
                        </a>
                        <Link v-else :href="item.href">
                            <component :is="item.icon" />
                            <span>{{ item.title }}</span>
                            <span
                                v-if="item.badge !== undefined"
                                class="ml-auto inline-flex min-w-5 items-center justify-center rounded-full bg-primary px-1.5 py-0.5 text-[10px] font-semibold text-primary-foreground"
                            >
                                {{ item.badge }}
                            </span>
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </template>
        </SidebarMenu>
    </SidebarGroup>
</template>
