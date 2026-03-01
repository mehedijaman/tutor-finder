<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

type Post = {
    id: number;
    title: string;
    excerpt: string;
    category: string;
    author: string;
    date: string;
    featured?: boolean;
};

const posts = ref<Post[]>([
    {
        id: 1,
        title: 'How to Choose the Right Tutor for Your Child',
        excerpt: 'Finding the right tutor can significantly impact your child’s academic success. Here’s what to look for...',
        category: 'Parents',
        author: 'Tutor Finder Team',
        date: 'March 1, 2026',
        featured: true,
    },
    {
        id: 2,
        title: 'How Tutors Can Stand Out in a Competitive Market',
        excerpt: 'With more tutors joining online platforms, it’s important to differentiate yourself...',
        category: 'Tutors',
        author: 'Tutor Finder Team',
        date: 'February 26, 2026',
    },
    {
        id: 3,
        title: 'Top Study Techniques That Actually Work',
        excerpt: 'Evidence-based study techniques can dramatically improve performance...',
        category: 'Students',
        author: 'Tutor Finder Team',
        date: 'February 20, 2026',
    },
]);

const search = ref('');
const categoryFilter = ref('All');

const filteredPosts = computed(() => {
    return posts.value.filter((post) => {
        const matchesSearch =
            post.title.toLowerCase().includes(search.value.toLowerCase());

        const matchesCategory =
            categoryFilter.value === 'All' ||
            post.category === categoryFilter.value;

        return matchesSearch && matchesCategory;
    });
});

const featuredPost = computed(() =>
    posts.value.find((post) => post.featured)
);
</script>

<template>
<Head title="Blog" />

<div class="min-h-screen bg-slate-50 p-6">

<!-- Header -->
<div class="mb-10">
<h1 class="text-3xl font-extrabold">Tutor Finder Blog</h1>
<p class="text-slate-600 mt-2">
Insights, tips, and resources for tutors and families.
</p>
</div>

<!-- Featured Post -->
<div v-if="featuredPost"
class="bg-gradient-to-r from-blue-600 to-sky-500 text-white p-8 rounded-2xl mb-10">

<h2 class="text-2xl font-bold">
{{ featuredPost.title }}
</h2>

<p class="mt-3 text-white/90 max-w-2xl">
{{ featuredPost.excerpt }}
</p>

<Link
:href="`/blog/${featuredPost.id}`"
class="mt-5 inline-block bg-white text-blue-600 px-5 py-2 rounded font-semibold"
>
Read More →
</Link>

</div>

<!-- Filters -->
<div class="grid md:grid-cols-3 gap-4 mb-8">

<input
v-model="search"
placeholder="Search posts..."
class="border p-3 rounded"
/>

<select
v-model="categoryFilter"
class="border p-3 rounded"
>
<option>All</option>
<option>Parents</option>
<option>Tutors</option>
<option>Students</option>
</select>

</div>

<!-- Blog Grid -->
<div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">

<div
v-for="post in filteredPosts"
:key="post.id"
class="bg-white p-6 rounded-xl shadow border hover:shadow-lg transition"
>

<p class="text-xs text-blue-600 font-semibold">
{{ post.category }}
</p>

<h3 class="mt-2 text-lg font-bold">
{{ post.title }}
</h3>

<p class="mt-3 text-sm text-slate-600">
{{ post.excerpt }}
</p>

<div class="mt-5 flex justify-between items-center text-xs text-slate-500">
<span>{{ post.author }}</span>
<span>{{ post.date }}</span>
</div>

<Link
:href="`/blog/${post.id}`"
class="mt-4 inline-block text-blue-600 font-semibold"
>
Read More →
</Link>

</div>

</div>

</div>
</template>