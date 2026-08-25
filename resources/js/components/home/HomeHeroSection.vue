<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowRight, CheckCircle2, ShieldCheck, Star } from 'lucide-vue-next';
import { jobs, tutors } from '@/routes';
</script>

<template>
    <section
        class="relative overflow-hidden bg-slate-50/70 py-10 sm:py-16 lg:py-24"
    >
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
                <!-- Left Column: Vector Illustration & Glassmorphic Badges -->
                <div class="relative flex justify-center lg:col-span-6">
                    <div class="relative w-full max-w-lg lg:max-w-xl">
                        <!-- Soft Gradient Ambient Circle Behind Illustration -->
                        <div
                            class="absolute top-1/2 left-1/2 -z-10 h-64 w-64 -translate-x-1/2 -translate-y-1/2 rounded-full bg-gradient-to-tr from-[#0eb0e6]/25 to-blue-600/15 blur-3xl sm:h-[420px] sm:w-[420px]"
                        ></div>

                        <!-- Floating Glassmorphic Badge 1: Verified Tutors (Top Left) -->
                        <div
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
                        </div>

                        <!-- Floating Glassmorphic Badge 2: Rating 5.0 (Bottom Right) -->
                        <div
                            class="absolute -right-2 -bottom-4 z-20 hidden items-center gap-2.5 rounded-2xl border border-white/80 bg-white/90 px-4 py-2.5 shadow-xl backdrop-blur-md transition-transform duration-300 hover:scale-105 sm:flex"
                        >
                            <div
                                class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-500/10 text-amber-500 ring-1 ring-amber-500/20"
                            >
                                <Star class="h-5 w-5 fill-amber-400" />
                            </div>
                            <div class="flex flex-col leading-tight">
                                <span class="text-xs font-bold text-slate-900"
                                    >5.0 Star Rating</span
                                >
                                <span
                                    class="text-[10px] font-medium text-slate-500"
                                    >Over 1,500+ Reviews</span
                                >
                            </div>
                        </div>

                        <!-- Main Vector Illustration -->
                        <svg
                            viewBox="0 0 600 480"
                            class="h-auto w-full drop-shadow-xl"
                            xmlns="http://www.w3.org/2000/svg"
                        >
                            <defs>
                                <linearGradient
                                    id="boardGrad"
                                    x1="0%"
                                    y1="0%"
                                    x2="100%"
                                    y2="100%"
                                >
                                    <stop offset="0%" stop-color="#1e293b" />
                                    <stop offset="100%" stop-color="#0f172a" />
                                </linearGradient>
                                <linearGradient
                                    id="shirtGrad"
                                    x1="0%"
                                    y1="0%"
                                    x2="100%"
                                    y2="100%"
                                >
                                    <stop offset="0%" stop-color="#ef4444" />
                                    <stop offset="100%" stop-color="#dc2626" />
                                </linearGradient>
                            </defs>

                            <!-- Clock Floating Badge -->
                            <g transform="translate(420, 30)">
                                <circle
                                    cx="26"
                                    cy="26"
                                    r="24"
                                    fill="#e0f2fe"
                                    stroke="#38bdf8"
                                    stroke-width="2"
                                />
                                <circle cx="26" cy="26" r="18" fill="#ffffff" />
                                <path
                                    d="M26 14 V26 H34"
                                    stroke="#0284c7"
                                    stroke-width="3"
                                    stroke-linecap="round"
                                />
                            </g>

                            <!-- Floating Books & Question Mark -->
                            <g transform="translate(425, 160)">
                                <rect
                                    x="0"
                                    y="12"
                                    width="34"
                                    height="9"
                                    rx="3"
                                    fill="#ef4444"
                                />
                                <rect
                                    x="4"
                                    y="3"
                                    width="30"
                                    height="9"
                                    rx="3"
                                    fill="#0eb0e6"
                                />
                                <text
                                    x="42"
                                    y="26"
                                    font-family="sans-serif"
                                    font-size="30"
                                    font-weight="900"
                                    fill="#ef4444"
                                >
                                    ?
                                </text>
                            </g>

                            <!-- Blackboard Frame -->
                            <rect
                                x="110"
                                y="60"
                                width="290"
                                height="185"
                                rx="16"
                                fill="url(#boardGrad)"
                                stroke="#334155"
                                stroke-width="6"
                            />
                            <path
                                d="M150 245 L130 295 M360 245 L380 295"
                                stroke="#475569"
                                stroke-width="6"
                                stroke-linecap="round"
                            />

                            <!-- Math & Geometry Drawings on Blackboard -->
                            <text
                                x="210"
                                y="100"
                                font-family="monospace"
                                font-size="16"
                                font-weight="bold"
                                fill="#f8fafc"
                                opacity="0.95"
                            >
                                563 - 35
                            </text>

                            <path
                                d="M145 110 A12 12 0 1 1 161 110 C161 115 157 119 157 123 H149 C149 119 145 115 145 110 Z"
                                fill="none"
                                stroke="#facc15"
                                stroke-width="2.5"
                            />
                            <path
                                d="M150 128 H156"
                                stroke="#facc15"
                                stroke-width="2.5"
                            />
                            <line
                                x1="153"
                                y1="91"
                                x2="153"
                                y2="86"
                                stroke="#facc15"
                                stroke-width="2.5"
                            />
                            <line
                                x1="135"
                                y1="105"
                                x2="130"
                                y2="102"
                                stroke="#facc15"
                                stroke-width="2.5"
                            />
                            <line
                                x1="171"
                                y1="105"
                                x2="176"
                                y2="102"
                                stroke="#facc15"
                                stroke-width="2.5"
                            />

                            <polygon
                                points="145,205 210,205 145,160"
                                fill="none"
                                stroke="#38bdf8"
                                stroke-width="2.5"
                            />
                            <text
                                x="235"
                                y="160"
                                font-family="sans-serif"
                                font-size="13"
                                font-weight="bold"
                                fill="#f8fafc"
                            >
                                c² = a² + b²
                            </text>

                            <!-- Teacher Figure -->
                            <path
                                d="M295 190 Q305 160 325 160 Q345 160 355 190 L365 295 H285 Z"
                                fill="url(#shirtGrad)"
                            />
                            <rect
                                x="295"
                                y="295"
                                width="30"
                                height="145"
                                fill="#1e293b"
                            />
                            <rect
                                x="330"
                                y="295"
                                width="30"
                                height="145"
                                fill="#0f172a"
                            />
                            <circle cx="325" cy="120" r="22" fill="#fca5a5" />
                            <path
                                d="M298 110 C298 80, 352 80, 352 110 C352 140, 362 215, 352 215 C342 215, 337 150, 325 140 C313 150, 308 215, 298 215 Z"
                                fill="#0f172a"
                            />
                            <circle cx="325" cy="115" r="18" fill="#fca5a5" />
                            <path
                                d="M300 180 L250 135"
                                stroke="#ef4444"
                                stroke-width="14"
                                stroke-linecap="round"
                            />
                            <circle cx="245" cy="130" r="6" fill="#fca5a5" />
                            <path
                                d="M350 190 L370 230"
                                stroke="#ef4444"
                                stroke-width="14"
                                stroke-linecap="round"
                            />
                            <rect
                                x="355"
                                y="210"
                                width="32"
                                height="42"
                                rx="5"
                                fill="#1e293b"
                                stroke="#3b82f6"
                                stroke-width="2"
                            />

                            <!-- Students -->
                            <path
                                d="M65 340 Q80 295 105 295 Q130 295 145 340 V440 H65 Z"
                                fill="#ef4444"
                            />
                            <circle cx="105" cy="270" r="18" fill="#fca5a5" />
                            <path d="M95 255 Q105 240 115 255" fill="#1e1b4b" />
                            <path
                                d="M125 305 L142 235"
                                stroke="#ef4444"
                                stroke-width="12"
                                stroke-linecap="round"
                            />
                            <circle cx="145" cy="228" r="5" fill="#fca5a5" />

                            <path
                                d="M165 350 Q180 310 200 310 Q220 310 235 350 V440 H165 Z"
                                fill="#dc2626"
                            />
                            <circle cx="200" cy="280" r="18" fill="#0f172a" />
                            <path
                                d="M200 290 L200 330"
                                stroke="#0f172a"
                                stroke-width="8"
                                stroke-linecap="round"
                            />

                            <path
                                d="M385 350 Q405 310 425 310 Q445 310 465 350 V440 H385 Z"
                                fill="#ef4444"
                            />
                            <circle cx="425" cy="280" r="18" fill="#451a03" />
                        </svg>
                    </div>
                </div>

                <!-- Right Column: Text & CTAs -->
                <div
                    class="flex flex-col items-center text-center lg:col-span-6 lg:items-start lg:text-left"
                >
                    <!-- Pill Badge -->
                    <div
                        class="mb-4 inline-flex items-center gap-2.5 rounded-full border border-[#0eb0e6]/40 bg-white/90 px-3.5 py-1.5 text-[11px] font-extrabold text-[#008cc2] shadow-sm backdrop-blur-md transition-all hover:border-[#0eb0e6] hover:bg-cyan-50/50 sm:mb-6 sm:px-4 sm:text-xs"
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
                            with Confidences
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
                            class="inline-flex h-12 w-full items-center justify-center rounded-full border-2 border-[#1b2880] bg-white px-8 text-sm font-bold text-[#1b2880] shadow-sm transition-all duration-300 hover:-translate-y-0.5 hover:bg-[#1b2880] hover:text-white hover:shadow-md active:translate-y-0 sm:h-13 sm:w-auto sm:text-base"
                        >
                            Post Tutor Requirement
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
