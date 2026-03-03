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

<template>
    <Head title="Welcome" />

    <PublicLayout>
        <!-- Hero Section -->
        <section class="relative overflow-hidden bg-white py-14 md:py-20">
            <div
                class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_top,_rgba(37,99,235,0.10),_transparent_55%)]"
            ></div>

            <div class="relative mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                <div class="mx-auto max-w-3xl text-center">
                    <p
                        class="inline-flex items-center rounded-full border border-blue-100 bg-blue-50 px-3 py-1 text-xs font-medium tracking-[0.12em] text-blue-700 uppercase"
                    >
                        Verified tutors. Professional hiring.
                    </p>

                    <h1
                        class="mt-5 text-4xl font-semibold tracking-tight text-slate-900 sm:text-5xl md:text-6xl"
                    >
                        Find the Right
                        <span class="text-blue-600">Tutor</span> with
                        Confidence
                    </h1>

                    <p
                        class="mx-auto mt-4 max-w-2xl text-base leading-relaxed text-slate-600 sm:text-lg"
                    >
                        {{ siteName }} connects families with trusted tutors
                        through a simple and transparent process.
                    </p>

                    <div
                        class="mt-8 flex flex-col gap-3 sm:flex-row sm:justify-center"
                    >
                        <Link
                            href="/tutors"
                            class="inline-flex h-11 items-center justify-center rounded-lg bg-blue-600 px-6 text-sm font-semibold text-white transition hover:bg-blue-700 focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 active:scale-[0.98]"
                        >
                            Find a Tutor
                        </Link>
                        <Link
                            :href="jobs()"
                            class="inline-flex h-11 items-center justify-center rounded-lg border border-slate-300 bg-white px-6 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 active:scale-[0.98]"
                        >
                            Post a Job
                        </Link>
                    </div>

                    <div
                        class="mt-10 grid gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:grid-cols-3"
                    >
                        <div class="rounded-lg bg-slate-50 p-4">
                            <p class="text-2xl font-semibold text-slate-900">
                                12k+
                            </p>
                            <p class="mt-1 text-xs text-slate-500 uppercase">
                                Active tutors
                            </p>
                        </div>
                        <div class="rounded-lg bg-slate-50 p-4">
                            <p class="text-2xl font-semibold text-slate-900">
                                3k+
                            </p>
                            <p class="mt-1 text-xs text-slate-500 uppercase">
                                Families served
                            </p>
                        </div>
                        <div class="rounded-lg bg-slate-50 p-4">
                            <p class="text-2xl font-semibold text-slate-900">
                                4.7/5
                            </p>
                            <p class="mt-1 text-xs text-slate-500 uppercase">
                                Average rating
                            </p>
                        </div>
                    </div>

                    <Link
                        :href="contact()"
                        class="mt-4 inline-flex text-sm font-medium text-slate-600 transition hover:text-slate-900"
                    >
                        Need help deciding? Contact us.
                    </Link>
                </div>
            </div>
        </section>

        <!-- How It Works (Premium / Minimal) -->
