<script setup>
import { Form, Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';

defineProps({
    tutor: {
        type: Object,
        required: true,
    },
});

const breadcrumbs = [
    { title: 'Tutors', href: '/admin/tutors' },
    { title: 'Details', href: '#' },
];
</script>

<template>
    <Head title="Tutor Details" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-4 p-6">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-semibold">Tutor Details</h1>
                <Link href="/admin/tutors" class="text-sm underline">Back</Link>
            </div>

            <div class="rounded-xl border bg-white p-4 text-sm">
                <p><span class="font-medium">Name:</span> {{ tutor.name }}</p>
                <p><span class="font-medium">Email:</span> {{ tutor.email || '—' }}</p>
                <p><span class="font-medium">Phone:</span> {{ tutor.phone || '—' }}</p>
                <p><span class="font-medium">Status:</span> {{ tutor.status }}</p>
                <p><span class="font-medium">Phone verified:</span> {{ tutor.phone_verified_at || 'No' }}</p>
            </div>

            <Form :action="`/admin/tutors/${tutor.id}/status`" method="patch" #default="{ processing }">
                <input type="hidden" name="status" :value="tutor.status === 'active' ? 'suspended' : 'active'" />
                <button type="submit" class="rounded-md bg-black px-4 py-2 text-sm text-white" :disabled="processing">
                    {{ tutor.status === 'active' ? 'Suspend Tutor' : 'Activate Tutor' }}
                </button>
            </Form>
        </div>
    </AdminLayout>
</template>
