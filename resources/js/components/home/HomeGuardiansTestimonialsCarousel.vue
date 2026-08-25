<script setup lang="ts">
import { ChevronLeft, ChevronRight, Quote, Star } from 'lucide-vue-next';
import { onBeforeUnmount, onMounted, ref } from 'vue';

type GuardianReview = {
    id: number;
    name: string;
    role: string;
    location: string;
    quote: string;
    rating: number;
};

const reviews: GuardianReview[] = [
    {
        id: 1,
        name: 'Anas Mahmud',
        role: 'Teacher',
        location: 'Moghbazar, Dhaka',
        quote: "Our son's math grades improved significantly after we hired a tutor from here. The verification process gave us peace of mind.",
        rating: 5,
    },
    {
        id: 2,
        name: 'Rafiqul Islam',
        role: 'Parent',
        location: 'Dhanmondi, Dhaka',
        quote: 'Finding an experienced English medium tutor was so effortless. Within 24 hours we had a demo class scheduled!',
        rating: 5,
    },
    {
        id: 3,
        name: 'Nusrat Jahan',
        role: 'Guardian',
        location: 'Chottogram',
        quote: 'The platform support team is extremely helpful. We found an excellent female tutor for our daughter.',
        rating: 5,
    },
];

const currentIndex = ref(0);
let timer: ReturnType<typeof setInterval> | null = null;

const nextSlide = () => {
    currentIndex.value = (currentIndex.value + 1) % reviews.length;
};

const prevSlide = () => {
    currentIndex.value =
        (currentIndex.value - 1 + reviews.length) % reviews.length;
};

onMounted(() => {
    // 3-second auto-play timer as specified in PDF ("3 second por por change hobe")
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
            <!-- Section Header -->
            <div class="mx-auto max-w-3xl text-center">
                <span
                    class="inline-block rounded-full bg-cyan-50 px-4 py-1.5 text-xs font-extrabold text-[#0eb0e6] ring-1 ring-[#0eb0e6]/30"
                >
                    GUARDIAN REVIEWS
                </span>
                <h2
                    class="mt-3 text-3xl font-extrabold tracking-tight text-[#1b2880] sm:text-4xl"
                >
                    Appreciation From Guardians &amp; Students
                </h2>
                <p class="mt-4 text-base text-slate-600 sm:text-lg">
                    Hear from satisfied Guardians and students
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
                        v-for="(rev, idx) in reviews"
                        :key="rev.id"
                        v-show="idx === currentIndex"
                        class="flex flex-col items-center text-center transition-all duration-500"
                    >
                        <!-- Rating Stars -->
                        <div class="flex items-center gap-1 text-amber-400">
                            <Star
                                v-for="s in rev.rating"
                                :key="s"
                                class="h-5 w-5 fill-amber-400"
                            />
                        </div>

                        <!-- Quote -->
                        <p
                            class="mt-6 text-lg leading-relaxed font-medium text-slate-800 sm:text-xl md:text-2xl"
                        >
                            &ldquo;{{ rev.quote }}&rdquo;
                        </p>

                        <!-- Profile Info -->
                        <div class="mt-8 flex flex-col items-center">
                            <div
                                class="flex h-14 w-14 items-center justify-center rounded-full bg-[#1b2880] text-lg font-bold text-white shadow-md"
                            >
                                {{ rev.name.charAt(0) }}
                            </div>
                            <span
                                class="mt-3 text-lg font-bold text-[#1b2880]"
                                >{{ rev.name }}</span
                            >
                            <span class="text-xs font-semibold text-slate-500">
                                {{ rev.role }} &bull; {{ rev.location }}
                            </span>
                        </div>
                    </div>

                    <!-- Navigation Dots -->
                    <div class="mt-8 flex items-center justify-center gap-2">
                        <button
                            v-for="(_, idx) in reviews"
                            :key="idx"
                            type="button"
                            :aria-label="`Go to slide ${idx + 1}`"
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

                <!-- Prev / Next Controls -->
                <button
                    type="button"
                    aria-label="Previous review"
                    class="absolute top-1/2 -left-4 hidden h-11 w-11 -translate-y-1/2 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-700 shadow-lg transition-transform hover:scale-110 sm:flex"
                    @click="prevSlide"
                >
                    <ChevronLeft class="h-6 w-6" />
                </button>
                <button
                    type="button"
                    aria-label="Next review"
                    class="absolute top-1/2 -right-4 hidden h-11 w-11 -translate-y-1/2 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-700 shadow-lg transition-transform hover:scale-110 sm:flex"
                    @click="nextSlide"
                >
                    <ChevronRight class="h-6 w-6" />
                </button>
            </div>
        </div>
    </section>
</template>
