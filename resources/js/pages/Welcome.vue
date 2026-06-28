<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowRight, Home, MonitorPlay, School } from 'lucide-vue-next';
import HomeTestimonialsCarousel from '@/components/home/HomeTestimonialsCarousel.vue';
import { useSiteSettings } from '@/composables/useSiteSettings';
import PublicLayout from '@/layouts/PublicLayout.vue';
import { jobs, register } from '@/routes';

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
            active_tutors: 0,
            families_served: 0,
            average_rating: null,
        }),
        testimonials: () => [],
        tuitionMethods: () => [],
    },
);

const { siteName } = useSiteSettings();

const compactNumberFormatter = new Intl.NumberFormat('en', {
    maximumFractionDigits: 1,
    notation: 'compact',
});

const formatCompactNumber = (value: number): string => {
    return compactNumberFormatter.format(value);
};

const formatAverageRating = (value: number | null): string => {
    if (value === null) {
        return 'N/A';
    }

    return `${value.toFixed(1)}/5`;
};

const tuitionMethodDecorations = [
    {
        icon: Home,
        iconWrapperClass: 'bg-blue-600',
    },
    {
        icon: MonitorPlay,
        iconWrapperClass: 'bg-emerald-600',
    },
    {
        icon: School,
        iconWrapperClass: 'bg-amber-600',
    },
] as const;

const tuitionMethodDecoration = (index: number) => {
    return tuitionMethodDecorations[index % tuitionMethodDecorations.length];
};
</script>