<section class="relative isolate overflow-hidden bg-white py-16 md:py-24">
    <!-- subtle backdrop -->
    <div class="pointer-events-none absolute inset-0 -z-10">
        <div
            class="absolute left-1/2 top-[-220px] h-[520px] w-[520px] -translate-x-1/2 rounded-full bg-gradient-to-b from-blue-200/35 via-indigo-200/15 to-transparent blur-3xl"
        ></div>
        <div class="absolute inset-0 bg-gradient-to-b from-white via-white to-slate-50/40"></div>
    </div>

    <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mx-auto max-w-3xl text-center">
            <div class="flex justify-center">
                <span
                    class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white/70 px-3 py-1 text-[11px] font-semibold tracking-[0.18em] text-slate-700 backdrop-blur"
                >
                    <span class="h-1.5 w-1.5 rounded-full bg-blue-600"></span>
                    HOW IT WORKS
                </span>
            </div>

            <h2 class="mt-6 text-3xl font-semibold tracking-tight text-slate-900 sm:text-4xl">
                Simple steps to find your tutor
            </h2>
            <p class="mx-auto mt-3 max-w-2xl text-base leading-relaxed text-slate-600">
                Get started in minutes with a clear, guided process.
            </p>
        </div>

        <!-- Steps -->
        <div class="relative mt-12">
            <!-- connector (desktop) -->
            <div
                class="pointer-events-none absolute left-1/2 top-[44px] hidden h-px w-[calc(100%-6rem)] -translate-x-1/2 bg-gradient-to-r from-transparent via-slate-200 to-transparent md:block"
            ></div>

            <div class="grid gap-4 md:grid-cols-3 md:gap-6">
                <!-- Step 1 -->
                <div class="group relative">
                    <div
                        class="h-full rounded-2xl border border-slate-200 bg-white/70 p-6 shadow-sm backdrop-blur transition hover:-translate-y-0.5 hover:bg-white hover:shadow-md md:p-7"
                    >
                        <div class="flex items-center gap-3">
                            <div
                                class="flex h-11 w-11 items-center justify-center rounded-xl bg-slate-900 text-white shadow-sm ring-1 ring-slate-900/10 transition group-hover:bg-slate-800"
                            >
                                <span class="text-sm font-semibold tracking-wide">01</span>
                            </div>
                            <div class="h-px flex-1 bg-slate-200 md:hidden"></div>
                        </div>

                        <h3 class="mt-5 text-lg font-semibold text-slate-900">
                            Post requirement
                        </h3>
                        <p class="mt-2 text-sm leading-relaxed text-slate-600">
                            Share subject, level, location/online preference, and your schedule.
                        </p>

                        <div class="mt-5 flex items-center gap-2 text-xs font-medium text-slate-500">
                            <span class="inline-flex h-5 items-center rounded-full bg-slate-100 px-2">2 min</span>
                            <span class="inline-flex h-5 items-center rounded-full bg-slate-100 px-2">Clear brief</span>
                        </div>
                    </div>
                </div>

                <!-- Step 2 -->
                <div class="group relative">
                    <div
                        class="h-full rounded-2xl border border-slate-200 bg-white/70 p-6 shadow-sm backdrop-blur transition hover:-translate-y-0.5 hover:bg-white hover:shadow-md md:p-7"
                    >
                        <div class="flex items-center gap-3">
                            <div
                                class="flex h-11 w-11 items-center justify-center rounded-xl bg-white text-slate-900 shadow-sm ring-1 ring-slate-200 transition group-hover:ring-slate-300"
                            >
                                <span class="text-sm font-semibold tracking-wide">02</span>
                            </div>
                            <div class="h-px flex-1 bg-slate-200 md:hidden"></div>
                        </div>

                        <h3 class="mt-5 text-lg font-semibold text-slate-900">
                            Get matched
                        </h3>
                        <p class="mt-2 text-sm leading-relaxed text-slate-600">
                            Receive curated tutor options based on your needs and availability.
                        </p>

                        <div class="mt-5 flex items-center gap-2 text-xs font-medium text-slate-500">
                            <span class="inline-flex h-5 items-center rounded-full bg-slate-100 px-2">Verified</span>
                            <span class="inline-flex h-5 items-center rounded-full bg-slate-100 px-2">Shortlist</span>
                        </div>
                    </div>
                </div>

                <!-- Step 3 -->
                <div class="group relative">
                    <div
                        class="h-full rounded-2xl border border-slate-200 bg-white/70 p-6 shadow-sm backdrop-blur transition hover:-translate-y-0.5 hover:bg-white hover:shadow-md md:p-7"
                    >
                        <div class="flex items-center gap-3">
                            <div
                                class="flex h-11 w-11 items-center justify-center rounded-xl bg-white text-slate-900 shadow-sm ring-1 ring-slate-200 transition group-hover:ring-slate-300"
                            >
                                <span class="text-sm font-semibold tracking-wide">03</span>
                            </div>
                            <div class="h-px flex-1 bg-slate-200 md:hidden"></div>
                        </div>

                        <h3 class="mt-5 text-lg font-semibold text-slate-900">
                            Start learning
                        </h3>
                        <p class="mt-2 text-sm leading-relaxed text-slate-600">
                            Confirm the tutor, begin sessions, and track progress over time.
                        </p>

                        <div class="mt-5 flex items-center gap-2 text-xs font-medium text-slate-500">
                            <span class="inline-flex h-5 items-center rounded-full bg-slate-100 px-2">Secure</span>
                            <span class="inline-flex h-5 items-center rounded-full bg-slate-100 px-2">Ongoing support</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Optional footer micro-copy -->
        <div class="mt-8 text-center">
            <p class="text-sm text-slate-600">
                Transparent matching • No clutter • Mobile-first
            </p>
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
