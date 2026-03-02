<script setup>
import { Head, Link } from '@inertiajs/vue3';
import SchoolClassForm from '@/components/admin/tuition/taxonomies/SchoolClassForm.vue';
import AdminLayout from '@/layouts/AdminLayout.vue';

const props = defineProps({
    categories: { type: Array, default: () => [] },
    statusOptions: { type: Array, default: () => [] },
});

const breadcrumbs = [
    { title: 'Classes', href: '/admin/tuition/taxonomies/classes' },
    { title: 'Create', href: '/admin/tuition/taxonomies/classes/create' },
];
</script>

<template>
    <Head title="Create Class" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <div class="flex items-center justify-between gap-3">
                <h1 class="text-2xl font-semibold">Create Class</h1>
                <Link href="/admin/tuition/taxonomies/classes" class="text-sm text-muted-foreground underline">Back</Link>
            </div>

            <SchoolClassForm
                action="/admin/tuition/taxonomies/classes"
                method="post"
                submit-label="Create Class"
                :categories="props.categories"
                :status-options="props.statusOptions"
            />
        </div>
    </AdminLayout>
</template>