<template>
    <Head title="Welcome" />

    <PublicLayout>
        <!-- Hero Section -->
        <section class="relative overflow-hidden py-16 sm:py-24 lg:py-32">
            <!-- Background Gradients -->
            <div
                class="absolute inset-0 -z-10 bg-[radial-gradient(45rem_50rem_at_top,theme(colors.blue.100),white)] opacity-20"
            />
            <div
                class="absolute inset-y-0 right-1/2 -z-10 mr-16 w-[200%] origin-bottom-left skew-x-[-30deg] bg-white shadow-xl ring-1 shadow-blue-600/10 ring-blue-50 sm:mr-28 lg:mr-0 lg:w-[200%] lg:origin-center"
            />

            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="relative mx-auto max-w-4xl text-center">
                    <div
                        class="mb-6 inline-flex items-center rounded-full border border-blue-200 bg-blue-50/50 px-3 py-1 text-sm font-medium text-blue-800 backdrop-blur-sm"
                    >
                        <span
                            class="mr-2 h-2 w-2 rounded-full bg-blue-600"
                        ></span>
                        #1 Trusted Tutoring Platform
                    </div>

                    <h1
                        class="font-display text-4xl font-bold tracking-tight text-slate-900 sm:text-5xl md:text-6xl lg:text-7xl"
                    >
                        Hire the Right Tutor
                        <span class="relative whitespace-nowrap text-blue-600">
                            <span class="relative">with Confidences</span>
                        </span>
                    </h1>

                    <p
                        class="mx-auto mt-6 max-w-2xl text-lg leading-relaxed text-slate-600 sm:text-xl"
                    >
                        Quickly match with competent tutors tailored to your child's need to succeed.
                    </p>

                    <div
                        class="mt-10 flex flex-col items-center justify-center gap-4 sm:flex-row sm:gap-6"
                    >
                        <Link
                            href="/tutors"
                            class="inline-flex h-12 items-center justify-center rounded-xl bg-blue-600 px-8 text-base font-semibold text-white shadow-lg shadow-blue-600/20 transition-all hover:bg-blue-700 hover:shadow-blue-600/30 focus-visible:ring-2 focus-visible:ring-blue-600 focus-visible:ring-offset-2 focus-visible:outline-none active:scale-95"
                        >
                            Find a Tutor
                        </Link>
                        <Link
                            :href="jobs()"
                            class="inline-flex h-12 items-center justify-center rounded-xl border border-slate-200 bg-white px-8 text-base font-semibold text-slate-700 shadow-sm transition-all hover:border-slate-300 hover:bg-slate-50 focus-visible:ring-2 focus-visible:ring-slate-400 focus-visible:ring-offset-2 focus-visible:outline-none active:scale-95"
                        >
                            Post a Requirement
                        </Link>
                    </div>

                    <!-- Hero Stats -->
                    <div
                        class="mt-12 grid grid-cols-2 gap-4 rounded-2xl border border-slate-200/60 bg-white/60 p-4 shadow-xl shadow-slate-200/40 backdrop-blur-md sm:grid-cols-4 md:mt-16 lg:mt-20"
                    >
                        <div class="p-4 text-center">
                            <p
                                class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl"
                            >
                                {{
                                    formatCompactNumber(
                                        props.heroStats.active_tutors,
                                    )
                                }}+
                            </p>
                            <p class="mt-1 text-sm font-medium text-slate-500">
                                Active Tutors
                            </p>
                        </div>
                        <div class="p-4 text-center">
                            <p
                                class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl"
                            >
                                {{
                                    formatCompactNumber(
                                        props.heroStats.families_served,
                                    )
                                }}+
                            </p>
                            <p class="mt-1 text-sm font-medium text-slate-500">
                                Students Helped
                            </p>
                        </div>
                        <div class="p-4 text-center">
                            <p
                                class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl"
                            >
                                {{
                                    formatAverageRating(
                                        props.heroStats.average_rating,
                                    )
                                }}
                            </p>
                            <p class="mt-1 text-sm font-medium text-slate-500">
                                Average Rating
                            </p>
                        </div>
                        <div class="p-4 text-center">
                            <div
                                class="flex items-center justify-center -space-x-2"
                            >
                                <div
                                    class="h-8 w-8 rounded-full border-2 border-white bg-slate-200"
                                ></div>
                                <div
                                    class="h-8 w-8 rounded-full border-2 border-white bg-slate-300"
                                ></div>
                                <div
                                    class="h-8 w-8 rounded-full border-2 border-white bg-slate-400"
                                ></div>
                                <div
                                    class="flex h-8 w-8 items-center justify-center rounded-full border-2 border-white bg-blue-100 text-xs font-bold text-blue-600"
                                >
                                    99%
                                </div>
                            </div>
                            <p class="mt-2 text-sm font-medium text-slate-500">
                                Satisfaction
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- How It Works (Premium / Minimal) -->
        <section
            class="relative isolate overflow-hidden bg-white py-24 sm:py-32"
        >
            <div
                class="absolute inset-0 -z-10 h-full w-full bg-white bg-[radial-gradient(#e5e7eb_1px,transparent_1px)] [mask-image:radial-gradient(ellipse_50%_50%_at_50%_50%,#000_70%,transparent_100%)] [background-size:16px_16px] opacity-50"
            ></div>

            <div class="mx-auto max-w-7xl px-6 lg:px-8">
                <div class="mx-auto max-w-2xl text-center">
                    <h2
                        class="text-base leading-7 font-semibold tracking-widest text-blue-600 uppercase"
                    >
                        How It Works
                    </h2>
                    <p
                        class="mt-2 text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl"
                    >
                        Find your perfect tutor in 3 steps
                    </p>
                    <p class="mt-6 text-lg leading-8 text-slate-600">
                        We've simplified the process to help you get started
                        quickly and securely.
                    </p>
                </div>

                <div
                    class="mx-auto mt-16 grid max-w-2xl grid-cols-1 gap-8 sm:mt-20 lg:mx-0 lg:max-w-none lg:grid-cols-3"
                >
                    <!-- Step 1 -->
                    <div
                        class="relative rounded-3xl bg-white p-8 shadow-2xl ring-1 shadow-slate-200/50 ring-slate-200 transition-shadow hover:shadow-xl hover:shadow-slate-200/60"
                    >
                        <div
                            class="mb-6 flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-900 text-white shadow-lg shadow-slate-900/20"
                        >
                            <span class="text-xl font-bold">1</span>
                        </div>
                        <h3 class="text-xl font-bold text-slate-900">
                            Post Your Needs
                        </h3>
                        <p
                            class="mt-4 text-base leading-relaxed text-slate-600"
                        >
                            Tell us what you're looking for—subject, location,
                            schedule, and any specific requirements for your
                            child.
                        </p>
                    </div>

                    <!-- Step 2 -->
                    <div
                        class="relative rounded-3xl bg-white p-8 shadow-2xl ring-1 shadow-slate-200/50 ring-slate-200 transition-shadow hover:shadow-xl hover:shadow-slate-200/60"
                    >
                        <div
                            class="mb-6 flex h-14 w-14 items-center justify-center rounded-2xl bg-blue-600 text-white shadow-lg shadow-blue-600/20"
                        >
                            <span class="text-xl font-bold">2</span>
                        </div>
                        <h3 class="text-xl font-bold text-slate-900">
                            Get Matched
                        </h3>
                        <p
                            class="mt-4 text-base leading-relaxed text-slate-600"
                        >
                            Receive applications from verified tutors. Review
                            their profiles, ratings, and experience to choose
                            the best fit.
                        </p>
                    </div>

                    <!-- Step 3 -->
                    <div
                        class="relative rounded-3xl bg-white p-8 shadow-2xl ring-1 shadow-slate-200/50 ring-slate-200 transition-shadow hover:shadow-xl hover:shadow-slate-200/60"
                    >
                        <div
                            class="mb-6 flex h-14 w-14 items-center justify-center rounded-2xl bg-white text-slate-900 shadow-lg ring-1 ring-slate-200"
                        >
                            <span class="text-xl font-bold">3</span>
                        </div>
                        <h3 class="text-xl font-bold text-slate-900">
                            Start Learning
                        </h3>
                        <p
                            class="mt-4 text-base leading-relaxed text-slate-600"
                        >
                            Connect with your tutor, schedule your first
                            session, and watch the progress happen. It's that
                            easy.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Tuition Methods -->
        <section class="bg-slate-50 py-24 sm:py-32">
            <div class="mx-auto max-w-7xl px-6 lg:px-8">
                <div class="mx-auto max-w-2xl text-center">
                    <h2
                        class="text-base leading-7 font-semibold tracking-widest text-blue-600 uppercase"
                    >
                        Tuition Methods
                    </h2>
                    <p
                        class="mt-2 text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl"
                    >
                        Choose your preferred learning style
                    </p>
                    <p class="mt-6 text-lg leading-8 text-slate-600">
                        Explore currently active tuition categories and pick
                        what matches your learning goals.
                    </p>
                </div>

                <div
                    v-if="props.tuitionMethods.length"
                    class="mx-auto mt-16 grid max-w-2xl grid-cols-1 gap-6 sm:mt-20 lg:mx-0 lg:max-w-none lg:grid-cols-3 lg:gap-8"
                >
                    <div
                        v-for="(method, index) in props.tuitionMethods"
                        :key="method.id"
                        class="flex flex-col rounded-3xl bg-white p-8 shadow-sm ring-1 ring-slate-200 transition-all hover:-translate-y-1 hover:shadow-md"
                    >
                        <dt
                            class="flex items-center gap-x-3 text-base leading-7 font-semibold text-slate-900"
                        >
                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-lg"
                                :class="
                                    tuitionMethodDecoration(index)
                                        .iconWrapperClass
                                "
                            >
                                <component
                                    :is="tuitionMethodDecoration(index).icon"
                                    class="h-6 w-6 text-white"
                                    aria-hidden="true"
                                />
                            </div>
                            {{ method.name }}
                        </dt>
                        <dd
                            class="mt-4 flex flex-auto flex-col text-base leading-7 text-slate-600"
                        >
                            <p class="flex-auto">
                                {{
                                    method.description ||
                                    'Browse available tutors and jobs under this category.'
                                }}
                            </p>
                            <p class="mt-6">
                                <Link
                                    :href="
                                        jobs({
                                            query: {
                                                category: method.slug,
                                            },
                                        })
                                    "
                                    class="text-sm leading-6 font-semibold text-blue-600 hover:text-blue-500"
                                >
                                    Explore category
                                    <ArrowRight class="inline-block h-4 w-4" />
                                </Link>
                            </p>
                        </dd>
                    </div>
                </div>

                <div
                    v-else
                    class="mx-auto mt-16 max-w-3xl rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-12 text-center"
                >
                    <p class="text-sm text-slate-500">
                        Tuition categories are not available right now.
                    </p>
                </div>
            </div>
        </section>

        <!-- Testimonials -->
        <section class="bg-white py-14 md:py-20">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="text-center">
                    <p
                        class="text-sm font-medium tracking-widest text-blue-600 uppercase"
                    >
                        Testimonials
                    </p>
                    <h2
                        class="mt-2 text-3xl font-semibold tracking-tight text-gray-900 sm:text-4xl"
                    >
                        What Parents Say
                    </h2>
                    <p class="mx-auto mt-3 max-w-2xl text-sm text-slate-500">
                        Hear from satisfied parents and guardians
                    </p>
                </div>
                <HomeTestimonialsCarousel
                    v-if="props.testimonials.length"
                    :testimonials="props.testimonials"
                />
                <div
                    v-else
                    class="mt-12 rounded-2xl border border-dashed border-slate-300 px-6 py-12 text-center"
                >
                    <p class="text-sm text-slate-500">
                        Testimonials will appear here once published.
                    </p>
                </div>
            </div>
        </section>

        <!-- Final CTA -->
        <section class="bg-slate-50 py-14 md:py-20">
            <div class="mx-auto max-w-7xl px-4 sm:px-6">
                <div
                    class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-blue-700 to-blue-600 px-6 py-10 text-center shadow-lg sm:px-8 md:px-12 md:py-14"
                >
                    <div
                        class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(255,255,255,0.25),transparent_45%)]"
                    ></div>
                    <h2
                        class="relative text-3xl font-bold tracking-tight text-white sm:text-4xl"
                    >
                        Start Your Learning Journey Today
                    </h2>
                    <p
                        class="relative mx-auto mt-4 max-w-xl text-base text-blue-100"
                    >
                        Join thousands of students and tutors already using
                        {{ siteName }}
                    </p>
                    <div
                        class="relative mt-8 flex flex-col gap-3 sm:flex-row sm:justify-center"
                    >
                        <Link
                            :href="register()"
                            class="inline-flex h-11 items-center justify-center rounded-xl bg-white px-6 py-3 text-base font-semibold text-blue-700 shadow-sm transition-all duration-200 hover:bg-blue-50 focus-visible:ring-2 focus-visible:ring-white focus-visible:ring-offset-2 focus-visible:ring-offset-blue-600 active:scale-[0.98]"
                        >
                            Register
                        </Link>
                        <Link
                            :href="jobs()"
                            class="inline-flex h-11 items-center justify-center rounded-xl border-2 border-white/90 px-6 py-3 text-base font-semibold text-white transition-all duration-200 hover:bg-white/10 focus-visible:ring-2 focus-visible:ring-white focus-visible:ring-offset-2 focus-visible:ring-offset-blue-600 active:scale-[0.98]"
                        >
                            Browse Jobs
                        </Link>
                    </div>
                </div>
            </div>
        </section>
    </PublicLayout>
</template>
