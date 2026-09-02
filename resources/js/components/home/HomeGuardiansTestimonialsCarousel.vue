<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { onBeforeUnmount, onMounted, ref } from 'vue';
import { jobs } from '@/routes';

type GuardianReview = {
    id: number;
    name: string;
    role: string;
    location: string;
    quote: string;
    avatar?: string;
};

const reviews: GuardianReview[] = [
    {
        id: 1,
        name: 'Anas Mahmud',
        role: 'Teacher',
        location: 'Moghbazar, Dhaka',
        quote: "“Our son's math grades improved significantly after we hired a tutor from here. The verification process gave us peace of mind.”",
    },
    {
        id: 2,
        name: 'Rafiqul Islam',
        role: 'Parent',
        location: 'Dhanmondi, Dhaka',
        quote: '“Finding an experienced English medium tutor was so effortless. Within 24 hours we had a demo class scheduled with an expert!”',
    },
    {
        id: 3,
        name: 'Nusrat Jahan',
        role: 'Guardian',
        location: 'Chottogram',
        quote: '“The platform support team is extremely helpful. We found an excellent female tutor for our daughter within days.”',
    },
    {
        id: 4,
        name: 'Shahriar Ahmed',
        role: 'Parent',
        location: 'Uttara, Dhaka',
        quote: '“Highly impressed by the quality of tutors available here. The demo class feature helped us make a confident choice.”',
    },
];

const currentIndex = ref(0);
let timer: ReturnType<typeof setInterval> | null = null;

const nextSlide = () => {
    currentIndex.value = (currentIndex.value + 1) % reviews.length;
};

onMounted(() => {
    timer = setInterval(nextSlide, 3500);
});

onBeforeUnmount(() => {
    if (timer) {
        clearInterval(timer);
    }
});

const getVisibleReviews = () => {
    const r1 = reviews[currentIndex.value];
    const r2 = reviews[(currentIndex.value + 1) % reviews.length];
    const r3 = reviews[(currentIndex.value + 2) % reviews.length];
    return [r1, r2, r3];
};
</script>

