<script setup>
import { Head, Link } from '@inertiajs/vue3';
import SmsSettingForm from '@/components/admin/sms-settings/SmsSettingForm.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';

const props = defineProps({
    providers: {
        type: Array,
        default: () => [],
    },
});

const breadcrumbs = [
    { title: 'SMS Settings', href: '/settings/sms' },
    { title: 'Create', href: '/settings/sms/create' },
];

const initialValues = {
    name: '',
    provider: props.providers[0]?.name ?? '',
    is_default: false,
    is_active: true,
    credentials: {},
};
</script>

<template>
    <Head title="Create SMS Setting" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <SettingsLayout full-width>
            <div class="space-y-6">
                <div class="flex items-center justify-between">
                    <h1 class="text-2xl font-semibold">Create SMS Setting</h1>
                    <Link href="/settings/sms" class="text-sm text-muted-foreground underline">Back</Link>
                </div>

                <SmsSettingForm
                    action="/settings/sms"
                    method="post"
                    submit-label="Save SMS Setting"
                    :providers="providers"
                    :initial="initialValues"
                />
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
