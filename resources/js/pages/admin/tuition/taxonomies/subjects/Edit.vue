<script setup>
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
    { title: 'Edit', href: `/admin/tuition/taxonomies/subjects/${props.subject.id}/edit` },
];
</script>

<template>
    <Head :title="`Edit Subject - ${props.subject.name}`" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <div class="flex items-center justify-between gap-3">
                <h1 class="text-2xl font-semibold">Edit Subject</h1>
                <Link href="/admin/tuition/taxonomies/subjects" class="text-sm text-muted-foreground underline">Back</Link>
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
