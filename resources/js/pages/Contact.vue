<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import {
    ExternalLink,
    Facebook,
    Globe,
    Instagram,
    Linkedin,
    Mail,
    MapPin,
    MessageCircle,
    Phone,
    Twitter,
    Youtube,
} from 'lucide-vue-next';
import { computed } from 'vue';
import { useSiteSettings } from '@/composables/useSiteSettings';
import PublicLayout from '@/layouts/PublicLayout.vue';

type ContactAddress = {
    label: string | null;
    address: string;
    map_url: string | null;
};

type ContactDetails = {
    phones: string[];
    emails: string[];
    addresses: ContactAddress[];
    social_details: Record<string, string>;
};

const props = withDefaults(
    defineProps<{
        canRegister: boolean;
        contactDetails?: ContactDetails;
    }>(),
    {
        canRegister: true,
        contactDetails: () => ({
            phones: [],
            emails: [],
            addresses: [],
            social_details: {},
        }),
    },
);

const {
    siteName,
    primaryPhone,
    primaryEmail,
    primaryAddress,
    socialDetails: sharedSocialDetails,
} = useSiteSettings();

const phones = computed<string[]>(() => {
    const values = Array.isArray(props.contactDetails.phones)
        ? props.contactDetails.phones.filter(
              (value) => typeof value === 'string' && value.trim() !== '',
          )
        : [];

    if (values.length > 0) {
        return values;
    }

    return primaryPhone.value ? [primaryPhone.value] : [];
});

const emails = computed<string[]>(() => {
    const values = Array.isArray(props.contactDetails.emails)
        ? props.contactDetails.emails.filter(
              (value) => typeof value === 'string' && value.trim() !== '',
          )
        : [];

    if (values.length > 0) {
        return values;
    }

    return primaryEmail.value ? [primaryEmail.value] : [];
});

const addresses = computed<ContactAddress[]>(() => {
    if (
        Array.isArray(props.contactDetails.addresses) &&
        props.contactDetails.addresses.length > 0
    ) {
        return props.contactDetails.addresses.filter(
            (value): value is ContactAddress =>
                Boolean(value) &&
                typeof value.address === 'string' &&
                value.address.trim() !== '',
        );
    }

    if (primaryAddress.value) {
        return [
            {
                label: 'Main Office',
                address: primaryAddress.value,
                map_url: null,
            },
        ];
    }

    return [];
});

const socialEntries = computed(() => {
    const source =
        props.contactDetails.social_details &&
        Object.keys(props.contactDetails.social_details).length > 0
            ? props.contactDetails.social_details
            : sharedSocialDetails.value;

    return Object.entries(source)
        .filter(([, url]) => typeof url === 'string' && url.trim() !== '')
        .map(([platform, url]) => ({
            platform,
            url,
            label: platform
                .replaceAll('_', ' ')
                .replaceAll('-', ' ')
                .replace(/\b\w/g, (letter) => letter.toUpperCase()),
        }));
});

const iconMap: Record<string, unknown> = {
    facebook: Facebook,
    instagram: Instagram,
    linkedin: Linkedin,
    twitter: Twitter,
    x: Twitter,
    youtube: Youtube,
    whatsapp: MessageCircle,
};

function socialIcon(platform: string): unknown {
    return iconMap[platform.toLowerCase()] ?? Globe;
}

const form = useForm({
    name: '',
    email: '',
    phone: '',
    subject: '',
    message: '',
    website: '',
});

function submit(): void {
    form.post('/contact', {
        preserveScroll: true,
        onSuccess: () =>
            form.reset(
                'name',
                'email',
                'phone',
                'subject',
                'message',
                'website',
            ),
    });
}
</script>

