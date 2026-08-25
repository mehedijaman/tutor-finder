<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import {
    Facebook,
    Instagram,
    Linkedin,
    Mail,
    Menu,
    MessageCircle,
    Phone,
    Twitter,
    Youtube,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import {
    Sheet,
    SheetContent,
    SheetHeader,
    SheetTitle,
    SheetTrigger,
} from '@/components/ui/sheet';
import { useSiteSettings } from '@/composables/useSiteSettings';
import {
    blog,
    contact,
    dashboard,
    home,
    jobs,
    login,
    register,
    tutors,
    tutorials,
} from '@/routes';

withDefaults(
    defineProps<{
        variant?: 'full' | 'simple';
    }>(),
    {
        variant: 'full',
    },
);

const { siteName, slogan, primaryPhone, primaryEmail, socialDetails } =
    useSiteSettings();

const mobileMenuOpen = ref(false);
const page = usePage();

const emailDisplay = computed(
    () => primaryEmail.value || 'tutorfinder14@gmail.com',
);
const phoneDisplay = computed(() => primaryPhone.value || '+880 1947-368456');

const navItems = computed(() => [
    {
        label: 'Find Tutor',
        href: tutors(),
        active: page.url.startsWith('/tutors'),
    },
    { label: 'Job Board', href: jobs(), active: page.url.startsWith('/jobs') },
    { label: 'Blog', href: blog(), active: page.url.startsWith('/blog') },
    {
        label: 'Tutorials',
        href: tutorials(),
        active: page.url.startsWith('/tutorials'),
    },
    {
        label: 'Contact',
        href: contact(),
        active: page.url.startsWith('/contact'),
    },
]);
</script>

<template>
    <div class="w-full">
        <!-- Topbar -->
        <div class="bg-[#1b2880] text-white">
            <div
                class="mx-auto flex min-h-10 max-w-7xl flex-wrap items-center justify-between gap-y-2 px-4 py-2 text-xs sm:px-6"
            >
                <!-- Left: Contact Details -->
                <div
                    class="flex flex-wrap items-center gap-4 font-medium sm:gap-6"
                >
                    <!-- Email -->
                    <a
                        :href="`mailto:${emailDisplay}`"
                        class="flex items-center gap-2 transition-opacity hover:opacity-90"
                    >
                        <div
                            class="flex h-5 w-6 items-center justify-center rounded-xs bg-white text-red-600 shadow-2xs"
                        >
                            <Mail class="h-3.5 w-3.5 fill-red-600 text-white" />
                        </div>
                        <span class="tracking-wide text-white/95">{{
                            emailDisplay
                        }}</span>
                    </a>

                    <!-- Phone -->
                    <a
                        :href="`tel:${phoneDisplay.replace(/\s+/g, '')}`"
                        class="flex items-center gap-2 transition-opacity hover:opacity-90"
                    >
                        <div
                            class="flex h-6 w-6 items-center justify-center rounded-full bg-[#0eb0e6] text-white shadow-2xs"
                        >
                            <Phone class="h-3.5 w-3.5 fill-current" />
                        </div>
                        <span
                            class="font-semibold tracking-wide text-white/95"
                            >{{ phoneDisplay }}</span
                        >
                    </a>
                </div>

                <!-- Right: Social Icons Badges -->
                <div class="flex items-center gap-2">
                    <a
                        v-if="socialDetails?.facebook"
                        :href="socialDetails.facebook"
                        target="_blank"
                        rel="noopener noreferrer"
                        aria-label="Facebook"
                        class="flex h-7 w-7 items-center justify-center rounded-full bg-[#1877f2] text-white transition-transform hover:scale-110"
                    >
                        <Facebook class="h-4 w-4 fill-current" />
                    </a>
                    <a
                        v-else
                        href="https://facebook.com"
                        target="_blank"
                        rel="noopener noreferrer"
                        aria-label="Facebook"
                        class="flex h-7 w-7 items-center justify-center rounded-full bg-[#1877f2] text-white transition-transform hover:scale-110"
                    >
                        <Facebook class="h-4 w-4 fill-current" />
                    </a>

                    <a
                        v-if="socialDetails?.whatsapp"
                        :href="socialDetails.whatsapp"
                        target="_blank"
                        rel="noopener noreferrer"
                        aria-label="WhatsApp"
                        class="flex h-7 w-7 items-center justify-center rounded-full bg-[#25d366] text-white transition-transform hover:scale-110"
                    >
                        <MessageCircle class="h-4 w-4 fill-current" />
                    </a>
                    <a
                        v-else
                        href="https://wa.me/8801947368456"
                        target="_blank"
                        rel="noopener noreferrer"
                        aria-label="WhatsApp"
                        class="flex h-7 w-7 items-center justify-center rounded-full bg-[#25d366] text-white transition-transform hover:scale-110"
                    >
                        <MessageCircle class="h-4 w-4 fill-current" />
                    </a>

                    <a
                        v-if="socialDetails?.instagram"
                        :href="socialDetails.instagram"
                        target="_blank"
                        rel="noopener noreferrer"
                        aria-label="Instagram"
                        class="flex h-7 w-7 items-center justify-center rounded-full bg-gradient-to-tr from-[#f9ce34] via-[#ee2a7b] to-[#6228d7] text-white transition-transform hover:scale-110"
                    >
                        <Instagram class="h-4 w-4" />
                    </a>
                    <a
                        v-else
                        href="https://instagram.com"
                        target="_blank"
                        rel="noopener noreferrer"
                        aria-label="Instagram"
                        class="flex h-7 w-7 items-center justify-center rounded-full bg-gradient-to-tr from-[#f9ce34] via-[#ee2a7b] to-[#6228d7] text-white transition-transform hover:scale-110"
                    >
                        <Instagram class="h-4 w-4" />
                    </a>

                    <a
                        v-if="socialDetails?.youtube"
                        :href="socialDetails.youtube"
                        target="_blank"
                        rel="noopener noreferrer"
                        aria-label="YouTube"
                        class="flex h-7 w-7 items-center justify-center rounded-full bg-[#ff0000] text-white transition-transform hover:scale-110"
                    >
                        <Youtube class="h-4 w-4 fill-current" />
                    </a>
                    <a
                        v-else
                        href="https://youtube.com"
                        target="_blank"
                        rel="noopener noreferrer"
                        aria-label="YouTube"
                        class="flex h-7 w-7 items-center justify-center rounded-full bg-[#ff0000] text-white transition-transform hover:scale-110"
                    >
                        <Youtube class="h-4 w-4 fill-current" />
                    </a>
                </div>
            </div>
        </div>

        <!-- Main Header / Navbar -->
        <header
            class="sticky top-0 z-50 w-full border-b border-slate-100 bg-white shadow-2xs"
        >
            <div
                class="mx-auto flex h-20 max-w-7xl items-center justify-between px-4 sm:px-6"
            >
                <!-- Brand / Logo -->
                <Link :href="home()" class="group flex items-center gap-3">
                    <AppLogoIcon
                        class="h-11 w-11 transition-transform group-hover:scale-105 sm:h-12 sm:w-12"
                    />
                    <div class="flex flex-col leading-tight">
                        <span
                            class="text-xl font-bold tracking-tight text-[#1b2880] sm:text-2xl"
                        >
                            Tutor <span class="text-[#0eb0e6]">Finder</span>
                        </span>
                        <span
                            class="text-[10px] font-extrabold tracking-widest text-slate-700 uppercase sm:text-[11px]"
                        >
                            {{ slogan || 'EXPLORE FOR EXCELLENCE' }}
                        </span>
                    </div>
                </Link>

                <!-- Desktop Navigation Links (Pill Style) -->
                <nav
                    v-if="variant === 'full'"
                    class="hidden items-center gap-2 lg:flex"
                >
                    <Link
                        v-for="item in navItems"
                        :key="item.label"
                        :href="item.href"
                        :class="[
                            'rounded-full border-2 border-[#0eb0e6] px-4 py-1.5 text-xs font-bold shadow-2xs transition-all',
                            item.active
                                ? 'bg-[#0eb0e6] text-white'
                                : 'bg-white text-slate-800 hover:bg-[#0eb0e6] hover:text-white',
                        ]"
                    >
                        {{ item.label }}
                    </Link>
                </nav>

                <!-- Right Actions: Auth Buttons -->
                <div class="flex items-center gap-2 sm:gap-3">
                    <template v-if="$page.props.auth.user">
                        <Link
                            :href="dashboard()"
                            class="hidden rounded-full bg-[#0eb0e6] px-6 py-2 text-xs font-bold text-white shadow-xs transition-colors hover:bg-[#0c9bd0] md:inline-flex"
                        >
                            Dashboard
                        </Link>
                    </template>
                    <template v-else>
                        <Link
                            :href="login()"
                            class="hidden rounded-full bg-[#e5e7eb] px-5 py-2 text-xs font-bold text-slate-800 transition-colors hover:bg-slate-300 md:inline-flex"
                        >
                            Login
                        </Link>
                        <Link
                            :href="register()"
                            class="hidden rounded-full bg-[#0eb0e6] px-6 py-2 text-xs font-bold text-white shadow-xs transition-colors hover:bg-[#0c9bd0] md:inline-flex"
                        >
                            Join Now
                        </Link>
                    </template>

                    <!-- Mobile Menu Trigger -->
                    <Sheet v-model:open="mobileMenuOpen">
                        <SheetTrigger as-child>
                            <button
                                class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 bg-slate-50 text-slate-700 transition-colors hover:bg-slate-100 lg:hidden"
                                aria-label="Open menu"
                            >
                                <Menu class="h-5 w-5" />
                            </button>
                        </SheetTrigger>
                        <SheetContent
                            side="right"
                            class="w-[300px] sm:w-[360px]"
                        >
                            <SheetHeader>
                                <SheetTitle
                                    class="text-left font-bold text-[#1b2880]"
                                    >Navigation Menu</SheetTitle
                                >
                            </SheetHeader>
                            <nav class="mt-6 flex flex-col gap-2">
                                <Link
                                    v-for="item in navItems"
                                    :key="item.label"
                                    :href="item.href"
                                    :class="[
                                        'rounded-full border-2 border-[#0eb0e6] px-4 py-2 text-center text-sm font-bold transition-all',
                                        item.active
                                            ? 'bg-[#0eb0e6] text-white'
                                            : 'bg-white text-slate-800 hover:bg-[#0eb0e6] hover:text-white',
                                    ]"
                                    @click="mobileMenuOpen = false"
                                >
                                    {{ item.label }}
                                </Link>

                                <div class="my-4 border-t border-slate-200" />

                                <template v-if="$page.props.auth.user">
                                    <Link
                                        :href="dashboard()"
                                        class="flex items-center justify-center rounded-full bg-[#0eb0e6] px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-[#0c9bd0]"
                                        @click="mobileMenuOpen = false"
                                    >
                                        Dashboard
                                    </Link>
                                </template>
                                <template v-else>
                                    <Link
                                        :href="login()"
                                        class="flex items-center justify-center rounded-full bg-[#e5e7eb] px-4 py-2.5 text-sm font-bold text-slate-800 transition-colors hover:bg-slate-300"
                                        @click="mobileMenuOpen = false"
                                    >
                                        Login
                                    </Link>
                                    <Link
                                        :href="register()"
                                        class="mt-2 flex items-center justify-center rounded-full bg-[#0eb0e6] px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-[#0c9bd0]"
                                        @click="mobileMenuOpen = false"
                                    >
                                        Join Now
                                    </Link>
                                </template>
                            </nav>
                        </SheetContent>
                    </Sheet>
                </div>
            </div>
        </header>
    </div>
</template>
