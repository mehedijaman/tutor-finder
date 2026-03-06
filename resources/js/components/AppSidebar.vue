<script setup lang="ts">
import { Link, router, usePage } from '@inertiajs/vue3';
import {
    BarChart3,
    Briefcase,
    BookOpen,
    CircleHelp,
    ClipboardCheck,
    Cog,
    FileText,
    FolderOpen,
    Globe,
    GraduationCap,
    HardDrive,
    History,
    Key,
    LayoutGrid,
    LifeBuoy,
    LogOut,
    MapPin,
    MessageSquare,
    MessagesSquare,
    Newspaper,
    PlayCircle,
    ScrollText,
    Shield,
    Star,
    Tags,
    Users,
    UserRound,
    Wrench,
} from 'lucide-vue-next';
import { computed } from 'vue';
import AppLogo from '@/components/AppLogo.vue';
import NavMain from '@/components/NavMain.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard, logout } from '@/routes';
import type { NavItem } from '@/types';

const page = usePage();
type AuthUser = {
    id: number;
    name?: string;
    avatar?: string;
    created_at?: string;
    verification_status?: string;
};

const authUser = computed(() => page.props.auth?.user as AuthUser | undefined);
const panelContext = computed<'admin' | 'tutor' | 'guardian'>(() => {
    const role = page.props.auth?.user?.role;
    const currentPath = new URL(page.url, window.location.origin).pathname;

    if (role === 'admin' || currentPath.startsWith('/admin')) {
        return 'admin';
    }

    if (role === 'tutor' || currentPath.startsWith('/tutor')) {
        return 'tutor';
    }

    return 'guardian';
});

const showMemberSummary = computed(
    () => panelContext.value !== 'admin' && authUser.value !== undefined,
);

const memberIdLabel = computed(() =>
    panelContext.value === 'tutor' ? 'Tutor ID' : 'Guardian ID',
);

const memberSince = computed(() => {
    const createdAt = authUser.value?.created_at;

    if (!createdAt) {
        return '—';
    }

    const parsed = new Date(createdAt);

    if (Number.isNaN(parsed.getTime())) {
        return '—';
    }

    return new Intl.DateTimeFormat('en-US', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    }).format(parsed);
});

const memberInitials = computed(() => {
    const name = authUser.value?.name?.trim() ?? '';

    if (!name) {
        return 'U';
    }

    const segments = name.split(/\s+/).slice(0, 2);

    return segments.map((segment) => segment[0]?.toUpperCase() ?? '').join('');
});

const verificationBadge = computed(() => {
    const status = String(
        authUser.value?.verification_status ?? '',
    ).toLowerCase();

    if (status === 'verified') {
        return {
            label: 'Verified',
            className:
                'border-emerald-300/70 bg-emerald-500/15 text-emerald-700 dark:text-emerald-300',
        };
    }

    return {
        label: 'Unverified',
        className:
            'border-amber-300/70 bg-amber-500/15 text-amber-700 dark:text-amber-300',
    };
});

const handleLogout = (): void => {
    router.flushAll();
};

