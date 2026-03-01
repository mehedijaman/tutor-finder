<script setup>
import { Head, Link } from '@inertiajs/vue3';
import SmsSettingForm from '@/components/admin/sms-settings/SmsSettingForm.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';

const props = defineProps({
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
    { title: 'SMS Settings', href: '/settings/sms' },
    { title: 'Edit', href: '#' },
];
</script>

<template>
    <Head title="Edit SMS Setting" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <SettingsLayout full-width>
            <div class="space-y-6">
                <div class="flex items-center justify-between">
                    <h1 class="text-2xl font-semibold">Edit SMS Setting</h1>
                    <Link href="/settings/sms" class="text-sm text-muted-foreground underline">Back</Link>
                </div>

                <SmsSettingForm
                    :action="`/settings/sms/${smsSetting.id}`"
                    method="put"
                    submit-label="Update SMS Setting"
                    :providers="providers"
                    :initial="smsSetting"
                />
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
