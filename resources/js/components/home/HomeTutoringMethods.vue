<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    Briefcase,
    Globe,
    Home,
    Languages,
    Laptop,
    Target,
    UserCheck,
    Users,
    Zap,
} from 'lucide-vue-next';
import { jobs } from '@/routes';

type TutoringMethodSlice = {
    step: string;
    title: string;
    description: string;
    icon: unknown;
    color: string;
    textColor: string;
    slug: string;
};

const methods: TutoringMethodSlice[] = [
    {
        step: '01',
        title: 'Home Tutoring',
        description:
            'A dedicated tutor comes to your home for customized, face-to-face learning.',
        icon: Home,
        color: '#14b8a6', // Teal Green
        textColor: 'text-[#14b8a6]',
        slug: 'home-tutoring',
    },
    {
        step: '02',
        title: 'Online Tutoring',
        description:
            'Learn from anywhere with live, interactive classes led by expert tutors.',
        icon: Laptop,
        color: '#65a30d', // Lime Green
        textColor: 'text-[#65a30d]',
        slug: 'online-tutoring',
    },
    {
        step: '03',
        title: 'Group Tutoring',
        description:
            'Study with a small group of students in an interactive learning environment.',
        icon: Users,
        color: '#dc2626', // Red
        textColor: 'text-[#dc2626]',
        slug: 'group-tutoring',
    },
    {
        step: '04',
        title: 'Exam Crash Program',
        description:
            'Short-term, intensive preparation designed to boost your exam performance quickly.',
        icon: Zap,
        color: '#ea580c', // Orange
        textColor: 'text-[#ea580c]',
        slug: 'exam-crash-program',
    },
    {
        step: '05',
        title: 'Shadow Tutoring',
        description:
            'Personalized support alongside school or college learning to reinforce understanding and keep you on track.',
        icon: UserCheck,
        color: '#d97706', // Golden Yellow
        textColor: 'text-[#d97706]',
        slug: 'shadow-tutoring',
    },
    {
        step: '06',
        title: 'Exam-Focused Tutoring',
        description:
            'Targeted preparation designed to help you excel in specific exams with structured guidance.',
        icon: Target,
        color: '#1e293b', // Slate Navy
        textColor: 'text-[#1e293b]',
        slug: 'exam-focused-tutoring',
    },
    {
        step: '07',
        title: 'Language Tutoring',
        description:
            'Improve your speaking, writing, reading, and communication skills with expert support.',
        icon: Languages,
        color: '#475569', // Muted Slate
        textColor: 'text-[#475569]',
        slug: 'language-tutoring',
    },
    {
        step: '08',
        title: 'Skill-Based Tutoring',
        description:
            'Learn practical, job-ready, and professional skills for personal and career growth.',
        icon: Briefcase,
        color: '#0284c7', // Sky Blue
        textColor: 'text-[#0284c7]',
        slug: 'skill-based-tutoring',
    },
];

// Slice geometry calculation for SVG wheel
const slicesCount = 8;
const sliceAngle = 360 / slicesCount; // 45 degrees
const cx = 320;
const cy = 320;
const rIn = 85;
const rOut = 275;

const getSlicePath = (index: number) => {
    // Start angle offset by -112.5 deg so slice 01 is centered at top (-90 deg)
    const startDeg = -112.5 + index * sliceAngle;
    const endDeg = startDeg + sliceAngle;

    const startRad = (startDeg * Math.PI) / 180;
    const endRad = (endDeg * Math.PI) / 180;

    const x1Out = cx + rOut * Math.cos(startRad);
    const y1Out = cy + rOut * Math.sin(startRad);
    const x2Out = cx + rOut * Math.cos(endRad);
    const y2Out = cy + rOut * Math.sin(endRad);

    const x2In = cx + rIn * Math.cos(endRad);
    const y2In = cy + rIn * Math.sin(endRad);
    const x1In = cx + rIn * Math.cos(startRad);
    const y1In = cy + rIn * Math.sin(startRad);

    return `M ${x1Out} ${y1Out} A ${rOut} ${rOut} 0 0 1 ${x2Out} ${y2Out} L ${x2In} ${y2In} A ${rIn} ${rIn} 0 0 0 ${x1In} ${y1In} Z`;
};

