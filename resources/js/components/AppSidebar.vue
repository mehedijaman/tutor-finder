<script setup lang="ts">
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import {
    FileText,
    FolderOpen,
    GraduationCap,
    MessagesSquare,
    CircleHelp,
    History,
    HardDrive,
    LayoutGrid,
    Newspaper,
    Globe,
    MapPin,
    BookOpen,
    Shield,
    Tags,
    Users,
    UserRound,
    Wrench,
    Cog,
    Key,
    Bell,
    Briefcase,
} from 'lucide-vue-next';
import AppLogo from '@/components/AppLogo.vue';
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
const unreadNotificationCount = computed(() => Number(page.props.notificationCounts?.unread ?? 0));

const mainNavItems = computed<NavItem[]>(() => {
    const role = page.props.auth?.user?.role;

    if (role === 'admin') {
        return [
            { title: 'Dashboard', href: '/admin/dashboard', icon: LayoutGrid },
            { title: 'Tutors', href: '/admin/tutors', icon: GraduationCap },
            { title: 'Guardians', href: '/admin/guardians', icon: UserRound },
            { title: 'Contact Messages', href: '/admin/contact-messages', icon: MessagesSquare },
            { title: 'FAQs', href: '/admin/faqs', icon: CircleHelp },
            {
                title: 'Jobs',
                href: '/admin/jobs',
                icon: Briefcase,
                children: [
                    { title: 'Create Job', href: '/admin/jobs/create', icon: FileText },
                    { title: 'Pending Jobs', href: '/admin/jobs/pending', icon: FileText },
                    { title: 'Live Jobs', href: '/admin/jobs/live', icon: FileText },
                    { title: 'Confirmed Jobs', href: '/admin/jobs/confirmed', icon: FileText },
                    { title: 'Cancelled Jobs', href: '/admin/jobs/cancelled', icon: FileText },
                    { title: 'All Jobs', href: '/admin/jobs', icon: FileText },
                ],
            },
            {
                title: 'Tuition Taxonomy',
                href: '/admin/tuition/taxonomies/countries',
                icon: BookOpen,
                children: [
                    { title: 'Countries', href: '/admin/tuition/taxonomies/countries', icon: Globe },
                    { title: 'Cities', href: '/admin/tuition/taxonomies/cities', icon: MapPin },
                    { title: 'Areas', href: '/admin/tuition/taxonomies/areas', icon: MapPin },
                    { title: 'Categories', href: '/admin/tuition/taxonomies/categories', icon: FolderOpen },
                    { title: 'Classes', href: '/admin/tuition/taxonomies/classes', icon: GraduationCap },
                    { title: 'Subjects', href: '/admin/tuition/taxonomies/subjects', icon: FileText },
                    { title: 'Tuition Types', href: '/admin/tuition/taxonomies/tuition-types', icon: Tags },
                ],
            },
            {
                title: 'Blog',
                href: '/admin/blog/posts',
                icon: Newspaper,
                children: [
                    { title: 'Posts', href: '/admin/blog/posts', icon: FileText },
                    { title: 'Categories', href: '/admin/blog/categories', icon: FolderOpen },
                    { title: 'Tags', href: '/admin/blog/tags', icon: Tags },
                ],
            },
            { title: 'Settings', href: '/settings', icon: Cog },
            {
                title: 'Access Control',
                href: '/admin/maintenance',
                icon: Key,
                children: [
                    { title: 'Users', href: '/admin/users', icon: Users },
                    { title: 'Roles', href: '/admin/roles', icon: Shield },
                ],
            },
            {
                title: 'Backup & Maintenance',
                href: '/admin/maintenance',
                icon: Wrench,
                children: [
                    { title: 'Backups', href: '/admin/backups', icon: HardDrive },
                    { title: 'Activity Logs', href: '/admin/activity-logs', icon: History },
                    { title: 'Log Viewer', href: '/admin/log-viewer', icon: FileText, fullPage: true },
                ],
            },
        ];
    }

    if (role === 'tutor') {
        return [
            { title: 'Dashboard', href: '/tutor/dashboard', icon: LayoutGrid },
            { title: 'Browse Jobs', href: '/jobs', icon: FileText },
            { title: 'My Applications', href: '/tutor/job-applications', icon: FolderOpen },
            {
                title: 'Notifications',
                href: '/tutor/notifications',
                icon: Bell,
                badge: unreadNotificationCount.value > 0 ? unreadNotificationCount.value : undefined,
            },
        ];
    }

    return [
        { title: 'Dashboard', href: '/guardian/dashboard', icon: LayoutGrid },
        { title: 'Jobs', href: '/guardian/jobs', icon: FileText },
        {
            title: 'Notifications',
            href: '/guardian/notifications',
            icon: Bell,
            badge: unreadNotificationCount.value > 0 ? unreadNotificationCount.value : undefined,
        },
    ];
});

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

        <!-- <SidebarFooter>
            <NavUser />
        </SidebarFooter> -->
    </Sidebar>
    <slot />
</template>
