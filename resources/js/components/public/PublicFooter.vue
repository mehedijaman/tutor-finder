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
    blog,
} from '@/routes';

const { siteName, logoUrl, primaryPhone, primaryEmail, primaryAddress } =
    useSiteSettings();
</script>

<template>
    <footer class="bg-gray-900 text-gray-300">
        <div class="mx-auto max-w-6xl px-4 py-12 sm:px-6">
            <div class="grid gap-8 md:grid-cols-4">
                <div class="md:col-span-1">
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
                        <span class="font-semibold text-white">{{
                            siteName
                        }}</span>
                    </Link>
                    <p class="mt-4 text-sm leading-relaxed text-gray-400">
                        Connecting students with verified tutors for better
                        learning outcomes.
                    </p>
                </div>

                <div>
                    <h3 class="mb-4 font-semibold text-white">Quick Links</h3>
                    <ul class="space-y-2 text-sm">
                        <li>
                            <Link
                                :href="home()"
                                class="text-gray-400 hover:text-white"
                                >Home</Link
                            >
                        </li>
                        <li>
                            <Link
                                :href="jobs()"
                                class="text-gray-400 hover:text-white"
                                >Find Tutor</Link
                            >
                        </li>
                        <li>
                            <Link
                                :href="jobs()"
                                class="text-gray-400 hover:text-white"
                                >Job Board</Link
                            >
                        </li>
                        <li>
                            <Link
                                :href="blog()"
                                class="text-gray-400 hover:text-white"
                                >Blog</Link
                            >
                        </li>
                        <li>
                            <Link
                                :href="contact()"
                                class="text-gray-400 hover:text-white"
                                >Contact</Link
                            >
                        </li>
                    </ul>
                </div>

                <div>
                    <h3 class="mb-4 font-semibold text-white">For</h3>
                    <ul class="space-y-2 text-sm">
                        <li>
                            <Link
                                :href="register()"
                                class="text-gray-400 hover:text-white"
                                >Students</Link
                            >
                        </li>
                        <li>
                            <Link
                                :href="register()"
                                class="text-gray-400 hover:text-white"
                                >Guardians</Link
                            >
                        </li>
                        <li>
                            <Link
                                :href="jobs()"
                                class="text-gray-400 hover:text-white"
                                >Tutors</Link
                            >
                        </li>
                    </ul>
                </div>

                <div>
                    <h3 class="mb-4 font-semibold text-white">Contact</h3>
                    <ul class="space-y-2 text-sm">
                        <li v-if="primaryAddress" class="text-gray-400">
                            {{ primaryAddress }}
                        </li>
                        <li v-if="primaryPhone">
                            <a
                                :href="`tel:${primaryPhone}`"
                                class="text-gray-400 hover:text-white"
                                >{{ primaryPhone }}</a
                            >
                        </li>
                        <li v-if="primaryEmail">
                            <a
                                :href="`mailto:${primaryEmail}`"
                                class="text-gray-400 hover:text-white"
                                >{{ primaryEmail }}</a
                            >
                        </li>
                    </ul>
                </div>
            </div>

            <div
                class="mt-10 flex flex-col items-center justify-between gap-4 border-t border-gray-800 pt-6 text-sm md:flex-row"
            >
                <p class="text-gray-500">
                    © {{ new Date().getFullYear() }} {{ siteName }}. All rights
                    reserved.
                </p>
                <div class="flex gap-6">
                    <Link
                        :href="privacyPolicy()"
                        class="text-gray-500 hover:text-white"
                        >Privacy Policy</Link
                    >
                    <Link
                        :href="termsOfService()"
                        class="text-gray-500 hover:text-white"
                        >Terms of Service</Link
                    >
                </div>
            </div>
        </div>
    </footer>
</template>
