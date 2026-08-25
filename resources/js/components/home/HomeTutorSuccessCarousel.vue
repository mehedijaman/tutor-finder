<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    ChevronLeft,
    ChevronRight,
    GraduationCap,
    Quote,
} from 'lucide-vue-next';
import { onBeforeUnmount, onMounted, ref } from 'vue';
import { register } from '@/routes';

type TutorStory = {
    id: number;
    name: string;
    department: string;
    university: string;
    quote: string;
};

const stories: TutorStory[] = [
    {
        id: 1,
        name: 'Anas Mahmud',
        department: 'ECE',
        university:
            'Hajee Mohammad Danesh Science & Technology University (HSTU)',
        quote: 'My experience as a tutor with Tutor Finder has been excellent. The quality of the tutoring opportunities and the overall service are both commendable. Highly recommended!',
    },
    {
        id: 2,
        name: 'Tanvir Hossain',
        department: 'CSE',
        university: 'BUET',
        quote: 'Tutor Finder connected me with reliable guardians in my local area. Payment process is transparent and support is always reachable.',
    },
    {
        id: 3,
        name: 'Sabrina Ahmed',
        department: 'English',
        university: 'Dhaka University',
        quote: 'I started tutoring online through Tutor Finder during my graduation. It helped me earn independently while continuing my studies.',
    },
];

const currentIndex = ref(0);
let timer: ReturnType<typeof setInterval> | null = null;

const nextSlide = () => {
    currentIndex.value = (currentIndex.value + 1) % stories.length;
};

const prevSlide = () => {
    currentIndex.value =
        (currentIndex.value - 1 + stories.length) % stories.length;
};

onMounted(() => {
    // Auto-play 3-second carousel timer as specified in PDF ("3 second por por change hobe")
    timer = setInterval(nextSlide, 3000);
});

onBeforeUnmount(() => {
    if (timer) {
        clearInterval(timer);
    }
});
</script>

<template>
    <section class="bg-slate-50/70 py-16 lg:py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mx-auto max-w-3xl text-center">
                <span
                    class="inline-block rounded-full bg-cyan-50 px-4 py-1.5 text-xs font-extrabold text-[#0eb0e6] ring-1 ring-[#0eb0e6]/30"
                >
                    TUTOR STORIES
                </span>
                <h2
                    class="mt-3 text-3xl font-extrabold tracking-tight text-[#1b2880] sm:text-4xl"
                >
                    Success Stories From Tutors
                </h2>
                <p class="mt-4 text-base text-slate-600 sm:text-lg">
                    Be Expert Tutor, Begin Earn
                </p>
            </div>

            <!-- Auto-Playing Carousel (3-second interval) -->
            <div class="relative mx-auto mt-12 max-w-4xl">
                <div
                    class="relative overflow-hidden rounded-3xl border border-slate-200/90 bg-white p-8 shadow-xl sm:p-12"
                >
                    <Quote
                        class="absolute top-6 right-8 h-16 w-16 text-cyan-100 opacity-60"
                    />

                    <div
                        v-for="(tutor, idx) in stories"
                        :key="tutor.id"
                        v-show="idx === currentIndex"
                        class="flex flex-col items-center text-center transition-all duration-500"
                    >
                        <p
                            class="text-lg leading-relaxed font-medium text-slate-800 sm:text-xl md:text-2xl"
                        >
                            &ldquo;{{ tutor.quote }}&rdquo;
                        </p>

                        <!-- Profile Info -->
                        <div class="mt-8 flex flex-col items-center">
                            <div
                                class="flex h-16 w-16 items-center justify-center rounded-full bg-[#0eb0e6] text-xl font-bold text-white shadow-lg"
                            >
                                {{ tutor.name.charAt(0) }}
                            </div>
                            <span
                                class="mt-3 text-xl font-bold text-[#1b2880]"
                                >{{ tutor.name }}</span
                            >
                            <div
                                class="mt-1 flex items-center gap-1.5 text-xs font-semibold text-slate-600"
                            >
                                <GraduationCap class="h-4 w-4 text-[#0eb0e6]" />
                                <span
                                    >{{ tutor.department }},
                                    {{ tutor.university }}</span
                                >
                            </div>
                        </div>
                    </div>

                    <!-- Navigation Dots -->
                    <div class="mt-8 flex items-center justify-center gap-2">
                        <button
                            v-for="(_, idx) in stories"
                            :key="idx"
                            type="button"
                            :aria-label="`Go to story slide ${idx + 1}`"
                            :class="[
                                'h-2.5 rounded-full transition-all duration-300',
                                idx === currentIndex
                                    ? 'w-8 bg-[#0eb0e6]'
                                    : 'w-2.5 bg-slate-300 hover:bg-slate-400',
                            ]"
                            @click="currentIndex = idx"
                        />
                    </div>
                </div>

                <!-- Controls -->
                <button
                    type="button"
                    aria-label="Previous story"
                    class="absolute top-1/2 -left-4 hidden h-11 w-11 -translate-y-1/2 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-700 shadow-lg transition-transform hover:scale-110 sm:flex"
                    @click="prevSlide"
                >
                    <ChevronLeft class="h-6 w-6" />
                </button>
                <button
                    type="button"
                    aria-label="Next story"
                    class="absolute top-1/2 -right-4 hidden h-11 w-11 -translate-y-1/2 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-700 shadow-lg transition-transform hover:scale-110 sm:flex"
                    @click="nextSlide"
                >
                    <ChevronRight class="h-6 w-6" />
                </button>
            </div>

            <!-- Become a Tutor Link -->
            <div class="mt-10 text-center">
                <Link
                    :href="register()"
                    class="inline-flex items-center gap-2 text-sm font-bold text-[#0096c7] transition-colors hover:text-[#1b2880]"
                >
                    <span>Become a Tutor Today</span>
                    <ChevronRight class="h-4 w-4" />
                </Link>
            </div>
        </div>
    </section>
</template>
