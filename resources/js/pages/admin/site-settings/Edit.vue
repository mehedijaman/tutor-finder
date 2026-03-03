<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, ref } from 'vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';

const props = defineProps({
    siteSettingsFull: {
        type: Object,
        required: true,
    },
});

const breadcrumbs = [
    { title: 'Settings', href: '/settings' },
    { title: 'Site Settings', href: '/settings/site' },
];

const phoneNumbers = ref(
    props.siteSettingsFull.phone_numbers?.length
        ? [...props.siteSettingsFull.phone_numbers]
        : [''],
);
const emails = ref(
    props.siteSettingsFull.emails?.length
        ? [...props.siteSettingsFull.emails]
        : [''],
);
const addresses = ref(
    props.siteSettingsFull.addresses?.length
        ? props.siteSettingsFull.addresses.map((address) => ({
              label: address.label ?? '',
              address: address.address ?? '',
              map_url: address.map_url ?? '',
          }))
        : [{ label: '', address: '', map_url: '' }],
);
const socialRows = ref(
    Object.entries(props.siteSettingsFull.social_details ?? {}).length
        ? Object.entries(props.siteSettingsFull.social_details).map(
              ([platform, url]) => ({
                  platform,
                  url,
              }),
          )
        : [{ platform: '', url: '' }],
);

const form = useForm({
    site_name: props.siteSettingsFull.site_name ?? '',
    slogan: props.siteSettingsFull.slogan ?? '',
    description: props.siteSettingsFull.description ?? '',
    trade_licence_no: props.siteSettingsFull.trade_licence_no ?? '',
    tin_no: props.siteSettingsFull.tin_no ?? '',
    bin_no: props.siteSettingsFull.bin_no ?? '',
    remove_logo: false,
    logo: null,
    phone_numbers: phoneNumbers.value,
    emails: emails.value,
    addresses: addresses.value,
    social_details: socialRows.value,
});

const temporaryLogoPreview = ref(null);

const logoUrl = computed(() => {
    if (temporaryLogoPreview.value) {
        return temporaryLogoPreview.value;
    }

    if (form.remove_logo) {
        return null;
    }

    return props.siteSettingsFull.logo_url ?? null;
});

function addPhoneNumber() {
    phoneNumbers.value.push('');
}

function removePhoneNumber(index) {
    if (phoneNumbers.value.length === 1) {
        phoneNumbers.value[0] = '';

        return;
    }

    phoneNumbers.value.splice(index, 1);
}

function addEmail() {
    emails.value.push('');
}

function removeEmail(index) {
    if (emails.value.length === 1) {
        emails.value[0] = '';

        return;
    }

    emails.value.splice(index, 1);
}

function addAddress() {
    addresses.value.push({ label: '', address: '', map_url: '' });
}

function removeAddress(index) {
    if (addresses.value.length === 1) {
        addresses.value[0] = { label: '', address: '', map_url: '' };

        return;
    }

    addresses.value.splice(index, 1);
}

function addSocial() {
    socialRows.value.push({ platform: '', url: '' });
}

function removeSocial(index) {
    if (socialRows.value.length === 1) {
        socialRows.value[0] = { platform: '', url: '' };

        return;
    }

    socialRows.value.splice(index, 1);
}

function releasePreviewUrl() {
    if (temporaryLogoPreview.value) {
        URL.revokeObjectURL(temporaryLogoPreview.value);
        temporaryLogoPreview.value = null;
    }
}

function onLogoChange(event) {
    const target = event.target;
    const file = target.files?.[0] ?? null;

    form.logo = file;
    form.remove_logo = false;

    releasePreviewUrl();

    if (file) {
        temporaryLogoPreview.value = URL.createObjectURL(file);
    }
}

function toggleRemoveLogo() {
    form.remove_logo = !form.remove_logo;

    if (form.remove_logo) {
        form.logo = null;
        releasePreviewUrl();
    }
}

