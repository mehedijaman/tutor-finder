<script setup>
import { Form, Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';

defineProps({
    tutors: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
});

const breadcrumbs = [
    { title: 'Tutors', href: '/admin/tutors' },
];
</script>

<template>
    <Head title="Manage Tutors" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-4 p-6">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-semibold">
                    {{ filters.trash ? 'Tutor Recycle Bin' : 'Tutors' }}
                </h1>
                <Link
                    :href="filters.trash ? '/admin/tutors' : '/admin/tutors?trash=1'"
                    class="rounded-md border px-4 py-2 text-sm"
                >
                    {{ filters.trash ? 'Back to Active' : 'Recycle Bin' }}
                </Link>
            </div>

            <Form method="get" action="/admin/tutors" class="grid gap-3 rounded-xl border bg-white p-4 md:grid-cols-4" #default="{ processing }">
                <input name="name" :value="filters.name || ''" placeholder="Name" class="h-10 rounded-md border px-3 text-sm" />
                <input name="phone" :value="filters.phone || ''" placeholder="Phone" class="h-10 rounded-md border px-3 text-sm" />
                <select name="status" :value="filters.status || ''" class="h-10 rounded-md border px-3 text-sm">
                    <option value="">All status</option>
                    <option value="active">Active</option>
                    <option value="suspended">Suspended</option>
                    <option value="pending_verification">Pending</option>
                </select>
                <input type="hidden" name="trash" :value="filters.trash ? 1 : 0">
                <button type="submit" class="rounded-md bg-black px-4 py-2 text-sm text-white" :disabled="processing">Filter</button>
            </Form>

            <div class="overflow-hidden rounded-xl border bg-white">
                <table class="w-full text-left text-sm">
                    <thead class="bg-muted/40">
                        <tr>
                            <th class="px-4 py-3">Name</th>
                            <th class="px-4 py-3">Phone</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="tutor in tutors.data" :key="tutor.id" class="border-t">
                            <td class="px-4 py-3">{{ tutor.name }}</td>
                            <td class="px-4 py-3">{{ tutor.phone || '—' }}</td>
                            <td class="px-4 py-3">{{ tutor.status }}</td>
                            <td class="px-4 py-3">
                                <div v-if="!filters.trash" class="flex items-center gap-2">
                                    <Link :href="`/admin/tutors/${tutor.id}`" class="text-sm underline">View</Link>
                                    <Link :href="`/admin/tutors/${tutor.id}/edit`" class="text-sm underline">Edit</Link>
                                    <Form :action="`/admin/tutors/${tutor.id}/status`" method="patch" #default="{ processing }">
                                        <input type="hidden" name="status" :value="tutor.status === 'active' ? 'suspended' : 'active'" />
                                        <button type="submit" class="text-sm underline" :disabled="processing">
                                            {{ tutor.status === 'active' ? 'Suspend' : 'Activate' }}
                                        </button>
                                    </Form>
                                    <Form :action="`/admin/tutors/${tutor.id}`" method="delete" #default="{ processing }">
                                        <button type="submit" class="text-sm underline text-rose-600" :disabled="processing">
                                            Delete
                                        </button>
                                    </Form>
                                </div>
                                <Form v-else :action="`/admin/tutors/${tutor.id}/restore`" method="patch" #default="{ processing }">
                                    <button type="submit" class="text-sm underline" :disabled="processing">
                                        Restore
                                    </button>
                                </Form>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AdminLayout>
</template>
