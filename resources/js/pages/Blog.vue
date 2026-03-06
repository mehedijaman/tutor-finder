<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    ArrowRight,
    CalendarDays,
    Search,
    SlidersHorizontal,
    Sparkles,
    UserRound,
    X,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';
import PublicPagination from '@/components/public/PublicPagination.vue';
import { useSiteSettings } from '@/composables/useSiteSettings';
import PublicLayout from '@/layouts/PublicLayout.vue';

type BlogCategoryFilter = {
    id: number;
    name: string;
    slug: string;
};

type BlogTagFilter = {
    id: number;
    name: string;
    slug: string;
};

type BlogPostListItem = {
    id: number;
    title: string;
    slug: string;
    summary: string | null;
    published_at: string | null;
    cover_url: string | null;
    author_name: string | null;
    categories: Array<{ name: string; slug: string }>;
    tags: Array<{ name: string; slug: string }>;
};

type PaginationLink = {
    url: string | null;
    label: string;
    active: boolean;
};

const props = defineProps<{
    posts: {
        data: BlogPostListItem[];
        current_page: number;
        last_page: number;
        from: number | null;
        to: number | null;
        total: number;
        links: PaginationLink[];
    };
    categories: BlogCategoryFilter[];
    tags: BlogTagFilter[];
    filters: {
        q: string;
        category: string;
        tag: string;
    };
    meta: {
        title: string;
        description: string;
    };
}>();

const { siteName } = useSiteSettings();

const search = ref(props.filters.q ?? '');
const selectedCategory = ref(props.filters.category ?? '');
const selectedTag = ref(props.filters.tag ?? '');
const featuredPost = computed(() => props.posts.data[0] ?? null);
const gridPosts = computed(() => props.posts.data.slice(1));
const hasPosts = computed(() => props.posts.data.length > 0);
const hasActiveFilters = computed(
    () =>
        search.value.trim().length > 0 ||
        selectedCategory.value.trim().length > 0 ||
        selectedTag.value.trim().length > 0,
);

