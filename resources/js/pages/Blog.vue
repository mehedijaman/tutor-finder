<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
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

const formatPaginationLabel = (label: string) =>
    String(label ?? '')
        .replaceAll('&laquo;', '«')
        .replaceAll('&raquo;', '»')
        .replace(/<[^>]*>/g, '')
        .trim();
</script>

<template>
    <Head :title="meta.title">
        <meta name="description" :content="meta.description" />
    </Head>

    <PublicLayout>
        <div class="min-h-screen bg-slate-50 p-6">
            <div class="mx-auto max-w-6xl space-y-6">
                <div class="space-y-2">
                    <h1 class="text-3xl font-bold">
                        {{ meta.title || `${siteName} Blog` }}
                    </h1>
                    <p class="text-sm text-muted-foreground">
                        {{ meta.description }}
                    </p>
                </div>

                <form
                    class="grid gap-3 rounded-xl border bg-white p-4 md:grid-cols-[1fr_220px_220px_auto]"
                    @submit.prevent="applyFilters"
                >
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Search posts..."
                        class="h-10 rounded-md border px-3 text-sm"
                    />
                    <select
                        v-model="selectedCategory"
                        class="h-10 rounded-md border px-3 text-sm"
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
                        class="h-10 rounded-md border px-3 text-sm"
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
                    <div class="flex gap-2">
                        <button
                            type="submit"
                            class="inline-flex h-10 items-center justify-center rounded-md bg-black px-4 text-sm text-white"
                        >
                            Filter
                        </button>
                        <button
                            type="button"
                            class="inline-flex h-10 items-center justify-center rounded-md border px-4 text-sm"
                            @click="resetFilters"
                        >
                            Reset
                        </button>
                    </div>
                </form>

                <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    <article
                        v-for="post in posts.data"
                        :key="post.id"
                        class="overflow-hidden rounded-xl border bg-white"
                    >
                        <img
                            v-if="post.cover_url"
                            :src="post.cover_url"
                            :alt="post.title"
                            class="h-44 w-full object-cover"
                        />
                        <div class="space-y-3 p-4">
                            <div
                                class="flex flex-wrap items-center gap-2 text-xs text-muted-foreground"
                            >
                                <span
                                    v-for="category in post.categories"
                                    :key="`post-${post.id}-category-${category.slug}`"
                                    class="rounded-full border px-2 py-0.5"
                                >
                                    {{ category.name }}
                                </span>
                            </div>

                            <h2 class="line-clamp-2 text-lg font-semibold">
                                <Link
                                    :href="`/blog/${post.slug}`"
                                    class="hover:underline"
                                >
                                    {{ post.title }}
                                </Link>
                            </h2>

                            <p
                                class="line-clamp-3 text-sm text-muted-foreground"
                            >
                                {{ post.summary || 'No summary available.' }}
                            </p>

                            <div class="text-xs text-muted-foreground">
                                {{
                                    post.published_at
                                        ? new Date(
                                              post.published_at,
                                          ).toLocaleDateString()
                                        : 'Unpublished'
                                }}
                            </div>
                        </div>
                    </article>
                </div>

                <div
                    v-if="posts.data.length === 0"
                    class="rounded-xl border bg-white p-8 text-center text-muted-foreground"
                >
                    No blog posts found.
                </div>

                <div
                    v-if="posts.links && posts.links.length > 3"
                    class="flex flex-wrap items-center justify-center gap-2"
                >
                    <template
                        v-for="(link, index) in posts.links"
                        :key="`${index}-${link.label}`"
                    >
                        <span
                            v-if="!link.url"
                            class="inline-flex h-8 items-center justify-center rounded-md border px-3 text-xs text-muted-foreground"
                        >
                            {{ formatPaginationLabel(link.label) }}
                        </span>
                        <Link
                            v-else
                            :href="link.url"
                            preserve-scroll
                            class="inline-flex h-8 items-center justify-center rounded-md border px-3 text-xs"
                            :class="
                                link.active
                                    ? 'bg-black text-white'
                                    : 'bg-white hover:bg-muted'
                            "
                        >
                            {{ formatPaginationLabel(link.label) }}
                        </Link>
                    </template>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>
