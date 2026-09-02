<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { GraduationCap, Lightbulb, Palette, School } from 'lucide-vue-next';
import { jobs } from '@/routes';

type SubCategory = {
    title: string;
    slug: string;
    alignClass?: string;
};

type CategoryGroup = {
    title: string;
    icon: unknown;
    items: SubCategory[];
};

const categoryGroups: CategoryGroup[] = [
    {
        title: 'Academic Curriculums',
        icon: School,
        items: [
            {
                title: 'Bangla Medium',
                slug: 'bangla-medium',
                alignClass: 'self-center sm:self-start sm:ml-0',
            },
            {
                title: 'English Version',
                slug: 'english-version',
                alignClass: 'self-center sm:self-end sm:ml-6 lg:ml-8',
            },
            {
                title: 'English Medium',
                slug: 'english-medium',
                alignClass: 'self-center sm:self-start sm:ml-0',
            },
            {
                title: 'Madrasah Medium',
                slug: 'madrasah-medium',
                alignClass: 'self-center sm:self-end sm:ml-8 lg:ml-10',
            },
        ],
    },
    {
        title: 'Study Support',
        icon: GraduationCap,
        items: [
            {
                title: 'Admission Preparation',
                slug: 'admission-preparation',
                alignClass: 'self-center sm:self-start sm:ml-0',
            },
            {
                title: 'University Help',
                slug: 'university-help',
                alignClass: 'self-center sm:self-end sm:ml-8 lg:ml-12',
            },
            {
                title: 'Job Preparation',
                slug: 'job-preparation',
                alignClass: 'self-center sm:self-start sm:ml-2',
            },
            {
                title: "Int'l Test Preparation",
                slug: 'intl-test-preparation',
                alignClass: 'self-center sm:self-end sm:ml-6 lg:ml-10',
            },
        ],
    },
    {
        title: 'Skills & Development',
        icon: Lightbulb,
        items: [
            {
                title: 'Professional Skill',
                slug: 'professional-skill',
                alignClass: 'self-center sm:self-start sm:ml-0',
            },
            {
                title: 'Special Skill',
                slug: 'special-skill',
                alignClass: 'self-center sm:self-end sm:ml-8 lg:ml-12',
            },
            {
                title: 'Special Child',
                slug: 'special-child',
                alignClass: 'self-center sm:self-start sm:ml-2',
            },
            {
                title: 'Creative & Language',
                slug: 'creative-language',
                alignClass: 'self-center sm:self-end sm:ml-6 lg:ml-8',
            },
        ],
    },
    {
        title: 'Arts & Craft',
        icon: Palette,
        items: [
            {
                title: 'Language Learning',
                slug: 'language-learning',
                alignClass: 'self-center sm:self-start sm:ml-0',
            },
            {
                title: 'Arabic Language',
                slug: 'arabic-language',
                alignClass: 'self-center sm:self-end sm:ml-8 lg:ml-10',
            },
            {
                title: 'Religious Studies',
                slug: 'religious-studies',
                alignClass: 'self-center sm:self-start sm:ml-0',
            },
            {
                title: 'Drawing',
                slug: 'drawing',
                alignClass: 'self-center sm:self-end sm:ml-8 lg:ml-12',
            },
        ],
    },
];
</script>

