<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    ArrowLeft,
    CalendarDays,
    Clock3,
    Hash,
    Sparkles,
    UserRound,
} from 'lucide-vue-next';
import { computed } from 'vue';
import PublicLayout from '@/layouts/PublicLayout.vue';
import { sanitizeHtml } from '@/lib/utils';

type BlogPostShow = {
    id: number;
    title: string;
    slug: string;
    summary: string | null;
    content: string;
    published_at: string | null;
    cover_url: string | null;
    author_name: string | null;
    categories: Array<{ name: string; slug: string }>;
    tags: Array<{ name: string; slug: string }>;
    meta_title: string | null;
    meta_description: string | null;
};

const props = defineProps<{
    post: BlogPostShow;
    meta: {
        title: string;
        description: string;
    };
}>();

const publishedLabel = computed(() => {
    if (!props.post.published_at) {
        return 'Unpublished';
    }

    return new Date(props.post.published_at).toLocaleDateString('en-GB', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    });
});

const authorLabel = computed(() => props.post.author_name || 'Editorial Team');

const sanitizedContent = computed(() => sanitizeHtml(props.post.content));

const readingTimeMinutes = computed(() => {
    const plainText = props.post.content
        .replace(/<[^>]*>/g, ' ')
        .replace(/\s+/g, ' ')
        .trim();

    if (!plainText) {
        return 1;
    }

    const words = plainText.split(' ').length;

    return Math.max(1, Math.ceil(words / 220));
});
</script>