function submit() {
    form.transform((data) => ({
        ...data,
        phone_numbers: phoneNumbers.value,
        emails: emails.value,
        addresses: addresses.value,
        social_details: socialRows.value,
    })).put('/settings/site', {
        forceFormData: true,
        preserveScroll: true,
    });
}

onBeforeUnmount(() => {
    releasePreviewUrl();
});
</script>

<template>
    <Head title="Site Settings" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <SettingsLayout full-width>
            <div class="space-y-6">
                <div class="flex items-center justify-between">
                    <h1 class="text-2xl font-semibold">Site Settings</h1>
                </div>

                <form class="space-y-8" @submit.prevent="submit">
                    <section class="grid gap-4 rounded-xl border bg-white p-4">
                        <h2 class="text-lg font-semibold">Basic Information</h2>

                        <div class="grid gap-2">
                            <Label for="site_name">Site Name</Label>
                            <Input
                                id="site_name"
                                v-model="form.site_name"
                                type="text"
                                required
                            />
                            <InputError :message="form.errors.site_name" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="slogan">Slogan</Label>
                            <Input
                                id="slogan"
                                v-model="form.slogan"
                                type="text"
                            />
                            <InputError :message="form.errors.slogan" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="description">Description</Label>
                            <textarea
                                id="description"
                                v-model="form.description"
                                rows="4"
                                class="rounded-md border px-3 py-2 text-sm"
                            ></textarea>
                            <InputError :message="form.errors.description" />
                        </div>
                    </section>

                    <section class="grid gap-4 rounded-xl border bg-white p-4">
                        <h2 class="text-lg font-semibold">Logo</h2>

                        <div class="flex flex-wrap items-center gap-4">
                            <img
                                v-if="logoUrl"
                                :src="logoUrl"
                                alt="Site Logo"
                                class="h-14 w-14 rounded-md border object-cover"
                            />
                            <div
                                v-else
                                class="flex h-14 w-14 items-center justify-center rounded-md border text-sm text-muted-foreground"
                            >
                                No Logo
                            </div>

                            <Input
                                type="file"
                                accept="image/*"
                                @change="onLogoChange"
                            />

                            <Button
                                type="button"
                                variant="outline"
                                @click="toggleRemoveLogo"
                            >
                                {{
                                    form.remove_logo
                                        ? 'Keep Existing Logo'
                                        : 'Remove Logo'
                                }}
                            </Button>
                        </div>

                        <InputError :message="form.errors.logo" />
                        <InputError :message="form.errors.remove_logo" />
                    </section>

                    <section class="grid gap-4 rounded-xl border bg-white p-4">
                        <div class="flex items-center justify-between gap-3">
                            <h2 class="text-lg font-semibold">Phone Numbers</h2>
                            <Button
                                type="button"
                                variant="outline"
                                @click="addPhoneNumber"
                                >Add</Button
                            >
                        </div>

                        <div
                            v-for="(phone, index) in phoneNumbers"
                            :key="`phone-${index}`"
                            class="grid gap-2 md:grid-cols-[1fr_auto]"
                        >
                            <Input
                                v-model="phoneNumbers[index]"
                                type="text"
                                placeholder="+15550000000"
                            />
                            <Button
                                type="button"
                                variant="ghost"
                                @click="removePhoneNumber(index)"
                            >
                                Remove
                            </Button>
                            <InputError
                                :message="form.errors[`phone_numbers.${index}`]"
                            />
                        </div>
                    </section>

                    <section class="grid gap-4 rounded-xl border bg-white p-4">
                        <div class="flex items-center justify-between gap-3">
                            <h2 class="text-lg font-semibold">Emails</h2>
                            <Button
                                type="button"
                                variant="outline"
                                @click="addEmail"
                                >Add</Button
                            >
                        </div>

                        <div
                            v-for="(email, index) in emails"
                            :key="`email-${index}`"
                            class="grid gap-2 md:grid-cols-[1fr_auto]"
                        >
                            <Input
                                v-model="emails[index]"
                                type="email"
                                placeholder="hello@example.com"
                            />
                            <Button
                                type="button"
                                variant="ghost"
                                @click="removeEmail(index)"
                            >
                                Remove
                            </Button>
                            <InputError
                                :message="form.errors[`emails.${index}`]"
                            />
                        </div>
                    </section>

                    <section class="grid gap-4 rounded-xl border bg-white p-4">
                        <div class="flex items-center justify-between gap-3">
                            <h2 class="text-lg font-semibold">Addresses</h2>
                            <Button
                                type="button"
                                variant="outline"
                                @click="addAddress"
                                >Add</Button
                            >
                        </div>

                        <div
                            v-for="(address, index) in addresses"
                            :key="`address-${index}`"
                            class="space-y-2 rounded-lg border p-3"
                        >
                            <Input
                                v-model="addresses[index].label"
                                type="text"
                                placeholder="Label (Head Office)"
                            />
                            <Input
                                v-model="addresses[index].address"
                                type="text"
                                placeholder="Address"
                            />
                            <Input
                                v-model="addresses[index].map_url"
                                type="url"
                                placeholder="Map URL"
                            />
                            <Button
                                type="button"
                                variant="ghost"
                                @click="removeAddress(index)"
                            >
                                Remove
                            </Button>
                            <InputError
                                :message="
                                    form.errors[`addresses.${index}.label`]
                                "
                            />
                            <InputError
                                :message="
                                    form.errors[`addresses.${index}.address`]
                                "
                            />
                            <InputError
                                :message="
                                    form.errors[`addresses.${index}.map_url`]
                                "
                            />
                        </div>
                    </section>

                    <section class="grid gap-4 rounded-xl border bg-white p-4">
                        <div class="flex items-center justify-between gap-3">
                            <h2 class="text-lg font-semibold">
                                Social Details
                            </h2>
                            <Button
                                type="button"
                                variant="outline"
                                @click="addSocial"
                                >Add</Button
                            >
                        </div>

                        <div
                            v-for="(social, index) in socialRows"
                            :key="`social-${index}`"
                            class="space-y-2 rounded-lg border p-3"
                        >
                            <Input
                                v-model="socialRows[index].platform"
                                type="text"
                                placeholder="Platform (facebook)"
                            />
                            <Input
                                v-model="socialRows[index].url"
                                type="url"
                                placeholder="https://..."
                            />
                            <Button
                                type="button"
                                variant="ghost"
                                @click="removeSocial(index)"
                            >
                                Remove
                            </Button>
                            <InputError
                                :message="
                                    form.errors[
                                        `social_details.${index}.platform`
                                    ]
                                "
                            />
                            <InputError
                                :message="
                                    form.errors[`social_details.${index}.url`]
                                "
                            />
                        </div>
                    </section>

                    <section
                        class="grid gap-4 rounded-xl border bg-white p-4 md:grid-cols-3"
                    >
                        <div class="grid gap-2">
                            <Label for="trade_licence_no"
                                >Trade Licence No</Label
                            >
                            <Input
                                id="trade_licence_no"
                                v-model="form.trade_licence_no"
                                type="text"
                            />
                            <InputError
                                :message="form.errors.trade_licence_no"
                            />
                        </div>

                        <div class="grid gap-2">
                            <Label for="tin_no">TIN No</Label>
                            <Input
                                id="tin_no"
                                v-model="form.tin_no"
                                type="text"
                            />
                            <InputError :message="form.errors.tin_no" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="bin_no">BIN No</Label>
                            <Input
                                id="bin_no"
                                v-model="form.bin_no"
                                type="text"
                            />
                            <InputError :message="form.errors.bin_no" />
                        </div>
                    </section>

                    <Button type="submit" :disabled="form.processing">
                        Save Site Settings
                    </Button>
                </form>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
