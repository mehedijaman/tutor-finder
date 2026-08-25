<script setup lang="ts">
import {
    CreditCard,
    Globe2,
    Headphones,
    ShieldCheck,
    UserCheck,
    Video,
} from 'lucide-vue-next';

const row1Features = [
    {
        title: 'Security Assurance',
        description:
            'Our prime concern the safety and verification both tutors and students',
        icon: ShieldCheck,
        color: 'bg-emerald-500',
    },
    {
        title: 'Verified Tutors',
        description:
            'All tutors are manually verified with proper documents and information.',
        icon: UserCheck,
        color: 'bg-blue-600',
    },
    {
        title: 'Demo Class',
        description:
            'Allow students to take demo classes before confirming a tutor.',
        icon: Video,
        color: 'bg-[#0eb0e6]',
    },
];

const row2Features = [
    {
        title: 'No Advance Payment',
        description: 'No advance payment required from tutors or guardians.',
        icon: CreditCard,
        color: 'bg-indigo-600',
    },
    {
        title: '24/7 Support',
        description:
            'Get instant assistance through phone, WhatsApp, or office support.',
        icon: Headphones,
        color: 'bg-rose-500',
    },
    {
        title: 'Wide Coverage',
        description:
            'Find the right tutor near you, anywhere in Bangladesh. We also connect students abroad with qualified tutors through our interactive online tutoring system.',
        icon: Globe2,
        color: 'bg-teal-600',
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
                    WHY CHOOSE US
                </span>
                <h2
                    class="mt-3 text-3xl font-extrabold tracking-tight text-[#1b2880] sm:text-4xl"
                >
                    Why Choose Tutor Finder?
                </h2>
                <p class="mt-4 text-base text-slate-600 sm:text-lg">
                    We desire to be spread elevated teaching
                </p>
            </div>

            <div class="mt-14 space-y-8">
                <!-- Row 1: Top-down entrance animation ("1st upor theke nambe") -->
                <div
                    class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3"
                >
                    <div
                        v-for="item in row1Features"
                        :key="item.title"
                        class="group relative flex flex-col justify-between rounded-3xl border border-slate-200/90 bg-white p-8 shadow-md transition-all duration-300 hover:-translate-y-1.5 hover:border-[#0eb0e6] hover:shadow-xl"
                    >
                        <div>
                            <div
                                :class="[
                                    'mb-6 flex h-14 w-14 items-center justify-center rounded-2xl text-white shadow-md transition-transform duration-300 group-hover:scale-110',
                                    item.color,
                                ]"
                            >
                                <component :is="item.icon" class="h-7 w-7" />
                            </div>
                            <h3
                                class="text-xl font-bold text-slate-900 transition-colors group-hover:text-[#1b2880]"
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
                </div>

                <!-- Row 2: Bottom-up entrance animation ("2nd line nich theke utbe") -->
                <div
                    class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3"
                >
                    <div
                        v-for="item in row2Features"
                        :key="item.title"
                        class="group relative flex flex-col justify-between rounded-3xl border border-slate-200/90 bg-white p-8 shadow-md transition-all duration-300 hover:-translate-y-1.5 hover:border-[#0eb0e6] hover:shadow-xl"
                    >
                        <div>
                            <div
                                :class="[
                                    'mb-6 flex h-14 w-14 items-center justify-center rounded-2xl text-white shadow-md transition-transform duration-300 group-hover:scale-110',
                                    item.color,
                                ]"
                            >
                                <component :is="item.icon" class="h-7 w-7" />
                            </div>
                            <h3
                                class="text-xl font-bold text-slate-900 transition-colors group-hover:text-[#1b2880]"
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
                </div>
            </div>
        </div>
    </section>
</template>
