<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';

defineProps({
    smsSettings: {
        type: Object,
        required: true,
    },
});

const breadcrumbs = [
    { title: 'SMS Settings', href: '/admin/sms-settings' },
];
</script>

<template>
    <Head title="SMS Settings" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-4 p-6">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-semibold">SMS Settings</h1>
                <Link href="/admin/sms-settings/create" class="rounded-md bg-black px-4 py-2 text-sm text-white">
                    Add SMS Setting
                </Link>
            </div>

            <div class="overflow-hidden rounded-xl border bg-white">
                <table class="w-full text-left text-sm">
                    <thead class="bg-muted/40">
                        <tr>
                            <th class="px-4 py-3">Name</th>
                            <th class="px-4 py-3">Provider</th>
                            <th class="px-4 py-3">Credentials</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Default</th>
                            <th class="px-4 py-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="smsSetting in smsSettings.data" :key="smsSetting.id" class="border-t">
                            <td class="px-4 py-3">{{ smsSetting.name }}</td>
                            <td class="px-4 py-3">{{ smsSetting.provider }}</td>
                            <td class="px-4 py-3 text-muted-foreground">
                                {{ smsSetting.credential_keys.length ? smsSetting.credential_keys.join(', ') : '—' }}
                            </td>
                            <td class="px-4 py-3">
                                <span :class="smsSetting.is_active ? 'text-emerald-700' : 'text-rose-700'">
                                    {{ smsSetting.is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                {{ smsSetting.is_default ? 'Yes' : 'No' }}
                            </td>
                            <td class="px-4 py-3">
                                <Link :href="`/admin/sms-settings/${smsSetting.id}/edit`" class="text-sm underline">Edit</Link>
                            </td>
                        </tr>
                        <tr v-if="smsSettings.data.length === 0" class="border-t">
                            <td class="px-4 py-8 text-center text-muted-foreground" colspan="6">
                                No SMS settings found.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AdminLayout>
</template>
