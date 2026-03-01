<script setup>
import { Form, Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';

defineProps({
    guardian: {
        type: Object,
        required: true,
    },
});

const breadcrumbs = [
    { title: 'Guardians', href: '/admin/guardians' },
    { title: 'Details', href: '#' },
];
</script>

<template>
    <Head title="Guardian Details" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-4 p-6">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-semibold">Guardian Details</h1>
                <Link href="/admin/guardians" class="text-sm underline">Back</Link>
            </div>

            <div class="rounded-xl border bg-white p-4 text-sm">
                <p><span class="font-medium">Name:</span> {{ guardian.name }}</p>
                <p><span class="font-medium">Email:</span> {{ guardian.email || '—' }}</p>
                <p><span class="font-medium">Phone:</span> {{ guardian.phone || '—' }}</p>
                <p><span class="font-medium">Status:</span> {{ guardian.status }}</p>
                <p><span class="font-medium">Phone verified:</span> {{ guardian.phone_verified_at || 'No' }}</p>
            </div>

            <Form :action="`/admin/guardians/${guardian.id}/status`" method="patch" #default="{ processing }">
                <input type="hidden" name="status" :value="guardian.status === 'active' ? 'suspended' : 'active'" />
                <button type="submit" class="rounded-md bg-black px-4 py-2 text-sm text-white" :disabled="processing">
                    {{ guardian.status === 'active' ? 'Suspend Guardian' : 'Activate Guardian' }}
                </button>
            </Form>
        </div>
    </AdminLayout>
</template>
