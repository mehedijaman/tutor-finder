<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    BookOpen,
    Brain,
    Briefcase,
    Building2,
    Calculator,
    Compass,
    GraduationCap,
    Globe,
    HeartHandshake,
    Languages,
    Layers,
    Lightbulb,
    Palette,
    PenTool,
    School,
    Sparkles,
    Trophy,
    UserCheck,
    Users,
} from 'lucide-vue-next';
import { jobs } from '@/routes';

type CategoryItem = {
    id: number;
    title: string;
    icon: unknown;
    color: string;
    slug: string;
};

const categories: CategoryItem[] = [
    {
        id: 1,
        title: 'Study Support',
        icon: BookOpen,
        color: 'bg-blue-500',
        slug: 'study-support',
    },
    {
        id: 2,
        title: 'Academic Curriculums',
        icon: GraduationCap,
        color: 'bg-cyan-500',
        slug: 'academic-curriculums',
    },
    {
        id: 3,
        title: 'Bangla Medium',
        icon: School,
        color: 'bg-[#1b2880]',
        slug: 'bangla-medium',
    },
    {
        id: 4,
        title: 'Religious Studies',
        icon: Compass,
        color: 'bg-emerald-600',
        slug: 'religious-studies',
    },
    {
        id: 5,
        title: 'Special Child',
        icon: HeartHandshake,
        color: 'bg-rose-500',
        slug: 'special-child',
    },
    {
        id: 6,
        title: 'Job Preparation',
        icon: Briefcase,
        color: 'bg-amber-600',
        slug: 'job-preparation',
    },
    {
        id: 7,
        title: 'Madrasah Medium',
        icon: Building2,
        color: 'bg-teal-600',
        slug: 'madrasah-medium',
    },
    {
        id: 8,
        title: 'Arabic Language',
        icon: Languages,
        color: 'bg-indigo-600',
        slug: 'arabic-language',
    },
    {
        id: 9,
        title: 'Special Skill',
        icon: Sparkles,
        color: 'bg-purple-600',
        slug: 'special-skill',
    },
    {
        id: 10,
        title: 'University Help',
        icon: Trophy,
        color: 'bg-blue-600',
        slug: 'university-help',
    },
    {
        id: 11,
        title: 'English Medium',
        icon: Globe,
        color: 'bg-sky-500',
        slug: 'english-medium',
    },
    {
        id: 12,
        title: 'Language Learning',
        icon: Languages,
        color: 'bg-orange-500',
        slug: 'language-learning',
    },
    {
        id: 13,
        title: 'Professional Skill',
        icon: Brain,
        color: 'bg-violet-600',
        slug: 'professional-skill',
    },
    {
        id: 14,
        title: 'Admission Preparation',
        icon: UserCheck,
        color: 'bg-emerald-500',
        slug: 'admission-preparation',
    },
    {
        id: 15,
        title: 'English Version',
        icon: Layers,
        color: 'bg-cyan-600',
        slug: 'english-version',
    },
    {
        id: 16,
        title: 'Arts & Craft',
        icon: Palette,
        color: 'bg-pink-500',
        slug: 'arts-craft',
    },
    {
        id: 17,
        title: 'Skills & Development',
        icon: Lightbulb,
        color: 'bg-yellow-500',
        slug: 'skills-development',
    },
    {
        id: 18,
        title: "Int'l Test Preparation",
        icon: Calculator,
        color: 'bg-blue-700',
        slug: 'intl-test-preparation',
    },
    {
        id: 19,
        title: 'Drawing',
        icon: PenTool,
        color: 'bg-fuchsia-500',
        slug: 'drawing',
    },
    {
        id: 20,
        title: 'Creative & Language',
        icon: Users,
        color: 'bg-teal-500',
        slug: 'creative-language',
    },
];
</script>

<template>
    <section class="bg-white py-16 lg:py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mx-auto max-w-3xl text-center">
                <span
                    class="inline-block rounded-full bg-cyan-50 px-4 py-1.5 text-xs font-extrabold text-[#0eb0e6] ring-1 ring-[#0eb0e6]/30"
                >
                    EXPLORE CATEGORIES
                </span>
                <h2
                    class="mt-3 text-3xl font-extrabold tracking-tight text-[#1b2880] sm:text-4xl"
                >
                    Our Learning Categories
                </h2>
                <p class="mt-4 text-base text-slate-600 sm:text-lg">
                    Explore currently active tuition categories and pick what
                    matches your learning goals.
                </p>
            </div>

            <!-- 20 Categories Grid with Step-by-Step Staggered Entrance -->
            <div
                class="mt-12 grid grid-cols-2 gap-3 sm:grid-cols-3 sm:gap-4 md:grid-cols-4 lg:grid-cols-5"
            >
                <Link
                    v-for="(cat, idx) in categories"
                    :key="cat.id"
                    :href="jobs({ query: { category: cat.slug } })"
                    class="group relative flex flex-col items-center justify-center rounded-2xl border border-slate-200/90 bg-white p-5 text-center shadow-xs transition-all duration-300 hover:-translate-y-1 hover:border-[#0eb0e6] hover:shadow-lg hover:shadow-[#0eb0e6]/10"
                >
                    <div
                        :class="[
                            'mb-3 flex h-12 w-12 items-center justify-center rounded-xl text-white shadow-sm transition-transform duration-300 group-hover:scale-110',
                            cat.color,
                        ]"
                    >
                        <component :is="cat.icon" class="h-6 w-6" />
                    </div>
                    <span
                        class="text-xs font-extrabold text-slate-900 transition-colors group-hover:text-[#0096c7] sm:text-sm"
                    >
                        {{ cat.title }}
                    </span>
                    <!-- Step Badge Pill -->
                    <span
                        class="mt-2 rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-bold text-slate-500 group-hover:bg-cyan-50 group-hover:text-[#0eb0e6]"
                    >
                        Step {{ String(idx + 1).padStart(2, '0') }}
                    </span>
                </Link>
            </div>
        </div>
    </section>
</template>
