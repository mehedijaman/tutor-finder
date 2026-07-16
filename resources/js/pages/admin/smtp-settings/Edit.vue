<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import SmtpSettingForm from '@/components/admin/smtp-settings/SmtpSettingForm.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';

interface Driver {
    name: string;
    label: string;
    fields: Array<{
        key: string;
        label: string;
        required: boolean;
        sensitive: boolean;
        placeholder?: string;
        description?: string;
    }>;
    required_fields: string[];
}

interface SmtpSetting {
    id: number;
    name: string;
    driver: string;
    from_address: string | null;
    from_name: string | null;
    is_default: boolean;
    is_active: boolean;
    credentials: Record<string, string>;
}

defineProps<{
    smtpSetting: SmtpSetting;
    drivers: Driver[];
}>();

const breadcrumbs = [
    { title: 'SMTP Settings', href: '/settings/smtp' },
    { title: 'Edit', href: '#' },
];
</script>

<template>
    <Head title="Edit SMTP Setting" />

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
                            Edit SMTP Setting
                        </h1>
                        <p class="text-sm text-slate-600">
                            Update mail driver credentials and activation state.
                        </p>
                    </div>
                    <Link
                        href="/settings/smtp"
                        class="inline-flex h-9 items-center rounded-md border border-slate-200 px-3 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
                        >Back</Link
                    >
                </div>

                <SmtpSettingForm
                    :action="`/settings/smtp/${smtpSetting.id}`"
                    method="put"
                    submit-label="Update SMTP Setting"
                    :drivers="drivers"
                    :initial="smtpSetting"
                />
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
