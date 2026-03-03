<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';

const props = defineProps({
    paymentSettings: {
        type: Object,
        required: true,
    },
});

const breadcrumbs = [
    { title: 'Settings', href: '/settings' },
    { title: 'Payment Settings', href: '/settings/payment' },
];

const form = useForm({
    bkash: {
        status: props.paymentSettings.bkash?.status ?? 'active',
        app_key: props.paymentSettings.bkash?.app_key ?? '',
        app_secret: '',
        username: props.paymentSettings.bkash?.username ?? '',
        password: '',
        base_url: props.paymentSettings.bkash?.base_url ?? '',
    },
    sslcommerz: {
        status: props.paymentSettings.sslcommerz?.status ?? 'active',
        store_id: props.paymentSettings.sslcommerz?.store_id ?? '',
        store_password: '',
        mode: props.paymentSettings.sslcommerz?.mode ?? 'sandbox',
    },
    manual: {
        status: props.paymentSettings.manual?.status ?? 'active',
        notes:
            props.paymentSettings.manual?.notes ??
            'Manual payment requires admin approval.',
    },
});

function submit() {
    form.put('/settings/payment', {
        preserveScroll: true,
    });
}
</script>

<template>
    <Head title="Payment Settings" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <SettingsLayout full-width>
            <div class="space-y-6">
                <div class="flex items-center justify-between">
                    <h1 class="text-2xl font-semibold">Payment Settings</h1>
                </div>

                <div
                    v-if="$page.props.flash?.status"
                    class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800"
                >
                    {{ $page.props.flash.status }}
                </div>

                <form class="space-y-8" @submit.prevent="submit">
                    <section class="grid gap-4 rounded-xl border bg-white p-4">
                        <div class="flex items-center justify-between gap-3">
                            <h2 class="text-lg font-semibold">bKash</h2>
                            <div class="w-40">
                                <Select v-model="form.bkash.status">
                                    <SelectTrigger>
                                        <SelectValue placeholder="Status" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="active"
                                            >Active</SelectItem
                                        >
                                        <SelectItem value="inactive"
                                            >Inactive</SelectItem
                                        >
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="grid gap-2">
                                <Label for="bkash_app_key">App Key</Label>
                                <Input
                                    id="bkash_app_key"
                                    v-model="form.bkash.app_key"
                                    type="text"
                                />
                                <InputError
                                    :message="form.errors['bkash.app_key']"
                                />
                            </div>

                            <div class="grid gap-2">
                                <Label for="bkash_username">Username</Label>
                                <Input
                                    id="bkash_username"
                                    v-model="form.bkash.username"
                                    type="text"
                                />
                                <InputError
                                    :message="form.errors['bkash.username']"
                                />
                            </div>

                            <div class="grid gap-2">
                                <Label for="bkash_app_secret">App Secret</Label>
                                <Input
                                    id="bkash_app_secret"
                                    v-model="form.bkash.app_secret"
                                    type="password"
                                    :placeholder="
                                        paymentSettings.bkash?.has_app_secret
                                            ? 'Configured (leave blank to keep)'
                                            : 'Enter app secret'
                                    "
                                />
                                <InputError
                                    :message="form.errors['bkash.app_secret']"
                                />
                            </div>

                            <div class="grid gap-2">
                                <Label for="bkash_password">Password</Label>
                                <Input
                                    id="bkash_password"
                                    v-model="form.bkash.password"
                                    type="password"
                                    :placeholder="
                                        paymentSettings.bkash?.has_password
                                            ? 'Configured (leave blank to keep)'
                                            : 'Enter password'
                                    "
                                />
                                <InputError
                                    :message="form.errors['bkash.password']"
                                />
                            </div>
                        </div>

                        <div class="grid gap-2">
                            <Label for="bkash_base_url">Base URL</Label>
                            <Input
                                id="bkash_base_url"
                                v-model="form.bkash.base_url"
                                type="url"
                                placeholder="https://tokenized.sandbox.bka.sh"
                            />
                            <InputError
                                :message="form.errors['bkash.base_url']"
                            />
                        </div>
                    </section>

                    <section class="grid gap-4 rounded-xl border bg-white p-4">
                        <div class="flex items-center justify-between gap-3">
                            <h2 class="text-lg font-semibold">SSLCommerz</h2>
                            <div class="w-40">
                                <Select v-model="form.sslcommerz.status">
                                    <SelectTrigger>
                                        <SelectValue placeholder="Status" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="active"
                                            >Active</SelectItem
                                        >
                                        <SelectItem value="inactive"
                                            >Inactive</SelectItem
                                        >
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="grid gap-2">
                                <Label for="ssl_store_id">Store ID</Label>
                                <Input
                                    id="ssl_store_id"
                                    v-model="form.sslcommerz.store_id"
                                    type="text"
                                />
                                <InputError
                                    :message="
                                        form.errors['sslcommerz.store_id']
                                    "
                                />
                            </div>

                            <div class="grid gap-2">
                                <Label for="ssl_store_password"
                                    >Store Password</Label
                                >
                                <Input
                                    id="ssl_store_password"
                                    v-model="form.sslcommerz.store_password"
                                    type="password"
                                    :placeholder="
                                        paymentSettings.sslcommerz
                                            ?.has_store_password
                                            ? 'Configured (leave blank to keep)'
                                            : 'Enter store password'
                                    "
                                />
                                <InputError
                                    :message="
                                        form.errors['sslcommerz.store_password']
                                    "
                                />
                            </div>

                            <div class="grid gap-2 md:max-w-xs">
                                <Label>Mode</Label>
                                <Select v-model="form.sslcommerz.mode">
                                    <SelectTrigger>
                                        <SelectValue
                                            placeholder="Select mode"
                                        />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="sandbox"
                                            >Sandbox</SelectItem
                                        >
                                        <SelectItem value="live"
                                            >Live</SelectItem
                                        >
                                    </SelectContent>
                                </Select>
                                <InputError
                                    :message="form.errors['sslcommerz.mode']"
                                />
                            </div>
                        </div>
                    </section>

                    <section class="grid gap-4 rounded-xl border bg-white p-4">
                        <div class="flex items-center justify-between gap-3">
                            <h2 class="text-lg font-semibold">
                                Manual Payment
                            </h2>
                            <div class="w-40">
                                <Select v-model="form.manual.status">
                                    <SelectTrigger>
                                        <SelectValue placeholder="Status" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="active"
                                            >Active</SelectItem
                                        >
                                        <SelectItem value="inactive"
                                            >Inactive</SelectItem
                                        >
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>

                        <div class="grid gap-2">
                            <Label for="manual_notes">Notes</Label>
                            <textarea
                                id="manual_notes"
                                v-model="form.manual.notes"
                                rows="4"
                                class="rounded-md border px-3 py-2 text-sm"
                            ></textarea>
                            <InputError
                                :message="form.errors['manual.notes']"
                            />
                        </div>
                    </section>

                    <Button type="submit" :disabled="form.processing"
                        >Save Payment Settings</Button
                    >
                </form>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