<template>
    <section class="relative overflow-hidden bg-white py-12 sm:py-16 lg:py-24">
        <!-- Background Sketch Doodle Overlay Pattern (Math, Books, Symbols) -->
        <div
            class="pointer-events-none absolute inset-0 -z-10 opacity-[0.06] select-none"
        >
            <svg
                class="h-full w-full"
                xmlns="http://www.w3.org/2000/svg"
                width="100%"
                height="100%"
            >
                <defs>
                    <pattern
                        id="edu-pattern"
                        width="200"
                        height="200"
                        patternUnits="userSpaceOnUse"
                    >
                        <!-- Math Pi symbol -->
                        <text
                            x="20"
                            y="40"
                            font-family="serif"
                            font-size="22"
                            font-weight="bold"
                            fill="#1b2880"
                        >
                            π = 3.14
                        </text>
                        <!-- Book icon outline -->
                        <path
                            d="M120 30 C130 25, 150 25, 160 30 V60 C150 55, 130 55, 120 60 Z M120 30 C110 25, 90 25, 80 30 V60 C90 55, 110 55, 120 60 Z"
                            fill="none"
                            stroke="#1b2880"
                            stroke-width="1.5"
                        />
                        <!-- Equation -->
                        <text
                            x="130"
                            y="100"
                            font-family="sans-serif"
                            font-size="16"
                            font-weight="bold"
                            fill="#1b2880"
                        >
                            M + N = ♡
                        </text>
                        <!-- Graduation Cap outline -->
                        <polygon
                            points="40,120 70,105 100,120 70,135"
                            fill="none"
                            stroke="#1b2880"
                            stroke-width="1.5"
                        />
                        <path
                            d="M48 124 V140 C48 145, 92 145, 92 140 V124"
                            fill="none"
                            stroke="#1b2880"
                            stroke-width="1.5"
                        />
                        <!-- Lightbulb outline -->
                        <path
                            d="M150 140 A15 15 0 1 0 170 140 L166 155 H154 Z"
                            fill="none"
                            stroke="#1b2880"
                            stroke-width="1.5"
                        />
                    </pattern>
                </defs>
                <rect width="100%" height="100%" fill="url(#edu-pattern)" />
            </svg>
        </div>

        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mx-auto max-w-3xl text-center">
                <!-- Stadium Outline Title Badge -->
                <div
                    class="inline-block rounded-full border-2 border-[#38bdf8] bg-white px-5 py-2 shadow-2xs sm:px-12 sm:py-3"
                >
                    <h2
                        class="text-xl font-extrabold tracking-tight text-[#1c2346] sm:text-3xl lg:text-4xl"
                    >
                        Our Learning Categories
                    </h2>
                </div>

                <p
                    class="mt-4 text-xs font-semibold text-slate-600 sm:text-base md:text-lg"
                >
                    Explore currently active tuition categories and pick what
                    matches your learning goals.
                </p>
            </div>

            <!-- 4 Columns Category Group Layout -->
            <div
                class="mt-10 grid grid-cols-1 gap-8 sm:grid-cols-2 sm:gap-10 lg:grid-cols-4"
            >
                <div
                    v-for="group in categoryGroups"
                    :key="group.title"
                    class="flex flex-col items-center sm:items-start"
                >
                    <!-- Cyan Gradient Group Header Pill -->
                    <div
                        class="mb-5 flex w-full justify-center sm:justify-start"
                    >
                        <div
                            class="inline-flex items-center gap-2 rounded-full bg-gradient-to-r from-[#38bdf8] via-[#0eb0e6] to-[#0284c7] px-4 py-2 text-white shadow-md shadow-cyan-500/20 sm:gap-2.5 sm:px-5 sm:py-2.5"
                        >
                            <div
                                class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-white text-[#0284c7] shadow-2xs sm:h-7 sm:w-7"
                            >
                                <component
                                    :is="group.icon"
                                    class="h-3.5 w-3.5 stroke-[2.5] sm:h-4 sm:w-4"
                                />
                            </div>
                            <span
                                class="text-xs font-extrabold tracking-wide sm:text-sm lg:text-base"
                            >
                                {{ group.title }}
                            </span>
                        </div>
                    </div>

                    <!-- Sub-Category Capsule Pills List -->
                    <div class="flex w-full flex-col gap-3">
                        <Link
                            v-for="item in group.items"
                            :key="item.title"
                            :href="jobs({ query: { category: item.slug } })"
                            :class="[
                                'inline-flex items-center justify-center rounded-full bg-[#181f3d] px-5 py-2 text-xs font-extrabold text-white shadow-md transition-all duration-300 hover:-translate-y-1 hover:bg-[#252f5a] hover:shadow-lg hover:ring-2 hover:ring-[#38bdf8] sm:px-6 sm:py-2.5 sm:text-sm',
                                item.alignClass || 'self-center sm:self-start',
                            ]"
                        >
                            <span>{{ item.title }}</span>
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
