<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import HomeGuardiansTestimonialsCarousel from '@/components/home/HomeGuardiansTestimonialsCarousel.vue';
import HomeHeroSection from '@/components/home/HomeHeroSection.vue';
import HomeHeroStatsBar from '@/components/home/HomeHeroStatsBar.vue';
import HomeHowItWorksGuardians from '@/components/home/HomeHowItWorksGuardians.vue';
import HomeHowItWorksTutors from '@/components/home/HomeHowItWorksTutors.vue';
import HomeLearningCategories from '@/components/home/HomeLearningCategories.vue';
import HomeTutorSuccessCarousel from '@/components/home/HomeTutorSuccessCarousel.vue';
import HomeTutoringMethods from '@/components/home/HomeTutoringMethods.vue';
import HomeWhyChooseUs from '@/components/home/HomeWhyChooseUs.vue';
import LiveTuitionDivisionsBar from '@/components/home/LiveTuitionDivisionsBar.vue';
import { useSiteSettings } from '@/composables/useSiteSettings';
import PublicLayout from '@/layouts/PublicLayout.vue';
import { jobs, register, tutors } from '@/routes';

type HomepageTestimonial = {
    id: number;
    name: string;
    role: string | null;
    avatar_url: string | null;
    content: string;
    rating: number;
};

type TuitionMethod = {
    id: number;
    name: string;
    slug: string;
    description: string | null;
};

const props = withDefaults(
    defineProps<{
        canRegister: boolean;
        heroStats: {
            active_tutors: number;
            families_served: number;
            average_rating: number | null;
        };
        testimonials: HomepageTestimonial[];
        tuitionMethods: TuitionMethod[];
    }>(),
    {
        canRegister: true,
        heroStats: () => ({
            active_tutors: 1532,
            families_served: 1532,
            average_rating: 5.0,
        }),
        testimonials: () => [],
        tuitionMethods: () => [],
    },
);

const { siteName } = useSiteSettings();
</script>

<template>
    <Head title="Welcome to Tutor Finder" />

    <PublicLayout>
        <!-- 1. Main Hero Banner -->
        <HomeHeroSection />

        <!-- 2. Real-Time Stat Counter Bar -->
        <HomeHeroStatsBar
            :stats="{
                live_tuitions: 1692,
                active_tutors: props.heroStats.active_tutors || 1532,
                guardians_students: props.heroStats.families_served || 1532,
                rating: props.heroStats.average_rating || 5.0,
            }"
        />

        <!-- 3. Live Tuition Division Filter Bar -->
        <LiveTuitionDivisionsBar />

        <!-- 4. Our Learning Categories (20 Categories Grid) -->
        <HomeLearningCategories />

        <!-- 5. Tutoring Methods (8 Numbered Methods 01 - 08) -->
        <HomeTutoringMethods />

        <!-- 6. How It Works For Guardians & Students (4-Step Timeline) -->
        <HomeHowItWorksGuardians />

        <!-- 7. Appreciation From Guardians & Students (3-sec Auto Carousel) -->
        <HomeGuardiansTestimonialsCarousel />

        <!-- 8. How It Works For Tutors (4-Step Vertical Entrance Timeline) -->
        <HomeHowItWorksTutors />

        <!-- 9. Success Stories From Tutors (3-sec Auto Carousel) -->
        <HomeTutorSuccessCarousel />

        <!-- 10. Why Choose Tutor Finder? (6 Staggered Feature Cards) -->
        <HomeWhyChooseUs />

        <!-- 11. Final Call To Action Banner -->
        <section class="bg-slate-50 py-16 md:py-24">
            <div class="mx-auto max-w-7xl px-4 sm:px-6">
                <div
                    class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-[#1b2880] to-[#0eb0e6] px-6 py-12 text-center shadow-xl sm:px-10 md:px-16 md:py-16"
                >
                    <div
                        class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(255,255,255,0.25),transparent_50%)]"
                    ></div>
                    <h2
                        class="relative text-3xl font-extrabold tracking-tight text-white sm:text-4xl lg:text-5xl"
                    >
                        Start Your Learning Journey Today
                    </h2>
                    <p
                        class="relative mx-auto mt-4 max-w-2xl text-base text-cyan-100 sm:text-lg"
                    >
                        Join thousands of students and tutors already using
                        {{ siteName }}
                    </p>
                    <div
                        class="relative mt-8 flex flex-col items-center justify-center gap-4 sm:flex-row sm:gap-5"
                    >
                        <Link
                            :href="tutors()"
                            class="inline-flex h-12 items-center justify-center rounded-full bg-white px-8 text-base font-extrabold text-[#1b2880] shadow-md transition-all hover:scale-105 hover:bg-slate-100"
                        >
                            Find a Tutor
                        </Link>
                        <Link
                            :href="register()"
                            class="inline-flex h-12 items-center justify-center rounded-full border-2 border-white/90 bg-transparent px-8 text-base font-extrabold text-white transition-all hover:scale-105 hover:bg-white/10"
                        >
                            Become a Tutor
                        </Link>
                    </div>
                </div>
            </div>
        </section>
    </PublicLayout>
</template>
