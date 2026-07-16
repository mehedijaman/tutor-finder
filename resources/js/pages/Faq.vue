<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import { useSiteSettings } from '@/composables/useSiteSettings';
import PublicLayout from '@/layouts/PublicLayout.vue';
import { sanitizeHtml } from '@/lib/utils';

type FaqItem = {
    id: number;
    question: string;
    answer: string;
    audience: 'tutor' | 'guardian' | 'both';
    sort_order: number;
};

const props = withDefaults(
    defineProps<{
        canRegister: boolean;
        faqs: FaqItem[];
        filters?: {
            audience?: '' | 'tutor' | 'guardian';
        };
        meta?: {
            title?: string;
            description?: string;
        };
    }>(),
    {
        canRegister: true,
        filters: () => ({
            audience: '',
        }),
        meta: () => ({
            title: 'FAQ',
            description: 'Frequently asked questions',
        }),
    },
);

const { siteName } = useSiteSettings();
const openFaqId = ref<number | null>(null);

watch(
    () => props.faqs,
    (items) => {
        openFaqId.value = items.length > 0 ? items[0].id : null;
    },
    {
        immediate: true,
    },
);

const selectedAudience = computed(() => props.filters?.audience ?? '');

const audienceTabs = [
    { key: '', label: 'All', href: '/faq' },
    { key: 'tutor', label: 'For Tutors', href: '/faq?audience=tutor' },
    { key: 'guardian', label: 'For Guardians', href: '/faq?audience=guardian' },
];

function toggleFaq(id: number): void {
    openFaqId.value = openFaqId.value === id ? null : id;
}

function getSanitizedAnswer(answer: string): string {
    return sanitizeHtml(answer);
}
</script>

<template>
    <Head :title="meta?.title || `FAQ | ${siteName}`">
        <meta
            name="description"
            :content="meta?.description || 'Frequently asked questions'"
        />
    </Head>

    <PublicLayout>
        <section class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 pb-8">
            <div
                class="rounded-3xl bg-gradient-to-r from-blue-600 to-cyan-500 p-8 text-white shadow-sm md:p-10"
            >
                <p class="text-sm font-semibold text-white/90">
                    {{ siteName }}
                </p>
                <h1 class="mt-2 text-3xl font-extrabold md:text-4xl">
                    Frequently Asked Questions
                </h1>
                <p class="mt-3 max-w-2xl text-sm text-white/90 md:text-base">
                    Find quick answers for tutors and guardians. Use the
                    audience filters to narrow down the list.
                </p>
            </div>
        </section>

        <main class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8 pb-16">
            <div class="rounded-xl border bg-white p-4 shadow-sm">
                <div class="mb-5 flex flex-wrap gap-2">
                    <Link
                        v-for="tab in audienceTabs"
                        :key="tab.key || 'all'"
                        :href="tab.href"
                        preserve-state
                        preserve-scroll
                        class="inline-flex items-center rounded-md border px-4 py-2 text-sm font-medium transition"
                        :class="
                            selectedAudience === tab.key
                                ? 'border-blue-600 bg-blue-600 text-white'
                                : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50'
                        "
                    >
                        {{ tab.label }}
                    </Link>
                </div>

                <div
                    v-if="faqs.length === 0"
                    class="rounded-lg border border-dashed p-8 text-center text-sm text-muted-foreground"
                >
                    No FAQs found for this audience.
                </div>

                <div v-else class="divide-y">
                    <section v-for="faq in faqs" :key="faq.id" class="py-3">
                        <button
                            type="button"
                            class="flex w-full items-center justify-between gap-4 text-left"
                            :aria-expanded="openFaqId === faq.id"
                            @click="toggleFaq(faq.id)"
                        >
                            <span class="font-semibold text-slate-900">{{
                                faq.question
                            }}</span>
                            <span
                                class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-md border text-slate-500 transition-transform"
                                :class="
                                    openFaqId === faq.id ? 'rotate-180' : ''
                                "
                            >
                                <svg
                                    class="h-4 w-4"
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

                        <transition
                            enter-active-class="transition duration-200 ease-out"
                            enter-from-class="-translate-y-1 opacity-0"
                            enter-to-class="translate-y-0 opacity-100"
                            leave-active-class="transition duration-150 ease-in"
                            leave-from-class="translate-y-0 opacity-100"
                            leave-to-class="-translate-y-1 opacity-0"
                        >
                            <div
                                v-if="openFaqId === faq.id"
                                class="prose prose-sm mt-3 max-w-none text-slate-700"
                                v-html="getSanitizedAnswer(faq.answer)"
                            />
                        </transition>
                    </section>
                </div>
            </div>
        </main>
    </PublicLayout>
</template>