const getBadgePosition = (index: number) => {
    const midDeg = -90 + index * sliceAngle;
    const midRad = (midDeg * Math.PI) / 180;
    const badgeR = rOut + 20;
    return {
        x: cx + badgeR * Math.cos(midRad),
        y: cy + badgeR * Math.sin(midRad),
    };
};

const getIconPosition = (index: number) => {
    const midDeg = -90 + index * sliceAngle;
    const midRad = (midDeg * Math.PI) / 180;
    const iconR = rOut - 40;
    return {
        x: cx + iconR * Math.cos(midRad),
        y: cy + iconR * Math.sin(midRad),
    };
};

const getTextPosition = (index: number) => {
    const midDeg = -90 + index * sliceAngle;
    const midRad = (midDeg * Math.PI) / 180;
    const textR = rIn + (rOut - rIn) * 0.42;
    return {
        x: cx + textR * Math.cos(midRad),
        y: cy + textR * Math.sin(midRad),
        angle: midDeg,
    };
};
</script>

<template>
    <section
        class="relative overflow-hidden bg-gradient-to-r from-[#8be0fd] via-[#82d6fa] to-[#78cdfc] py-16 lg:py-24"
    >
        <!-- Ambient Mesh Glow Layer -->
        <div
            class="pointer-events-none absolute -top-32 left-1/2 -z-10 h-[500px] w-[800px] -translate-x-1/2 rounded-full bg-radial from-white/40 via-cyan-100/20 to-transparent blur-3xl"
        ></div>
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div
                class="grid grid-cols-1 items-center gap-10 lg:grid-cols-12 lg:gap-8"
            >
                <!-- Left Column: Pointing Arrow Banner -->
                <div class="flex justify-center lg:col-span-4 lg:justify-start">
                    <div class="relative w-full max-w-lg">
                        <!-- Arrow Container -->
                        <div
                            class="relative flex flex-col justify-center rounded-3xl bg-gradient-to-r from-[#0096c7] via-[#0eb0e6] to-[#0284c7] p-8 text-white shadow-2xl sm:p-12 lg:rounded-r-none"
                            style="
                                clip-path: polygon(
                                    0% 0%,
                                    82% 0%,
                                    100% 50%,
                                    82% 100%,
                                    0% 100%
                                );
                            "
                        >
                            <h2
                                class="text-3xl font-black tracking-tight sm:text-5xl"
                            >
                                Tutoring
                                <span class="block text-[#181f3d]"
                                    >Methods</span
                                >
                            </h2>
                            <p
                                class="mt-4 max-w-xs text-sm font-medium text-cyan-50 sm:text-base"
                            >
                                Choose the tutoring method that best fits your
                                learning needs.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Right Column: 8-Slice Wheel Diagram (Desktop & Tablet SVG Wheel) -->
                <div class="hidden lg:col-span-8 lg:flex lg:justify-center">
                    <div class="relative h-[660px] w-[660px] shrink-0">
                        <svg
                            viewBox="0 0 640 640"
                            class="h-full w-full drop-shadow-xl select-none"
                        >
                            <!-- Slices -->
                            <g v-for="(item, idx) in methods" :key="item.step">
                                <Link
                                    :href="
                                        jobs({ query: { method: item.slug } })
                                    "
                                    class="group cursor-pointer"
                                >
                                    <path
                                        :d="getSlicePath(idx)"
                                        :fill="item.color"
                                        class="stroke-white stroke-2 transition-opacity duration-300 hover:opacity-90"
                                    />

                                    <!-- White Circle Icon Badge -->
                                    <circle
                                        :cx="getIconPosition(idx).x"
                                        :cy="getIconPosition(idx).y"
                                        r="20"
                                        fill="#ffffff"
                                        class="drop-shadow-md transition-transform duration-300 group-hover:scale-110"
                                    />

                                    <!-- Number Badge Circle -->
                                    <circle
                                        :cx="getBadgePosition(idx).x"
                                        :cy="getBadgePosition(idx).y"
                                        r="15"
                                        fill="#ffffff"
                                        stroke="#38bdf8"
                                        stroke-width="2"
                                        class="drop-shadow-sm"
                                    />
                                    <text
                                        :x="getBadgePosition(idx).x"
                                        :y="getBadgePosition(idx).y + 4"
                                        font-family="sans-serif"
                                        font-size="11"
                                        font-weight="800"
                                        fill="#0284c7"
                                        text-anchor="middle"
                                    >
                                        {{ item.step }}
                                    </text>
                                </Link>
                            </g>

                            <!-- Icons inside Icon Badges -->
                            <g
                                v-for="(item, idx) in methods"
                                :key="'icon-' + item.step"
                                class="pointer-events-none"
                            >
                                <foreignObject
                                    :x="getIconPosition(idx).x - 12"
                                    :y="getIconPosition(idx).y - 12"
                                    width="24"
                                    height="24"
                                >
                                    <div
                                        class="flex h-full w-full items-center justify-center"
                                    >
                                        <component
                                            :is="item.icon"
                                            :class="['h-4 w-4', item.textColor]"
                                        />
                                    </div>
                                </foreignObject>

                                <!-- Title and Description Text Overlay -->
                                <foreignObject
                                    :x="getTextPosition(idx).x - 65"
                                    :y="getTextPosition(idx).y - 32"
                                    width="130"
                                    height="64"
                                >
                                    <div
                                        class="flex h-full flex-col items-center justify-center text-center leading-tight text-white"
                                    >
                                        <span
                                            class="text-[11px] font-black tracking-tight drop-shadow-xs"
                                        >
                                            {{ item.title }} –
                                        </span>
                                        <span
                                            class="mt-0.5 line-clamp-3 text-[9px] font-semibold opacity-95"
                                        >
                                            {{ item.description }}
                                        </span>
                                    </div>
                                </foreignObject>
                            </g>

                            <!-- Center Ring Circle -->
                            <circle
                                cx="320"
                                cy="320"
                                r="82"
                                fill="#ffffff"
                                stroke="#38bdf8"
                                stroke-width="6"
                                class="drop-shadow-lg"
                            />
                            <foreignObject
                                x="248"
                                y="248"
                                width="144"
                                height="144"
                            >
                                <div
                                    class="flex h-full flex-col items-center justify-center text-center"
                                >
                                    <span
                                        class="text-xs font-black tracking-wider text-[#0284c7]"
                                    >
                                        TUTORING
                                    </span>
                                    <span
                                        class="text-sm font-black tracking-wider text-[#181f3d]"
                                    >
                                        METHODS
                                    </span>
                                </div>
                            </foreignObject>
                        </svg>
                    </div>
                </div>

                <!-- Responsive Grid Fallback for Mobile Viewports -->
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:hidden">
                    <Link
                        v-for="item in methods"
                        :key="item.step"
                        :href="jobs({ query: { method: item.slug } })"
                        class="group relative flex flex-col justify-between rounded-2xl border border-slate-200/90 bg-white p-5 shadow-md transition-all duration-300 hover:-translate-y-1 hover:shadow-xl"
                    >
                        <div>
                            <div class="flex items-center justify-between">
                                <span
                                    class="flex h-9 w-9 items-center justify-center rounded-xl text-xs font-extrabold text-white shadow-xs"
                                    :style="{ backgroundColor: item.color }"
                                >
                                    {{ item.step }}
                                </span>
                                <div
                                    class="flex h-9 w-9 items-center justify-center rounded-full bg-slate-100"
                                >
                                    <component
                                        :is="item.icon"
                                        :class="['h-4 w-4', item.textColor]"
                                    />
                                </div>
                            </div>

                            <h3
                                class="mt-4 text-base font-extrabold text-slate-900 group-hover:text-[#0284c7]"
                            >
                                {{ item.title }}
                            </h3>
                            <p
                                class="mt-2 text-xs leading-relaxed text-slate-600"
                            >
                                {{ item.description }}
                            </p>
                        </div>
                    </Link>
                </div>
            </div>
        </div>
    </section>
</template>
