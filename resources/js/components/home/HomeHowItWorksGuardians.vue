<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    CalendarCheck,
    CheckCircle2,
    FileEdit,
    Search,
    UserCheck,
} from 'lucide-vue-next';
import { jobs } from '@/routes';

const steps = [
    {
        num: '01',
        title: 'Post Your Tutor Requirement',
        description:
            'Share your needs—subject, class, location, schedule, and budget.',
        icon: FileEdit,
        align: 'left',
    },
    {
        num: '02',
        title: 'Get Tutor Matches',
        description:
            'Receive profiles & get a free demo class qualified tutors based on your preferences.',
        icon: Search,
        align: 'right',
    },
    {
        num: '03',
        title: 'Review & Select',
        description:
            'Compare tutors, check experience, ratings, and choose the best fit.',
        icon: UserCheck,
        align: 'left',
    },
    {
        num: '04',
        title: 'Confirm & Start Learning',
        description:
            'Finalize your tutor and begin classes at your convenience.',
        icon: CalendarCheck,
        align: 'right',
    },
];
</script>

<template>
    <section class="relative overflow-hidden bg-white py-16 lg:py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mx-auto max-w-3xl text-center">
                <span
                    class="inline-block rounded-full bg-cyan-50 px-4 py-1.5 text-xs font-extrabold text-[#0eb0e6] ring-1 ring-[#0eb0e6]/30"
                >
                    FOR GUARDIANS &amp; STUDENTS
                </span>
                <h2
                    class="mt-3 text-3xl font-extrabold tracking-tight text-[#1b2880] sm:text-4xl"
                >
                    How It Works For Guardians &amp; Students?
                </h2>
                <p class="mt-4 text-base text-slate-600 sm:text-lg">
                    Finding the right tutor is simple, fast, and secure.
                </p>
            </div>

            <!-- Alternating Left-Right Timeline ("Dane Bame Show korbe") -->
            <div class="relative mt-16 lg:mt-20">
                <!-- Center Line for Desktop -->
                <div
                    class="absolute top-0 bottom-0 left-1/2 hidden w-1 -translate-x-1/2 bg-gradient-to-b from-[#0eb0e6] via-[#1b2880] to-[#0eb0e6] opacity-30 lg:block"
                ></div>

                <div class="space-y-12 lg:space-y-16">
                    <div
                        v-for="(item, idx) in steps"
                        :key="item.num"
                        :class="[
                            'flex flex-col items-center gap-8 lg:flex-row lg:gap-16',
                            item.align === 'right' ? 'lg:flex-row-reverse' : '',
                        ]"
                    >
                        <!-- Content Box -->
                        <div class="w-full lg:w-1/2">
                            <div
                                :class="[
                                    'group relative rounded-3xl border border-slate-200/90 bg-white p-8 shadow-md transition-all duration-300 hover:-translate-y-1 hover:border-[#0eb0e6] hover:shadow-xl',
                                    item.align === 'right'
                                        ? 'lg:ml-auto lg:text-left'
                                        : 'lg:mr-auto lg:text-left',
                                ]"
                            >
                                <div class="flex items-center gap-4">
                                    <span
                                        class="flex h-12 w-12 items-center justify-center rounded-2xl bg-[#0eb0e6] text-lg font-black text-white shadow-md transition-transform duration-300 group-hover:scale-110"
                                    >
                                        {{ item.num }}
                                    </span>
                                    <div
                                        class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-100 text-[#1b2880]"
                                    >
                                        <component
                                            :is="item.icon"
                                            class="h-5 w-5"
                                        />
                                    </div>
                                </div>

                                <h3
                                    class="mt-5 text-xl font-bold text-[#1b2880]"
                                >
                                    {{ item.title }}
                                </h3>
                                <p
                                    class="mt-3 text-sm leading-relaxed text-slate-600"
                                >
                                    {{ item.description }}
                                </p>
                            </div>
                        </div>

                        <!-- Timeline Center Node Badge -->
                        <div
                            class="relative z-10 hidden h-12 w-12 shrink-0 items-center justify-center rounded-full border-4 border-white bg-[#1b2880] text-white shadow-lg lg:flex"
                        >
                            <CheckCircle2 class="h-6 w-6 text-[#0eb0e6]" />
                        </div>

                        <!-- Empty Balance Column -->
                        <div class="hidden w-1/2 lg:block"></div>
                    </div>
                </div>
            </div>

            <!-- CTA Button -->
            <div class="mt-16 text-center">
                <Link
                    :href="jobs()"
                    class="inline-flex h-12 items-center justify-center rounded-full bg-[#0eb0e6] px-8 text-base font-bold text-white shadow-lg shadow-[#0eb0e6]/25 transition-all hover:scale-105 hover:bg-[#0c9bd0]"
                >
                    Post Tutor Requirement Now
                </Link>
            </div>
        </div>
    </section>
</template>
