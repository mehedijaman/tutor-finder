<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import {
    Building2,
    Globe,
    Image,
    Mail,
    MapPin,
    Phone,
    Share2,
} from 'lucide-vue-next';
import { computed, onBeforeUnmount, ref } from 'vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
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
    remove_favicon: false,
    favicon: null,
    phone_numbers: phoneNumbers.value,
    emails: emails.value,
    addresses: addresses.value,
    social_details: socialRows.value,
});

const temporaryLogoPreview = ref(null);
const temporaryFaviconPreview = ref(null);

const logoUrl = computed(() => {
    if (temporaryLogoPreview.value) {
        return temporaryLogoPreview.value;
    }

    if (form.remove_logo) {
        return null;
    }

    return props.siteSettingsFull.logo_url ?? null;
});

const faviconUrl = computed(() => {
    if (temporaryFaviconPreview.value) {
        return temporaryFaviconPreview.value;
    }

    if (form.remove_favicon) {
        return null;
    }

    return props.siteSettingsFull.favicon_url ?? null;
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

    if (temporaryFaviconPreview.value) {
        URL.revokeObjectURL(temporaryFaviconPreview.value);
        temporaryFaviconPreview.value = null;
    }
}

function onLogoChange(event) {
    const target = event.target;
    const file = target.files?.[0] ?? null;

    form.logo = file;
    form.remove_logo = false;

    if (temporaryLogoPreview.value) {
        URL.revokeObjectURL(temporaryLogoPreview.value);
        temporaryLogoPreview.value = null;
    }

    if (file) {
        temporaryLogoPreview.value = URL.createObjectURL(file);
    }
}

function onFaviconChange(event) {
    const target = event.target;
    const file = target.files?.[0] ?? null;

    form.favicon = file;
    form.remove_favicon = false;

    if (temporaryFaviconPreview.value) {
        URL.revokeObjectURL(temporaryFaviconPreview.value);
        temporaryFaviconPreview.value = null;
    }

    if (file) {
        temporaryFaviconPreview.value = URL.createObjectURL(file);
    }
}

function toggleRemoveLogo() {
    form.remove_logo = !form.remove_logo;

    if (form.remove_logo) {
        form.logo = null;

        if (temporaryLogoPreview.value) {
            URL.revokeObjectURL(temporaryLogoPreview.value);
            temporaryLogoPreview.value = null;
        }
    }
}

function toggleRemoveFavicon() {
    form.remove_favicon = !form.remove_favicon;

    if (form.remove_favicon) {
        form.favicon = null;

        if (temporaryFaviconPreview.value) {
            URL.revokeObjectURL(temporaryFaviconPreview.value);
            temporaryFaviconPreview.value = null;
        }
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
            <div class="space-y-6 p-4 sm:p-6 lg:p-8">
                <!-- Page Header -->
                <div
                    class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6"
                >
                    <div class="flex items-center gap-3">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary/10"
                        >
                            <Globe class="h-5 w-5 text-primary" />
                        </div>
                        <div>
                            <h1
                                class="text-2xl font-semibold tracking-tight text-slate-900 sm:text-3xl"
                            >
                                Site Settings
                            </h1>
                            <p class="text-sm text-slate-600">
                                Manage brand identity, contact channels, and
                                social details.
                            </p>
                        </div>
                    </div>
                </div>

                <form class="space-y-6" @submit.prevent="submit">
                    <!-- Basic Information -->
                    <section
                        class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6"
                    >
                        <div class="mb-5 border-b border-slate-100 pb-4">
                            <h2 class="text-lg font-semibold text-slate-900">
                                Basic Information
                            </h2>
                            <p class="mt-1 text-sm text-slate-600">
                                Core details that define your site's identity.
                            </p>
                        </div>

                        <div class="grid gap-5 sm:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="site_name">Site Name</Label>
                                <Input
                                    id="site_name"
                                    v-model="form.site_name"
                                    type="text"
                                    placeholder="Your Site Name"
                                    required
                                />
                                <p class="text-xs text-muted-foreground">
                                    Appears in browser tabs and site header.
                                </p>
                                <InputError :message="form.errors.site_name" />
                            </div>

                            <div class="space-y-2">
                                <Label for="slogan">Slogan</Label>
                                <Input
                                    id="slogan"
                                    v-model="form.slogan"
                                    type="text"
                                    placeholder="Your catchy tagline"
                                />
                                <p class="text-xs text-muted-foreground">
                                    A brief tagline that describes your site.
                                </p>
                                <InputError :message="form.errors.slogan" />
                            </div>

                            <div class="space-y-2 sm:col-span-2">
                                <Label for="description">Description</Label>
                                <Textarea
                                    id="description"
                                    v-model="form.description"
                                    rows="4"
                                    placeholder="Tell visitors about your site..."
                                />
                                <p class="text-xs text-muted-foreground">
                                    Used for SEO meta descriptions and site
                                    introductions.
                                </p>
                                <InputError
                                    :message="form.errors.description"
                                />
                            </div>
                        </div>
                    </section>

                    <!-- Branding -->
                    <section
                        class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6"
                    >
                        <div class="mb-5 border-b border-slate-100 pb-4">
                            <div class="flex items-center gap-2">
                                <Image class="h-5 w-5 text-slate-600" />
                                <h2
                                    class="text-lg font-semibold text-slate-900"
                                >
                                    Branding
                                </h2>
                            </div>
                            <p class="mt-1 text-sm text-slate-600">
                                Logo and favicon for your site's visual
                                identity.
                            </p>
                        </div>

                        <div class="grid gap-6 sm:grid-cols-2">
                            <!-- Logo -->
                            <div
                                class="space-y-4 rounded-xl border border-slate-200/80 bg-slate-50/50 p-4"
                            >
                                <div>
                                    <h3 class="font-medium text-slate-900">
                                        Site Logo
                                    </h3>
                                    <p class="text-sm text-muted-foreground">
                                        Recommended size: 200x60 pixels
                                    </p>
                                </div>

                                <div class="flex items-center gap-4">
                                    <div
                                        class="flex h-16 w-16 shrink-0 items-center justify-center overflow-hidden rounded-lg border bg-white"
                                    >
                                        <img
                                            v-if="logoUrl"
                                            :src="logoUrl"
                                            alt="Site Logo"
                                            class="h-full w-full object-contain p-1"
                                        />
                                        <span
                                            v-else
                                            class="text-xs text-muted-foreground"
                                            >No Logo</span
                                        >
                                    </div>

                                    <div class="flex-1 space-y-2">
                                        <Input
                                            type="file"
                                            accept="image/*"
                                            class="text-sm"
                                            @change="onLogoChange"
                                        />
                                    </div>
                                </div>

                                <Button
                                    v-if="logoUrl"
                                    type="button"
                                    variant="outline"
                                    size="sm"
                                    class="text-rose-600 hover:text-rose-700"
                                    @click="toggleRemoveLogo"
                                >
                                    {{
                                        form.remove_logo
                                            ? 'Keep Existing Logo'
                                            : 'Remove Logo'
                                    }}
                                </Button>

                                <InputError :message="form.errors.logo" />
                                <InputError
                                    :message="form.errors.remove_logo"
                                />
                            </div>

                            <!-- Favicon -->
                            <div
                                class="space-y-4 rounded-xl border border-slate-200/80 bg-slate-50/50 p-4"
                            >
                                <div>
                                    <h3 class="font-medium text-slate-900">
                                        Favicon
                                    </h3>
                                    <p class="text-sm text-muted-foreground">
                                        16x16 to 64x64 pixels (ICO, PNG, SVG)
                                    </p>
                                </div>

                                <div class="flex items-center gap-4">
                                    <div
                                        class="flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-lg border bg-white"
                                    >
                                        <img
                                            v-if="faviconUrl"
                                            :src="faviconUrl"
                                            alt="Favicon"
                                            class="h-8 w-8 object-contain"
                                        />
                                        <span
                                            v-else
                                            class="text-xs text-muted-foreground"
                                            >None</span
                                        >
                                    </div>

                                    <div class="flex-1 space-y-2">
                                        <Input
                                            type="file"
                                            accept="image/*,.ico"
                                            class="text-sm"
                                            @change="onFaviconChange"
                                        />
                                    </div>
                                </div>

                                <Button
                                    v-if="faviconUrl"
                                    type="button"
                                    variant="outline"
                                    size="sm"
                                    class="text-rose-600 hover:text-rose-700"
                                    @click="toggleRemoveFavicon"
                                >
                                    {{
                                        form.remove_favicon
                                            ? 'Keep Existing Favicon'
                                            : 'Remove Favicon'
                                    }}
                                </Button>

                                <InputError :message="form.errors.favicon" />
                                <InputError
                                    :message="form.errors.remove_favicon"
                                />
                            </div>
                        </div>
                    </section>

                    <!-- Contact Information -->
                    <section
                        class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6"
                    >
                        <div class="mb-5 border-b border-slate-100 pb-4">
                            <h2 class="text-lg font-semibold text-slate-900">
                                Contact Information
                            </h2>
                            <p class="mt-1 text-sm text-slate-600">
                                Phone numbers and email addresses for customer
                                contact.
                            </p>
                        </div>

                        <div class="grid gap-6 sm:grid-cols-2">
                            <!-- Phone Numbers -->
                            <div class="space-y-4">
                                <div
                                    class="flex items-center justify-between gap-3"
                                >
                                    <div class="flex items-center gap-2">
                                        <Phone class="h-4 w-4 text-slate-600" />
                                        <h3 class="font-medium text-slate-900">
                                            Phone Numbers
                                        </h3>
                                    </div>
                                    <Button
                                        type="button"
                                        variant="outline"
                                        size="sm"
                                        @click="addPhoneNumber"
                                    >
                                        Add
                                    </Button>
                                </div>

                                <div
                                    v-if="phoneNumbers.length === 0"
                                    class="rounded-lg border border-dashed p-4 text-center text-sm text-muted-foreground"
                                >
                                    No phone numbers added yet.
                                </div>

                                <div
                                    v-for="(phone, index) in phoneNumbers"
                                    :key="`phone-${index}`"
                                    class="flex items-center gap-2"
                                >
                                    <Input
                                        v-model="phoneNumbers[index]"
                                        type="text"
                                        placeholder="+15550000000"
                                        class="flex-1"
                                    />
                                    <Button
                                        type="button"
                                        variant="ghost"
                                        size="sm"
                                        class="text-rose-600 hover:text-rose-700"
                                        @click="removePhoneNumber(index)"
                                    >
                                        Remove
                                    </Button>
                                    <InputError
                                        :message="
                                            form.errors[
                                                `phone_numbers.${index}`
                                            ]
                                        "
                                    />
                                </div>
                            </div>

                            <!-- Emails -->
                            <div class="space-y-4">
                                <div
                                    class="flex items-center justify-between gap-3"
                                >
                                    <div class="flex items-center gap-2">
                                        <Mail class="h-4 w-4 text-slate-600" />
                                        <h3 class="font-medium text-slate-900">
                                            Emails
                                        </h3>
                                    </div>
                                    <Button
                                        type="button"
                                        variant="outline"
                                        size="sm"
                                        @click="addEmail"
                                    >
                                        Add
                                    </Button>
                                </div>

                                <div
                                    v-if="emails.length === 0"
                                    class="rounded-lg border border-dashed p-4 text-center text-sm text-muted-foreground"
                                >
                                    No email addresses added yet.
                                </div>

                                <div
                                    v-for="(email, index) in emails"
                                    :key="`email-${index}`"
                                    class="flex items-center gap-2"
                                >
                                    <Input
                                        v-model="emails[index]"
                                        type="email"
                                        placeholder="hello@example.com"
                                        class="flex-1"
                                    />
                                    <Button
                                        type="button"
                                        variant="ghost"
                                        size="sm"
                                        class="text-rose-600 hover:text-rose-700"
                                        @click="removeEmail(index)"
                                    >
                                        Remove
                                    </Button>
                                    <InputError
                                        :message="
                                            form.errors[`emails.${index}`]
                                        "
                                    />
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- Addresses -->
                    <section
                        class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6"
                    >
                        <div
                            class="mb-5 flex items-center justify-between border-b border-slate-100 pb-4"
                        >
                            <div>
                                <div class="flex items-center gap-2">
                                    <MapPin class="h-5 w-5 text-slate-600" />
                                    <h2
                                        class="text-lg font-semibold text-slate-900"
                                    >
                                        Addresses
                                    </h2>
                                </div>
                                <p class="mt-1 text-sm text-slate-600">
                                    Physical locations with optional map links.
                                </p>
                            </div>
                            <Button
                                type="button"
                                variant="outline"
                                size="sm"
                                @click="addAddress"
                            >
                                Add Address
                            </Button>
                        </div>

                        <div
                            v-if="addresses.length === 0"
                            class="rounded-lg border border-dashed p-6 text-center text-sm text-muted-foreground"
                        >
                            No addresses added yet. Click "Add Address" to get
                            started.
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div
                                v-for="(address, index) in addresses"
                                :key="`address-${index}`"
                                class="space-y-3 rounded-xl border border-slate-200/80 bg-slate-50/50 p-4"
                            >
                                <div class="space-y-2">
                                    <Label :for="`address-label-${index}`"
                                        >Label</Label
                                    >
                                    <Input
                                        :id="`address-label-${index}`"
                                        v-model="addresses[index].label"
                                        type="text"
                                        placeholder="Head Office"
                                    />
                                    <InputError
                                        :message="
                                            form.errors[
                                                `addresses.${index}.label`
                                            ]
                                        "
                                    />
                                </div>

                                <div class="space-y-2">
                                    <Label :for="`address-address-${index}`"
                                        >Address</Label
                                    >
                                    <Input
                                        :id="`address-address-${index}`"
                                        v-model="addresses[index].address"
                                        type="text"
                                        placeholder="123 Main St, City, Country"
                                    />
                                    <InputError
                                        :message="
                                            form.errors[
                                                `addresses.${index}.address`
                                            ]
                                        "
                                    />
                                </div>

                                <div class="space-y-2">
                                    <Label :for="`address-map-${index}`"
                                        >Map URL</Label
                                    >
                                    <Input
                                        :id="`address-map-${index}`"
                                        v-model="addresses[index].map_url"
                                        type="url"
                                        placeholder="https://maps.google.com/..."
                                    />
                                    <InputError
                                        :message="
                                            form.errors[
                                                `addresses.${index}.map_url`
                                            ]
                                        "
                                    />
                                </div>

                                <Button
                                    type="button"
                                    variant="ghost"
                                    size="sm"
                                    class="text-rose-600 hover:text-rose-700"
                                    @click="removeAddress(index)"
                                >
                                    Remove Address
                                </Button>
                            </div>
                        </div>
                    </section>

                    <!-- Social Details -->
                    <section
                        class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6"
                    >
                        <div
                            class="mb-5 flex items-center justify-between border-b border-slate-100 pb-4"
                        >
                            <div>
                                <div class="flex items-center gap-2">
                                    <Share2 class="h-5 w-5 text-slate-600" />
                                    <h2
                                        class="text-lg font-semibold text-slate-900"
                                    >
                                        Social Details
                                    </h2>
                                </div>
                                <p class="mt-1 text-sm text-slate-600">
                                    Links to your social media profiles.
                                </p>
                            </div>
                            <Button
                                type="button"
                                variant="outline"
                                size="sm"
                                @click="addSocial"
                            >
                                Add Social
                            </Button>
                        </div>

                        <div
                            v-if="socialRows.length === 0"
                            class="rounded-lg border border-dashed p-6 text-center text-sm text-muted-foreground"
                        >
                            No social profiles added yet. Click "Add Social" to
                            get started.
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div
                                v-for="(social, index) in socialRows"
                                :key="`social-${index}`"
                                class="space-y-3 rounded-xl border border-slate-200/80 bg-slate-50/50 p-4"
                            >
                                <div class="space-y-2">
                                    <Label :for="`social-platform-${index}`"
                                        >Platform</Label
                                    >
                                    <Input
                                        :id="`social-platform-${index}`"
                                        v-model="socialRows[index].platform"
                                        type="text"
                                        placeholder="facebook"
                                    />
                                    <InputError
                                        :message="
                                            form.errors[
                                                `social_details.${index}.platform`
                                            ]
                                        "
                                    />
                                </div>

                                <div class="space-y-2">
                                    <Label :for="`social-url-${index}`"
                                        >URL</Label
                                    >
                                    <Input
                                        :id="`social-url-${index}`"
                                        v-model="socialRows[index].url"
                                        type="url"
                                        placeholder="https://facebook.com/yourpage"
                                    />
                                    <InputError
                                        :message="
                                            form.errors[
                                                `social_details.${index}.url`
                                            ]
                                        "
                                    />
                                </div>

                                <Button
                                    type="button"
                                    variant="ghost"
                                    size="sm"
                                    class="text-rose-600 hover:text-rose-700"
                                    @click="removeSocial(index)"
                                >
                                    Remove Social
                                </Button>
                            </div>
                        </div>
                    </section>

                    <!-- Business Information -->
                    <section
                        class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6"
                    >
                        <div class="mb-5 border-b border-slate-100 pb-4">
                            <div class="flex items-center gap-2">
                                <Building2 class="h-5 w-5 text-slate-600" />
                                <h2
                                    class="text-lg font-semibold text-slate-900"
                                >
                                    Business Information
                                </h2>
                            </div>
                            <p class="mt-1 text-sm text-slate-600">
                                Legal and regulatory identification numbers.
                            </p>
                        </div>

                        <div class="grid gap-5 sm:grid-cols-3">
                            <div class="space-y-2">
                                <Label for="trade_licence_no"
                                    >Trade Licence No</Label
                                >
                                <Input
                                    id="trade_licence_no"
                                    v-model="form.trade_licence_no"
                                    type="text"
                                    placeholder="TRAD-XXXXXXX"
                                />
                                <InputError
                                    :message="form.errors.trade_licence_no"
                                />
                            </div>

                            <div class="space-y-2">
                                <Label for="tin_no">TIN No</Label>
                                <Input
                                    id="tin_no"
                                    v-model="form.tin_no"
                                    type="text"
                                    placeholder="TIN-XXXXXXX"
                                />
                                <InputError :message="form.errors.tin_no" />
                            </div>

                            <div class="space-y-2">
                                <Label for="bin_no">BIN No</Label>
                                <Input
                                    id="bin_no"
                                    v-model="form.bin_no"
                                    type="text"
                                    placeholder="BIN-XXXXXXX"
                                />
                                <InputError :message="form.errors.bin_no" />
                            </div>
                        </div>
                    </section>

                    <!-- Submit -->
                    <div class="flex items-center gap-3">
                        <Button type="submit" :disabled="form.processing">
                            Save Site Settings
                        </Button>
                        <p
                            v-if="form.recentlySuccessful"
                            class="text-sm text-green-600"
                        >
                            Settings saved successfully.
                        </p>
                    </div>
                </form>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
