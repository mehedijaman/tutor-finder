<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { useSiteSettings } from '@/composables/useSiteSettings';
import {
    jobs,
    contact,
    privacyPolicy,
    termsOfService,
    home,
    register,
} from '@/routes';

withDefaults(
    defineProps<{
        variant?: 'full' | 'simple';
    }>(),
    {
        variant: 'simple',
    },
);

const { siteName, logoUrl, primaryPhone, primaryEmail, primaryAddress } =
    useSiteSettings();
</script>

<template>
    <!-- Full Footer -->
    <footer
        v-if="variant === 'full'"
        class="border-t border-slate-800 bg-slate-950 text-slate-300"
    >
        <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 sm:py-14">
            <div class="grid gap-10 sm:grid-cols-2 lg:grid-cols-4">
                <!-- Brand -->
                <div class="sm:col-span-2 lg:col-span-1">
                    <Link :href="home()" class="group flex items-center gap-3">
                        <img
                            v-if="logoUrl"
                            :src="logoUrl"
                            :alt="siteName"
                            class="h-10 w-10 rounded-xl object-cover ring-1 ring-white/10"
                        />
                        <div
                            v-else
                            class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-600 text-sm font-bold text-white shadow-sm"
                        >
                            {{ siteName.charAt(0).toUpperCase() }}
                        </div>
                        <span
                            class="text-base font-semibold tracking-tight text-white transition-colors group-hover:text-blue-300"
                            >{{ siteName }}</span
                        >
                    </Link>
                    <p
                        class="mt-4 max-w-xs text-sm leading-relaxed text-slate-400"
                    >
                        Connecting students with verified tutors for better
                        learning outcomes.
                    </p>
                </div>

                <!-- Quick Links -->
                <div>
                    <h3
                        class="mb-4 text-sm font-semibold tracking-[0.08em] text-white uppercase"
                    >
                        Quick Links
                    </h3>
                    <ul class="space-y-2.5 text-sm">
                        <li>
                            <Link
                                :href="home()"
                                class="text-slate-400 transition-colors hover:text-white focus-visible:text-white focus-visible:underline focus-visible:outline-none"
                                >Home</Link
                            >
                        </li>
                        <li>
                            <Link
                                :href="jobs()"
                                class="text-slate-400 transition-colors hover:text-white focus-visible:text-white focus-visible:underline focus-visible:outline-none"
                                >Find Tutor</Link
                            >
                        </li>
                        <li>
                            <Link
                                :href="jobs()"
                                class="text-slate-400 transition-colors hover:text-white focus-visible:text-white focus-visible:underline focus-visible:outline-none"
                                >Job Board</Link
                            >
                        </li>
                        <li>
                            <Link
                                :href="contact()"
                                class="text-slate-400 transition-colors hover:text-white focus-visible:text-white focus-visible:underline focus-visible:outline-none"
                                >Contact</Link
                            >
                        </li>
                    </ul>
                </div>

                <!-- For -->
                <div>
                    <h3
                        class="mb-4 text-sm font-semibold tracking-[0.08em] text-white uppercase"
                    >
                        For
                    </h3>
                    <ul class="space-y-2.5 text-sm">
                        <li>
                            <Link
                                :href="register()"
                                class="text-slate-400 transition-colors hover:text-white focus-visible:text-white focus-visible:underline focus-visible:outline-none"
                                >Students</Link
                            >
                        </li>
                        <li>
                            <Link
                                :href="register()"
                                class="text-slate-400 transition-colors hover:text-white focus-visible:text-white focus-visible:underline focus-visible:outline-none"
                                >Guardians</Link
                            >
                        </li>
                        <li>
                            <Link
                                :href="jobs()"
                                class="text-slate-400 transition-colors hover:text-white focus-visible:text-white focus-visible:underline focus-visible:outline-none"
                                >Tutors</Link
                            >
                        </li>
                    </ul>
                </div>

                <!-- Contact -->
                <div>
                    <h3
                        class="mb-4 text-sm font-semibold tracking-[0.08em] text-white uppercase"
                    >
                        Contact
                    </h3>
                    <ul class="space-y-2.5 text-sm">
                        <li
                            v-if="primaryAddress"
                            class="leading-relaxed text-slate-400"
                        >
                            {{ primaryAddress }}
                        </li>
                        <li v-if="primaryPhone">
                            <a
                                :href="`tel:${primaryPhone}`"
                                class="text-slate-400 transition-colors hover:text-white"
                                >{{ primaryPhone }}</a
                            >
                        </li>
                        <li v-if="primaryEmail">
                            <a
                                :href="`mailto:${primaryEmail}`"
                                class="break-all text-slate-400 transition-colors hover:text-white"
                                >{{ primaryEmail }}</a
                            >
                        </li>
                    </ul>
                </div>
            </div>

            <div
                class="mt-10 flex flex-col items-center justify-between gap-4 border-t border-slate-800 pt-6 text-sm md:flex-row"
            >
                <p class="text-slate-500">
                    © {{ new Date().getFullYear() }} {{ siteName }}. All rights
                    reserved.
                </p>
                <div class="flex flex-wrap items-center gap-x-6 gap-y-2">
                    <Link
                        :href="privacyPolicy()"
                        class="text-slate-500 transition-colors hover:text-white focus-visible:text-white focus-visible:underline focus-visible:outline-none"
                        >Privacy Policy</Link
                    >
                    <Link
                        :href="termsOfService()"
                        class="text-slate-500 transition-colors hover:text-white focus-visible:text-white focus-visible:underline focus-visible:outline-none"
                        >Terms of Service</Link
                    >
                </div>
            </div>
        </div>
    </footer>

    <!-- Simple Footer -->
    <footer v-else class="border-t border-slate-200 bg-white/95 backdrop-blur">
        <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6">
            <div
                class="flex flex-col items-center justify-between gap-3 text-sm sm:flex-row sm:gap-4"
            >
                <p class="text-center text-slate-500 sm:text-left">
                    © {{ new Date().getFullYear() }} {{ siteName }}. All rights
                    reserved.
                </p>
                <div class="flex items-center gap-5">
                    <Link
                        :href="privacyPolicy()"
                        class="font-medium text-slate-600 transition-colors hover:text-blue-700 focus-visible:underline focus-visible:outline-none"
                        >Privacy</Link
                    >
                    <Link
                        :href="termsOfService()"
                        class="font-medium text-slate-600 transition-colors hover:text-blue-700 focus-visible:underline focus-visible:outline-none"
                        >Terms</Link
                    >
                </div>
            </div>
        </div>
    </footer>
</template>
