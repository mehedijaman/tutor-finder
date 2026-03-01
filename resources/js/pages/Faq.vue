<!-- Faq.vue -->
<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { useSiteSettings } from '@/composables/useSiteSettings';
import { dashboard, login, register } from '@/routes';
import { computed, ref } from 'vue';

withDefaults(
    defineProps<{
        canRegister: boolean;
    }>(),
    { canRegister: true },
);

type FaqItem = { q: string; a: string };

const { siteName } = useSiteSettings();

const activeTab = ref<'guardian' | 'tutor'>('guardian');
const openIndex = ref<number | null>(0);

const guardianFaqs: FaqItem[] = [
    {
        q: 'How do I find a tutor on Tutor Finder?',
        a: 'Create an account, add your learning needs (grade, subject, schedule), then browse tutor profiles and request a session.',
    },
    {
        q: 'Are tutors verified?',
        a: 'We offer verification steps (profile review and optional checks). Always review tutor details, ratings, and experience before booking.',
    },
    {
        q: 'Can I choose online or in-person tutoring?',
        a: 'Yes. Tutors may offer online, in-person, or hybrid options depending on their location and availability.',
    },
    {
        q: 'How do scheduling and rescheduling work?',
        a: 'You can propose times and confirm with your tutor. If you need to reschedule, message the tutor as early as possible to agree on a new time.',
    },
    {
        q: 'How can I track my child’s progress?',
        a: 'Use session notes and shared goals (if enabled) to monitor progress. You can also ask tutors for a weekly summary after sessions.',
    },
    {
        q: 'What should I do if there’s an issue with a tutor?',
        a: 'Message the tutor first to clarify. If the issue continues, contact support with details and we’ll help review the situation.',
    },
];

const tutorFaqs: FaqItem[] = [
    {
        q: 'How do I apply for tutoring jobs?',
        a: 'Create your tutor profile, list subjects/grades you teach, and browse the Job Board. You can submit proposals to families directly.',
    },
    {
        q: 'What should I include in my tutor profile?',
        a: 'Add subjects, grade levels, experience, teaching style, availability, and (optional) documents for verification. A clear intro helps you get more requests.',
    },
    {
        q: 'How do I set my availability?',
        a: 'Set your weekly schedule and update it regularly. Keeping availability current helps families book you faster.',
    },
    {
        q: 'Do I need to tutor online or in-person?',
        a: 'It’s up to you. Many tutors offer online sessions for flexibility, while others offer in-person depending on location.',
    },
    {
        q: 'How do I handle cancellations or no-shows?',
        a: 'Set expectations up front. If a student cancels late or no-shows, document it in messages and follow your policy (or platform policy if you use one).',
    },
    {
        q: 'How can I get more tutoring requests?',
        a: 'Complete your profile, respond quickly, keep availability accurate, and ask for reviews after successful sessions.',
    },
];

const faqs = computed(() => (activeTab.value === 'guardian' ? guardianFaqs : tutorFaqs));

function switchTab(tab: 'guardian' | 'tutor') {
    activeTab.value = tab;
    openIndex.value = 0;
}

function toggle(i: number) {
    openIndex.value = openIndex.value === i ? null : i;
}
</script>

