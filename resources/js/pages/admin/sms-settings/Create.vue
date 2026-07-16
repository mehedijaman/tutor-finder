<script setup lang="ts">
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
            <div class="space-y-6 p-4 sm:p-6 lg:p-8">
                <div
                    class="flex flex-wrap items-start justify-between gap-4 rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6"
                >
                    <div class="space-y-1">
                        <h1
                            class="text-2xl sm:text-3xl font-semibold tracking-tight text-slate-900"
                        >
                            Create SMS Setting
                        </h1>
                        <p class="text-sm text-slate-600">
                            Configure a new provider profile and credentials.
                        </p>
                    </div>
                    <Link
                        href="/settings/sms"
                        class="inline-flex h-9 items-center rounded-md border border-slate-200 px-3 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
                        >Back</Link
                    >
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
