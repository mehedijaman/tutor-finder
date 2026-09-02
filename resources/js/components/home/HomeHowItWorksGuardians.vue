<script setup lang="ts">
import { CalendarCheck, CheckSquare, Search, UserCheck } from 'lucide-vue-next';

const steps = [
    {
        num: '01',
        title: 'Post Your Tutor Requirement',
        description:
            'Share your needs—subject, class, location, schedule, and budget.',
        icon: CheckSquare,
        offsetClass: 'ml-0',
    },
    {
        num: '02',
        title: 'Get Tutor Matches',
        description:
            'Receive profiles & get a free demo class qualified tutors based on your preferences.',
        icon: Search,
        offsetClass: 'ml-2 sm:ml-16',
    },
    {
        num: '03',
        title: 'Review & Select',
        description:
            'Compare tutors, check experience, ratings, and choose the best fit.',
        icon: UserCheck,
        offsetClass: 'ml-0',
    },
    {
        num: '04',
        title: 'Confirm & Start Learning',
        description:
            'Finalize / Ensure your tutor and begin classes at your convenience.',
        icon: CalendarCheck,
        offsetClass: 'ml-2 sm:ml-16',
    },
];
</script>

<template>
    <section class="relative overflow-hidden bg-white py-16 lg:py-24">
        <!-- Dot Grid Pattern Overlay (Top-Left & Bottom-Right) -->
        <div class="pointer-events-none absolute top-6 left-6 -z-10 opacity-20">
            <svg width="120" height="120" fill="none">
                <pattern
                    id="dot-grid-1"
                    x="0"
                    y="0"
                    width="16"
                    height="16"
                    patternUnits="userSpaceOnUse"
                >
                    <circle cx="2" cy="2" r="2" fill="#0eb0e6" />
                </pattern>
                <rect width="120" height="120" fill="url(#dot-grid-1)" />
            </svg>
        </div>

        <div
            class="pointer-events-none absolute right-6 bottom-6 -z-10 opacity-20"
        >
            <svg width="120" height="120" fill="none">
                <pattern
                    id="dot-grid-2"
                    x="0"
                    y="0"
                    width="16"
                    height="16"
                    patternUnits="userSpaceOnUse"
                >
                    <circle cx="2" cy="2" r="2" fill="#1c2346" />
                </pattern>
                <rect width="120" height="120" fill="url(#dot-grid-2)" />
            </svg>
        </div>

        <!-- Geometric Sketch Angle Lines -->
        <div
            class="pointer-events-none absolute inset-y-0 left-0 -z-10 hidden w-1/3 opacity-15 lg:block"
        >
            <svg
                class="h-full w-full"
                viewBox="0 0 400 800"
                preserveAspectRatio="none"
            >
                <line
                    x1="0"
                    y1="300"
                    x2="350"
                    y2="500"
                    stroke="#1c2346"
                    stroke-width="2"
                />
            </svg>
        </div>
        <div
            class="pointer-events-none absolute inset-y-0 right-0 -z-10 hidden w-1/3 opacity-15 lg:block"
        >
            <svg
                class="h-full w-full"
                viewBox="0 0 400 800"
                preserveAspectRatio="none"
            >
                <line
                    x1="400"
                    y1="200"
                    x2="50"
                    y2="550"
                    stroke="#1c2346"
                    stroke-width="2"
                />
            </svg>
        </div>

        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <!-- Header Badge Pill -->
            <div class="mx-auto max-w-4xl text-center">
                <div
                    class="inline-block rounded-full border-2 border-[#38bdf8] bg-white px-8 py-2.5 shadow-2xs sm:px-12 sm:py-3"
                >
                    <h2
                        class="text-xl font-black tracking-tight text-[#1c2346] sm:text-3xl lg:text-4xl"
                    >
                        How It Works For Guardians &amp; Students ?
                    </h2>
                </div>
                <p
                    class="mt-4 text-base font-semibold text-slate-600 sm:text-lg"
                >
                    Finding the right tutor is simple, fast, and secure.
                </p>
            </div>

            <!-- 2 Column Layout (Left: Staggered Navy Cards, Right: Illustration) -->
            <div
                class="mt-12 grid grid-cols-1 items-center gap-12 lg:grid-cols-12 lg:gap-8"
            >
                <!-- Left Column: 4 Staggered Navy Cards -->
                <div class="flex flex-col gap-5 lg:col-span-7">
                    <div
                        v-for="item in steps"
                        :key="item.num"
                        :class="[
                            'group relative flex w-full max-w-xl items-center gap-4 rounded-3xl border-2 border-[#1c2346] bg-white p-4 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-lg',
                            item.offsetClass,
                        ]"
                    >
                        <!-- Icon Circle Badge -->
                        <div
                            class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-cyan-50 text-[#38bdf8] shadow-xs ring-1 ring-[#38bdf8]/30"
                        >
                            <component
                                :is="item.icon"
                                class="h-7 w-7 stroke-[2.2]"
                            />
                        </div>

                        <!-- Card Content -->
                        <div class="flex flex-col justify-center">
                            <h3
                                class="text-lg font-black text-[#38bdf8] sm:text-xl"
                            >
                                {{ item.title }}
                            </h3>
                            <p
                                class="mt-1 text-xs leading-relaxed font-semibold text-[#0284c7] sm:text-sm"
                            >
                                {{ item.description }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Section Illustration Image -->
                <div class="flex justify-center lg:col-span-5">
                    <div class="relative w-full max-w-lg">
                        <img
                            src="/images/how-it-works-guardians.png"
                            alt="How It Works For Guardians &amp; Students"
                            class="h-auto w-full object-contain drop-shadow-xl select-none"
                        />
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
