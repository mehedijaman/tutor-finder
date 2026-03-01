<script setup>
import { Form, Head, Link } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AdminLayout from '@/layouts/AdminLayout.vue';

defineProps({
    smsSetting: {
        type: Object,
        required: true,
    },
    providers: {
        type: Array,
        default: () => [],
    },
});

const breadcrumbs = [
    { title: 'SMS Settings', href: '/admin/sms-settings' },
    { title: 'Edit', href: '#' },
];
</script>

<template>
    <Head title="Edit SMS Setting" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-semibold">Edit SMS Setting</h1>
                <Link href="/admin/sms-settings" class="text-sm text-muted-foreground underline">Back</Link>
            </div>

            <Form :action="`/admin/sms-settings/${smsSetting.id}`" method="put" class="space-y-6" #default="{ errors, processing }">
                <div class="grid gap-4 md:grid-cols-2">
                    <div class="grid gap-2">
                        <Label for="name">Name</Label>
                        <Input id="name" name="name" type="text" :value="smsSetting.name" required />
                        <InputError :message="errors.name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="provider">Provider</Label>
                        <select id="provider" name="provider" :value="smsSetting.provider" required class="h-10 rounded-md border px-3 text-sm">
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
                    >{{ smsSetting.credentials_json }}</textarea>
                    <InputError :message="errors.credentials_json" />
                </div>

                <div class="grid gap-3 rounded-lg border p-4">
                    <label class="flex items-center gap-2 text-sm">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" :checked="smsSetting.is_active" class="h-4 w-4 rounded border">
                        <span>Active</span>
                    </label>

                    <label class="flex items-center gap-2 text-sm">
                        <input type="hidden" name="is_default" value="0">
                        <input type="checkbox" name="is_default" value="1" :checked="smsSetting.is_default" class="h-4 w-4 rounded border">
                        <span>Set as default</span>
                    </label>

                    <InputError :message="errors.is_active" />
                    <InputError :message="errors.is_default" />
                </div>

                <Button type="submit" :disabled="processing">Update SMS Setting</Button>
            </Form>
        </div>
    </AdminLayout>
</template>
