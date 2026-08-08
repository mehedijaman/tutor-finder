<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import TutorialForm from '@/components/admin/tutorials/TutorialForm.vue';
import AdminLayout from '@/layouts/AdminLayout.vue';

const props = defineProps({
    tutorial: {
        type: Object,
        required: true,
    },
    audienceOptions: {
        type: Array,
        default: () => [],
    },
});

const breadcrumbs = [
    { title: 'Tutorials', href: '/admin/tutorials' },
    {
        title: 'Edit Tutorial',
        href: `/admin/tutorials/${props.tutorial.id}/edit`,
    },
];
</script>

<template>
    <Head :title="`Edit: ${tutorial.title}`" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-4 sm:p-6 lg:p-8">
            <div
                class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6 dark:border-slate-800 dark:bg-slate-900"
            >
                <h1
                    class="text-2xl font-semibold tracking-tight text-slate-900 sm:text-3xl dark:text-slate-100"
                >
                    Edit Tutorial
                </h1>
            </div>

            <TutorialForm
                :action="`/admin/tutorials/${tutorial.id}`"
                method="put"
                submit-label="Update Tutorial"
                :initial="tutorial"
                :audience-options="audienceOptions"
            />
        </div>
    </AdminLayout>
</template>