<template>
    <Head title="FAQ">
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    </Head>

    <div class="min-h-screen bg-slate-50 text-slate-900">
        <!-- Auth header -->
        <header class="mx-auto w-full max-w-7xl px-4 py-4 text-sm">
            <nav class="flex items-center justify-end gap-4">
                <Link
                    v-if="$page.props.auth.user"
                    :href="dashboard()"
                    class="inline-block rounded-sm border border-[#19140035] px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#1915014a] dark:border-[#3E3E3A] dark:text-[#EDEDEC] dark:hover:border-[#62605b]"
                >
                    Dashboard
                </Link>
                <template v-else>
                    <Link
                        :href="login()"
                        class="inline-block rounded-sm border border-transparent px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#19140035] dark:text-[#EDEDEC] dark:hover:border-[#3E3E3A]"
                    >
                        Log in
                    </Link>
                    <Link
                        v-if="canRegister"
                        :href="register()"
                        class="inline-block rounded-sm border border-[#19140035] px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#1915014a] dark:border-[#3E3E3A] dark:text-[#EDEDEC] dark:hover:border-[#62605b]"
                    >
                        Register
                    </Link>
                </template>
            </nav>
        </header>

        <!-- Hero -->
        <section class="mx-auto max-w-7xl px-4 pb-8">
            <div class="rounded-3xl bg-gradient-to-r from-blue-600 to-sky-500 p-8 md:p-10 text-white shadow-sm">
                <p class="text-white/90 text-sm font-semibold">{{ siteName }}</p>
                <h1 class="mt-2 text-3xl md:text-4xl font-extrabold">Frequently Asked Questions</h1>
                <p class="mt-3 max-w-2xl text-white/90">
                    Answers for both guardians/students and tutors. Use the tabs to switch.
                </p>
            </div>
        </section>

        <!-- Tabs + FAQ -->
        <main class="mx-auto max-w-7xl px-4 pb-16">
            <div class="grid lg:grid-cols-5 gap-8 items-start">
                <!-- Left: tab switch + quick help -->
                <aside class="lg:col-span-2 space-y-6">
                    <div
                        class="rounded-2xl border border-slate-100 bg-white p-6 shadow-[0_10px_30px_rgba(2,32,71,0.06)]"
                    >
                        <h2 class="text-lg font-bold">Choose a topic</h2>
                        <p class="mt-2 text-sm text-slate-600">
                            Switch between FAQ sets using the tabbed toggle.
                        </p>

                        <!-- Tab switch -->
                        <div class="mt-5 rounded-xl bg-slate-100 p-1">
                            <button
                                type="button"
                                class="w-1/2 rounded-lg px-4 py-2 text-sm font-semibold transition"
                                :class="
                                    activeTab === 'guardian'
                                        ? 'bg-white text-slate-900 shadow-sm'
                                        : 'text-slate-600 hover:text-slate-900'
                                "
                                @click="switchTab('guardian')"
                            >
                                Guardian
                            </button>
                            <button
                                type="button"
                                class="w-1/2 rounded-lg px-4 py-2 text-sm font-semibold transition"
                                :class="
                                    activeTab === 'tutor'
                                        ? 'bg-white text-slate-900 shadow-sm'
                                        : 'text-slate-600 hover:text-slate-900'
                                "
                                @click="switchTab('tutor')"
                            >
                                Tutor
                            </button>
                        </div>

                        <div class="mt-6 grid grid-cols-2 gap-3 text-sm">
                            <div class="rounded-xl bg-blue-50 border border-blue-100 p-4">
                                <p class="font-semibold text-blue-900">Need help now?</p>
                                <p class="mt-1 text-blue-900/70 text-xs">
                                    Use the contact page for support.
                                </p>
                                <a href="/contact" class="mt-3 inline-flex text-blue-700 font-semibold text-sm hover:text-blue-800">
                                    Contact →
                                </a>
                            </div>
                            <div class="rounded-xl bg-slate-50 border border-slate-100 p-4">
                                <p class="font-semibold">Safety tip</p>
                                <p class="mt-1 text-slate-600 text-xs">
                                    Keep communication on-platform when possible.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div
                        class="rounded-2xl border border-slate-100 bg-white p-6 shadow-[0_10px_30px_rgba(2,32,71,0.06)]"
                    >
                        <h3 class="text-lg font-bold">Popular questions</h3>
                        <ul class="mt-3 space-y-2 text-sm text-slate-600">
                            <li class="flex items-start gap-2">
                                <span class="mt-0.5 text-blue-600">•</span>
                                How to book / apply quickly
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="mt-0.5 text-blue-600">•</span>
                                Online vs in-person tutoring
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="mt-0.5 text-blue-600">•</span>
                                Rescheduling and cancellations
                            </li>
                        </ul>
                    </div>
                </aside>

                <!-- Right: accordion -->
                <section class="lg:col-span-3">
                    <div
                        class="rounded-2xl border border-slate-100 bg-white p-6 md:p-8 shadow-[0_10px_30px_rgba(2,32,71,0.06)]"
                    >
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h2 class="text-xl font-extrabold">
                                    {{ activeTab === 'guardian' ? 'Guardian FAQs' : 'Tutor FAQs' }}
                                </h2>
                                <p class="mt-2 text-sm text-slate-600">
                                    Click a question to expand the answer.
                                </p>
                            </div>
                            <div class="text-xs text-slate-500 pt-1">
                                {{ faqs.length }} questions
                            </div>
                        </div>

                        <div class="mt-6 divide-y divide-slate-100">
                            <div v-for="(item, i) in faqs" :key="item.q" class="py-3">
                                <button
                                    type="button"
                                    class="w-full flex items-center justify-between gap-4 text-left"
                                    @click="toggle(i)"
                                    :aria-expanded="openIndex === i"
                                >
                                    <span class="font-semibold text-slate-900">
                                        {{ item.q }}
                                    </span>

                                    <span
                                        class="shrink-0 inline-flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600"
                                    >
                                        <svg
                                            class="h-4 w-4 transition-transform duration-200"
                                            :class="openIndex === i ? 'rotate-180' : ''"
                                            viewBox="0 0 20 20"
                                            fill="currentColor"
                                            aria-hidden="true"
                                        >
                                            <path
                                                fill-rule="evenodd"
                                                d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.24a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z"
                                                clip-rule="evenodd"
                                            />
                                        </svg>
                                    </span>
                                </button>

                                <div v-show="openIndex === i" class="mt-3 text-sm text-slate-600">
                                    {{ item.a }}
                                </div>
                            </div>
                        </div>

                        <div class="mt-8 rounded-xl bg-slate-50 border border-slate-100 p-5">
                            <p class="font-semibold">Didn’t find what you need?</p>
                            <p class="mt-1 text-sm text-slate-600">
                                Send us a message and we’ll help you out.
                            </p>
                            <a href="/contact" class="mt-3 inline-flex font-semibold text-blue-700 hover:text-blue-800">
                                Go to Contact →
                            </a>
                        </div>
                    </div>
                </section>
            </div>
        </main>

        <!-- Footer -->
        <footer class="bg-slate-900 text-slate-200">
            <div class="mx-auto max-w-7xl px-4 py-10">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-3 text-sm text-slate-400">
                    <p>© {{ new Date().getFullYear() }} {{ siteName }}. All rights reserved.</p>
                    <div class="flex items-center gap-4">
                        <a class="hover:text-slate-200" href="/privacy">Privacy</a>
                        <a class="hover:text-slate-200" href="/terms">Terms</a>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</template>
