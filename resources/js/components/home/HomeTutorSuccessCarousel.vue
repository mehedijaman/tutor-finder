<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { onBeforeUnmount, onMounted, ref } from 'vue';
import { register } from '@/routes';

type TutorStory = {
    id: number;
    name: string;
    department: string;
    university: string;
    quote: string;
    avatar?: string;
};

const stories: TutorStory[] = [
    {
        id: 1,
        name: 'Anas Mahmud',
        department: 'ECE',
        university:
            'Hajee Mohammad Danesh Science & Technology University (HSTU)',
        quote: '“My experience as a tutor with Tutor Finder has been excellent. The quality of the tutoring opportunities and the overall service are both commendable. Highly recommended!”',
    },
    {
        id: 2,
        name: 'Tanvir Hossain',
        department: 'CSE',
        university:
            'Bangladesh University of Engineering and Technology (BUET)',
        quote: '“Tutor Finder connected me with reliable guardians in my local area. Payment process is transparent and support is always reachable. Highly recommended!”',
    },
    {
        id: 3,
        name: 'Sabrina Ahmed',
        department: 'English',
        university: 'University of Dhaka (DU)',
        quote: '“I started tutoring online through Tutor Finder during my graduation. It helped me earn independently while continuing my studies smoothly.”',
    },
    {
        id: 4,
        name: 'Mahfuzur Rahman',
        department: 'EEE',
        university: 'Chittagong University of Engineering & Technology (CUET)',
        quote: '“Excellent platform for genuine tutors. Received verified tuition posts and demo requests within a few days of completing my profile.”',
    },
];

const currentIndex = ref(0);
let timer: ReturnType<typeof setInterval> | null = null;

const nextSlide = () => {
    currentIndex.value = (currentIndex.value + 1) % stories.length;
};

onMounted(() => {
    timer = setInterval(nextSlide, 3500);
});

onBeforeUnmount(() => {
    if (timer) {
        clearInterval(timer);
    }
});

const getVisibleStories = () => {
    const story1 = stories[currentIndex.value];
    const story2 = stories[(currentIndex.value + 1) % stories.length];
    return [story1, story2];
};
</script>

<template>
    <section
        class="relative overflow-hidden bg-gradient-to-b from-[#b8e4fc] via-[#d4effe] to-[#b8e4fc] py-16 lg:py-24"
    >
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
                        Success Stories From Tutors
                    </h2>
                </div>
                <p
                    class="mt-3 text-base font-semibold text-slate-700 sm:text-lg"
                >
                    Be Expert Tutor, Begin Earn
                </p>

                <!-- Become a Tutor CTA Pill Button -->
                <div class="mt-5">
                    <Link
                        :href="register()"
                        class="inline-block rounded-2xl bg-[#38bdf8] px-8 py-3 text-lg font-extrabold text-white shadow-md transition-all duration-300 hover:scale-105 hover:bg-[#0284c7] hover:shadow-lg sm:text-xl"
                    >
                        Become a Tutor
                    </Link>
                </div>
            </div>

            <!-- 2 Cards Display Layout -->
            <div class="mt-14 grid grid-cols-1 gap-8 md:grid-cols-2">
                <div
                    v-for="tutor in getVisibleStories()"
                    :key="tutor.id"
                    class="group relative flex flex-col transition-all duration-300 hover:-translate-y-1"
                >
                    <!-- Top Circular Avatar Badge Overlapping -->
                    <div
                        class="relative z-10 mx-auto -mb-12 flex h-24 w-24 items-center justify-center overflow-hidden rounded-full border-4 border-[#38bdf8] bg-gradient-to-br from-[#38bdf8] to-[#0284c7] text-white shadow-xl"
                    >
                        <template v-if="tutor.avatar">
                            <img
                                :src="tutor.avatar"
                                :alt="tutor.name"
                                class="h-full w-full object-cover"
                            />
                        </template>
                        <template v-else>
                            <span class="text-3xl font-black drop-shadow-sm">{{
                                tutor.name.charAt(0)
                            }}</span>
                        </template>
                    </div>

                    <!-- Card Body Box -->
                    <div
                        class="flex flex-1 flex-col justify-between rounded-3xl border-2 border-[#38bdf8]/50 bg-white/95 p-6 pt-16 text-center shadow-md backdrop-blur-xs"
                    >
                        <!-- Top Credentials -->
                        <div>
                            <h3
                                class="text-xl font-black tracking-tight text-slate-900 sm:text-2xl"
                            >
                                {{ tutor.name }}
                            </h3>
                            <p
                                class="mt-1.5 border-b border-slate-200/80 pb-4 text-xs font-bold text-slate-600 sm:text-sm"
                            >
                                {{ tutor.department }}, {{ tutor.university }}
                            </p>
                        </div>

                        <!-- Bottom Quote -->
                        <p
                            class="pt-4 text-sm leading-relaxed font-extrabold text-slate-800 sm:text-base"
                        >
                            {{ tutor.quote }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Carousel Pagination Dots -->
            <div class="mt-8 flex items-center justify-center gap-1.5">
                <button
                    v-for="(_, idx) in stories"
                    :key="idx"
                    type="button"
                    :aria-label="`Go to story slide ${idx + 1}`"
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
