<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { useSiteSettings } from '@/composables/useSiteSettings';
import { dashboard, login, register, home, jobs, contact } from '@/routes';

withDefaults(
    defineProps<{
        variant?: 'full' | 'simple';
    }>(),
    {
        variant: 'simple',
    },
);

const { siteName, logoUrl } = useSiteSettings();
</script>

<template>
    <!-- Full Header -->
    <header
        v-if="variant === 'full'"
        class="sticky top-0 z-50 w-full border-b border-gray-200 bg-white"
    >
        <div
            class="mx-auto flex h-16 max-w-6xl items-center justify-between px-4 sm:px-6"
        >
            <!-- Logo -->
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

            <!-- Desktop Nav -->
            <nav
                class="hidden items-center gap-8 text-sm font-medium text-gray-700 md:flex"
            >
                <Link :href="home()" class="hover:text-blue-600">Home</Link>
                <Link :href="jobs()" class="hover:text-blue-600"
                    >Find Tutor</Link
                >
                <Link :href="jobs()" class="hover:text-blue-600"
                    >Job Board</Link
                >
                <Link :href="contact()" class="hover:text-blue-600">About</Link>
                <Link :href="contact()" class="hover:text-blue-600"
                    >Contact</Link
                >
            </nav>

            <!-- Auth Buttons -->
            <div class="flex items-center gap-3">
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
            </div>
        </div>
    </header>

    <!-- Simple Header -->
    <header
        v-else
        class="sticky top-0 z-50 w-full border-b border-gray-200 bg-white"
    >
        <div
            class="mx-auto flex h-16 max-w-6xl items-center justify-between px-4 sm:px-6"
        >
            <Link :href="home()" class="flex items-center gap-2">
                <img
                    v-if="logoUrl"
                    :src="logoUrl"
                    :alt="siteName"
                    class="h-8 w-8 rounded-lg object-cover"
                />
                <div
                    v-else
                    class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-600 text-sm font-bold text-white"
                >
                    {{ siteName.charAt(0).toUpperCase() }}
                </div>
                <span class="font-semibold text-gray-900">{{ siteName }}</span>
            </Link>

            <nav class="flex items-center gap-3 text-sm">
                <Link
                    v-if="$page.props.auth.user"
                    :href="dashboard()"
                    class="rounded-lg bg-blue-600 px-4 py-2 font-semibold text-white hover:bg-blue-700"
                >
                    Dashboard
                </Link>
                <template v-else>
                    <Link
                        :href="login()"
                        class="font-medium text-gray-700 hover:text-blue-600"
                        >Login</Link
                    >
                    <Link
                        :href="register()"
                        class="rounded-lg bg-blue-600 px-4 py-2 font-semibold text-white hover:bg-blue-700"
                    >
                        Register
                    </Link>
                </template>
            </nav>
        </div>
    </header>
</template>
