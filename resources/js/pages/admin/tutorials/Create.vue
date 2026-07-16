<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import TutorialForm from '@/components/admin/tutorials/TutorialForm.vue';
import AdminLayout from '@/layouts/AdminLayout.vue';

defineProps({
    audienceOptions: {
        type: Array,
        default: () => [],
    },
});

const breadcrumbs = [
    { title: 'Tutorials', href: '/admin/tutorials' },
    { title: 'Create Tutorial', href: '/admin/tutorials/create' },
];
</script>

<template>
    <Head title="Create Tutorial" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-4 sm:p-6 lg:p-8">
            <div
                class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6"
            >
                <h1 class="text-2xl sm:text-3xl font-semibold tracking-tight">
                    Create Tutorial
                </h1>
            </div>

            <TutorialForm
                action="/admin/tutorials"
                submit-label="Create Tutorial"
                :audience-options="audienceOptions"
            />
        </div>
    </AdminLayout>
</template>
