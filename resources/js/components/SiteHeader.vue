<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
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
                        :href="contact()"
                        class="rounded-full px-3 py-1.5 transition-colors hover:bg-white hover:text-blue-700 focus-visible:ring-2 focus-visible:ring-blue-200 focus-visible:ring-offset-1 focus-visible:outline-none"
                        >Contact</Link
                    >
                </nav>

                <!-- Auth Buttons -->
                <div class="flex items-center gap-2 sm:gap-3">
                    <Link
                        :href="login()"
                        class="hidden rounded-lg px-3 py-2 text-sm font-medium text-slate-700 transition-colors hover:bg-slate-100 hover:text-blue-700 focus-visible:ring-2 focus-visible:ring-blue-200 focus-visible:ring-offset-1 focus-visible:outline-none sm:block"
                    >
                        Login
                    </Link>
                    <Link
                        :href="register()"
                        class="inline-flex items-center rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus-visible:ring-2 focus-visible:ring-blue-200 focus-visible:ring-offset-2"
                    >
                        Join Now
                    </Link>
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
