<script setup>
import { Form, Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';

defineProps({
    adminUsers: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
});

const breadcrumbs = [
    { title: 'Admin Users', href: '/admin/users' },
];
</script>

<template>
    <Head title="Admin Users" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-4 p-6">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-semibold">
                    {{ filters.trash ? 'Admin Users Recycle Bin' : 'Admin Users' }}
                </h1>
                <div class="flex items-center gap-2">
                    <Link
                        :href="filters.trash ? '/admin/users' : '/admin/users?trash=1'"
                        class="rounded-md border px-4 py-2 text-sm"
                    >
                        {{ filters.trash ? 'Back to Active' : 'Recycle Bin' }}
                    </Link>
                    <Link v-if="!filters.trash" href="/admin/users/create" class="rounded-md bg-black px-4 py-2 text-sm text-white">
                        Create Admin User
                    </Link>
                </div>
            </div>

            <div class="overflow-hidden rounded-xl border bg-white">
                <table class="w-full text-left text-sm">
                    <thead class="bg-muted/40">
                        <tr>
                            <th class="px-4 py-3">Name</th>
                            <th class="px-4 py-3">Email</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Roles</th>
                            <th class="px-4 py-3">Permissions</th>
                            <th class="px-4 py-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="admin in adminUsers.data" :key="admin.id" class="border-t">
                            <td class="px-4 py-3">{{ admin.name }}</td>
                            <td class="px-4 py-3">{{ admin.email }}</td>
                            <td class="px-4 py-3">{{ admin.status }}</td>
                            <td class="px-4 py-3">{{ admin.roles.map((role) => role.name).join(', ') || '—' }}</td>
                            <td class="px-4 py-3">{{ admin.permissions.map((permission) => permission.name).join(', ') || '—' }}</td>
                            <td class="px-4 py-3">
                                <div v-if="!filters.trash" class="flex items-center gap-2">
                                    <Link :href="`/admin/users/${admin.id}/edit`" class="text-sm underline">Edit</Link>
                                    <Form :action="`/admin/users/${admin.id}`" method="delete" #default="{ processing }">
                                        <button type="submit" class="text-sm underline text-rose-600" :disabled="processing">
                                            Delete
                                        </button>
                                    </Form>
                                </div>
                                <Form v-else :action="`/admin/users/${admin.id}/restore`" method="patch" #default="{ processing }">
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
