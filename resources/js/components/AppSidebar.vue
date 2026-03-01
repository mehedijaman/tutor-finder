<script setup lang="ts">
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import {
    BookOpen,
    FolderGit2,
    GraduationCap,
    LayoutGrid,
    MessageSquareText,
    Shield,
    Users,
    UserRound,
} from 'lucide-vue-next';
import AppLogo from '@/components/AppLogo.vue';
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import type { NavItem } from '@/types';

const page = usePage();

const mainNavItems = computed<NavItem[]>(() => {
    const role = page.props.auth?.user?.role;

    if (role === 'admin') {
        return [
            { title: 'Dashboard', href: '/admin/dashboard', icon: LayoutGrid },
            { title: 'Admin Users', href: '/admin/users', icon: Users },
            { title: 'Roles', href: '/admin/roles', icon: Shield },
            { title: 'Tutors', href: '/admin/tutors', icon: GraduationCap },
            { title: 'Guardians', href: '/admin/guardians', icon: UserRound },
            { title: 'SMS Settings', href: '/admin/sms-settings', icon: MessageSquareText },
        ];
    }

    if (role === 'tutor') {
        return [
            { title: 'Dashboard', href: '/tutor/dashboard', icon: LayoutGrid },
        ];
    }

    return [
        { title: 'Dashboard', href: '/guardian/dashboard', icon: LayoutGrid },
    ];
});

const footerNavItems: NavItem[] = [
    {
        title: 'Repository',
        href: 'https://github.com/laravel/vue-starter-kit',
        icon: FolderGit2,
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits#vue',
        icon: BookOpen,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
