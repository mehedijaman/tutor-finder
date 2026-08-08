<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import FaqForm from '@/components/admin/faqs/FaqForm.vue';
import AdminLayout from '@/layouts/AdminLayout.vue';

const props = defineProps({
    audienceOptions: {
        type: Array,
        default: () => [],
    },
    statusOptions: {
        type: Array,
        default: () => [],
    },
});

const breadcrumbs = [
    { title: 'FAQs', href: '/admin/faqs' },
    { title: 'Create', href: '/admin/faqs/create' },
];

const initialValues = {
    question: '',
    answer: '<p></p>',
    audience: 'both',
    status: 'active',
    sort_order: 0,
};
</script>

<template>
    <Head title="Create FAQ" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-4 sm:p-6 lg:p-8">
            <div
                class="flex flex-wrap items-start justify-between gap-4 rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6"
            >
                <div class="space-y-1">
                    <h1
                        class="text-2xl font-semibold tracking-tight text-slate-900 sm:text-3xl"
                    >
                        Create FAQ
                    </h1>
                    <p class="text-sm text-slate-600">
                        Add a new frequently asked question and control
                        visibility.
                    </p>
                </div>
                <Link
                    href="/admin/faqs"
                    class="inline-flex h-9 items-center rounded-md border border-slate-200 px-3 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
                >
                    Back
                </Link>
            </div>

            <FaqForm
                action="/admin/faqs"
                method="post"
                submit-label="Create FAQ"
                :initial="initialValues"
                :audience-options="props.audienceOptions"
                :status-options="props.statusOptions"
            />
        </div>
    </AdminLayout>
</template>
