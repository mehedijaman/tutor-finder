<script setup lang="ts">
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
        <div class="space-y-6 p-4 sm:p-6 lg:p-8">
            <div
                class="flex flex-wrap items-start justify-between gap-4 rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6"
            >
                <div class="space-y-1">
                    <h1
                        class="text-2xl font-semibold tracking-tight text-slate-900 sm:text-3xl"
                    >
                        Edit Guardian
                    </h1>
                    <p class="text-sm text-slate-600">
                        Update guardian details and account status.
                    </p>
                </div>
                <Link
                    href="/admin/guardians"
                    class="inline-flex h-9 items-center rounded-md border border-slate-200 px-3 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
                    >Back</Link
                >
            </div>

            <Form
                :action="`/admin/guardians/${guardian.id}`"
                method="put"
                class="space-y-6 rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6"
                #default="{ errors, processing }"
            >
                <div class="grid gap-4 sm:grid-cols-2">
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
                            class="h-10 w-full rounded-md border px-3 text-sm"
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
