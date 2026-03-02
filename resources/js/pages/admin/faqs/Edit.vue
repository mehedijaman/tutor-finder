<script setup>
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
        <div class="space-y-6 p-6">
            <div class="flex items-center justify-between gap-3">
                <h1 class="text-2xl font-semibold">Edit FAQ</h1>
                <Link href="/admin/faqs" class="text-sm text-muted-foreground underline">
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
