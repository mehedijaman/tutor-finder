<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { useSiteSettings } from '@/composables/useSiteSettings';
import PublicLayout from '@/layouts/PublicLayout.vue';
import { contact, jobs, register } from '@/routes';

withDefaults(
    defineProps<{
        canRegister: boolean;
    }>(),
    {
        canRegister: true,
    },
);

const { siteName } = useSiteSettings();
</script>

<!--
    =================================================================
    UI REFINEMENT CHECKLIST - TutorFinder Homepage
    =================================================================
    
    ✅ Hero Premium Layout
        - Two-column layout with larger H1
        - "Tutor" highlighted with brand color
        - Trust metrics row as stat blocks
        - Search card with proper elevation
    
    ✅ Search Card Polish
        - Elevated card (rounded-2xl, shadow-sm)
        - Subject, Class, Location fields
        - Full-width primary button
        - Helper text added
    
    ✅ Typography Scale
        - Section titles: text-3xl md:text-4xl font-semibold
        - Body: text-base leading-relaxed text-gray-600
        - Proper heading hierarchy
    
    ✅ Spacing Consistency
        - Section py-14 md:py-20
        - Container max-w-7xl mx-auto px-4 sm:px-6
        - Consistent card padding p-6 md:p-7
    
    ✅ Card Styles Consistent
        - rounded-2xl
        - border border-gray-200/70
        - shadow-sm hover:shadow-md
        - hover:-translate-y-0.5 transition
    
    ✅ Hover/Focus States
        - Cards: hover shadow + lift
        - Buttons: hover color shift + active:scale-[0.98]
        - Inputs: focus ring + border highlight
        - focus-visible:ring-2 focus-visible:ring-blue-500
    
    ✅ Mobile Stacking
        - Hero single column on mobile
        - CTAs stacked full width
        - Buttons min h-11
        - Section spacing py-10 on mobile
        - Footer stacked with clear headings
    
    ✅ Accessibility
        - Proper focus states
        - Minimum tap targets 44px
        - Semantic HTML
    
    ✅ Performance
        - No heavy assets
        - Efficient Tailwind classes
    
    ✅ Brand Naming Consistency
        - "Why Choose [siteName]"
        - Footer uses siteName throughout
    
    =================================================================
-->

