<script setup>
import { Form, Head, Link } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AdminLayout from '@/layouts/AdminLayout.vue';

defineProps({
    permissions: {
        type: Array,
        default: () => [],
    },
});

const breadcrumbs = [
    { title: 'Roles', href: '/admin/roles' },
    { title: 'Create', href: '/admin/roles/create' },
];
</script>

<template>
    <Head title="Create Role" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-semibold">Create Role</h1>
                <Link
                    href="/admin/roles"
                    class="text-sm text-muted-foreground underline"
                    >Back</Link
                >
            </div>

            <Form
                action="/admin/roles"
                method="post"
                class="rounded-xl border bg-white p-4"
                #default="{ errors, processing }"
            >
                <div class="grid gap-4 md:grid-cols-2">
                    <div class="grid gap-2">
                        <Label for="name">Role name</Label>
                        <Input
                            id="name"
                            name="name"
                            type="text"
                            required
                            placeholder="manager"
                        />
                        <InputError :message="errors.name" />
                    </div>
                </div>

                <div class="mt-4 space-y-2">
                    <h2 class="text-sm font-medium">Permissions</h2>
                    <label
                        v-for="permission in permissions"
                        :key="permission"
                        class="flex items-center gap-2 text-sm"
                    >
                        <Checkbox
                            :id="`role-perm-${permission}`"
                            name="permissions[]"
                            :value="permission"
                        />
                        <span>{{ permission }}</span>
                    </label>
                </div>

                <Button class="mt-4" type="submit" :disabled="processing"
                    >Create Role</Button
                >
            </Form>
        </div>
    </AdminLayout>
</template>
