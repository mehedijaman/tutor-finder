<script setup>
import { Form, Head, Link } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AdminLayout from '@/layouts/AdminLayout.vue';

defineProps({
    guardian: {
        type: Object,
        required: true,
    },
});

const breadcrumbs = [
    { title: 'Guardians', href: '/admin/guardians' },
    { title: 'Edit', href: '#' },
];
</script>

<template>
    <Head title="Edit Guardian" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-semibold">Edit Guardian</h1>
                <Link
                    href="/admin/guardians"
                    class="text-sm text-muted-foreground underline"
                    >Back</Link
                >
            </div>

            <Form
                :action="`/admin/guardians/${guardian.id}`"
                method="put"
                class="space-y-4"
                #default="{ errors, processing }"
            >
                <div class="grid gap-4 md:grid-cols-2">
                    <div class="grid gap-2">
                        <Label for="name">Name</Label>
                        <Input
                            id="name"
                            name="name"
                            type="text"
                            :default-value="guardian.name"
                            required
                        />
                        <InputError :message="errors.name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="phone">Phone</Label>
                        <Input
                            id="phone"
                            name="phone"
                            type="text"
                            :default-value="guardian.phone || ''"
                        />
                        <InputError :message="errors.phone" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="email">Email</Label>
                        <Input
                            id="email"
                            name="email"
                            type="email"
                            :default-value="guardian.email || ''"
                        />
                        <InputError :message="errors.email" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="status">Status</Label>
                        <select
                            id="status"
                            name="status"
                            class="h-10 rounded-md border px-3 text-sm"
                        >
                            <option
                                value="active"
                                :selected="guardian.status === 'active'"
                            >
                                Active
                            </option>
                            <option
                                value="suspended"
                                :selected="guardian.status === 'suspended'"
                            >
                                Suspended
                            </option>
                            <option
                                value="pending_verification"
                                :selected="
                                    guardian.status === 'pending_verification'
                                "
                            >
                                Pending verification
                            </option>
                        </select>
                        <InputError :message="errors.status" />
                    </div>
                </div>

                <Button type="submit" :disabled="processing"
                    >Update Guardian</Button
                >
            </Form>
        </div>
    </AdminLayout>
</template>
