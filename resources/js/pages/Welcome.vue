<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { dashboard, login, register } from '@/routes';

withDefaults(
    defineProps<{
        canRegister: boolean;
    }>(),
    {
        canRegister: true,
    },
);
</script>

<template>
    <Head title="Welcome">
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    </Head>

    <div class="min-h-screen bg-slate-50 text-slate-900">
        <!-- Top bar -->
        <div class="hidden sm:block bg-white">
            <div
                class="mx-auto max-w-7xl px-4 py-2 flex items-center justify-between text-sm text-slate-600"
            >
                <div class="flex items-center gap-4">
                    <span class="inline-flex items-center gap-2">
                        <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                        Available today
                    </span>
                    <span>📞 +1 (000) 000-0000</span>
                    <span>✉️ hello@tutorfinder.com</span>
                </div>
                <div class="flex items-center gap-4">
                    <!-- Changed FAQ -> Blog -->
                    <a class="hover:text-slate-900" href="#blog">Blog</a>
                    <a class="hover:text-slate-900" href="#contact">Support</a>
                </div>
            </div>
        </div>

        <!-- Navbar -->
        <div
            class="bg-white sticky top-0 z-50 border-b border-slate-100"
        >
            <div class="mx-auto max-w-7xl px-4 py-4 flex items-center justify-between">
                <!-- Brand updated -->
                <a href="#home" class="flex items-center gap-2">
                    <div class="h-9 w-9 rounded-lg bg-blue-600 grid place-items-center text-white font-bold">
                        T
                    </div>
                    <div class="leading-tight">
                        <div class="font-semibold">Tutor Finder</div>
                        <div class="text-xs text-slate-500 -mt-0.5">
                            Tutors • Students • Parents
                        </div>
                    </div>
                </a>

                <nav class="hidden md:flex items-center gap-7 text-sm text-slate-700">
                    <a class="hover:text-blue-700" href="#home">Home</a>
                    <a class="hover:text-blue-700" href="#about">About</a>

                    <!-- Removed "Services" menu item -->
                    <!-- Replaced "Testimonials" -> "Job Board" -->
                    <a class="hover:text-blue-700" href="#jobs">Job Board</a>

                    <a class="hover:text-blue-700" href="#contact">Contact</a>
                </nav>

                <div class="flex items-center gap-3">
                    <Link
                        v-if="$page.props.auth.user"
                        :href="dashboard()"
                        class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700 transition"
                    >
                        Dashboard
                    </Link>
                    <template v-else>
                        <Link
                            :href="login()"
                            class="hidden sm:inline-flex text-sm font-medium text-blue-700 hover:text-blue-800"
                        >
                            Login
                        </Link>
                        <Link
                            v-if="canRegister"
                            :href="register()"
                            class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700 transition"
                        >
                            Join Now
                        </Link>
                    </template>
                </div>
            </div>
        </div>

        <!-- Hero -->
        <section id="home" class="relative overflow-hidden">
            <div class="mx-auto max-w-7xl px-4 pt-14 pb-10">
                <div class="grid lg:grid-cols-2 gap-10 items-center">
                    <div>
                        <p
                            class="inline-flex items-center gap-2 rounded-full bg-blue-50 px-4 py-1.5 text-sm text-blue-700"
                        >
                            <span class="h-2 w-2 rounded-full bg-blue-600"></span>
                            Find your best tutor in minutes
                        </p>

                        <h1 class="mt-5 text-4xl sm:text-5xl font-extrabold leading-tight">
                            Find The Best <span class="text-blue-600">Tutor</span> Today
                        </h1>

                        <p class="mt-4 text-slate-600 max-w-xl">
                            Match with verified tutors for online or in-person learning. Easy registration,
                            quick bookings, and progress tracking for guardians and students.
                        </p>

                        <div class="mt-7 flex flex-col sm:flex-row gap-3">
                            <a
                                href="#"
                                class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-6 py-3 text-white font-semibold hover:bg-blue-700 transition"
                            >
                                Find a Tutor
                            </a>
                            <a
                                href="#jobs"
                                class="inline-flex items-center justify-center rounded-lg bg-white px-6 py-3 font-semibold text-slate-800 border border-slate-200 hover:border-slate-300 transition"
                            >
                                View Job Board
                            </a>
                        </div>

                        <div class="mt-7 flex items-center gap-6 text-sm text-slate-600">
                            <div class="flex items-center gap-2">
                                <span class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-blue-50 text-blue-700">✓</span>
                                Verified tutors
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-blue-50 text-blue-700">★</span>
                                Rated by families
                            </div>
                        </div>
                    </div>

                    <!-- Hero visual -->
                    <div class="relative">
                        <div class="absolute -top-12 -right-14 h-48 w-48 rounded-full bg-blue-100 blur-2xl"></div>
                        <div class="absolute -bottom-16 -left-10 h-56 w-56 rounded-full bg-sky-100 blur-2xl"></div>

                        <div class="relative rounded-2xl bg-white p-6 border border-slate-100 shadow-[0_10px_30px_rgba(2,32,71,0.08)]">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm text-slate-500">Featured tutors</p>
                                    <p class="text-lg font-semibold">Top matches for you</p>
                                </div>
                                <span class="rounded-full bg-blue-50 px-3 py-1 text-sm text-blue-700 font-medium">Live</span>
                            </div>

                            <div class="mt-6 grid grid-cols-3 gap-3">
                                <div class="aspect-square rounded-xl bg-gradient-to-br from-blue-50 to-slate-100 grid place-items-center text-slate-400">
                                    Photo
                                </div>
                                <div class="aspect-square rounded-xl bg-gradient-to-br from-blue-50 to-slate-100 grid place-items-center text-slate-400">
                                    Photo
                                </div>
                                <div class="aspect-square rounded-xl bg-gradient-to-br from-blue-50 to-slate-100 grid place-items-center text-slate-400">
                                    Photo
                                </div>
                            </div>

                            <div class="mt-5 rounded-xl bg-slate-50 p-4 border border-slate-100">
                                <div class="flex items-center justify-between">
                                    <p class="font-semibold">Quick match</p>
                                    <p class="text-sm text-slate-500">~2 mins</p>
                                </div>
                                <p class="mt-1 text-sm text-slate-600">
                                    Tell us grade, subject, and schedule—get tutor suggestions instantly.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Stats strip -->
                <div class="mt-10">
                    <div
                        class="bg-white border border-slate-100 rounded-2xl p-6 shadow-[0_10px_30px_rgba(2,32,71,0.08)]"
                    >
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                            <div class="flex items-start gap-3">
                                <div class="h-10 w-10 rounded-lg bg-blue-50 grid place-items-center text-blue-700 font-bold">📘</div>
                                <div>
                                    <div class="text-xl font-extrabold">190+</div>
                                    <div class="text-sm text-slate-500">Subjects</div>
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <div class="h-10 w-10 rounded-lg bg-emerald-50 grid place-items-center text-emerald-700 font-bold">👩‍🏫</div>
                                <div>
                                    <div class="text-xl font-extrabold">3,000+</div>
                                    <div class="text-sm text-slate-500">Tutors</div>
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <div class="h-10 w-10 rounded-lg bg-amber-50 grid place-items-center text-amber-700 font-bold">🎓</div>
                                <div>
                                    <div class="text-xl font-extrabold">12k+</div>
                                    <div class="text-sm text-slate-500">Students helped</div>
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <div class="h-10 w-10 rounded-lg bg-sky-50 grid place-items-center text-sky-700 font-bold">⭐</div>
                                <div>
                                    <div class="text-xl font-extrabold">4.7</div>
                                    <div class="text-sm text-slate-500">Avg rating</div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6 flex flex-wrap gap-2">
                            <span class="rounded-full bg-blue-600 text-white px-4 py-1.5 text-sm font-medium">For Students</span>
                            <span class="rounded-full bg-slate-100 text-slate-700 px-4 py-1.5 text-sm font-medium">For Guardians</span>
                            <span class="rounded-full bg-slate-100 text-slate-700 px-4 py-1.5 text-sm font-medium">For Tutors</span>
                            <span class="rounded-full bg-slate-100 text-slate-700 px-4 py-1.5 text-sm font-medium">How it works</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- About -->
        <section id="about" class="py-16">
            <div class="mx-auto max-w-7xl px-4">
                <div class="text-center max-w-2xl mx-auto">
                    <p class="text-blue-600 font-semibold">About</p>
                    <h2 class="mt-2 text-3xl font-extrabold">About Tutor Finder</h2>
                    <p class="mt-4 text-slate-600">
                        Tutor Finder connects learners with trusted tutors for personalized support—homework help,
                        exam prep, and skill building. Track progress, schedule sessions, and learn with confidence.
                    </p>

                    <div class="mt-6 flex justify-center gap-3">
                        <a href="#jobs" class="rounded-lg bg-blue-600 px-5 py-2.5 text-white font-semibold hover:bg-blue-700">
                            Browse Jobs
                        </a>
                        <a
                            href="#contact"
                            class="rounded-lg bg-white px-5 py-2.5 font-semibold border border-slate-200 hover:border-slate-300"
                        >
                            Contact Us
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- Tuition Methods -->
        <section class="py-16 bg-gradient-to-b from-slate-50 to-blue-50/30">
            <div class="mx-auto max-w-7xl px-4">
                <div class="text-center">
                    <p class="text-blue-600 font-semibold">Tuition Methods</p>
                    <h2 class="mt-2 text-3xl font-extrabold">Learn your way</h2>
                    <p class="mt-3 text-slate-600 max-w-2xl mx-auto">
                        Pick the format that fits your schedule and comfort.
                    </p>
                </div>

                <div class="mt-10 grid md:grid-cols-3 gap-6">
                    <div class="bg-white border border-slate-100 rounded-2xl p-6 shadow-[0_10px_30px_rgba(2,32,71,0.08)]">
                        <div class="h-11 w-11 rounded-xl bg-blue-50 grid place-items-center text-blue-700">🏠</div>
                        <h3 class="mt-4 font-bold text-lg">Home Tutoring</h3>
                        <p class="mt-2 text-sm text-slate-600">Face-to-face sessions at your home for focused learning.</p>
                    </div>

                    <div class="bg-white border border-slate-100 rounded-2xl p-6 shadow-[0_10px_30px_rgba(2,32,71,0.08)]">
                        <div class="h-11 w-11 rounded-xl bg-emerald-50 grid place-items-center text-emerald-700">💻</div>
                        <h3 class="mt-4 font-bold text-lg">Online Tutoring</h3>
                        <p class="mt-2 text-sm text-slate-600">Live video classes, whiteboard, and shared resources.</p>
                    </div>

                    <div class="bg-white border border-slate-100 rounded-2xl p-6 shadow-[0_10px_30px_rgba(2,32,71,0.08)]">
                        <div class="h-11 w-11 rounded-xl bg-amber-50 grid place-items-center text-amber-700">🏫</div>
                        <h3 class="mt-4 font-bold text-lg">Institute Tutoring</h3>
                        <p class="mt-2 text-sm text-slate-600">Structured sessions in a dedicated learning environment.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Job Board (replaces Testimonials section) -->
        <section id="jobs" class="py-16">
            <div class="mx-auto max-w-7xl px-4">
                <div class="text-center">
                    <p class="text-blue-600 font-semibold">Job Board</p>
                    <h2 class="mt-2 text-3xl font-extrabold">Find tutoring opportunities</h2>
                    <p class="mt-3 text-slate-600 max-w-2xl mx-auto">
                        Browse new tutoring requests from families. Apply quickly and start teaching.
                    </p>
                </div>

                <div class="mt-10 grid lg:grid-cols-3 gap-6">
                    <div class="bg-white border border-slate-100 rounded-2xl p-6 shadow-[0_10px_30px_rgba(2,32,71,0.08)]">
                        <div class="flex items-center justify-between">
                            <p class="font-bold">Math Tutor (Grade 8)</p>
                            <span class="text-xs rounded-full bg-blue-50 text-blue-700 px-2 py-1 font-medium">New</span>
                        </div>
                        <p class="mt-2 text-sm text-slate-600">2x per week • Online • Evening</p>
                        <div class="mt-4 flex items-center gap-2 text-xs text-slate-500">
                            <span class="rounded-full bg-slate-100 px-2 py-1">Algebra</span>
                            <span class="rounded-full bg-slate-100 px-2 py-1">Homework</span>
                        </div>
                        <a href="#" class="mt-5 inline-flex font-semibold text-blue-700 hover:text-blue-800">
                            View details →
                        </a>
                    </div>

                    <div class="bg-white border border-slate-100 rounded-2xl p-6 shadow-[0_10px_30px_rgba(2,32,71,0.08)]">
                        <div class="flex items-center justify-between">
                            <p class="font-bold">English Writing Coach</p>
                            <span class="text-xs rounded-full bg-emerald-50 text-emerald-700 px-2 py-1 font-medium">
                                Verified family
                            </span>
                        </div>
                        <p class="mt-2 text-sm text-slate-600">1x per week • In-person • Weekend</p>
                        <div class="mt-4 flex items-center gap-2 text-xs text-slate-500">
                            <span class="rounded-full bg-slate-100 px-2 py-1">Essay</span>
                            <span class="rounded-full bg-slate-100 px-2 py-1">Grammar</span>
                        </div>
                        <a href="#" class="mt-5 inline-flex font-semibold text-blue-700 hover:text-blue-800">
                            View details →
                        </a>
                    </div>

                    <div class="bg-white border border-slate-100 rounded-2xl p-6 shadow-[0_10px_30px_rgba(2,32,71,0.08)]">
                        <div class="flex items-center justify-between">
                            <p class="font-bold">Science Exam Prep</p>
                            <span class="text-xs rounded-full bg-amber-50 text-amber-700 px-2 py-1 font-medium">
                                Urgent
                            </span>
                        </div>
                        <p class="mt-2 text-sm text-slate-600">3 sessions • Online • Flexible</p>
                        <div class="mt-4 flex items-center gap-2 text-xs text-slate-500">
                            <span class="rounded-full bg-slate-100 px-2 py-1">Biology</span>
                            <span class="rounded-full bg-slate-100 px-2 py-1">Mock tests</span>
                        </div>
                        <a href="#" class="mt-5 inline-flex font-semibold text-blue-700 hover:text-blue-800">
                            View details →
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- How it works for Students/Guardians -->
        <section class="py-16 bg-blue-50/50">
            <div class="mx-auto max-w-7xl px-4">
                <div class="text-center">
                    <p class="text-blue-600 font-semibold">How it works</p>
                    <h2 class="mt-2 text-3xl font-extrabold">For Guardians & Students</h2>
                    <p class="mt-3 text-slate-600 max-w-2xl mx-auto">Simple steps to start learning.</p>
                </div>

                <div class="mt-10 grid lg:grid-cols-2 gap-10 items-start">
                    <div class="relative">
                        <div class="absolute left-4 top-0 bottom-0 w-px bg-slate-200"></div>

                        <div class="relative pl-12 pb-8">
                            <div class="absolute left-2 top-1 h-5 w-5 rounded-full bg-blue-600 border-4 border-blue-100"></div>
                            <div class="bg-white border border-slate-100 rounded-2xl p-5 shadow-[0_10px_30px_rgba(2,32,71,0.08)]">
                                <p class="font-bold">Step 1</p>
                                <p class="mt-1 text-sm text-slate-600">Create an account and complete your student profile.</p>
                            </div>
                        </div>

                        <div class="relative pl-12 pb-8">
                            <div class="absolute left-2 top-1 h-5 w-5 rounded-full bg-blue-600 border-4 border-blue-100"></div>
                            <div class="bg-white border border-slate-100 rounded-2xl p-5 shadow-[0_10px_30px_rgba(2,32,71,0.08)]">
                                <p class="font-bold">Step 2</p>
                                <p class="mt-1 text-sm text-slate-600">Search tutors by subject, grade, and availability.</p>
                            </div>
                        </div>

                        <div class="relative pl-12 pb-2">
                            <div class="absolute left-2 top-1 h-5 w-5 rounded-full bg-blue-600 border-4 border-blue-100"></div>
                            <div class="bg-white border border-slate-100 rounded-2xl p-5 shadow-[0_10px_30px_rgba(2,32,71,0.08)]">
                                <p class="font-bold">Step 3</p>
                                <p class="mt-1 text-sm text-slate-600">Book sessions and track learning progress.</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white border border-slate-100 rounded-2xl p-6 shadow-[0_10px_30px_rgba(2,32,71,0.08)]">
                        <p class="text-sm text-slate-500">Preview</p>
                        <h3 class="mt-1 font-extrabold text-xl">Your dashboard</h3>
                        <p class="mt-2 text-sm text-slate-600">
                            Manage sessions, payments, and tutor communication in one place.
                        </p>

                        <div class="mt-5 rounded-2xl bg-slate-50 border border-slate-100 p-5">
                            <div class="flex items-center justify-between">
                                <p class="font-semibold">Next session</p>
                                <span class="text-xs rounded-full bg-blue-50 text-blue-700 px-2 py-1 font-medium">Today</span>
                            </div>
                            <div class="mt-3 grid grid-cols-2 gap-3">
                                <div class="rounded-xl bg-white border border-slate-100 p-3">
                                    <p class="text-xs text-slate-500">Subject</p>
                                    <p class="font-semibold">Math</p>
                                </div>
                                <div class="rounded-xl bg-white border border-slate-100 p-3">
                                    <p class="text-xs text-slate-500">Time</p>
                                    <p class="font-semibold">5:00 PM</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Why Choose Us -->
        <section class="py-16 bg-gradient-to-b from-blue-950 to-slate-900 text-white">
            <div class="mx-auto max-w-7xl px-4">
                <div class="text-center">
                    <p class="text-blue-200 font-semibold">Why Choose Us?</p>
                    <h2 class="mt-2 text-3xl font-extrabold">Trusted tutoring made simple</h2>
                    <p class="mt-3 text-blue-100/80 max-w-2xl mx-auto">
                        A safe, reliable experience for families and tutors.
                    </p>
                </div>

                <div class="mt-10 grid md:grid-cols-3 gap-6">
                    <div class="bg-white/10 border border-white/10 rounded-2xl p-6">
                        <div class="h-11 w-11 rounded-xl bg-white/10 grid place-items-center">✅</div>
                        <h3 class="mt-4 font-bold">Verified & Experienced</h3>
                        <p class="mt-2 text-sm text-blue-100/80">We verify tutors and highlight proven teaching experience.</p>
                    </div>

                    <div class="bg-white/10 border border-white/10 rounded-2xl p-6">
                        <div class="h-11 w-11 rounded-xl bg-white/10 grid place-items-center">🔒</div>
                        <h3 class="mt-4 font-bold">Secure & Trustworthy</h3>
                        <p class="mt-2 text-sm text-blue-100/80">Protected profiles, reliable scheduling, and clear policies.</p>
                    </div>

                    <div class="bg-white/10 border border-white/10 rounded-2xl p-6">
                        <div class="h-11 w-11 rounded-xl bg-white/10 grid place-items-center">⚡</div>
                        <h3 class="mt-4 font-bold">Fast & Reliable</h3>
                        <p class="mt-2 text-sm text-blue-100/80">Get matched quickly with tutors who fit your needs.</p>
                    </div>

                    <div class="bg-white/10 border border-white/10 rounded-2xl p-6">
                        <div class="h-11 w-11 rounded-xl bg-white/10 grid place-items-center">🎯</div>
                        <h3 class="mt-4 font-bold">Customized Plans</h3>
                        <p class="mt-2 text-sm text-blue-100/80">Learning goals and session plans tailored to the student.</p>
                    </div>

                    <div class="bg-white/10 border border-white/10 rounded-2xl p-6">
                        <div class="h-11 w-11 rounded-xl bg-white/10 grid place-items-center">🤝</div>
                        <h3 class="mt-4 font-bold">Helpful Support</h3>
                        <p class="mt-2 text-sm text-blue-100/80">We help with setup, tutor selection, and scheduling tips.</p>
                    </div>

                    <div class="bg-white/10 border border-white/10 rounded-2xl p-6">
                        <div class="h-11 w-11 rounded-xl bg-white/10 grid place-items-center">📈</div>
                        <h3 class="mt-4 font-bold">Progress Monitoring</h3>
                        <p class="mt-2 text-sm text-blue-100/80">Track improvement over time with consistent feedback.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA -->
        <section class="py-16">
            <div class="mx-auto max-w-7xl px-4">
                <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-blue-600 to-sky-500 shadow-[0_10px_30px_rgba(2,32,71,0.08)]">
                    <div class="absolute -top-24 -right-24 h-72 w-72 rounded-full bg-white/20 blur-2xl"></div>
                    <div class="absolute -bottom-24 -left-24 h-72 w-72 rounded-full bg-white/20 blur-2xl"></div>

                    <div class="relative p-10 md:p-14 text-center text-white">
                        <h2 class="text-3xl md:text-4xl font-extrabold">Start your journey to effective learning</h2>
                        <p class="mt-3 text-white/90 max-w-2xl mx-auto">
                            Register in minutes and match with the right tutor for your goals.
                        </p>
                        <div class="mt-7 flex flex-col sm:flex-row justify-center gap-3">
                            <Link
                                v-if="canRegister"
                                :href="register()"
                                class="rounded-lg bg-white px-6 py-3 font-semibold text-blue-700 hover:bg-blue-50 transition"
                            >
                                Register
                            </Link>
                            <a
                                href="#jobs"
                                class="rounded-lg bg-blue-900/30 px-6 py-3 font-semibold text-white border border-white/30 hover:bg-blue-900/40 transition"
                            >
                                Browse Job Board
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Blog (simple section since you added Blog in top bar) -->
        <section id="blog" class="py-16 bg-white">
            <div class="mx-auto max-w-7xl px-4">
                <div class="text-center">
                    <p class="text-blue-600 font-semibold">Blog</p>
                    <h2 class="mt-2 text-3xl font-extrabold">Tips & learning resources</h2>
                    <p class="mt-3 text-slate-600 max-w-2xl mx-auto">
                        Short posts to help students learn smarter and tutors teach better.
                    </p>
                </div>

                <div class="mt-10 grid md:grid-cols-3 gap-6">
                    <article class="rounded-2xl border border-slate-100 bg-slate-50 p-6">
                        <p class="text-xs text-slate-500">Study</p>
                        <h3 class="mt-2 font-bold">How to build a weekly study plan</h3>
                        <p class="mt-2 text-sm text-slate-600">
                            A simple routine that actually sticks—without burnout.
                        </p>
                        <a href="#" class="mt-4 inline-flex font-semibold text-blue-700 hover:text-blue-800">Read more →</a>
                    </article>
                    <article class="rounded-2xl border border-slate-100 bg-slate-50 p-6">
                        <p class="text-xs text-slate-500">Tutoring</p>
                        <h3 class="mt-2 font-bold">How to run an effective first lesson</h3>
                        <p class="mt-2 text-sm text-slate-600">
                            Set goals, assess level, and build trust fast.
                        </p>
                        <a href="#" class="mt-4 inline-flex font-semibold text-blue-700 hover:text-blue-800">Read more →</a>
                    </article>
                    <article class="rounded-2xl border border-slate-100 bg-slate-50 p-6">
                        <p class="text-xs text-slate-500">Exams</p>
                        <h3 class="mt-2 font-bold">Exam prep: what to do 7 days out</h3>
                        <p class="mt-2 text-sm text-slate-600">
                            A checklist for confidence and calm before test day.
                        </p>
                        <a href="#" class="mt-4 inline-flex font-semibold text-blue-700 hover:text-blue-800">Read more →</a>
                    </article>
                </div>
            </div>
        </section>

        <!-- Footer (Newsletter removed) -->
        <footer id="contact" class="bg-slate-900 text-slate-200">
            <div class="mx-auto max-w-7xl px-4 py-12">
                <div class="grid md:grid-cols-3 gap-8">
                    <div>
                        <div class="flex items-center gap-2">
                            <div class="h-9 w-9 rounded-lg bg-blue-600 grid place-items-center text-white font-bold">
                                T
                            </div>
                            <div class="font-semibold">Tutor Finder</div>
                        </div>
                        <p class="mt-3 text-sm text-slate-400">
                            Connecting students with tutors for better outcomes.
                        </p>
                    </div>

                    <div>
                        <p class="font-semibold">Quick Links</p>
                        <ul class="mt-3 space-y-2 text-sm text-slate-400">
                            <li><a class="hover:text-white" href="#about">About</a></li>
                            <li><a class="hover:text-white" href="#jobs">Job Board</a></li>
                            <li><a class="hover:text-white" href="#blog">Blog</a></li>
                        </ul>
                    </div>

                    <div>
                        <p class="font-semibold">Contact</p>
                        <ul class="mt-3 space-y-2 text-sm text-slate-400">
                            <li>📍 Your City, Country</li>
                            <li>📞 +1 (000) 000-0000</li>
                            <li>✉️ hello@yourdomain.com</li>
                        </ul>
                    </div>
                </div>

                <div
                    class="mt-10 pt-6 border-t border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-3 text-sm text-slate-500"
                >
                    <p>© {{ new Date().getFullYear() }} Tutor Finder. All rights reserved.</p>
                    <div class="flex items-center gap-4">
                        <a class="hover:text-slate-300" href="#">Privacy</a>
                        <a class="hover:text-slate-300" href="#">Terms</a>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</template>