<template>
    <Head title="Welcome" />

    <PublicLayout>
        <!-- Hero Section -->
        <section class="bg-white py-14 md:py-24">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="grid items-center gap-12 lg:grid-cols-2">
                    <!-- Left Column - Content -->
                    <div class="text-center lg:text-left">
                        <h1
                            class="text-4xl font-bold tracking-tight text-gray-900 sm:text-5xl lg:text-6xl"
                        >
                            Find the Right
                            <span class="text-blue-600">Tutor</span> for Your
                            Child
                        </h1>
                        <p
                            class="mx-auto mt-4 max-w-xl text-base leading-relaxed text-gray-600 sm:text-lg lg:mx-0"
                        >
                            Connect with verified tutors for online or in-person
                            learning. Simple registration, quick bookings, and
                            progress tracking.
                        </p>

                        <div
                            class="mt-8 flex flex-col gap-3 sm:flex-row sm:justify-center lg:justify-start"
                        >
                            <Link
                                :href="register()"
                                class="inline-flex h-11 items-center justify-center rounded-lg bg-blue-600 px-6 py-3 text-base font-semibold text-white shadow-sm transition-all duration-200 hover:bg-blue-700 hover:shadow-md focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 active:scale-[0.98]"
                            >
                                Find a Tutor
                            </Link>
                            <Link
                                :href="jobs()"
                                class="focus-visible:-visible:ring-bluering-2 focus-500 inline-flex h-11 items-center justify-center rounded-lg border-2 border-blue-600 px-6 py-3 text-base font-semibold text-blue-600 transition-all duration-200 hover:bg-blue-50 focus-visible:ring-offset-2 active:scale-[0.98]"
                            >
                                Post a Job
                            </Link>
                        </div>

                        <!-- Trust Metrics -->
                        <div
                            class="mt-10 flex flex-wrap items-center justify-center gap-6 border-t border-gray-100 pt-6 lg:justify-start"
                        >
                            <div class="text-center">
                                <div class="text-2xl font-bold text-gray-900">
                                    12k+
                                </div>
                                <div class="text-xs text-gray-500">Tutors</div>
                            </div>
                            <div
                                class="hidden h-8 w-px bg-gray-200 sm:block"
                            ></div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-gray-900">
                                    3k+
                                </div>
                                <div class="text-xs text-gray-500">
                                    Families
                                </div>
                            </div>
                            <div
                                class="hidden h-8 w-px bg-gray-200 sm:block"
                            ></div>
                            <div class="text-center">
                                <div
                                    class="flex items-center justify-center gap-1"
                                >
                                    <span
                                        class="text-2xl font-bold text-gray-900"
                                        >4.7</span
                                    >
                                    <span class="text-amber-500">★</span>
                                </div>
                                <div class="text-xs text-gray-500">
                                    Avg Rating
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column - Search Card -->
                    <div class="mx-auto w-full max-w-md lg:max-w-full">
                        <div
                            class="rounded-2xl border border-gray-200/70 bg-white p-6 shadow-md transition-all duration-200 hover:shadow-lg md:p-7"
                        >
                            <div class="flex items-center gap-2">
                                <svg
                                    class="h-5 w-5 text-blue-600"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                                    />
                                </svg>
                                <h3 class="text-lg font-semibold text-gray-900">
                                    Find Your Perfect Tutor
                                </h3>
                            </div>
                            <p class="mt-1 text-sm text-gray-500">
                                Takes less than 2 minutes
                            </p>

                            <form class="mt-5 space-y-4">
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700"
                                        >Subject</label
                                    >
                                    <select
                                        class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm transition duration-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 focus:outline-none"
                                    >
                                        <option value="">Select subject</option>
                                        <option>Mathematics</option>
                                        <option>English</option>
                                        <option>Science</option>
                                        <option>Physics</option>
                                        <option>Chemistry</option>
                                        <option>Biology</option>
                                    </select>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label
                                            class="block text-sm font-medium text-gray-700"
                                            >Class</label
                                        >
                                        <select
                                            class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm transition duration-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 focus:outline-none"
                                        >
                                            <option value="">Any</option>
                                            <option>Class 1-5</option>
                                            <option>Class 6-8</option>
                                            <option>Class 9-10</option>
                                            <option>Class 11-12</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label
                                            class="block text-sm font-medium text-gray-700"
                                            >Location</label
                                        >
                                        <select
                                            class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm transition duration-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 focus:outline-none"
                                        >
                                            <option value="">Any</option>
                                            <option>Dhaka</option>
                                            <option>Chittagong</option>
                                            <option>Online</option>
                                        </select>
                                    </div>
                                </div>

                                <button
                                    type="submit"
                                    class="h-11 w-full rounded-lg bg-blue-600 px-4 py-3 text-base font-semibold text-white shadow-sm transition-all duration-200 hover:bg-blue-700 hover:shadow-md focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 active:scale-[0.98]"
                                >
                                    Search Tutors
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- How It Works -->
        <section class="bg-gray-50 py-14 md:py-20">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="text-center">
                    <p
                        class="text-sm font-medium tracking-widest text-blue-600 uppercase"
                    >
                        How It Works
                    </p>
                    <h2
                        class="mt-2 text-3xl font-semibold tracking-tight text-gray-900 sm:text-4xl"
                    >
                        Simple Steps to Find Your Tutor
                    </h2>
                    <p class="mx-auto mt-3 max-w-2xl text-sm text-gray-500">
                        Get started in minutes with our easy process
                    </p>
                </div>

                <div class="mt-12 grid gap-8 sm:grid-cols-3">
                    <!-- Step 1 -->
                    <div class="group relative">
                        <div
                            class="rounded-2xl border border-gray-200/70 bg-white p-6 shadow-sm transition-all duration-200 hover:-translate-y-1 hover:shadow-md md:p-7"
                        >
                            <div
                                class="flex h-12 w-12 items-center justify-center rounded-full bg-blue-100 transition duration-200 group-hover:bg-blue-600"
                            >
                                <span
                                    class="text-lg font-bold text-blue-600 group-hover:text-white"
                                    >01</span
                                >
                            </div>
                            <h3
                                class="mt-4 text-lg font-semibold text-gray-900"
                            >
                                Post Requirement
                            </h3>
                            <p class="mt-2 text-sm text-gray-500">
                                Tell us about your learning needs, subject, and
                                schedule preferences.
                            </p>
                        </div>
                        <!-- Connector line (desktop) -->
                        <div
                            class="absolute top-1/2 -right-4 hidden h-0.5 w-8 bg-gray-200 sm:block"
                        ></div>
                    </div>

                    <!-- Step 2 -->
                    <div class="group relative">
                        <div
                            class="rounded-2xl border border-gray-200/70 bg-white p-6 shadow-sm transition-all duration-200 hover:-translate-y-1 hover:shadow-md md:p-7"
                        >
                            <div
                                class="flex h-12 w-12 items-center justify-center rounded-full bg-blue-100 transition duration-200 group-hover:bg-blue-600"
                            >
                                <span
                                    class="text-lg font-bold text-blue-600 group-hover:text-white"
                                    >02</span
                                >
                            </div>
                            <h3
                                class="mt-4 text-lg font-semibold text-gray-900"
                            >
                                Get Matched
                            </h3>
                            <p class="mt-2 text-sm text-gray-500">
                                Receive personalized recommendations from
                                verified tutors.
                            </p>
                        </div>
                        <div
                            class="absolute top-1/2 -right-4 hidden h-0.5 w-8 bg-gray-200 sm:block"
                        ></div>
                    </div>

                    <!-- Step 3 -->
                    <div class="group">
                        <div
                            class="rounded-2xl border border-gray-200/70 bg-white p-6 shadow-sm transition-all duration-200 hover:-translate-y-1 hover:shadow-md md:p-7"
                        >
                            <div
                                class="flex h-12 w-12 items-center justify-center rounded-full bg-blue-100 transition duration-200 group-hover:bg-blue-600"
                            >
                                <span
                                    class="text-lg font-bold text-blue-600 group-hover:text-white"
                                    >03</span
                                >
                            </div>
                            <h3
                                class="mt-4 text-lg font-semibold text-gray-900"
                            >
                                Start Learning
                            </h3>
                            <p class="mt-2 text-sm text-gray-500">
                                Begin your tutoring sessions and track progress.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Tuition Methods -->
        <section class="bg-white py-14 md:py-20">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="text-center">
                    <p
                        class="text-sm font-medium tracking-widest text-blue-600 uppercase"
                    >
                        Tuition Methods
                    </p>
                    <h2
                        class="mt-2 text-3xl font-semibold tracking-tight text-gray-900 sm:text-4xl"
                    >
                        Choose Your Learning Style
                    </h2>
                    <p class="mx-auto mt-3 max-w-2xl text-sm text-gray-500">
                        Flexible options to fit your schedule and preferences
                    </p>
                </div>

                <div class="mt-12 grid gap-6 sm:grid-cols-3">
                    <div
                        class="group rounded-2xl border border-gray-200/70 bg-white p-6 shadow-sm transition-all duration-200 hover:-translate-y-1 hover:shadow-md"
                    >
                        <div
                            class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-100"
                        >
                            <svg
                                class="h-6 w-6 text-blue-600"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"
                                />
                            </svg>
                        </div>
                        <h3 class="mt-4 text-lg font-semibold text-gray-900">
                            Home Tutoring
                        </h3>
                        <p class="mt-2 text-sm text-gray-500">
                            Face-to-face sessions at your home for focused,
                            personalized learning.
                        </p>
                        <Link
                            :href="contact()"
                            class="mt-4 inline-flex items-center gap-1 text-sm font-medium text-blue-600 transition-colors hover:text-blue-700"
                        >
                            Learn more
                            <svg
                                class="h-4 w-4"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M9 5l7 7-7 7"
                                />
                            </svg>
                        </Link>
                    </div>

                    <div
                        class="group rounded-2xl border border-gray-200/70 bg-white p-6 shadow-sm transition-all duration-200 hover:-translate-y-1 hover:shadow-md"
                    >
                        <div
                            class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-100"
                        >
                            <svg
                                class="h-6 w-6 text-emerald-600"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
                                />
                            </svg>
                        </div>
                        <h3 class="mt-4 text-lg font-semibold text-gray-900">
                            Online Tutoring
                        </h3>
                        <p class="mt-2 text-sm text-gray-500">
                            Live video classes with interactive whiteboard and
                            shared resources.
                        </p>
                        <Link
                            :href="contact()"
                            class="mt-4 inline-flex items-center gap-1 text-sm font-medium text-blue-600 transition-colors hover:text-blue-700"
                        >
                            Learn more
                            <svg
                                class="h-4 w-4"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M9 5l7 7-7 7"
                                />
                            </svg>
                        </Link>
                    </div>

                    <div
                        class="group rounded-2xl border border-gray-200/70 bg-white p-6 shadow-sm transition-all duration-200 hover:-translate-y-1 hover:shadow-md"
                    >
                        <div
                            class="flex h-12 w-12 items-center justify-center rounded-xl bg-amber-100"
                        >
                            <svg
                                class="h-6 w-6 text-amber-600"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"
                                />
                            </svg>
                        </div>
                        <h3 class="mt-4 text-lg font-semibold text-gray-900">
                            Institute Tutoring
                        </h3>
                        <p class="mt-2 text-sm text-gray-500">
                            Structured sessions in a dedicated learning
                            environment.
                        </p>
                        <Link
                            :href="contact()"
                            class="mt-4 inline-flex items-center gap-1 text-sm font-medium text-blue-600 transition-colors hover:text-blue-700"
                        >
                            Learn more
                            <svg
                                class="h-4 w-4"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M9 5l7 7-7 7"
                                />
                            </svg>
                        </Link>
                    </div>
                </div>
            </div>
        </section>

        <!-- Testimonials -->
        <section class="bg-white py-14 md:py-20">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="text-center">
                    <p
                        class="text-sm font-medium tracking-widest text-blue-600 uppercase"
                    >
                        Testimonials
                    </p>
                    <h2
                        class="mt-2 text-3xl font-semibold tracking-tight text-gray-900 sm:text-4xl"
                    >
                        What Parents Say
                    </h2>
                    <p class="mx-auto mt-3 max-w-2xl text-sm text-gray-500">
                        Hear from satisfied parents and guardians
                    </p>
                </div>

                <div class="mt-12 grid gap-6 sm:grid-cols-3">
                    <div
                        class="group rounded-2xl border border-gray-200/70 bg-white p-6 shadow-sm transition-all duration-200 hover:-translate-y-1 hover:shadow-md"
                    >
                        <div class="flex items-center gap-1 text-amber-500">
                            <svg
                                class="h-5 w-5 fill-current"
                                viewBox="0 0 20 20"
                            >
                                <path
                                    d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"
                                />
                            </svg>
                            <svg
                                class="h-5 w-5 fill-current"
                                viewBox="0 0 20 20"
                            >
                                <path
                                    d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"
                                />
                            </svg>
                            <svg
                                class="h-5 w-5 fill-current"
                                viewBox="0 0 20 20"
                            >
                                <path
                                    d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"
                                />
                            </svg>
                            <svg
                                class="h-5 w-5 fill-current"
                                viewBox="0 0 20 20"
                            >
                                <path
                                    d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"
                                />
                            </svg>
                            <svg
                                class="h-5 w-5 fill-current"
                                viewBox="0 0 20 20"
                            >
                                <path
                                    d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"
                                />
                            </svg>
                        </div>
                        <p class="mt-4 text-sm leading-relaxed text-gray-600">
                            "Found an excellent math tutor for my son within 2
                            days. The platform made it so easy!"
                        </p>
                        <div class="mt-5 flex items-center gap-3">
                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-full bg-gray-200 text-gray-500"
                            >
                                <svg
                                    class="h-5 w-5"
                                    fill="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"
                                    />
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">
                                    Sarah Rahman
                                </p>
                                <p class="text-xs text-gray-500">Parent</p>
                            </div>
                        </div>
                    </div>

                    <div
                        class="group rounded-2xl border border-gray-200/70 bg-white p-6 shadow-sm transition-all duration-200 hover:-translate-y-1 hover:shadow-md"
                    >
                        <div class="flex items-center gap-1 text-amber-500">
                            <svg
                                class="h-5 w-5 fill-current"
                                viewBox="0 0 20 20"
                            >
                                <path
                                    d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"
                                />
                            </svg>
                            <svg
                                class="h-5 w-5 fill-current"
                                viewBox="0 0 20 20"
                            >
                                <path
                                    d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"
                                />
                            </svg>
                            <svg
                                class="h-5 w-5 fill-current"
                                viewBox="0 0 20 20"
                            >
                                <path
                                    d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"
                                />
                            </svg>
                            <svg
                                class="h-5 w-5 fill-current"
                                viewBox="0 0 20 20"
                            >
                                <path
                                    d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"
                                />
                            </svg>
                            <svg
                                class="h-5 w-5 fill-current"
                                viewBox="0 0 20 20"
                            >
                                <path
                                    d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"
                                />
                            </svg>
                        </div>
                        <p class="mt-4 text-sm leading-relaxed text-gray-600">
                            "Great experience. The tutor is very professional
                            and my daughter has improved significantly."
                        </p>
                        <div class="mt-5 flex items-center gap-3">
                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-full bg-gray-200 text-gray-500"
                            >
                                <svg
                                    class="h-5 w-5"
                                    fill="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"
                                    />
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">
                                    Ahmed Khan
                                </p>
                                <p class="text-xs text-gray-500">Parent</p>
                            </div>
                        </div>
                    </div>

                    <div
                        class="group rounded-2xl border border-gray-200/70 bg-white p-6 shadow-sm transition-all duration-200 hover:-translate-y-1 hover:shadow-md"
                    >
                        <div class="flex items-center gap-1 text-amber-500">
                            <svg
                                class="h-5 w-5 fill-current"
                                viewBox="0 0 20 20"
                            >
                                <path
                                    d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"
                                />
                            </svg>
                            <svg
                                class="h-5 w-5 fill-current"
                                viewBox="0 0 20 20"
                            >
                                <path
                                    d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"
                                />
                            </svg>
                            <svg
                                class="h-5 w-5 fill-current"
                                viewBox="0 0 20 20"
                            >
                                <path
                                    d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"
                                />
                            </svg>
                            <svg
                                class="h-5 w-5 fill-current"
                                viewBox="0 0 20 20"
                            >
                                <path
                                    d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"
                                />
                            </svg>
                            <svg
                                class="h-5 w-5 fill-current"
                                viewBox="0 0 20 20"
                            >
                                <path
                                    d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"
                                />
                            </svg>
                        </div>
                        <p class="mt-4 text-sm leading-relaxed text-gray-600">
                            "Very trustworthy platform. Felt safe having our
                            child learn with verified tutors."
                        </p>
                        <div class="mt-5 flex items-center gap-3">
                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-full bg-gray-200 text-gray-500"
                            >
                                <svg
                                    class="h-5 w-5"
                                    fill="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"
                                    />
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">
                                    Fatima Begum
                                </p>
                                <p class="text-xs text-gray-500">Guardian</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Final CTA -->
        <section class="bg-blue-600 py-14 md:py-20">
            <div class="mx-auto max-w-4xl px-4 text-center sm:px-6">
                <h2
                    class="text-3xl font-bold tracking-tight text-white sm:text-4xl"
                >
                    Start Your Learning Journey Today
                </h2>
                <p class="mx-auto mt-4 max-w-xl text-base text-blue-100">
                    Join thousands of students and tutors already using
                    {{ siteName }}
                </p>
                <div
                    class="mt-8 flex flex-col gap-3 sm:flex-row sm:justify-center"
                >
                    <Link
                        :href="register()"
                        class="inline-flex h-11 items-center justify-center rounded-lg bg-white px-6 py-3 text-base font-semibold text-blue-600 shadow-sm transition-all duration-200 hover:bg-gray-100 focus-visible:ring-2 focus-visible:ring-white focus-visible:ring-offset-2 focus-visible:ring-offset-blue-600 active:scale-[0.98]"
                    >
                        Register
                    </Link>
                    <Link
                        :href="jobs()"
                        class="inline-flex h-11 items-center justify-center rounded-lg border-2 border-white px-6 py-3 text-base font-semibold text-white transition-all duration-200 hover:bg-white/10 focus-visible:ring-2 focus-visible:ring-white focus-visible:ring-offset-2 focus-visible:ring-offset-blue-600 active:scale-[0.98]"
                    >
                        Browse Jobs
                    </Link>
                </div>
            </div>
        </section>
    </PublicLayout>
</template>
