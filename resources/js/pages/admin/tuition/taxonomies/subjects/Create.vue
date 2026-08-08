<script setup lang="ts">
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
        <div class="space-y-6 p-4 sm:p-6 lg:p-8">
            <div
                class="flex flex-wrap items-start justify-between gap-4 rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6"
            >
                <div class="space-y-1">
                    <h1
                        class="text-2xl font-semibold tracking-tight text-slate-900 sm:text-3xl"
                    >
                        Create Subject
                    </h1>
                    <p class="text-sm text-slate-600">
                        Add a subject and assign its class-level mapping.
                    </p>
                </div>
                <Link
                    href="/admin/tuition/taxonomies/subjects"
                    class="inline-flex h-9 items-center rounded-md border border-slate-200 px-3 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
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