const mainNavItems = computed<NavItem[]>(() => {
    const role = panelContext.value;

    if (role === 'admin') {
        return [
            { title: 'Dashboard', href: '/admin/dashboard', icon: LayoutGrid },
            {
                title: 'Tutors',
                href: '/admin/tutors',
                icon: GraduationCap,
                children: [
                    {
                        title: 'Create Tutor',
                        href: '/admin/tutors/create',
                        icon: FileText,
                    },
                    {
                        title: 'Pending',
                        href: '/admin/tutors?verification=pending',
                        icon: Shield,
                    },
                    {
                        title: 'Unverified',
                        href: '/admin/tutors?verification=unverified',
                        icon: Shield,
                    },
                    {
                        title: 'Verified',
                        href: '/admin/tutors?verification=verified',
                        icon: Shield,
                    },
                    { title: 'All Tutors', href: '/admin/tutors', icon: Users },
                ],
            },
            {
                title: 'Guardians',
                href: '/admin/guardians',
                icon: UserRound,
                children: [
                    {
                        title: 'Create Guardian',
                        href: '/admin/guardians/create',
                        icon: FileText,
                    },
                    {
                        title: 'Pending',
                        href: '/admin/guardians?verification=pending',
                        icon: Shield,
                    },
                    {
                        title: 'Unverified',
                        href: '/admin/guardians?verification=unverified',
                        icon: Shield,
                    },
                    {
                        title: 'Verified',
                        href: '/admin/guardians?verification=verified',
                        icon: Shield,
                    },
                    {
                        title: 'All Guardians',
                        href: '/admin/guardians',
                        icon: Users,
                    },
                ],
            },
            {
                title: 'Verifications',
                href: '/admin/verifications',
                icon: ClipboardCheck,
                children: [
                    {
                        title: 'All Requests',
                        href: '/admin/verifications',
                        icon: FileText,
                    },
                    {
                        title: 'Pending Profiles',
                        href: '/admin/profile-verification/pending',
                        icon: Shield,
                    },
                    {
                        title: 'Unverified Profiles',
                        href: '/admin/profile-verification/unverified',
                        icon: Shield,
                    },
                    {
                        title: 'Verified Profiles',
                        href: '/admin/profile-verification/verified',
                        icon: Shield,
                    },
                ],
            },
            {
                title: 'Contact Messages',
                href: '/admin/contact-messages',
                icon: MessagesSquare,
            },
            {
                title: 'Support Tickets',
                href: '/admin/support-tickets',
                icon: LifeBuoy,
            },
            {
                title: 'Tutor Reviews',
                href: '/admin/reviews',
                icon: Star,
            },
            {
                title: 'Testimonials',
                href: '/admin/testimonials',
                icon: MessageSquare,
            },
            { title: 'FAQs', href: '/admin/faqs', icon: CircleHelp },
            { title: 'Notices', href: '/admin/notices', icon: Newspaper },
            { title: 'Pages', href: '/admin/pages', icon: FileText },
            {
                title: 'Tutorials',
                href: '/admin/tutorials',
                icon: PlayCircle,
            },
            {
                title: 'Jobs',
                href: '/admin/jobs',
                icon: Briefcase,
                children: [
                    {
                        title: 'Create Job',
                        href: '/admin/jobs/create',
                        icon: FileText,
                    },
                    {
                        title: 'Pending Jobs',
                        href: '/admin/jobs/pending',
                        icon: FileText,
                    },
                    {
                        title: 'Live Jobs',
                        href: '/admin/jobs/live',
                        icon: FileText,
                    },
                    {
                        title: 'Expired Jobs',
                        href: '/admin/jobs/expired',
                        icon: FileText,
                    },
                    {
                        title: 'Confirmed Jobs',
                        href: '/admin/jobs/confirmed',
                        icon: FileText,
                    },
                    {
                        title: 'Cancelled Jobs',
                        href: '/admin/jobs/cancelled',
                        icon: FileText,
                    },
                    { title: 'All Jobs', href: '/admin/jobs', icon: FileText },
                ],
            },
            {
                title: 'Finance',
                href: '/admin/finance/invoices',
                icon: FileText,
                children: [
                    {
                        title: 'Invoices',
                        href: '/admin/finance/invoices',
                        icon: FileText,
                    },
                    {
                        title: 'Payments',
                        href: '/admin/finance/payments',
                        icon: FileText,
                    },
                    {
                        title: 'Escrow',
                        href: '/admin/finance/invoices?type=online_month1_escrow',
                        icon: FileText,
                    },
                    {
                        title: 'Refund Requests',
                        href: '/admin/finance/refund-requests',
                        icon: FileText,
                    },
                    {
                        title: 'Ledger',
                        href: '/admin/finance/ledger',
                        icon: FileText,
                    },
                ],
            },
            {
                title: 'Reports',
                href: '/admin/reports/income',
                icon: BarChart3,
                children: [
                    {
                        title: 'Income Report',
                        href: '/admin/reports/income',
                        icon: FileText,
                    },
                    {
                        title: 'Tuition Report',
                        href: '/admin/reports/tuition',
                        icon: FileText,
                    },
                    {
                        title: 'Refund Report',
                        href: '/admin/reports/refunds',
                        icon: FileText,
                    },
                    {
                        title: 'User Registrations',
                        href: '/admin/reports/user-registrations',
                        icon: FileText,
                    },
                    {
                        title: 'Job Performance',
                        href: '/admin/reports/job-performance',
                        icon: FileText,
                    },
                ],
            },
            {
                title: 'Tuition Taxonomy',
                href: '/admin/tuition/taxonomies/countries',
                icon: BookOpen,
                children: [
                    {
                        title: 'Countries',
                        href: '/admin/tuition/taxonomies/countries',
                        icon: Globe,
                    },
                    {
                        title: 'Cities',
                        href: '/admin/tuition/taxonomies/cities',
                        icon: MapPin,
                    },
                    {
                        title: 'Areas',
                        href: '/admin/tuition/taxonomies/areas',
                        icon: MapPin,
                    },
                    {
                        title: 'Categories',
                        href: '/admin/tuition/taxonomies/categories',
                        icon: FolderOpen,
                    },
                    {
                        title: 'Classes',
                        href: '/admin/tuition/taxonomies/classes',
                        icon: GraduationCap,
                    },
                    {
                        title: 'Subjects',
                        href: '/admin/tuition/taxonomies/subjects',
                        icon: FileText,
                    },
                    {
                        title: 'Tuition Types',
                        href: '/admin/tuition/taxonomies/tuition-types',
                        icon: Tags,
                    },
                ],
            },
            {
                title: 'Blog',
                href: '/admin/blog/posts',
                icon: Newspaper,
                children: [
                    {
                        title: 'Posts',
                        href: '/admin/blog/posts',
                        icon: FileText,
                    },
                    {
                        title: 'Categories',
                        href: '/admin/blog/categories',
                        icon: FolderOpen,
                    },
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
                    {
                        title: 'Backups',
                        href: '/admin/backups',
                        icon: HardDrive,
                    },
                    {
                        title: 'Activity Logs',
                        href: '/admin/activity-logs',
                        icon: History,
                    },
                    {
                        title: 'Log Viewer',
                        href: '/admin/log-viewer',
                        icon: FileText,
                        fullPage: true,
                    },
                ],
            },
        ];
    }

    if (role === 'tutor') {
        return [
            { title: 'Dashboard', href: '/tutor/dashboard', icon: LayoutGrid },
            { title: 'Browse Jobs', href: '/tutor/jobs', icon: FileText },
            {
                title: 'My Applications',
                href: '/tutor/job-applications',
                icon: FolderOpen,
            },
            {
                title: 'Fees & Refunds',
                href: '/tutor/finance/invoices',
                icon: FileText,
                children: [
                    {
                        title: 'Invoices',
                        href: '/tutor/finance/invoices',
                        icon: FileText,
                    },
                    {
                        title: 'Refund Requests',
                        href: '/tutor/finance/refunds',
                        icon: FileText,
                    },
                ],
            },
            { title: 'Profile', href: '/tutor/profile', icon: UserRound },
            { title: 'Tutorials', href: '/tutor/tutorials', icon: PlayCircle },
            {
                title: 'Terms of Service',
                href: '/tutor/terms-of-service',
                icon: ScrollText,
            },
            {
                title: 'Support Tickets',
                href: '/tutor/support-tickets',
                icon: LifeBuoy,
            },
            { title: 'Settings', href: '/settings/profile', icon: Cog },
        ];
    }

    return [
        { title: 'Dashboard', href: '/guardian/dashboard', icon: LayoutGrid },
        {
            title: 'Hiring Pipeline',
            href: '/guardian/jobs',
            icon: Briefcase,
            isActive: true,
            children: [
                {
                    title: 'Post New Job',
                    href: '/guardian/jobs/create',
                    icon: FileText,
                },
                {
                    title: 'All Jobs',
                    href: '/guardian/jobs',
                    icon: FileText,
                },
                {
                    title: 'Pending Approval',
                    href: '/guardian/jobs/pending',
                    icon: FileText,
                },
                {
                    title: 'Live (Applications Open)',
                    href: '/guardian/jobs/live',
                    icon: FileText,
                },
                {
                    title: 'Confirmed Hires',
                    href: '/guardian/jobs/confirmed',
                    icon: Briefcase,
                },
                {
                    title: 'Cancelled Jobs',
                    href: '/guardian/jobs/cancelled',
                    icon: FileText,
                },
                {
                    title: 'Closed Jobs',
                    href: '/guardian/jobs/closed',
                    icon: FileText,
                },
            ],
        },
        {
            title: 'Payments & Escrow',
            href: '/guardian/finance/invoices',
            icon: FileText,
        },
        { title: 'Profile', href: '/guardian/profile', icon: UserRound },
        { title: 'My Reviews', href: '/guardian/reviews', icon: Star },
        { title: 'Tutorials', href: '/guardian/tutorials', icon: PlayCircle },
        {
            title: 'Terms of Service',
            href: '/guardian/terms-of-service',
            icon: ScrollText,
        },
        {
            title: 'Support Tickets',
            href: '/guardian/support-tickets',
            icon: LifeBuoy,
        },
        { title: 'Settings', href: '/settings/profile', icon: Cog },
    ];
});
</script>

