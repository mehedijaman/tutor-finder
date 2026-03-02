<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

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
</script>

<template>
    <Head :title="meta.title">
        <meta name="description" :content="meta.description">
    </Head>

    <div class="min-h-screen bg-slate-50 p-6">
        <div class="mx-auto max-w-4xl space-y-6">
            <Link href="/blog" class="inline-block text-sm text-blue-600 hover:underline">
                ← Back to Blog
            </Link>

            <article class="overflow-hidden rounded-2xl border bg-white">
                <img
                    v-if="post.cover_url"
                    :src="post.cover_url"
                    :alt="post.title"
                    class="h-64 w-full object-cover"
                >

                <div class="space-y-5 p-6 md:p-8">
                    <div class="flex flex-wrap items-center gap-2 text-xs text-muted-foreground">
                        <span
                            v-for="category in post.categories"
                            :key="`post-category-${category.slug}`"
                            class="rounded-full border px-2 py-0.5"
                        >
                            {{ category.name }}
                        </span>
                    </div>

                    <h1 class="text-3xl font-bold">{{ post.title }}</h1>

                    <div class="text-sm text-muted-foreground">
                        {{ post.author_name || 'Editorial Team' }}
                        <span class="mx-1">•</span>
                        {{ post.published_at ? new Date(post.published_at).toLocaleDateString() : 'Unpublished' }}
                    </div>

                    <p v-if="post.summary" class="text-muted-foreground">
                        {{ post.summary }}
                    </p>

                    <div class="prose max-w-none" v-html="post.content"></div>

                    <div v-if="post.tags.length" class="flex flex-wrap items-center gap-2 pt-3">
                        <span class="text-sm font-medium text-muted-foreground">Tags:</span>
                        <Link
                            v-for="tag in post.tags"
                            :key="`post-tag-${tag.slug}`"
                            :href="`/blog?tag=${tag.slug}`"
                            class="rounded-full border px-2 py-0.5 text-xs hover:bg-muted"
                        >
                            {{ tag.name }}
                        </Link>
                    </div>
                </div>
            </article>
        </div>
    </div>
</template>
