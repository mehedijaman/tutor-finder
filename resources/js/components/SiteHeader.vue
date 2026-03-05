<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Menu, X } from 'lucide-vue-next';
import { ref } from 'vue';
import {
    Sheet,
    SheetContent,
    SheetHeader,
    SheetTitle,
    SheetTrigger,
} from '@/components/ui/sheet';
import { useSiteSettings } from '@/composables/useSiteSettings';
import {
    dashboard,
    login,
    register,
    home,
    jobs,
    contact,
    tutors,
    blog,
    tutorials,
} from '@/routes';

withDefaults(
    defineProps<{
        variant?: 'full' | 'simple';
    }>(),
    {
        variant: 'simple',
    },
);

const { siteName, logoUrl, slogan, primaryPhone, primaryEmail } =
    useSiteSettings();

const mobileMenuOpen = ref(false);
</script>

<template>
    <!-- Full Header -->
    <template v-if="variant === 'full'">
        <div
            v-if="primaryPhone || primaryEmail"
            class="border-b border-white/10 bg-slate-950 text-slate-100"
        >
            <div
                class="mx-auto flex min-h-9 max-w-7xl flex-wrap items-center gap-x-4 gap-y-1 px-4 py-2 text-xs sm:px-6"
            >
                <span v-if="primaryPhone" class="font-medium tracking-wide"
                    >Phone: {{ primaryPhone }}</span
                >
                <span
                    v-if="primaryPhone && primaryEmail"
                    class="hidden text-slate-400 sm:inline"
                    >|</span
                >
                <span v-if="primaryEmail" class="font-medium tracking-wide"
                    >Email: {{ primaryEmail }}</span
                >
            </div>
        </div>

        <header
            class="sticky top-0 z-50 w-full border-b border-slate-200/80 bg-white/95 backdrop-blur"
        >
            <div
                class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:h-[4.5rem] sm:px-6"
            >
                <!-- Logo -->
                <Link :href="home()" class="group flex items-center gap-3">
                    <img
                        v-if="logoUrl"
                        :src="logoUrl"
                        :alt="siteName"
                        class="h-9 w-9 rounded-xl object-cover ring-1 ring-slate-200"
                    />
                    <div
                        v-else
                        class="flex h-9 w-9 items-center justify-center rounded-xl bg-blue-600 text-sm font-bold text-white shadow-sm"
                    >
                        {{ siteName.charAt(0).toUpperCase() }}
                    </div>
                    <div class="flex flex-col leading-tight">
                        <span
                            class="text-base font-semibold tracking-tight text-slate-900 transition-colors group-hover:text-blue-700 sm:text-lg"
                            >{{ siteName }}</span
                        >
                        <span
                            v-if="slogan"
                            class="max-w-52 text-[11px] text-slate-500 sm:max-w-none"
                            >{{ slogan }}</span
                        >
                    </div>
                </Link>

                <!-- Desktop Nav -->
                <nav
                    class="hidden items-center gap-1 rounded-full border border-slate-200 bg-slate-50/70 p-1 text-sm font-medium text-slate-700 md:flex"
                >
                    <Link
                        :href="home()"
                        class="rounded-full px-3 py-1.5 transition-colors hover:bg-white hover:text-blue-700 focus-visible:ring-2 focus-visible:ring-blue-200 focus-visible:ring-offset-1 focus-visible:outline-none"
                        >Home</Link
                    >
                    <Link
                        :href="tutors()"
                        class="rounded-full px-3 py-1.5 transition-colors hover:bg-white hover:text-blue-700 focus-visible:ring-2 focus-visible:ring-blue-200 focus-visible:ring-offset-1 focus-visible:outline-none"
                        >Find Tutor</Link
                    >
                    <Link
                        :href="jobs()"
                        class="rounded-full px-3 py-1.5 transition-colors hover:bg-white hover:text-blue-700 focus-visible:ring-2 focus-visible:ring-blue-200 focus-visible:ring-offset-1 focus-visible:outline-none"
                        >Job Board</Link
                    >
                    <Link
                        :href="blog()"
                        class="rounded-full px-3 py-1.5 transition-colors hover:bg-white hover:text-blue-700 focus-visible:ring-2 focus-visible:ring-blue-200 focus-visible:ring-offset-1 focus-visible:outline-none"
                        >Blog</Link
                    >
                    <Link
                        :href="tutorials()"
                        class="rounded-full px-3 py-1.5 transition-colors hover:bg-white hover:text-blue-700 focus-visible:ring-2 focus-visible:ring-blue-200 focus-visible:ring-offset-1 focus-visible:outline-none"
                        >Tutorials</Link
                    >
                    <Link
                        :href="contact()"
                        class="rounded-full px-3 py-1.5 transition-colors hover:bg-white hover:text-blue-700 focus-visible:ring-2 focus-visible:ring-blue-200 focus-visible:ring-offset-1 focus-visible:outline-none"
                        >Contact</Link
                    >
                </nav>

                <!-- Auth Buttons -->
                <div class="flex items-center gap-2 sm:gap-3">
                    <!-- Mobile Menu Button -->
                    <Sheet v-model:open="mobileMenuOpen">
                        <SheetTrigger as-child>
                            <button
                                class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-700 transition-colors hover:bg-slate-50 md:hidden"
                                aria-label="Open menu"
                            >
                                <Menu class="h-5 w-5" />
                            </button>
                        </SheetTrigger>
                        <SheetContent side="right" class="w-[300px] sm:w-[360px]">
                            <SheetHeader>
                                <SheetTitle class="text-left">Menu</SheetTitle>
                            </SheetHeader>
                            <nav class="mt-6 flex flex-col gap-1">
                                <Link
                                    :href="home()"
                                    class="flex items-center rounded-xl px-4 py-3 text-base font-medium text-slate-700 transition-colors hover:bg-slate-100 hover:text-blue-700"
                                    @click="mobileMenuOpen = false"
                                >
                                    Home
                                </Link>
                                <Link
                                    :href="tutors()"
                                    class="flex items-center rounded-xl px-4 py-3 text-base font-medium text-slate-700 transition-colors hover:bg-slate-100 hover:text-blue-700"
                                    @click="mobileMenuOpen = false"
                                >
                                    Find Tutor
                                </Link>
                                <Link
                                    :href="jobs()"
                                    class="flex items-center rounded-xl px-4 py-3 text-base font-medium text-slate-700 transition-colors hover:bg-slate-100 hover:text-blue-700"
                                    @click="mobileMenuOpen = false"
                                >
                                    Job Board
                                </Link>
                                <Link
                                    :href="blog()"
                                    class="flex items-center rounded-xl px-4 py-3 text-base font-medium text-slate-700 transition-colors hover:bg-slate-100 hover:text-blue-700"
                                    @click="mobileMenuOpen = false"
                                >
                                    Blog
                                </Link>
                                <Link
                                    :href="tutorials()"
                                    class="flex items-center rounded-xl px-4 py-3 text-base font-medium text-slate-700 transition-colors hover:bg-slate-100 hover:text-blue-700"
                                    @click="mobileMenuOpen = false"
                                >
                                    Tutorials
                                </Link>
                                <Link
                                    :href="contact()"
                                    class="flex items-center rounded-xl px-4 py-3 text-base font-medium text-slate-700 transition-colors hover:bg-slate-100 hover:text-blue-700"
                                    @click="mobileMenuOpen = false"
                                >
                                    Contact
                                </Link>
                                <div class="my-4 border-t border-slate-200" />
                                <template v-if="$page.props.auth.user">
                                    <Link
                                        :href="dashboard()"
                                        class="flex items-center justify-center rounded-xl bg-blue-600 px-4 py-3 text-base font-semibold text-white shadow-sm transition hover:bg-blue-700"
                                        @click="mobileMenuOpen = false"
                                    >
                                        Dashboard
                                    </Link>
                                </template>
                                <template v-else>
                                    <Link
                                        :href="login()"
                                        class="flex items-center justify-center rounded-xl border border-slate-200 px-4 py-3 text-base font-medium text-slate-700 transition-colors hover:bg-slate-50"
                                        @click="mobileMenuOpen = false"
                                    >
                                        Login
                                    </Link>
                                    <Link
                                        :href="register()"
                                        class="mt-2 flex items-center justify-center rounded-xl bg-blue-600 px-4 py-3 text-base font-semibold text-white shadow-sm transition hover:bg-blue-700"
                                        @click="mobileMenuOpen = false"
                                    >
                                        Join Now
                                    </Link>
                                </template>
                            </nav>
                        </SheetContent>
                    </Sheet>

                    <template v-if="$page.props.auth.user">
                        <Link
                            :href="dashboard()"
                            class="hidden items-center rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-md shadow-blue-600/25 transition-all hover:bg-blue-700 hover:shadow-lg hover:shadow-blue-600/30 focus-visible:ring-2 focus-visible:ring-blue-200 focus-visible:ring-offset-2 md:inline-flex"
                        >
                            Dashboard
                        </Link>
                    </template>
                    <template v-else>
                        <Link
                            :href="login()"
                            class="hidden rounded-xl px-4 py-2.5 text-sm font-medium text-slate-700 transition-colors hover:bg-slate-100 hover:text-blue-700 focus-visible:ring-2 focus-visible:ring-blue-200 focus-visible:ring-offset-1 focus-visible:outline-none md:block"
                        >
                            Login
                        </Link>
                        <Link
                            :href="register()"
                            class="hidden items-center rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-md shadow-blue-600/25 transition-all hover:bg-blue-700 hover:shadow-lg hover:shadow-blue-600/30 focus-visible:ring-2 focus-visible:ring-blue-200 focus-visible:ring-offset-2 md:inline-flex"
                        >
                            Join Now
                        </Link>
                    </template>
                </div>
            </div>
        </header>
    </template>

    <!-- Simple Header -->
    <template v-else>
        <div
            v-if="primaryPhone || primaryEmail"
            class="border-b border-white/10 bg-slate-950 text-slate-100"
        >
            <div
                class="mx-auto flex min-h-9 max-w-7xl flex-wrap items-center gap-x-4 gap-y-1 px-4 py-2 text-xs sm:px-6"
            >
                <span v-if="primaryPhone" class="font-medium tracking-wide"
                    >Phone: {{ primaryPhone }}</span
                >
                <span
                    v-if="primaryPhone && primaryEmail"
                    class="hidden text-slate-400 sm:inline"
                    >|</span
                >
                <span v-if="primaryEmail" class="font-medium tracking-wide"
                    >Email: {{ primaryEmail }}</span
                >
            </div>
        </div>

        <header
            class="sticky top-0 z-50 w-full border-b border-slate-200/80 bg-white/95 backdrop-blur"
        >
            <div
                class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:h-[4.5rem] sm:px-6"
            >
                <Link :href="home()" class="group flex items-center gap-2.5">
                    <img
                        v-if="logoUrl"
                        :src="logoUrl"
                        :alt="siteName"
                        class="h-8 w-8 rounded-xl object-cover ring-1 ring-slate-200 sm:h-9 sm:w-9"
                    />
                    <div
                        v-else
                        class="flex h-8 w-8 items-center justify-center rounded-xl bg-blue-600 text-sm font-bold text-white shadow-sm sm:h-9 sm:w-9"
                    >
                        {{ siteName.charAt(0).toUpperCase() }}
                    </div>
                    <div class="flex flex-col leading-tight">
                        <span
                            class="max-w-32 text-sm font-semibold tracking-tight text-slate-900 transition-colors group-hover:text-blue-700 sm:max-w-none sm:text-base"
                            >{{ siteName }}</span
                        >
                        <span
                            v-if="slogan"
                            class="max-w-32 truncate text-[11px] text-slate-500 sm:max-w-none"
                            >{{ slogan }}</span
                        >
                    </div>
                </Link>

                <nav class="flex items-center gap-2 text-sm sm:gap-3">
                    <Link
                        v-if="$page.props.auth.user"
                        :href="dashboard()"
                        class="inline-flex items-center rounded-xl bg-blue-600 px-3.5 py-2 font-semibold text-white shadow-sm transition hover:bg-blue-700 focus-visible:ring-2 focus-visible:ring-blue-200 focus-visible:ring-offset-2 sm:px-4"
                    >
                        Dashboard
                    </Link>
                    <template v-else>
                        <Link
                            :href="login()"
                            class="rounded-md px-1.5 py-1 font-medium text-slate-700 transition-colors hover:text-blue-700 focus-visible:ring-2 focus-visible:ring-blue-200 focus-visible:ring-offset-1 focus-visible:outline-none"
                            >Login</Link
                        >
                        <Link
                            :href="register()"
                            class="inline-flex items-center rounded-xl bg-blue-600 px-3.5 py-2 font-semibold text-white shadow-sm transition hover:bg-blue-700 focus-visible:ring-2 focus-visible:ring-blue-200 focus-visible:ring-offset-2 sm:px-4"
                        >
                            Register
                        </Link>
                    </template>
                </nav>
            </div>
        </header>
    </template>
</template>
