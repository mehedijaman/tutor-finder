<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    Briefcase,
    CheckCircle,
    GraduationCap,
    UserPlus,
} from 'lucide-vue-next';
import { register } from '@/routes';

const tutorSteps = [
    {
        step: '01',
        title: 'Create Your Profile',
        description:
            'Sign up and complete your profile with educational background, teaching experience, subjects, preferred classes, and location.',
        icon: UserPlus,
    },
    {
        step: '02',
        title: 'Browse Tuition Jobs',
        description:
            'Explore available tuition jobs opportunities based on your preferred subjects, area, salary range, and tutoring style.',
        icon: Briefcase,
    },
    {
        step: '03',
        title: 'Confirm the Tuition Jobs',
        description:
            'Once selected, confirm the tuition job details and finalize the class schedule with the guardian.',
        icon: CheckCircle,
    },
    {
        step: '04',
        title: 'Start Teaching',
        description:
            'Begin tutoring sessions online or offline and help students achieve academic success.',
        icon: GraduationCap,
    },
];
</script>

<template>
    <section class="bg-white py-16 lg:py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <!-- Section Header -->
            <div class="mx-auto max-w-3xl text-center">
                <span
                    class="inline-block rounded-full bg-cyan-50 px-4 py-1.5 text-xs font-extrabold text-[#0eb0e6] ring-1 ring-[#0eb0e6]/30"
                >
                    FOR TUTORS
                </span>
                <h2
                    class="mt-3 text-3xl font-extrabold tracking-tight text-[#1b2880] sm:text-4xl"
                >
                    How It Works For Tutors?
                </h2>
                <p class="mt-4 text-base text-slate-600 sm:text-lg">
                    Find your ideal tuition opportunities in just a few simple
                    steps.
                </p>
            </div>

            <!-- Vertical Entrance Timeline ("upor theke step by step show hobe", "icone gula move hove") -->
            <div
                class="mt-16 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4"
            >
                <div
                    v-for="item in tutorSteps"
                    :key="item.step"
                    class="group relative flex flex-col justify-between rounded-3xl border border-slate-200/90 bg-white p-7 shadow-md transition-all duration-300 hover:-translate-y-2 hover:border-[#0eb0e6] hover:shadow-xl"
                >
                    <div>
                        <!-- Icon Circle with Floating Animation -->
                        <div class="flex items-center justify-between">
                            <div
                                class="flex h-14 w-14 items-center justify-center rounded-2xl bg-[#1b2880] text-white shadow-lg transition-transform duration-300 group-hover:scale-110 group-hover:bg-[#0eb0e6]"
                            >
                                <component :is="item.icon" class="h-7 w-7" />
                            </div>
                            <span
                                class="text-3xl font-black text-slate-200 transition-colors group-hover:text-[#0eb0e6]"
                            >
                                {{ item.step }}
                            </span>
                        </div>

                        <!-- Step Content -->
                        <h3
                            class="mt-6 text-xl font-bold text-slate-900 transition-colors group-hover:text-[#1b2880]"
                        >
                            {{ item.title }}
                        </h3>
                        <p class="mt-3 text-sm leading-relaxed text-slate-600">
                            {{ item.description }}
                        </p>
                    </div>

                    <div
                        class="mt-6 border-t border-slate-100 pt-4 text-xs font-bold text-[#0eb0e6]"
                    >
                        Step {{ item.step }} of 04
                    </div>
                </div>
            </div>

            <!-- CTA Button -->
            <div class="mt-16 text-center">
                <Link
                    :href="register()"
                    class="inline-flex h-12 items-center justify-center rounded-full bg-[#1b2880] px-8 text-base font-bold text-white shadow-lg transition-all hover:scale-105 hover:bg-[#15206b]"
                >
                    Become a Tutor Now
                </Link>
            </div>
        </div>
    </section>
</template>
