<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowRight, CheckCircle2, ShieldCheck, Star } from 'lucide-vue-next';
import { jobs, tutors } from '@/routes';
</script>

<template>
    <section
        class="relative overflow-hidden bg-[#f4faff] py-10 sm:py-16 lg:py-24"
    >
        <!-- Hero Section Background Image Layer -->
        <div
            class="pointer-events-none absolute inset-0 -z-10 bg-cover bg-center bg-no-repeat opacity-85 sm:opacity-90"
            style="background-image: url('/images/home-page-background.png')"
        ></div>

        <!-- Ambient Mesh Radial Background Glow -->
        <div
            class="pointer-events-none absolute -top-24 left-1/2 -z-10 h-[400px] w-[600px] -translate-x-1/2 rounded-full bg-radial from-cyan-200/50 via-blue-100/20 to-transparent opacity-70 blur-3xl sm:h-[550px] sm:w-[900px]"
        ></div>

        <!-- Dot Matrix Geometric Grid Background Pattern -->
        <div
            class="pointer-events-none absolute inset-0 -z-10 bg-[radial-gradient(#cbd5e1_1px,transparent_1px)] [background-size:20px_20px] opacity-40 sm:[background-size:24px_24px]"
        ></div>

        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div
                class="grid grid-cols-1 items-center gap-8 sm:gap-12 lg:grid-cols-12 lg:gap-8"
            >
                <!-- Left Column: Hero Image & Glassmorphic Badges -->
                <div class="relative flex justify-center lg:col-span-6">
                    <div class="relative w-full max-w-lg lg:max-w-xl">
                        <!-- Soft Gradient Ambient Circle Behind Hero Image -->
                        <div
                            class="absolute top-1/2 left-1/2 -z-10 h-64 w-64 -translate-x-1/2 -translate-y-1/2 rounded-full bg-gradient-to-tr from-[#0eb0e6]/25 to-blue-600/15 blur-3xl sm:h-[420px] sm:w-[420px]"
                        ></div>

                        <!-- Floating Glassmorphic Badge 1: Verified Tutors (Top Left) -->
                        <!-- <div
                            class="absolute -top-3 -left-2 z-20 hidden items-center gap-2.5 rounded-2xl border border-white/80 bg-white/90 px-4 py-2.5 shadow-xl backdrop-blur-md transition-transform duration-300 hover:scale-105 sm:flex"
                        >
                            <div
                                class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-600 ring-1 ring-emerald-500/20"
                            >
                                <ShieldCheck class="h-5 w-5" />
                            </div>
                            <div class="flex flex-col leading-tight">
                                <span class="text-xs font-bold text-slate-900"
                                    >Verified Tutors</span
                                >
                                <span
                                    class="text-[10px] font-medium text-slate-500"
                                    >100% Background Checked</span
                                >
                            </div>
                        </div> -->

                        <!-- Floating Glassmorphic Badge 2: Rating 5.0 (Bottom Right) -->
                        <!-- <div
                            class="absolute -right-2 -bottom-4 z-20 hidden items-center gap-2.5 rounded-2xl border border-white/80 bg-white/90 px-4 py-2.5 shadow-xl backdrop-blur-md transition-transform duration-300 hover:scale-105 sm:flex">
                            <div
                                class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-500/10 text-amber-500 ring-1 ring-amber-500/20">
                                <Star class="h-5 w-5 fill-amber-400" />
                            </div>
                            <div class="flex flex-col leading-tight">
                                <span class="text-xs font-bold text-slate-900">5.0 Star Rating</span>
                                <span class="text-[10px] font-medium text-slate-500">Over 1,500+ Reviews</span>
                            </div>
                        </div> -->

                        <!-- Hero Section Photo Image -->
                        <img
                            src="/images/home-page-photo.png"
                            alt="Tutor Finder Hero Photo"
                            class="relative z-10 mx-auto h-auto w-full max-w-md object-contain drop-shadow-2xl transition-transform duration-500 hover:scale-102 sm:max-w-lg"
                        />
                    </div>
                </div>

                <!-- Right Column: Text & CTAs -->
                <div
                    class="flex flex-col items-center text-center lg:col-span-6 lg:items-start lg:text-left"
                >
                    <!-- Pill Badge -->
                    <div
                        class="mb-4 inline-flex items-center gap-2.5 rounded-full border border-[#0eb0e6]/40 bg-white/90 px-3.5 py-1.5 text-[11px] font-extrabold text-[#008cc2] shadow-xs backdrop-blur-md transition-all hover:border-[#0eb0e6] hover:bg-cyan-50/50 sm:mb-6 sm:px-4 sm:text-xs"
                    >
                        <span
                            class="flex h-2 w-2 animate-pulse rounded-full bg-emerald-500"
                        ></span>
                        <span class="tracking-wide"
                            >#1 Trusted Tutoring Platform in Bangladesh</span
                        >
                    </div>

                    <!-- Main Headline -->
                    <h1
                        class="text-3xl font-extrabold tracking-tight text-[#1b2880] sm:text-5xl lg:text-6xl/tight"
                    >
                        Hire the Right Tutor
                        <br class="hidden sm:inline" />
                        <span
                            class="bg-gradient-to-r from-[#0eb0e6] via-[#0096c7] to-[#0077b6] bg-clip-text text-transparent"
                        >
                            with Confidence
                        </span>
                    </h1>

                    <!-- Subtitle -->
                    <p
                        class="mt-4 max-w-xl text-sm leading-relaxed text-slate-600 sm:mt-5 sm:text-lg lg:text-xl/relaxed"
                    >
                        Quickly match with competent tutors tailored to your
                        child's need to succeed.
                    </p>

                    <!-- Feature Checkmarks -->
                    <div
                        class="mt-5 flex flex-wrap justify-center gap-x-5 gap-y-2 text-xs font-semibold text-slate-700 sm:mt-6 lg:justify-start"
                    >
                        <div class="flex items-center gap-1.5">
                            <CheckCircle2 class="h-4 w-4 text-[#0eb0e6]" />
                            <span>100% Verified Tutors</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <CheckCircle2 class="h-4 w-4 text-[#0eb0e6]" />
                            <span>Zero Booking Fees</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <CheckCircle2 class="h-4 w-4 text-[#0eb0e6]" />
                            <span>Fast Match Guarantee</span>
                        </div>
                    </div>

                    <!-- Responsive CTA Buttons -->
                    <div
                        class="mt-7 flex w-full flex-col items-stretch gap-3 sm:mt-9 sm:w-auto sm:flex-row sm:items-center sm:gap-5"
                    >
                        <Link
                            :href="tutors()"
                            class="group inline-flex h-12 w-full items-center justify-center gap-2 rounded-full bg-gradient-to-r from-[#0eb0e6] to-[#0096c7] px-8 text-sm font-bold text-white shadow-lg shadow-[#0eb0e6]/30 transition-all duration-300 hover:-translate-y-0.5 hover:shadow-xl hover:shadow-[#0eb0e6]/40 active:translate-y-0 sm:h-13 sm:w-auto sm:text-base"
                        >
                            <span>Find a Tutor</span>
                            <ArrowRight
                                class="h-4 w-4 transition-transform duration-300 group-hover:translate-x-1 sm:h-5 sm:w-5"
                            />
                        </Link>

                        <Link
                            :href="jobs()"
                            class="inline-flex h-12 w-full items-center justify-center rounded-full border-2 border-[#1b2880] bg-white px-8 text-sm font-bold text-[#1b2880] shadow-xs transition-all duration-300 hover:-translate-y-0.5 hover:bg-[#1b2880] hover:text-white hover:shadow-md active:translate-y-0 sm:h-13 sm:w-auto sm:text-base"
                        >
                            Post Tutor Requirement
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
