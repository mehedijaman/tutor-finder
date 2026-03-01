<script setup>
import { Form, Head, Link } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AdminLayout from '@/layouts/AdminLayout.vue';

defineProps({
    role: {
        type: Object,
        required: true,
    },
    permissions: {
        type: Array,
        default: () => [],
    },
});

const breadcrumbs = [
    { title: 'Roles', href: '/admin/roles' },
    { title: 'Edit', href: '#' },
];
</script>

<template>
    <Head title="Edit Role" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-semibold">Edit Role</h1>
                <Link href="/admin/roles" class="text-sm text-muted-foreground underline">Back</Link>
            </div>

            <Form :action="`/admin/roles/${role.id}`" method="put" class="rounded-xl border bg-white p-4" #default="{ errors, processing }">
                <div class="grid gap-4 md:grid-cols-2">
                    <div class="grid gap-2">
                        <Label for="name">Role name</Label>
                        <Input id="name" name="name" type="text" :value="role.name" required />
                        <InputError :message="errors.name" />
                    </div>
                </div>

                <div class="mt-4 space-y-2">
                    <h2 class="text-sm font-medium">Permissions</h2>
                    <label v-for="permission in permissions" :key="permission" class="flex items-center gap-2 text-sm">
                        <input
                            type="checkbox"
                            name="permissions[]"
                            :value="permission"
                            :checked="role.permissions.includes(permission)"
                            class="h-4 w-4 rounded border"
                        >
                        <span>{{ permission }}</span>
                    </label>
                </div>

                <Button class="mt-4" type="submit" :disabled="processing">Update Role</Button>
            </Form>
        </div>
    </AdminLayout>
</template>