<template>
    <Sidebar
        collapsible="icon"
        variant="inset"
        class="bg-sidebar/95 backdrop-blur"
    >
        <SidebarHeader class="border-b border-sidebar-border/80 pb-3">
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
                            <AppLogo :show-slogan="true" />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>

            <div
                v-if="showMemberSummary"
                class="mt-2 px-2 group-data-[collapsible=icon]:hidden"
            >
                <div
                    class="rounded-xl border border-sidebar-border/70 p-3 text-center"
                >
                    <div
                        class="mx-auto flex h-20 w-20 items-center justify-center overflow-hidden rounded-full border border-sidebar-border/70 bg-sidebar-accent text-xl font-semibold text-sidebar-accent-foreground"
                    >
                        <img
                            v-if="authUser?.avatar"
                            :src="String(authUser.avatar)"
                            :alt="authUser.name || 'User avatar'"
                            class="h-full w-full object-cover"
                        />
                        <span v-else>{{ memberInitials }}</span>
                    </div>

                    <p
                        class="mt-3 truncate text-base font-semibold text-sidebar-foreground"
                    >
                        {{ authUser?.name }}
                    </p>
                    <div class="mt-1">
                        <span
                            class="inline-flex items-center rounded-full border px-2 py-0.5 text-[11px] font-semibold"
                            :class="verificationBadge.className"
                        >
                            {{ verificationBadge.label }}
                        </span>
                    </div>
                    <p class="mt-1 text-xs text-sidebar-foreground/80">
                        {{ memberIdLabel }}: {{ authUser?.id }}
                    </p>
                    <p class="text-xs text-sidebar-foreground/70">
                        Member since {{ memberSince }}
                    </p>
                </div>
            </div>
        </SidebarHeader>

        <SidebarContent class="pt-2">
            <NavMain :items="mainNavItems" />
        </SidebarContent>


        <!-- <SidebarFooter class="pt-3">
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton
                        as-child
                        class="text-rose-600 hover:text-rose-700 data-[active=true]:text-rose-700"
                    >
                        <Link
                            :href="logout()"
                            method="post"
                            as="button"
                            class="w-full"
                            data-test="sidebar-logout-button"
                            @click="handleLogout"
                        >
                            <LogOut />
                            <span>Logout</span>
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarFooter> -->
    </Sidebar>
    <slot />
</template>
