<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    ArrowRight,
    Award,
    BookCheck,
    Globe,
    Home,
    Languages,
    Sparkles,
    UserCheck,
    Users,
} from 'lucide-vue-next';
import { jobs } from '@/routes';

type MethodItem = {
    step: string;
    title: string;
    description: string;
    icon: unknown;
    slug: string;
};

const methods: MethodItem[] = [
    {
        step: '01',
        title: 'Home Tutoring',
        description:
            'A dedicated tutor comes to your home for customized, face-to-face learning.',
        icon: Home,
        slug: 'home-tutoring',
    },
    {
        step: '02',
        title: 'Group Tutoring',
        description:
            'Study with a small group of students in an interactive learning environment.',
        icon: Users,
        slug: 'group-tutoring',
    },
    {
        step: '03',
        title: 'Online Tutoring',
        description:
            'Learn from anywhere with live, interactive classes led by expert tutors.',
        icon: Globe,
        slug: 'online-tutoring',
    },
    {
        step: '04',
        title: 'Language Tutoring',
        description:
            'Improve your speaking, writing, reading, and communication skills with expert support.',
        icon: Languages,
        slug: 'language-tutoring',
    },
    {
        step: '05',
        title: 'Shadow Tutoring',
        description:
            'Personalized support alongside school or college learning to reinforce understanding and keep you on track.',
        icon: UserCheck,
        slug: 'shadow-tutoring',
    },
    {
        step: '06',
        title: 'Skill-Based Tutoring',
        description:
            'Learn practical, job-ready, and professional skills for personal and career growth.',
        icon: Sparkles,
        slug: 'skill-based-tutoring',
    },
    {
        step: '07',
        title: 'Exam Crash Program',
        description:
            'Short-term, intensive preparation designed to boost your exam performance quickly.',
        icon: Award,
        slug: 'exam-crash-program',
    },
    {
        step: '08',
        title: 'Exam-Focused Tutoring',
        description:
            'Targeted preparation designed to help you excel in specific exams with structured guidance.',
        icon: BookCheck,
        slug: 'exam-focused-tutoring',
    },
];
</script>

<template>
    <section class="bg-slate-50/70 py-16 lg:py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <!-- Section Header -->
            <div class="mx-auto max-w-3xl text-center">
                <span
                    class="inline-block rounded-full bg-cyan-50 px-4 py-1.5 text-xs font-extrabold text-[#0eb0e6] ring-1 ring-[#0eb0e6]/30"
                >
                    TUTORING METHODS
                </span>
                <h2
                    class="mt-3 text-3xl font-extrabold tracking-tight text-[#1b2880] sm:text-4xl"
                >
                    Choose Your Tutoring Method
                </h2>
                <p class="mt-4 text-base text-slate-600 sm:text-lg">
                    Choose the tutoring method that best fits your learning
                    needs.
                </p>
            </div>

            <!-- 8 Method Cards Grid with Numbered Step Badges 01 - 08 -->
            <div
                class="mt-12 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4"
            >
                <div
                    v-for="item in methods"
                    :key="item.step"
                    class="group relative flex flex-col justify-between rounded-3xl border border-slate-200/90 bg-white p-7 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:border-[#0eb0e6] hover:shadow-xl hover:shadow-cyan-900/5"
                >
                    <div>
                        <!-- Top Header with Step Badge & Icon -->
                        <div class="flex items-center justify-between">
                            <span
                                class="flex h-10 w-10 items-center justify-center rounded-2xl bg-[#1b2880] text-sm font-black text-white shadow-xs transition-colors group-hover:bg-[#0eb0e6]"
                            >
                                {{ item.step }}
                            </span>
                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-xl bg-cyan-50 text-[#0eb0e6]"
                            >
                                <component :is="item.icon" class="h-5 w-5" />
                            </div>
                        </div>

                        <!-- Content -->
                        <h3
                            class="mt-6 text-xl font-bold text-slate-900 transition-colors group-hover:text-[#1b2880]"
                        >
                            {{ item.title }}
                        </h3>
                        <p class="mt-3 text-sm leading-relaxed text-slate-600">
                            {{ item.description }}
                        </p>
                    </div>

                    <div class="mt-6 border-t border-slate-100 pt-4">
                        <Link
                            :href="jobs({ query: { method: item.slug } })"
                            class="inline-flex items-center gap-1.5 text-xs font-extrabold text-[#0096c7] transition-colors group-hover:text-[#1b2880]"
                        >
                            <span>Explore Method</span>
                            <ArrowRight
                                class="h-3.5 w-3.5 transition-transform group-hover:translate-x-1"
                            />
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
