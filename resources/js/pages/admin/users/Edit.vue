<script setup>
import { Form, Head, Link } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AdminLayout from '@/layouts/AdminLayout.vue';

defineProps({
    adminUser: {
        type: Object,
        required: true,
    },
    roles: {
        type: Array,
        default: () => [],
    },
    permissions: {
        type: Array,
        default: () => [],
    },
});

const breadcrumbs = [
    { title: 'Admin Users', href: '/admin/users' },
    { title: 'Edit', href: '#' },
];
</script>

<template>
    <Head title="Edit Admin User" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-semibold">Edit Admin User</h1>
                <Link href="/admin/users" class="text-sm text-muted-foreground underline">Back</Link>
            </div>

            <Form :action="`/admin/users/${adminUser.id}`" method="put" class="space-y-6" #default="{ errors, processing }">
                <div class="grid gap-4 md:grid-cols-3">
                    <div class="grid gap-2">
                        <Label for="name">Name</Label>
                        <Input id="name" name="name" type="text" :default-value="adminUser.name" required />
                        <InputError :message="errors.name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="email">Email</Label>
                        <Input id="email" name="email" type="email" :default-value="adminUser.email" required />
                        <InputError :message="errors.email" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="status">Status</Label>
                        <select id="status" name="status" class="h-10 rounded-md border px-3 text-sm">
                            <option value="active" :selected="adminUser.status === 'active'">Active</option>
                            <option value="suspended" :selected="adminUser.status === 'suspended'">Suspended</option>
                        </select>
                        <InputError :message="errors.status" />
                    </div>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <div class="rounded-lg border p-4">
                        <h2 class="mb-3 text-sm font-medium">Roles</h2>
                        <div class="space-y-2">
                            <label v-for="role in roles" :key="role" class="flex items-center gap-2 text-sm">
                                <input
                                    type="checkbox"
                                    name="roles[]"
                                    :value="role"
                                    :checked="adminUser.roles.includes(role)"
                                    class="h-4 w-4 rounded border"
                                >
                                <span>{{ role }}</span>
                            </label>
                        </div>
                        <InputError :message="errors.roles" />
                    </div>

                    <div class="rounded-lg border p-4">
                        <h2 class="mb-3 text-sm font-medium">Direct Permissions</h2>
                        <div class="space-y-2">
                            <label v-for="permission in permissions" :key="permission" class="flex items-center gap-2 text-sm">
                                <input
                                    type="checkbox"
                                    name="permissions[]"
                                    :value="permission"
                                    :checked="adminUser.permissions.includes(permission)"
                                    class="h-4 w-4 rounded border"
                                >
                                <span>{{ permission }}</span>
                            </label>
                        </div>
                        <InputError :message="errors.permissions" />
                    </div>
                </div>

                <Button type="submit" :disabled="processing">Update Admin</Button>
            </Form>
        </div>
    </AdminLayout>
</template>