<template>
    <section
        class="relative overflow-hidden bg-gradient-to-b from-[#b8e4fc] via-[#d4effe] to-[#b8e4fc] py-16 lg:py-24"
    >
        <!-- Dot Grid Overlays -->
        <div class="pointer-events-none absolute top-6 left-6 -z-10 opacity-20">
            <svg width="120" height="120" fill="none">
                <pattern
                    id="dot-grid-g1"
                    x="0"
                    y="0"
                    width="16"
                    height="16"
                    patternUnits="userSpaceOnUse"
                >
                    <circle cx="2" cy="2" r="2" fill="#0eb0e6" />
                </pattern>
                <rect width="120" height="120" fill="url(#dot-grid-g1)" />
            </svg>
        </div>

        <div
            class="pointer-events-none absolute top-6 right-6 -z-10 opacity-20"
        >
            <svg width="120" height="120" fill="none">
                <pattern
                    id="dot-grid-g2"
                    x="0"
                    y="0"
                    width="16"
                    height="16"
                    patternUnits="userSpaceOnUse"
                >
                    <circle cx="2" cy="2" r="2" fill="#1c2346" />
                </pattern>
                <rect width="120" height="120" fill="url(#dot-grid-g2)" />
            </svg>
        </div>

        <!-- Top Right Geometric Sketch Line Overlay -->
        <div
            class="pointer-events-none absolute top-0 right-0 -z-10 hidden h-full w-1/3 opacity-25 lg:block"
        >
            <svg
                class="h-full w-full"
                viewBox="0 0 400 600"
                preserveAspectRatio="none"
            >
                <line
                    x1="400"
                    y1="50"
                    x2="100"
                    y2="450"
                    stroke="#1c2346"
                    stroke-width="2"
                />
            </svg>
        </div>

        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <!-- Header Section -->
            <div class="mx-auto max-w-4xl text-center">
                <!-- Stadium Outline Title Pill -->
                <div
                    class="inline-block rounded-full border-2 border-[#38bdf8] bg-white px-8 py-2.5 shadow-2xs sm:px-12 sm:py-3"
                >
                    <h2
                        class="text-2xl font-black tracking-tight text-[#1c2346] sm:text-3xl lg:text-4xl"
                    >
                        Appreciation From Guardians &amp; Students
                    </h2>
                </div>
                <p
                    class="mt-3 text-base font-semibold text-slate-700 sm:text-lg"
                >
                    Hear from satisfied Guardians and students
                </p>

                <!-- Hire a Verified Tutor CTA Pill Button -->
                <div class="mt-5">
                    <Link
                        :href="jobs()"
                        class="inline-block rounded-2xl bg-[#4f3d9b] px-8 py-3 text-lg font-extrabold text-white shadow-md transition-all duration-300 hover:scale-105 hover:bg-[#3f2f87] hover:shadow-lg sm:text-xl"
                    >
                        Hire a Verified Tutor
                    </Link>
                </div>
            </div>

            <!-- 3 Parallelogram Cards Display Layout -->
            <div class="mt-14 grid grid-cols-1 gap-6 md:grid-cols-3">
                <div
                    v-for="rev in getVisibleReviews()"
                    :key="rev.id"
                    class="group relative flex flex-col justify-between rounded-xl bg-[#7cd3fc] p-6 shadow-md transition-all duration-300 hover:-translate-y-1 hover:shadow-xl"
                    style="
                        clip-path: polygon(0% 0%, 100% 0%, 100% 93%, 0% 100%);
                    "
                >
                    <div class="grid grid-cols-12 items-center gap-3">
                        <!-- Left Part: Quote & Tag -->
                        <div class="col-span-7 flex flex-col justify-between">
                            <p
                                class="text-xs leading-relaxed font-bold text-slate-900 sm:text-xs md:text-sm"
                            >
                                {{ rev.quote }}
                            </p>
                            <span
                                class="mt-3 block text-[11px] font-extrabold text-slate-800"
                            >
                                &mdash; Satisfied Guardian
                            </span>
                        </div>

                        <!-- Right Part: Avatar & Credentials -->
                        <div
                            class="col-span-5 flex flex-col items-center text-center"
                        >
                            <div
                                class="mb-2 flex h-18 w-18 shrink-0 items-center justify-center overflow-hidden rounded-full border-4 border-[#38bdf8] bg-gradient-to-br from-[#38bdf8] to-[#0284c7] text-white shadow-md"
                            >
                                <template v-if="rev.avatar">
                                    <img
                                        :src="rev.avatar"
                                        :alt="rev.name"
                                        class="h-full w-full object-cover"
                                    />
                                </template>
                                <template v-else>
                                    <span
                                        class="text-2xl font-black drop-shadow-sm"
                                        >{{ rev.name.charAt(0) }}</span
                                    >
                                </template>
                            </div>
                            <span
                                class="text-xs font-black tracking-tight text-slate-900"
                            >
                                {{ rev.name }}
                            </span>
                            <span class="text-[11px] font-bold text-slate-800">
                                {{ rev.role }}
                            </span>
                            <span
                                class="text-[10px] font-semibold text-slate-800"
                            >
                                {{ rev.location }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Carousel Pagination Dots -->
            <div class="mt-8 flex items-center justify-center gap-1.5">
                <button
                    v-for="(_, idx) in reviews"
                    :key="idx"
                    type="button"
                    :aria-label="`Go to review slide ${idx + 1}`"
                    :class="[
                        'h-2.5 rounded-full transition-all duration-300',
                        idx === currentIndex
                            ? 'w-7 bg-[#38bdf8]'
                            : 'w-2.5 bg-slate-400/50 hover:bg-slate-400',
                    ]"
                    @click="currentIndex = idx"
                />
            </div>
        </div>
    </section>
</template>
