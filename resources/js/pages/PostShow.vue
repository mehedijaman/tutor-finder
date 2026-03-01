<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps<{
    post: {
        id: number;
        title: string;
        category: string;
        author: string;
        date: string;
        content: string;
    };
}>();
</script>

<template>
<Head :title="post.title" />

<div class="min-h-screen bg-slate-50 p-6">

<Link href="/blog" class="text-blue-600 mb-6 inline-block">
← Back to Blog
</Link>

<article class="bg-white p-8 rounded-2xl shadow border max-w-4xl mx-auto">

<p class="text-sm text-blue-600 font-semibold">
{{ post.category }}
</p>

<h1 class="mt-2 text-3xl font-extrabold">
{{ post.title }}
</h1>

<div class="mt-3 text-sm text-slate-500">
By {{ post.author }} • {{ post.date }}
</div>

<div class="mt-8 prose max-w-none">
<p v-html="post.content"></p>
</div>

</article>

</div>
</template>