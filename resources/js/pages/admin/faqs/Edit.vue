<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import FaqForm from '@/components/admin/faqs/FaqForm.vue';
import AdminLayout from '@/layouts/AdminLayout.vue';

const props = defineProps({
    faq: {
        type: Object,
        required: true,
    },
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
    { title: 'Edit', href: `/admin/faqs/${props.faq.id}/edit` },
];
</script>

<template>
    <Head title="Edit FAQ" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-4 sm:p-6 lg:p-8">
            <div
                class="flex flex-wrap items-start justify-between gap-4 rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6"
            >
                <div class="space-y-1">
                    <h1
                        class="text-2xl font-semibold tracking-tight text-slate-900 sm:text-3xl"
                    >
                        Edit FAQ
                    </h1>
                    <p class="text-sm text-slate-600">
                        Update content, audience, and publishing status.
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
                :action="`/admin/faqs/${faq.id}`"
                method="put"
                submit-label="Update FAQ"
                :initial="faq"
                :audience-options="props.audienceOptions"
                :status-options="props.statusOptions"
            />
        </div>
    </AdminLayout>
</template>
