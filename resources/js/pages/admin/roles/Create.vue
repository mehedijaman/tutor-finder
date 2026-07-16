<script setup lang="ts">
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
        <div class="space-y-6 p-4 sm:p-6 lg:p-8">
            <div
                class="flex flex-wrap items-start justify-between gap-4 rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6"
            >
                <div class="space-y-1">
                    <h1
                        class="text-2xl sm:text-3xl font-semibold tracking-tight text-slate-900"
                    >
                        Create Role
                    </h1>
                    <p class="text-sm text-slate-600">
                        Define a new role and assign relevant permissions.
                    </p>
                </div>
                <Link
                    href="/admin/roles"
                    class="inline-flex h-9 items-center rounded-md border border-slate-200 px-3 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
                    >Back</Link
                >
            </div>

            <Form
                action="/admin/roles"
                method="post"
                class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6"
                #default="{ errors, processing }"
            >
                <div class="grid gap-4 sm:grid-cols-2">
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