<template>
    <Head title="Contact" />

    <PublicLayout>
        <section class="mx-auto max-w-7xl px-4 pb-8">
            <div
                class="rounded-3xl bg-gradient-to-r from-sky-700 to-blue-600 p-8 text-white shadow-sm md:p-10"
            >
                <p class="text-sm font-semibold text-white/90">
                    {{ siteName }}
                </p>
                <h1 class="mt-2 text-3xl font-extrabold md:text-4xl">
                    Contact Us
                </h1>
                <p class="mt-3 max-w-2xl text-sm text-white/90 md:text-base">
                    Have a question, feedback, or partnership inquiry? Send us a
                    message and our team will reply soon.
                </p>
            </div>
        </section>

        <main class="mx-auto max-w-7xl px-4 pb-16">
            <div class="grid gap-8 lg:grid-cols-5 lg:items-start">
                <aside class="space-y-6 lg:col-span-2">
                    <section
                        class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"
                    >
                        <h2 class="text-lg font-semibold">Get in touch</h2>
                        <p class="mt-2 text-sm text-muted-foreground">
                            Reach us directly through phone, email, or by
                            visiting our office.
                        </p>

                        <div class="mt-6 space-y-5 text-sm">
                            <div>
                                <p
                                    class="mb-2 flex items-center gap-2 font-medium"
                                >
                                    <Mail class="h-4 w-4 text-sky-600" />
                                    Email
                                </p>
                                <ul
                                    v-if="emails.length"
                                    class="space-y-1 text-muted-foreground"
                                >
                                    <li v-for="email in emails" :key="email">
                                        <a
                                            :href="`mailto:${email}`"
                                            class="hover:text-slate-900"
                                            >{{ email }}</a
                                        >
                                    </li>
                                </ul>
                                <p v-else class="text-muted-foreground">
                                    Not available
                                </p>
                            </div>

                            <div>
                                <p
                                    class="mb-2 flex items-center gap-2 font-medium"
                                >
                                    <Phone class="h-4 w-4 text-emerald-600" />
                                    Phone
                                </p>
                                <ul
                                    v-if="phones.length"
                                    class="space-y-1 text-muted-foreground"
                                >
                                    <li v-for="phone in phones" :key="phone">
                                        <a
                                            :href="`tel:${phone}`"
                                            class="hover:text-slate-900"
                                            >{{ phone }}</a
                                        >
                                    </li>
                                </ul>
                                <p v-else class="text-muted-foreground">
                                    Not available
                                </p>
                            </div>

                            <div>
                                <p
                                    class="mb-2 flex items-center gap-2 font-medium"
                                >
                                    <MapPin class="h-4 w-4 text-amber-600" />
                                    Address
                                </p>
                                <ul
                                    v-if="addresses.length"
                                    class="space-y-2 text-muted-foreground"
                                >
                                    <li
                                        v-for="(address, index) in addresses"
                                        :key="`${address.address}-${index}`"
                                        class="space-y-1"
                                    >
                                        <p>
                                            <span
                                                v-if="address.label"
                                                class="font-medium text-slate-900"
                                                >{{ address.label }}:</span
                                            >
                                            {{ address.address }}
                                        </p>
                                        <a
                                            v-if="address.map_url"
                                            :href="address.map_url"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="inline-flex items-center gap-1 text-xs text-sky-700 hover:text-sky-900"
                                        >
                                            Open map
                                            <ExternalLink class="h-3.5 w-3.5" />
                                        </a>
                                    </li>
                                </ul>
                                <p v-else class="text-muted-foreground">
                                    Not available
                                </p>
                            </div>
                        </div>
                    </section>

                    <section
                        v-if="socialEntries.length"
                        class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"
                    >
                        <h3
                            class="text-sm font-semibold tracking-wide text-muted-foreground uppercase"
                        >
                            Follow us
                        </h3>
                        <div class="mt-3 flex flex-wrap gap-2">
                            <a
                                v-for="social in socialEntries"
                                :key="social.platform"
                                :href="social.url"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="inline-flex items-center gap-2 rounded-md border px-3 py-2 text-sm hover:bg-slate-50"
                            >
                                <component
                                    :is="socialIcon(social.platform)"
                                    class="h-4 w-4"
                                />
                                {{ social.label }}
                            </a>
                        </div>
                    </section>
                </aside>

                <section class="lg:col-span-3">
                    <div
                        class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm md:p-8"
                    >
                        <h2 class="text-xl font-semibold">Send a message</h2>
                        <p class="mt-2 text-sm text-muted-foreground">
                            Please provide at least one contact method (email or
                            phone). We usually respond within one business day.
                        </p>

                        <div
                            v-if="$page.props.flash?.success"
                            role="status"
                            aria-live="polite"
                            class="mt-5 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800"
                        >
                            {{ $page.props.flash.success }}
                        </div>

                        <form class="mt-6 space-y-5" @submit.prevent="submit">
                            <div class="grid gap-5 md:grid-cols-2">
                                <div>
                                    <label
                                        for="name"
                                        class="text-sm font-medium"
                                        >Full name
                                        <span class="text-red-600"
                                            >*</span
                                        ></label
                                    >
                                    <input
                                        id="name"
                                        v-model="form.name"
                                        type="text"
                                        autocomplete="name"
                                        class="mt-2 w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm ring-offset-white outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-100"
                                        placeholder="Your full name"
                                    />
                                    <p
                                        v-if="form.errors.name"
                                        class="mt-1 text-sm text-red-600"
                                    >
                                        {{ form.errors.name }}
                                    </p>
                                </div>

                                <div>
                                    <label
                                        for="subject"
                                        class="text-sm font-medium"
                                        >Subject</label
                                    >
                                    <input
                                        id="subject"
                                        v-model="form.subject"
                                        type="text"
                                        class="mt-2 w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm ring-offset-white outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-100"
                                        placeholder="How can we help?"
                                    />
                                    <p
                                        v-if="form.errors.subject"
                                        class="mt-1 text-sm text-red-600"
                                    >
                                        {{ form.errors.subject }}
                                    </p>
                                </div>
                            </div>

                            <div class="grid gap-5 md:grid-cols-2">
                                <div>
                                    <label
                                        for="email"
                                        class="text-sm font-medium"
                                        >Email</label
                                    >
                                    <input
                                        id="email"
                                        v-model="form.email"
                                        type="email"
                                        autocomplete="email"
                                        class="mt-2 w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm ring-offset-white outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-100"
                                        placeholder="you@example.com"
                                    />
                                    <p
                                        v-if="form.errors.email"
                                        class="mt-1 text-sm text-red-600"
                                    >
                                        {{ form.errors.email }}
                                    </p>
                                </div>

                                <div>
                                    <label
                                        for="phone"
                                        class="text-sm font-medium"
                                        >Phone</label
                                    >
                                    <input
                                        id="phone"
                                        v-model="form.phone"
                                        type="text"
                                        autocomplete="tel"
                                        class="mt-2 w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm ring-offset-white outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-100"
                                        placeholder="017XXXXXXXX"
                                    />
                                    <p
                                        v-if="form.errors.phone"
                                        class="mt-1 text-sm text-red-600"
                                    >
                                        {{ form.errors.phone }}
                                    </p>
                                </div>
                            </div>

                            <div>
                                <label for="message" class="text-sm font-medium"
                                    >Message
                                    <span class="text-red-600">*</span></label
                                >
                                <textarea
                                    id="message"
                                    v-model="form.message"
                                    rows="6"
                                    class="mt-2 w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm ring-offset-white outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-100"
                                    placeholder="Write your message..."
                                ></textarea>
                                <p
                                    v-if="form.errors.message"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ form.errors.message }}
                                </p>
                            </div>

                            <div class="hidden">
                                <label for="website" class="sr-only"
                                    >Website</label
                                >
                                <input
                                    id="website"
                                    v-model="form.website"
                                    type="text"
                                    autocomplete="off"
                                    tabindex="-1"
                                />
                            </div>

                            <div
                                class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
                            >
                                <p class="text-xs text-muted-foreground">
                                    Fields marked with * are required.
                                </p>
                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="inline-flex items-center justify-center rounded-lg bg-sky-700 px-6 py-2.5 text-sm font-medium text-white transition hover:bg-sky-800 disabled:cursor-not-allowed disabled:opacity-60"
                                >
                                    <span v-if="form.processing"
                                        >Sending...</span
                                    >
                                    <span v-else>Send Message</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </section>
            </div>
        </main>
    </PublicLayout>
</template>
