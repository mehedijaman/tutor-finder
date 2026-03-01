<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps<{
    job: {
        id: number;
        title: string;
        subject: string;
        level: string;
        type: string;
        location: string;
        budget: number;
        description: string;
    };
}>();
</script>

<template>
<Head :title="props.job.title" />

<div class="min-h-screen bg-slate-50 p-6">

<Link href="/jobs" class="text-blue-600 mb-6 inline-block">
← Back to Jobs
</Link>

<div class="bg-white p-8 rounded-xl shadow border">

<h1 class="text-2xl font-extrabold mb-2">
{{ props.job.title }}
</h1>

<div class="text-slate-500 mb-6">
{{ props.job.subject }} • {{ props.job.level }} • {{ props.job.type }}
</div>

<p class="mb-6">
{{ props.job.description }}
</p>

<div class="flex justify-between items-center">
<p class="text-xl font-bold text-blue-600">
${{ props.job.budget }}/hr
</p>

<button class="bg-blue-600 text-white px-6 py-3 rounded">
Apply Now
</button>
</div>

</div>

</div>
</template>