<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    Briefcase,
    FileSignature,
    GraduationCap,
    Handshake,
} from 'lucide-vue-next';
import { register } from '@/routes';

const tutorSteps = [
    {
        step: '01',
        title: 'Create Your Profile',
        description:
            'Sign up and complete your profile with educational background, teaching experience, subjects, preferred classes, and location.',
        icon: FileSignature,
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
            'Once selected, confirm the tuition job details and finalize / ensure the class schedule with the guardian.',
        icon: Handshake,
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
    <section
        class="relative overflow-hidden bg-gradient-to-b from-[#eaf6ff] via-[#e1f0fc] to-[#eaf6ff] py-16 lg:py-24"
    >
        <!-- Ambient Radial Background Glow -->
        <div
            class="pointer-events-none absolute -top-24 left-1/2 -z-10 h-[400px] w-[700px] -translate-x-1/2 rounded-full bg-radial from-white/60 via-cyan-100/30 to-transparent blur-3xl"
        ></div>

        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <!-- Header Badge Pill -->
            <div class="mx-auto max-w-4xl text-center">
                <div
                    class="inline-block rounded-full border-2 border-[#38bdf8] bg-white px-8 py-2.5 shadow-2xs sm:px-12 sm:py-3"
                >
                    <h2
                        class="text-2xl font-black tracking-tight text-[#1c2346] sm:text-3xl lg:text-4xl"
                    >
                        How It Works For Tutors ?
                    </h2>
                </div>
                <p
                    class="mt-4 text-base font-semibold text-slate-600 sm:text-lg"
                >
                    Find your ideal tuition opportunities in just a few simple
                    steps.
                </p>
            </div>

            <!-- 4 Step Cards Grid -->
            <div
                class="mt-14 grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-4"
            >
                <div
                    v-for="item in tutorSteps"
                    :key="item.step"
                    class="group relative flex flex-col transition-all duration-300 hover:-translate-y-1"
                >
                    <!-- Element A: Circular Icon Badge Top Center -->
                    <div class="relative z-10 -mb-7 flex justify-center">
                        <div
                            class="flex h-16 w-16 items-center justify-center rounded-full border-2 border-[#38bdf8] bg-white text-[#38bdf8] shadow-md transition-transform duration-300 group-hover:scale-110 sm:h-20 sm:w-20"
                        >
                            <component
                                :is="item.icon"
                                class="h-8 w-8 stroke-[2.2] text-[#38bdf8]"
                            />
                        </div>
                    </div>

                    <!-- Element B: Dark Navy Header Capsule Pill -->
                    <div
                        class="relative z-0 flex min-h-[64px] items-center justify-center rounded-3xl bg-[#223180] px-4 pt-7 pb-3.5 text-center text-white shadow-md"
                    >
                        <h3
                            class="text-base font-black tracking-tight sm:text-lg"
                        >
                            {{ item.title }}
                        </h3>
                    </div>

                    <!-- Element C: White Card Box with Cyan Outline -->
                    <div
                        class="mt-3 flex min-h-[200px] flex-1 items-center justify-center rounded-3xl border-4 border-[#58b6ec] bg-white p-6 text-center shadow-sm backdrop-blur-xs"
                    >
                        <p
                            class="text-xs leading-relaxed font-semibold text-slate-700 sm:text-sm"
                        >
                            {{ item.description }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- CTA Button -->
            <div class="mt-16 text-center">
                <Link
                    :href="register()"
                    class="inline-flex h-12 items-center justify-center rounded-full bg-[#223180] px-9 text-base font-extrabold text-white shadow-lg shadow-indigo-900/20 transition-all duration-300 hover:scale-105 hover:bg-[#1b2668] hover:shadow-xl"
                >
                    Become a Tutor Now
                </Link>
            </div>
        </div>
    </section>
</template>
