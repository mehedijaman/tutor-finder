<script setup lang="ts">
import {
    CreditCard,
    Globe2,
    Headphones,
    ShieldCheck,
    UserCheck,
    Video,
} from 'lucide-vue-next';

type FeatureItem = {
    title: string;
    description: string;
    icon: unknown;
    gradientClass: string;
    titleColor: string;
};

const features: FeatureItem[] = [
    {
        title: 'Security Assurance',
        description:
            'Our prime concern the safety and we ensure that highly and verification both tutors and students',
        icon: ShieldCheck,
        gradientClass:
            'bg-gradient-to-br from-[#2563eb] via-[#1d4ed8] to-[#1e3a8a]',
        titleColor: 'text-[#1e40af]',
    },
    {
        title: 'Verified Tutors',
        description:
            'All tutors are manually verified with proper documents and information.',
        icon: UserCheck,
        gradientClass:
            'bg-gradient-to-br from-[#9333ea] via-[#7e22ce] to-[#3b0764]',
        titleColor: 'text-[#6b21a8]',
    },
    {
        title: 'Demo Class',
        description:
            'Allow students to take demo classes before confirming a tutor.',
        icon: Video,
        gradientClass:
            'bg-gradient-to-br from-[#22c55e] via-[#16a34a] to-[#15803d]',
        titleColor: 'text-[#16a34a]',
    },
    {
        title: 'No Advance Payment',
        description: 'No advance payment required from tutors or guardians.',
        icon: CreditCard,
        gradientClass:
            'bg-gradient-to-br from-[#f59e0b] via-[#d97706] to-[#b45309]',
        titleColor: 'text-[#ea580c]',
    },
    {
        title: '24/7 Support',
        description:
            'Get instant assistance through phone, WhatsApp, or office support.',
        icon: Headphones,
        gradientClass:
            'bg-gradient-to-br from-[#6366f1] via-[#4f46e5] to-[#312e81]',
        titleColor: 'text-[#4f46e5]',
    },
    {
        title: 'Wide Coverage',
        description:
            'Find the right tutor near you, anywhere in Bangladesh. We also connect students abroad with qualified tutors through our interactive online tutoring system.',
        icon: Globe2,
        gradientClass:
            'bg-gradient-to-br from-[#0284c7] via-[#0369a1] to-[#1e3a8a]',
        titleColor: 'text-[#0284c7]',
    },
];
</script>

<template>
    <section class="relative overflow-hidden bg-white py-16 lg:py-24">
        <!-- Background Sketch Pattern & Dot Grids -->
        <div class="pointer-events-none absolute top-6 left-6 -z-10 opacity-20">
            <svg width="120" height="120" fill="none">
                <pattern
                    id="dot-grid-us-1"
                    x="0"
                    y="0"
                    width="16"
                    height="16"
                    patternUnits="userSpaceOnUse"
                >
                    <circle cx="2" cy="2" r="2" fill="#0eb0e6" />
                </pattern>
                <rect width="120" height="120" fill="url(#dot-grid-us-1)" />
            </svg>
        </div>

        <div
            class="pointer-events-none absolute right-6 bottom-6 -z-10 opacity-20"
        >
            <svg width="120" height="120" fill="none">
                <pattern
                    id="dot-grid-us-2"
                    x="0"
                    y="0"
                    width="16"
                    height="16"
                    patternUnits="userSpaceOnUse"
                >
                    <circle cx="2" cy="2" r="2" fill="#1c2346" />
                </pattern>
                <rect width="120" height="120" fill="url(#dot-grid-us-2)" />
            </svg>
        </div>

        <!-- Floating Sketch Ring Circles -->
        <div
            class="pointer-events-none absolute top-20 left-16 h-5 w-5 rounded-full border-2 border-cyan-400 opacity-40"
        ></div>
        <div
            class="pointer-events-none absolute top-1/3 right-20 h-4 w-4 rounded-full border-2 border-amber-400 opacity-40"
        ></div>
        <div
            class="pointer-events-none absolute bottom-20 left-24 h-6 w-6 rounded-full border-2 border-purple-400 opacity-40"
        ></div>

        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mx-auto max-w-3xl text-center">
                <h2
                    class="text-3xl font-black tracking-tight text-[#1c2346] sm:text-4xl lg:text-5xl"
                >
                    Why
                    <span class="text-[#0284c7]">Choose Tutor Finder?</span>
                </h2>
                <p
                    class="mt-4 text-base font-semibold text-slate-600 sm:text-lg"
                >
                    We desire to be spread elevated teaching
                </p>
            </div>

            <!-- 6 Feature Cards Grid -->
            <div
                class="mt-20 grid grid-cols-1 gap-x-8 gap-y-16 sm:grid-cols-2 lg:grid-cols-3"
            >
                <div
                    v-for="item in features"
                    :key="item.title"
                    class="group relative flex flex-col justify-between rounded-3xl border border-slate-200/80 bg-white px-7 pt-14 pb-8 text-center shadow-lg shadow-slate-100/80 transition-all duration-300 hover:-translate-y-2 hover:border-slate-300 hover:shadow-xl"
                >
                    <!-- Top Center Gradient Icon Circle Badge -->
                    <div
                        :class="[
                            'absolute -top-10 left-1/2 flex h-20 w-20 -translate-x-1/2 items-center justify-center rounded-full border-4 border-white text-white shadow-xl transition-transform duration-300 group-hover:scale-110',
                            item.gradientClass,
                        ]"
                    >
                        <component
                            :is="item.icon"
                            class="h-9 w-9 stroke-[2.2]"
                        />
                    </div>

                    <!-- Content -->
                    <div>
                        <h3
                            :class="[
                                'text-xl font-extrabold tracking-tight',
                                item.titleColor,
                            ]"
                        >
                            {{ item.title }}
                        </h3>
                        <p
                            class="mt-4 text-sm leading-relaxed font-medium text-slate-600"
                        >
                            {{ item.description }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