<template>
    <Head :title="meta.title">
        <meta name="description" :content="meta.description" />
    </Head>

    <PublicLayout>
        <div
            class="relative min-h-screen overflow-hidden bg-gradient-to-b from-slate-100 via-white to-slate-100 py-10 sm:py-12"
        >
            <div
                class="pointer-events-none absolute inset-x-0 top-0 h-72 bg-gradient-to-b from-indigo-100/60 via-cyan-100/25 to-transparent"
            />

            <div class="relative mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                <div class="mb-6">
                    <Link
                        href="/blog"
                        class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 hover:text-slate-900"
                    >
                        <ArrowLeft class="h-4 w-4" />
                        Back to Blog
                    </Link>
                </div>

                <article
                    class="overflow-hidden rounded-3xl border border-slate-200/70 bg-white shadow-xl ring-1 ring-slate-900/5"
                >
                    <header class="relative overflow-hidden">
                        <img
                            v-if="post.cover_url"
                            :src="post.cover_url"
                            :alt="post.title"
                            class="h-72 w-full object-cover sm:h-96"
                        />
                        <div
                            v-else
                            class="h-72 w-full bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 sm:h-96"
                        />

                        <div
                            class="absolute inset-0 bg-gradient-to-t from-slate-900 via-slate-900/65 to-transparent"
                        />

                        <div
                            class="absolute inset-x-0 bottom-0 p-6 sm:p-8 md:p-10"
                        >
                            <div
                                class="mb-4 flex flex-wrap items-center gap-2 text-xs"
                            >
                                <span
                                    class="inline-flex items-center gap-1 rounded-full border border-white/20 bg-white/10 px-2.5 py-1 font-semibold tracking-wide text-white uppercase"
                                >
                                    <Sparkles class="h-3.5 w-3.5" />
                                    Article
                                </span>
                                <span
                                    v-for="category in post.categories"
                                    :key="`post-category-${category.slug}`"
                                    class="inline-flex items-center rounded-full border border-white/20 bg-white/10 px-2.5 py-1 font-medium text-white/90"
                                >
                                    {{ category.name }}
                                </span>
                            </div>

                            <h1
                                class="max-w-4xl text-2xl font-bold tracking-tight text-white sm:text-4xl"
                            >
                                {{ post.title }}
                            </h1>

                            <div
                                class="mt-4 flex flex-wrap items-center gap-4 text-xs font-medium text-white/85 sm:text-sm"
                            >
                                <span class="inline-flex items-center gap-1.5">
                                    <UserRound class="h-4 w-4" />
                                    {{ authorLabel }}
                                </span>
                                <span class="inline-flex items-center gap-1.5">
                                    <CalendarDays class="h-4 w-4" />
                                    {{ publishedLabel }}
                                </span>
                                <span class="inline-flex items-center gap-1.5">
                                    <Clock3 class="h-4 w-4" />
                                    {{ readingTimeMinutes }} min read
                                </span>
                            </div>
                        </div>
                    </header>

                    <div class="grid lg:grid-cols-[minmax(0,1fr)_260px]">
                        <section
                            class="order-2 border-t border-slate-100 p-6 sm:p-8 lg:order-1 lg:border-t-0 lg:border-r"
                        >
                            <p
                                v-if="post.summary"
                                class="rounded-2xl border border-blue-100 bg-blue-50/70 p-4 text-sm leading-relaxed text-slate-700 sm:text-base"
                            >
                                {{ post.summary }}
                            </p>

                            <div
                                class="prose prose-slate prose-headings:mt-8 prose-headings:font-semibold prose-p:leading-7 prose-a:text-blue-700 hover:prose-a:text-blue-800 prose-img:rounded-xl prose-img:shadow-sm mt-6 max-w-none"
                                v-html="sanitizedContent"
                            ></div>
                        </section>

                        <aside
                            class="order-1 border-b border-slate-100 bg-slate-50/50 p-6 lg:order-2 lg:border-b-0"
                        >
                            <div class="space-y-6 lg:sticky lg:top-24">
                                <div>
                                    <h2
                                        class="text-xs font-semibold tracking-wide text-slate-500 uppercase"
                                    >
                                        Post Details
                                    </h2>
                                    <dl class="mt-3 space-y-3 text-sm">
                                        <div
                                            class="rounded-xl border border-slate-200 bg-white px-3 py-2.5"
                                        >
                                            <dt
                                                class="text-[11px] font-medium tracking-wide text-slate-500 uppercase"
                                            >
                                                Author
                                            </dt>
                                            <dd
                                                class="mt-1 font-medium text-slate-800"
                                            >
                                                {{ authorLabel }}
                                            </dd>
                                        </div>

                                        <div
                                            class="rounded-xl border border-slate-200 bg-white px-3 py-2.5"
                                        >
                                            <dt
                                                class="text-[11px] font-medium tracking-wide text-slate-500 uppercase"
                                            >
                                                Published
                                            </dt>
                                            <dd
                                                class="mt-1 font-medium text-slate-800"
                                            >
                                                {{ publishedLabel }}
                                            </dd>
                                        </div>

                                        <div
                                            class="rounded-xl border border-slate-200 bg-white px-3 py-2.5"
                                        >
                                            <dt
                                                class="text-[11px] font-medium tracking-wide text-slate-500 uppercase"
                                            >
                                                Reading Time
                                            </dt>
                                            <dd
                                                class="mt-1 font-medium text-slate-800"
                                            >
                                                {{ readingTimeMinutes }} minutes
                                            </dd>
                                        </div>
                                    </dl>
                                </div>

                                <div v-if="post.tags.length">
                                    <h2
                                        class="inline-flex items-center gap-1.5 text-xs font-semibold tracking-wide text-slate-500 uppercase"
                                    >
                                        <Hash class="h-3.5 w-3.5" />
                                        Topics
                                    </h2>
                                    <div class="mt-2 flex flex-wrap gap-2">
                                        <Link
                                            v-for="tag in post.tags"
                                            :key="`post-tag-${tag.slug}`"
                                            :href="`/blog?tag=${tag.slug}`"
                                            class="inline-flex items-center rounded-full border border-slate-200 bg-white px-2.5 py-1 text-xs font-medium text-slate-700 transition-colors hover:bg-slate-100"
                                        >
                                            {{ tag.name }}
                                        </Link>
                                    </div>
                                </div>

                                <div
                                    class="rounded-2xl border border-slate-200 bg-white p-4"
                                >
                                    <p
                                        class="text-sm font-semibold text-slate-900"
                                    >
                                        Continue Reading
                                    </p>
                                    <p
                                        class="mt-1 text-xs leading-relaxed text-slate-600"
                                    >
                                        Explore more platform updates, tutoring
                                        strategies, and learning insights.
                                    </p>
                                    <Link
                                        href="/blog"
                                        class="mt-3 inline-flex items-center gap-1.5 text-sm font-semibold text-blue-700 hover:text-blue-800"
                                    >
                                        Back to all posts
                                        <ArrowLeft class="h-4 w-4 rotate-180" />
                                    </Link>
                                </div>
                            </div>
                        </aside>
                    </div>
                </article>
            </div>
        </div>
    </PublicLayout>
</template>
