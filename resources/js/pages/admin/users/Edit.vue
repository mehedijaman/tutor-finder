<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AdminLayout from '@/layouts/AdminLayout.vue';

const props = defineProps<{
    adminUser: any;
    roles: string[];
    permissions: string[];
}>();

const breadcrumbs = [
    { title: 'Admin Users', href: '/admin/users' },
    { title: 'Edit', href: '#' },
];
</script>

<template>
    <Head title="Edit Admin User" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-4 sm:p-6 lg:p-8">
            <div
                class="flex flex-wrap items-center justify-between gap-4 rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6"
            >
                <div class="flex items-center gap-4">
                    <Avatar
                        class="h-14 w-14 border-2 border-white shadow-sm ring-1 ring-slate-100"
                    >
                        <AvatarImage
                            v-if="adminUser.photo_url"
                            :src="adminUser.photo_url"
                            :alt="adminUser.name"
                        />
                        <AvatarFallback
                            class="bg-indigo-50 text-lg font-bold text-indigo-700 uppercase"
                        >
                            {{
                                adminUser.name
                                    ?.split(' ')
                                    .map((n: string) => n[0])
                                    .join('')
                                    .slice(0, 2)
                            }}
                        </AvatarFallback>
                    </Avatar>
                    <div class="space-y-1">
                        <h1
                            class="text-2xl sm:text-3xl font-semibold tracking-tight text-slate-900"
                        >
                            Edit Admin User
                        </h1>
                        <p class="text-sm text-slate-600">
                            Update account details, status, roles, and
                            permissions.
                        </p>
                    </div>
                </div>
                <Link
                    href="/admin/users"
                    class="inline-flex h-9 items-center rounded-md border border-slate-200 px-3 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
                    >Back</Link
                >
            </div>

            <Form
                :action="`/admin/users/${adminUser.id}`"
                method="put"
                class="space-y-6 rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6"
                #default="{ errors, processing }"
            >
                <div class="grid gap-4 sm:grid-cols-3">
                    <div class="grid gap-2">
                        <Label for="name">Name</Label>
                        <Input
                            id="name"
                            name="name"
                            type="text"
                            :default-value="adminUser.name"
                            required
                        />
                        <InputError :message="errors.name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="email">Email</Label>
                        <Input
                            id="email"
                            name="email"
                            type="email"
                            :default-value="adminUser.email"
                            required
                        />
                        <InputError :message="errors.email" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="status">Status</Label>
                        <select
                            id="status"
                            name="status"
                            class="h-10 w-full rounded-md border px-3 text-sm"
                        >
                            <option
                                value="active"
                                :selected="adminUser.status === 'active'"
                            >
                                Active
                            </option>
                            <option
                                value="suspended"
                                :selected="adminUser.status === 'suspended'"
                            >
                                Suspended
                            </option>
                        </select>
                        <InputError :message="errors.status" />
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
                                <input
                                    type="checkbox"
                                    name="roles[]"
                                    :value="role"
                                    :checked="adminUser.roles.includes(role)"
                                    class="h-4 w-4 rounded border"
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
                                <input
                                    type="checkbox"
                                    name="permissions[]"
                                    :value="permission"
                                    :checked="
                                        adminUser.permissions.includes(
                                            permission,
                                        )
                                    "
                                    class="h-4 w-4 rounded border"
                                />
                                <span>{{ permission }}</span>
                            </label>
                        </div>
                        <InputError :message="errors.permissions" />
                    </div>
                </div>

                <Button type="submit" :disabled="processing"
                    >Update Admin</Button
                >
            </Form>
        </div>
    </AdminLayout>
</template>
