<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { useSiteSettings } from '@/composables/useSiteSettings';
import {
    home,
    jobs,
    tutors,
    blog,
    contact,
    login,
    register,
    dashboard,
} from '@/routes';

const { siteName, logoUrl, primaryPhone, primaryEmail } = useSiteSettings();
const page = usePage();

const mobileMenuOpen = ref(false);
const isAuthenticated = computed(() => Boolean(page.props.auth?.user));

const navLinks = [
    { href: home, label: 'Home' },
    { href: tutors, label: 'Find Tutor' },
    { href: jobs, label: 'Job Board' },
    { href: blog, label: 'Blog' },
    { href: contact, label: 'Contact' },
];
</script>

<template>
    <div class="sticky top-0 z-50 w-full">
        <div
            v-if="primaryPhone || primaryEmail"
            class="border-b border-gray-200 bg-slate-900 text-slate-100"
        >
            <div
                class="mx-auto flex min-h-9 max-w-6xl flex-wrap items-center gap-x-4 gap-y-1 px-4 py-2 text-xs sm:px-6"
            >
                <span v-if="primaryPhone" class="font-medium"
                    >Phone: {{ primaryPhone }}</span
                >
                <span
                    v-if="primaryPhone && primaryEmail"
                    class="hidden text-slate-400 sm:inline"
                    >|</span
                >
                <span v-if="primaryEmail" class="font-medium"
                    >Email: {{ primaryEmail }}</span
                >
            </div>
        </div>

        <header class="w-full border-b border-gray-200 bg-white">
            <div
                class="mx-auto flex h-16 max-w-6xl items-center justify-between px-4 sm:px-6"
            >
                <Link :href="home()" class="flex items-center gap-2">
                    <img
                        v-if="logoUrl"
                        :src="logoUrl"
                        :alt="siteName"
                        class="h-9 w-9 rounded-lg object-cover"
                    />
                    <div
                        v-else
                        class="flex h-9 w-9 items-center justify-center rounded-lg bg-blue-600 font-bold text-white"
                    >
                        {{ siteName.charAt(0).toUpperCase() }}
                    </div>
                    <span class="text-lg font-semibold text-gray-900">{{
                        siteName
                    }}</span>
                </Link>

                <nav
                    class="hidden items-center gap-8 text-sm font-medium text-gray-700 md:flex"
                >
                    <Link
                        v-for="link in navLinks"
                        :key="link.href().url"
                        :href="link.href()"
                        class="hover:text-blue-600"
                    >
                        {{ link.label }}
                    </Link>
                </nav>

                <div class="flex items-center gap-3">
                    <template v-if="isAuthenticated">
                        <Link
                            :href="dashboard()"
                            class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700"
                        >
                            Dashboard
                        </Link>
                    </template>
                    <template v-else>
                        <Link
                            :href="login()"
                            class="hidden text-sm font-medium text-gray-700 hover:text-blue-600 sm:block"
                        >
                            Login
                        </Link>
                        <Link
                            :href="register()"
                            class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700"
                        >
                            Join Now
                        </Link>
                    </template>
                    <button
                        class="ml-2 rounded-lg p-2 text-gray-600 hover:bg-gray-100 md:hidden"
                        @click="mobileMenuOpen = !mobileMenuOpen"
                        aria-label="Toggle menu"
                    >
                        <svg
                            v-if="!mobileMenuOpen"
                            class="h-6 w-6"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16"
                            />
                        </svg>
                        <svg
                            v-else
                            class="h-6 w-6"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"
                            />
                        </svg>
                    </button>
                </div>
            </div>

            <div
                v-if="mobileMenuOpen"
                class="border-t border-gray-200 bg-white md:hidden"
            >
                <div class="space-y-1 px-4 py-4">
                    <Link
                        v-for="link in navLinks"
                        :key="link.href().url"
                        :href="link.href()"
                        class="block rounded-lg px-3 py-2 text-base font-medium text-gray-700 hover:bg-gray-100"
                        @click="mobileMenuOpen = false"
                    >
                        {{ link.label }}
                    </Link>
                    <div class="mt-4 border-t border-gray-200 pt-4">
                        <template v-if="isAuthenticated">
                            <Link
                                :href="dashboard()"
                                class="block rounded-lg bg-blue-600 px-3 py-2 text-center text-base font-semibold text-white hover:bg-blue-700"
                                @click="mobileMenuOpen = false"
                            >
                                Dashboard
                            </Link>
                        </template>
                        <template v-else>
                            <Link
                                :href="login()"
                                class="block rounded-lg px-3 py-2 text-base font-medium text-gray-700 hover:bg-gray-100"
                                @click="mobileMenuOpen = false"
                            >
                                Login
                            </Link>
                            <Link
                                :href="register()"
                                class="mt-2 block rounded-lg bg-blue-600 px-3 py-2 text-center text-base font-semibold text-white hover:bg-blue-700"
                                @click="mobileMenuOpen = false"
                            >
                                Join Now
                            </Link>
                        </template>
                    </div>
                </div>
            </div>
        </header>
    </div>
</template>
