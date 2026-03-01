<script setup>
import { Head } from '@inertiajs/vue3';
import GuardianLayout from '@/layouts/GuardianLayout.vue';

const breadcrumbs = [
    { title: 'Guardian Dashboard', href: '/guardian/dashboard' },
];
</script>

<template>
    <Head title="Guardian Dashboard" />

    <GuardianLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <div class="rounded-xl border bg-white p-6 shadow-sm">
                <h1 class="text-2xl font-semibold">Guardian Dashboard</h1>
                <p class="mt-2 text-sm text-muted-foreground">
                    You are logged in as a guardian.
                </p>
            </div>
        </div>
    </GuardianLayout>
</template>
