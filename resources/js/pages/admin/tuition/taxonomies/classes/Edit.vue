<script setup>
import { Head, Link } from '@inertiajs/vue3';
import SchoolClassForm from '@/components/admin/tuition/taxonomies/SchoolClassForm.vue';
import AdminLayout from '@/layouts/AdminLayout.vue';

const props = defineProps({
    schoolClass: { type: Object, required: true },
    categories: { type: Array, default: () => [] },
    statusOptions: { type: Array, default: () => [] },
});

const breadcrumbs = [
    { title: 'Classes', href: '/admin/tuition/taxonomies/classes' },
    { title: 'Edit', href: `/admin/tuition/taxonomies/classes/${props.schoolClass.id}/edit` },
];
</script>

<template>
    <Head :title="`Edit Class - ${props.schoolClass.name}`" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <div class="flex items-center justify-between gap-3">
                <h1 class="text-2xl font-semibold">Edit Class</h1>
                <Link href="/admin/tuition/taxonomies/classes" class="text-sm text-muted-foreground underline">Back</Link>
            </div>

            <SchoolClassForm
                :action="`/admin/tuition/taxonomies/classes/${props.schoolClass.id}`"
                method="put"
                submit-label="Update Class"
                :initial="props.schoolClass"
                :categories="props.categories"
                :status-options="props.statusOptions"
            />
        </div>
    </AdminLayout>
</template>
