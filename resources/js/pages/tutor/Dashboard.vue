<script setup>
import { Head } from '@inertiajs/vue3';
import TutorLayout from '@/layouts/TutorLayout.vue';

const breadcrumbs = [{ title: 'Tutor Dashboard', href: '/tutor/dashboard' }];
</script>

<template>
    <Head title="Tutor Dashboard" />

    <TutorLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <div class="rounded-xl border bg-white p-6 shadow-sm">
                <h1 class="text-2xl font-semibold">Tutor Dashboard</h1>
                <p class="mt-2 text-sm text-muted-foreground">
                    You are logged in as a tutor.
                </p>
            </div>
        </div>
    </TutorLayout>
</template>
