<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import SubjectForm from '@/components/admin/tuition/taxonomies/SubjectForm.vue';
import AdminLayout from '@/layouts/AdminLayout.vue';

const props = defineProps({
    subject: { type: Object, required: true },
    schoolClasses: { type: Array, default: () => [] },
    statusOptions: { type: Array, default: () => [] },
});

const breadcrumbs = [
    { title: 'Subjects', href: '/admin/tuition/taxonomies/subjects' },
    {
        title: 'Edit',
        href: `/admin/tuition/taxonomies/subjects/${props.subject.id}/edit`,
    },
];
</script>

<template>
    <Head :title="`Edit Subject - ${props.subject.name}`" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-4 sm:p-6 lg:p-8">
            <div
                class="flex flex-wrap items-start justify-between gap-4 rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6"
            >
                <div class="space-y-1">
                    <h1
                        class="text-2xl font-semibold tracking-tight text-slate-900 sm:text-3xl"
                    >
                        Edit Subject
                    </h1>
                    <p class="text-sm text-slate-600">
                        Refine subject details, class assignment, and status.
                    </p>
                </div>
                <Link
                    href="/admin/tuition/taxonomies/subjects"
                    class="inline-flex h-9 items-center rounded-md border border-slate-200 px-3 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
                    >Back</Link
                >
            </div>

            <SubjectForm
                :action="`/admin/tuition/taxonomies/subjects/${props.subject.id}`"
                method="put"
                submit-label="Update Subject"
                :initial="props.subject"
                :school-classes="props.schoolClasses"
                :status-options="props.statusOptions"
            />
        </div>
    </AdminLayout>
</template>
