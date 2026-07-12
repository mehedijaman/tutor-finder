import type { Component } from 'vue';

export type StatCardColor =
    | 'blue'
    | 'emerald'
    | 'amber'
    | 'violet'
    | 'rose'
    | 'slate'
    | 'cyan'
    | 'orange'
    | 'indigo'
    | 'purple'
    | 'teal'
    | 'pink';

export type ActionCardColor =
    | 'blue'
    | 'emerald'
    | 'amber'
    | 'violet'
    | 'rose'
    | 'cyan'
    | 'indigo'
    | 'slate'
    | 'orange'
    | 'purple'
    | 'teal'
    | 'pink';

export interface StatCardConfig {
    label: string;
    value: number | string;
    subValue?: string;
    href: string;
    icon: Component;
    color: StatCardColor;
    trend?: {
        direction: 'up' | 'down';
        value: string;
    };
}

export interface ActionCardConfig {
    title: string;
    description: string;
    href: string;
    icon: Component;
    color: ActionCardColor;
}
