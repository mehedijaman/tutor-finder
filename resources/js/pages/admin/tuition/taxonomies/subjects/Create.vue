<script setup>
import { Head, Link } from '@inertiajs/vue3';
import SubjectForm from '@/components/admin/tuition/taxonomies/SubjectForm.vue';
import AdminLayout from '@/layouts/AdminLayout.vue';

const props = defineProps({
    schoolClasses: { type: Array, default: () => [] },
    statusOptions: { type: Array, default: () => [] },
});

const breadcrumbs = [
    { title: 'Subjects', href: '/admin/tuition/taxonomies/subjects' },
    { title: 'Create', href: '/admin/tuition/taxonomies/subjects/create' },
];
</script>

<template>
    <Head title="Create Subject" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <div class="flex items-center justify-between gap-3">
                <h1 class="text-2xl font-semibold">Create Subject</h1>
                <Link
                    href="/admin/tuition/taxonomies/subjects"
                    class="text-sm text-muted-foreground underline"
                    >Back</Link
                >
            </div>

            <SubjectForm
                action="/admin/tuition/taxonomies/subjects"
                method="post"
                submit-label="Create Subject"
                :school-classes="props.schoolClasses"
                :status-options="props.statusOptions"
            />
        </div>
    </AdminLayout>
</template>