const applyFilters = () => {
    router.get(
        '/blog',
        {
            q: search.value || '',
            category: selectedCategory.value || '',
            tag: selectedTag.value || '',
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
};

const resetFilters = () => {
    search.value = '';
    selectedCategory.value = '';
    selectedTag.value = '';
    applyFilters();
};

const formatDate = (date: string | null): string => {
    if (!date) {
        return 'Unpublished';
    }

    return new Date(date).toLocaleDateString('en-GB', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    });
};
</script>

<template>
    <Head :title="meta.title">
        <meta name="description" :content="meta.description" />
    </Head>

    <PublicLayout>
        <div
            class="relative min-h-screen overflow-hidden bg-slate-100 py-10 sm:py-12"
        >
            <div
                class="pointer-events-none absolute inset-x-0 top-0 h-64 bg-gradient-to-b from-indigo-100/60 via-cyan-100/30 to-transparent"
            />

            <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <section
                    class="relative overflow-hidden rounded-3xl border border-slate-200/70 bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 p-6 text-white shadow-xl sm:p-10"
                >
                    <div
                        class="pointer-events-none absolute -top-20 -right-10 h-44 w-44 rounded-full bg-cyan-300/20 blur-3xl"
                    />
                    <div
                        class="pointer-events-none absolute -bottom-24 -left-12 h-56 w-56 rounded-full bg-indigo-400/20 blur-3xl"
                    />

                    <div class="relative">
                        <span
                            class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-3 py-1 text-xs font-semibold tracking-wide uppercase"
                        >
                            <Sparkles class="h-3.5 w-3.5" />
                            Insights & Stories
                        </span>

                        <h1
                            class="mt-4 max-w-3xl text-3xl font-bold tracking-tight sm:text-4xl lg:text-5xl"
                        >
                            Ideas That Help Tutors and Guardians Move Faster
                        </h1>
                        <p
                            class="mt-4 max-w-2xl text-sm leading-relaxed text-slate-200 sm:text-base"
                        >
                            Platform updates, learning strategies, and practical
                            advice from {{ siteName }} to help you make smarter
                            decisions.
                        </p>

                        <div
                            class="mt-6 flex flex-wrap items-center gap-3 text-xs sm:text-sm"
                        >
                            <span
                                class="inline-flex items-center rounded-full border border-white/20 bg-white/10 px-3 py-1.5 font-medium text-white/90"
                            >
                                {{ posts.total }} total articles
                            </span>
                            <span
                                class="inline-flex items-center rounded-full border border-white/20 bg-white/10 px-3 py-1.5 font-medium text-white/90"
                            >
                                {{ posts.current_page }} / {{ posts.last_page }}
                                pages
                            </span>
                        </div>
                    </div>
                </section>

                <form
                    class="mt-8 rounded-2xl border border-slate-200/80 bg-white/90 p-4 shadow-sm backdrop-blur-sm sm:p-5"
                    @submit.prevent="applyFilters"
                >
                    <div class="mb-4 flex items-center justify-between gap-3">
                        <h2
                            class="inline-flex items-center gap-2 text-sm font-semibold tracking-wide text-slate-900 uppercase"
                        >
                            <SlidersHorizontal class="h-4 w-4 text-slate-600" />
                            Filter Articles
                        </h2>
                        <button
                            v-if="hasActiveFilters"
                            type="button"
                            class="inline-flex items-center gap-1.5 rounded-full border border-slate-200 bg-slate-100 px-3 py-1.5 text-xs font-medium text-slate-700 transition-colors hover:bg-slate-200"
                            @click="resetFilters"
                        >
                            <X class="h-3.5 w-3.5" />
                            Clear Filters
                        </button>
                    </div>

                    <div class="grid gap-3 md:grid-cols-[1fr_220px_220px_auto]">
                        <label class="relative block">
                            <Search
                                class="pointer-events-none absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-slate-400"
                            />
                            <input
                                v-model="search"
                                type="text"
                                placeholder="Search by title or summary..."
                                class="h-11 w-full rounded-xl border border-slate-200 bg-white pr-3 pl-10 text-sm text-slate-700 transition outline-none focus:border-blue-400 focus:ring-4 focus:ring-blue-100"
                            />
                        </label>

                        <select
                            v-model="selectedCategory"
                            class="h-11 rounded-xl border border-slate-200 bg-white px-3 text-sm text-slate-700 transition outline-none focus:border-blue-400 focus:ring-4 focus:ring-blue-100"
                        >
                            <option value="">All categories</option>
                            <option
                                v-for="category in categories"
                                :key="`filter-category-${category.id}`"
                                :value="category.slug"
                            >
                                {{ category.name }}
                            </option>
                        </select>

                        <select
                            v-model="selectedTag"
                            class="h-11 rounded-xl border border-slate-200 bg-white px-3 text-sm text-slate-700 transition outline-none focus:border-blue-400 focus:ring-4 focus:ring-blue-100"
                        >
                            <option value="">All tags</option>
                            <option
                                v-for="tag in tags"
                                :key="`filter-tag-${tag.id}`"
                                :value="tag.slug"
                            >
                                {{ tag.name }}
                            </option>
                        </select>

                        <button
                            type="submit"
                            class="inline-flex h-11 items-center justify-center rounded-xl bg-slate-900 px-5 text-sm font-semibold text-white transition-colors hover:bg-slate-700"
                        >
                            Apply
                        </button>
                    </div>
                </form>

                <section v-if="hasPosts" class="mt-8 space-y-8">
                    <article
                        v-if="featuredPost"
                        class="overflow-hidden rounded-3xl border border-slate-200/80 bg-white shadow-sm ring-1 ring-slate-900/5 lg:grid lg:grid-cols-[1.05fr_1fr]"
                    >
                        <div
                            class="relative h-64 overflow-hidden sm:h-80 lg:h-full"
                        >
                            <img
                                v-if="featuredPost.cover_url"
                                :src="featuredPost.cover_url"
                                :alt="featuredPost.title"
                                class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                            />
                            <div
                                v-else
                                class="flex h-full items-center justify-center bg-gradient-to-br from-slate-800 via-slate-700 to-slate-900 p-6 text-center"
                            >
                                <p class="text-lg font-semibold text-white/90">
                                    {{ featuredPost.title }}
                                </p>
                            </div>
                            <div
                                class="absolute inset-x-0 bottom-0 h-24 bg-gradient-to-t from-black/55 to-transparent"
                            />
                        </div>

                        <div class="flex flex-col p-5 sm:p-7">
                            <p
                                class="inline-flex items-center gap-2 text-xs font-semibold tracking-wide text-blue-700 uppercase"
                            >
                                <Sparkles class="h-3.5 w-3.5" />
                                Featured Story
                            </p>

                            <h2
                                class="mt-3 text-2xl font-semibold tracking-tight text-slate-900"
                            >
                                <Link
                                    :href="`/blog/${featuredPost.slug}`"
                                    class="transition hover:text-blue-700"
                                >
                                    {{ featuredPost.title }}
                                </Link>
                            </h2>

                            <p
                                class="mt-3 line-clamp-4 text-sm leading-relaxed text-slate-600"
                            >
                                {{
                                    featuredPost.summary ||
                                    'No summary available.'
                                }}
                            </p>

                            <div class="mt-5 flex flex-wrap items-center gap-2">
                                <span
                                    v-for="category in featuredPost.categories"
                                    :key="`featured-category-${category.slug}`"
                                    class="inline-flex items-center rounded-full border border-blue-200 bg-blue-50 px-2.5 py-1 text-xs font-medium text-blue-700"
                                >
                                    {{ category.name }}
                                </span>
                                <span
                                    v-for="tag in featuredPost.tags.slice(0, 3)"
                                    :key="`featured-tag-${tag.slug}`"
                                    class="inline-flex items-center rounded-full border border-slate-200 bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-600"
                                >
                                    #{{ tag.name }}
                                </span>
                            </div>

                            <div
                                class="mt-6 flex flex-wrap items-center gap-4 text-xs text-slate-500"
                            >
                                <span class="inline-flex items-center gap-1.5">
                                    <CalendarDays class="h-3.5 w-3.5" />
                                    {{ formatDate(featuredPost.published_at) }}
                                </span>
                                <span
                                    v-if="featuredPost.author_name"
                                    class="inline-flex items-center gap-1.5"
                                >
                                    <UserRound class="h-3.5 w-3.5" />
                                    {{ featuredPost.author_name }}
                                </span>
                            </div>

                            <Link
                                :href="`/blog/${featuredPost.slug}`"
                                class="mt-6 inline-flex items-center gap-2 text-sm font-semibold text-blue-700 transition hover:text-blue-800"
                            >
                                Read Full Story
                                <ArrowRight class="h-4 w-4" />
                            </Link>
                        </div>
                    </article>

                    <div
                        v-if="gridPosts.length > 0"
                        class="grid gap-6 md:grid-cols-2 lg:grid-cols-3"
                    >
                        <article
                            v-for="post in gridPosts"
                            :key="post.id"
                            class="group flex h-full flex-col overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm ring-1 ring-slate-900/5 transition-all duration-300 hover:-translate-y-1 hover:shadow-lg"
                        >
                            <div class="relative h-44 overflow-hidden">
                                <img
                                    v-if="post.cover_url"
                                    :src="post.cover_url"
                                    :alt="post.title"
                                    class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                                />
                                <div
                                    v-else
                                    class="flex h-full items-center justify-center bg-gradient-to-br from-slate-800 via-slate-700 to-slate-900 p-4 text-center"
                                >
                                    <p
                                        class="line-clamp-2 text-sm font-semibold text-white/90"
                                    >
                                        {{ post.title }}
                                    </p>
                                </div>

                                <div
                                    class="absolute top-3 left-3 flex max-w-[90%] flex-wrap gap-1.5"
                                >
                                    <span
                                        v-for="category in post.categories.slice(
                                            0,
                                            2,
                                        )"
                                        :key="`post-${post.id}-category-${category.slug}`"
                                        class="rounded-full border border-white/35 bg-black/30 px-2 py-0.5 text-[11px] font-medium text-white backdrop-blur-sm"
                                    >
                                        {{ category.name }}
                                    </span>
                                </div>
                            </div>

                            <div class="flex flex-1 flex-col p-4">
                                <div
                                    class="flex items-center gap-2 text-xs text-slate-500"
                                >
                                    <CalendarDays class="h-3.5 w-3.5" />
                                    <span>{{
                                        formatDate(post.published_at)
                                    }}</span>
                                    <span v-if="post.author_name"
                                        >• {{ post.author_name }}</span
                                    >
                                </div>

                                <h2
                                    class="mt-3 line-clamp-2 text-lg font-semibold tracking-tight text-slate-900"
                                >
                                    <Link
                                        :href="`/blog/${post.slug}`"
                                        class="transition hover:text-blue-700"
                                    >
                                        {{ post.title }}
                                    </Link>
                                </h2>

                                <p
                                    class="mt-2 line-clamp-3 text-sm leading-relaxed text-slate-600"
                                >
                                    {{
                                        post.summary || 'No summary available.'
                                    }}
                                </p>

                                <div class="mt-4 flex flex-wrap gap-1.5">
                                    <span
                                        v-for="tag in post.tags.slice(0, 3)"
                                        :key="`post-${post.id}-tag-${tag.slug}`"
                                        class="inline-flex items-center rounded-full border border-slate-200 bg-slate-100 px-2 py-0.5 text-[11px] font-medium text-slate-600"
                                    >
                                        #{{ tag.name }}
                                    </span>
                                </div>

                                <Link
                                    :href="`/blog/${post.slug}`"
                                    class="mt-5 inline-flex items-center gap-1.5 text-sm font-semibold text-blue-700 transition hover:text-blue-800"
                                >
                                    Read article
                                    <ArrowRight class="h-4 w-4" />
                                </Link>
                            </div>
                        </article>
                    </div>
                </section>

                <section
                    v-else
                    class="mt-8 rounded-2xl border border-slate-200/80 bg-white p-10 text-center shadow-sm"
                >
                    <h3 class="text-xl font-semibold text-slate-900">
                        No blog posts found
                    </h3>
                    <p class="mt-2 text-sm text-slate-600">
                        Try changing your filters or search query to find more
                        articles.
                    </p>
                </section>

                <PublicPagination
                    class="mt-10"
                    :links="posts.links"
                    :current-page="posts.current_page"
                    :last-page="posts.last_page"
                    :from="posts.from"
                    :to="posts.to"
                    :total="posts.total"
                />
            </div>
        </div>
    </PublicLayout>
</template>
