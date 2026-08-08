<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AdminLayout from '@/layouts/AdminLayout.vue';

defineProps({
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
    { title: 'Create', href: '/admin/users/create' },
];
</script>

<template>
    <Head title="Create Admin User" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-4 sm:p-6 lg:p-8">
            <div
                class="flex flex-wrap items-start justify-between gap-4 rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6"
            >
                <div class="space-y-1">
                    <h1
                        class="text-2xl font-semibold tracking-tight text-slate-900 sm:text-3xl"
                    >
                        Create Admin User
                    </h1>
                    <p class="text-sm text-slate-600">
                        Add an admin account with roles and direct permissions.
                    </p>
                </div>
                <Link
                    href="/admin/users"
                    class="inline-flex h-9 items-center rounded-md border border-slate-200 px-3 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
                    >Back</Link
                >
            </div>

            <Form
                action="/admin/users"
                method="post"
                class="space-y-6 rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6"
                #default="{ errors, processing }"
            >
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="grid gap-2">
                        <Label for="name">Name</Label>
                        <Input id="name" name="name" type="text" required />
                        <InputError :message="errors.name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="email">Email</Label>
                        <Input id="email" name="email" type="email" required />
                        <InputError :message="errors.email" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="password">Password</Label>
                        <Input
                            id="password"
                            name="password"
                            type="password"
                            required
                        />
                        <InputError :message="errors.password" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="password_confirmation"
                            >Confirm password</Label
                        >
                        <Input
                            id="password_confirmation"
                            name="password_confirmation"
                            type="password"
                            required
                        />
                        <InputError :message="errors.password_confirmation" />
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="rounded-xl border border-slate-200/80 p-4">
                        <h2 class="mb-3 text-sm font-medium">Roles</h2>
                        <div class="space-y-2">
                            <label
                                v-for="role in roles"
                                :key="role"
                                class="flex items-center gap-2 text-sm"
                            >
                                <Checkbox
                                    :id="`role-${role}`"
                                    name="roles[]"
                                    :value="role"
                                />
                                <span>{{ role }}</span>
                            </label>
                        </div>
                        <InputError :message="errors.roles" />
                    </div>

                    <div class="rounded-xl border border-slate-200/80 p-4">
                        <h2 class="mb-3 text-sm font-medium">
                            Direct Permissions
                        </h2>
                        <div class="space-y-2">
                            <label
                                v-for="permission in permissions"
                                :key="permission"
                                class="flex items-center gap-2 text-sm"
                            >
                                <Checkbox
                                    :id="`permission-${permission}`"
                                    name="permissions[]"
                                    :value="permission"
                                />
                                <span>{{ permission }}</span>
                            </label>
                        </div>
                        <InputError :message="errors.permissions" />
                    </div>
                </div>

                <Button type="submit" :disabled="processing"
                    >Create Admin</Button
                >
            </Form>
        </div>
    </AdminLayout>
</template>
