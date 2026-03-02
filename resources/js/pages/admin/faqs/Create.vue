<script setup>
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
        <div class="space-y-6 p-6">
            <div class="flex items-center justify-between gap-3">
                <h1 class="text-2xl font-semibold">Create FAQ</h1>
                <Link href="/admin/faqs" class="text-sm text-muted-foreground underline">
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
