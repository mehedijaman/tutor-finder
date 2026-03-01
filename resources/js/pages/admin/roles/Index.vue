<script setup>
import { Form, Head } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AdminLayout from '@/layouts/AdminLayout.vue';

defineProps({
    roles: {
        type: Object,
        required: true,
    },
    permissions: {
        type: Array,
        default: () => [],
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
});

const breadcrumbs = [
    { title: 'Roles', href: '/admin/roles' },
];
</script>

<template>
    <Head title="Manage Roles" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-semibold">
                    {{ filters.trash ? 'Role Recycle Bin' : 'Role Management' }}
                </h1>
                <a
                    :href="filters.trash ? '/admin/roles' : '/admin/roles?trash=1'"
                    class="rounded-md border px-4 py-2 text-sm"
                >
                    {{ filters.trash ? 'Back to Active' : 'Recycle Bin' }}
                </a>
            </div>

            <Form v-if="!filters.trash" action="/admin/roles" method="post" class="rounded-xl border bg-white p-4" #default="{ errors, processing }">
                <div class="grid gap-4 md:grid-cols-2">
                    <div class="grid gap-2">
                        <Label for="name">Role name</Label>
                        <Input id="name" name="name" type="text" required placeholder="manager" />
                        <InputError :message="errors.name" />
                    </div>
                </div>

                <div class="mt-4 space-y-2">
                    <h2 class="text-sm font-medium">Permissions</h2>
                    <label v-for="permission in permissions" :key="permission" class="flex items-center gap-2 text-sm">
                        <Checkbox :id="`role-perm-${permission}`" name="permissions[]" :value="permission" />
                        <span>{{ permission }}</span>
                    </label>
                </div>

                <Button class="mt-4" type="submit" :disabled="processing">Create Role</Button>
            </Form>

            <div class="overflow-hidden rounded-xl border bg-white">
                <table class="w-full text-left text-sm">
                    <thead class="bg-muted/40">
                        <tr>
                            <th class="px-4 py-3">Role</th>
                            <th class="px-4 py-3">Permissions</th>
                            <th class="px-4 py-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="role in roles.data" :key="role.id" class="border-t">
                            <td class="px-4 py-3">{{ role.name }}</td>
                            <td class="px-4 py-3">{{ role.permissions.join(', ') || '—' }}</td>
                            <td class="px-4 py-3">
                                <div v-if="!filters.trash" class="flex items-center gap-2">
                                    <a :href="`/admin/roles/${role.id}/edit`" class="text-sm underline">Edit</a>
                                    <Form :action="`/admin/roles/${role.id}`" method="delete" #default="{ processing }">
                                        <button type="submit" class="text-sm underline text-rose-600" :disabled="processing">
                                            Delete
                                        </button>
                                    </Form>
                                </div>
                                <Form v-else :action="`/admin/roles/${role.id}/restore`" method="patch" #default="{ processing }">
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
