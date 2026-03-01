<script setup>
import { Form, Head, Link } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AdminLayout from '@/layouts/AdminLayout.vue';

defineProps({
    providers: {
        type: Array,
        default: () => [],
    },
});

const breadcrumbs = [
    { title: 'SMS Settings', href: '/admin/sms-settings' },
    { title: 'Create', href: '/admin/sms-settings/create' },
];

const defaultCredentials = '{\n  "api_token": "",\n  "sender_id": ""\n}';
</script>

<template>
    <Head title="Create SMS Setting" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-semibold">Create SMS Setting</h1>
                <Link href="/admin/sms-settings" class="text-sm text-muted-foreground underline">Back</Link>
            </div>

            <Form action="/admin/sms-settings" method="post" class="space-y-6" #default="{ errors, processing }">
                <div class="grid gap-4 md:grid-cols-2">
                    <div class="grid gap-2">
                        <Label for="name">Name</Label>
                        <Input id="name" name="name" type="text" placeholder="Primary OTP Gateway" required />
                        <InputError :message="errors.name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="provider">Provider</Label>
                        <select id="provider" name="provider" required class="h-10 rounded-md border px-3 text-sm">
                            <option value="" disabled selected>Select provider</option>
                            <option v-for="provider in providers" :key="provider" :value="provider">{{ provider }}</option>
                        </select>
                        <InputError :message="errors.provider" />
                    </div>
                </div>

                <div class="grid gap-2">
                    <Label for="credentials_json">Credentials JSON</Label>
                    <textarea
                        id="credentials_json"
                        name="credentials_json"
                        rows="12"
                        class="rounded-md border px-3 py-2 font-mono text-sm"
                        required
                    >{{ defaultCredentials }}</textarea>
                    <InputError :message="errors.credentials_json" />
                </div>

                <div class="grid gap-3 rounded-lg border p-4">
                    <label class="flex items-center gap-2 text-sm">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" checked class="h-4 w-4 rounded border">
                        <span>Active</span>
                    </label>

                    <label class="flex items-center gap-2 text-sm">
                        <input type="hidden" name="is_default" value="0">
                        <input type="checkbox" name="is_default" value="1" class="h-4 w-4 rounded border">
                        <span>Set as default</span>
                    </label>

                    <InputError :message="errors.is_active" />
                    <InputError :message="errors.is_default" />
                </div>

                <Button type="submit" :disabled="processing">Save SMS Setting</Button>
            </Form>
        </div>
    </AdminLayout>
</template>
