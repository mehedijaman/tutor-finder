<script setup>
import { Form, Head, Link } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AdminLayout from '@/layouts/AdminLayout.vue';

const breadcrumbs = [
    { title: 'Guardians', href: '/admin/guardians' },
    { title: 'Create Guardian', href: '/admin/guardians/create' },
];
</script>

<template>
    <Head title="Create Guardian" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-semibold">Create Guardian</h1>
                <Link
                    href="/admin/guardians"
                    class="text-sm text-muted-foreground underline"
                    >Back</Link
                >
            </div>

            <Form
                action="/admin/guardians"
                method="post"
                class="space-y-6"
                #default="{ errors, processing }"
            >
                <div class="grid gap-4 md:grid-cols-2">
                    <div class="grid gap-2">
                        <Label for="name">Name</Label>
                        <Input id="name" name="name" type="text" required />
                        <InputError :message="errors.name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="phone">Phone</Label>
                        <Input
                            id="phone"
                            name="phone"
                            type="text"
                            placeholder="01XXXXXXXXX"
                        />
                        <InputError :message="errors.phone" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="email">Email</Label>
                        <Input id="email" name="email" type="email" />
                        <InputError :message="errors.email" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="status">Status</Label>
                        <select
                            id="status"
                            name="status"
                            class="h-10 rounded-md border px-3 text-sm"
                        >
                            <option value="active">Active</option>
                            <option value="suspended">Suspended</option>
                            <option value="pending_verification">
                                Pending Verification
                            </option>
                        </select>
                        <InputError :message="errors.status" />
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
                            >Confirm Password</Label
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

                <Button type="submit" :disabled="processing"
                    >Create Guardian</Button
                >
            </Form>
        </div>
    </AdminLayout>
</template>
