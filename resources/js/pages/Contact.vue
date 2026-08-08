<script setup lang="ts">
import { Head, useForm, usePage } from '@inertiajs/vue3';
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

const page = usePage();
const successMessage = computed(
    () => (page.props.flash as { success?: string })?.success,
);

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
        <section
            class="mx-auto max-w-7xl px-4 pt-4 pb-12 sm:px-6 sm:pt-8 lg:px-8"
        >
            <div
                class="relative overflow-hidden rounded-3xl bg-blue-600 px-6 py-12 text-center shadow-2xl shadow-blue-600/20 sm:px-12 sm:py-16"
            >
                <div
                    class="absolute inset-0 -z-10 bg-[radial-gradient(45rem_50rem_at_top,theme(colors.blue.400),theme(colors.blue.600))]"
                />

                <h1
                    class="font-display text-3xl font-bold tracking-tight text-white sm:text-4xl md:text-5xl"
                >
                    Contact Us
                </h1>
                <p
                    class="mx-auto mt-4 max-w-2xl text-lg leading-relaxed text-blue-100"
                >
                    Have a question or feedback? We'd love to hear from you.
                </p>
            </div>
        </section>

        <main class="mx-auto max-w-7xl px-4 pb-16 sm:px-6 lg:px-8">
            <div class="grid gap-6 lg:grid-cols-5 lg:items-start lg:gap-8">
                <aside class="space-y-6 lg:sticky lg:top-24 lg:col-span-2">
                    <section
                        class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6 dark:border-slate-800 dark:bg-slate-900"
                    >
                        <h2
                            class="text-lg font-semibold tracking-tight text-slate-900 dark:text-slate-100"
                        >
                            Get in touch
                        </h2>
                        <p
                            class="mt-2 text-sm leading-relaxed text-muted-foreground"
                        >
                            Reach us directly through phone, email, or by
                            visiting our office.
                        </p>

                        <div class="mt-6 space-y-5 text-sm">
                            <div>
                                <p
                                    class="mb-2 flex items-center gap-2 font-medium text-slate-900 dark:text-slate-100"
                                >
                                    <Mail class="h-4 w-4 text-blue-600" />
                                    Email
                                </p>
                                <ul
                                    v-if="emails.length"
                                    class="space-y-1.5 text-muted-foreground"
                                >
                                    <li v-for="email in emails" :key="email">
                                        <a
                                            :href="`mailto:${email}`"
                                            class="inline-flex items-center break-all transition-colors hover:text-slate-900 dark:hover:text-slate-100"
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
                                    class="mb-2 flex items-center gap-2 font-medium text-slate-900 dark:text-slate-100"
                                >
                                    <Phone class="h-4 w-4 text-emerald-600" />
                                    Phone
                                </p>
                                <ul
                                    v-if="phones.length"
                                    class="space-y-1.5 text-muted-foreground"
                                >
                                    <li v-for="phone in phones" :key="phone">
                                        <a
                                            :href="`tel:${phone}`"
                                            class="inline-flex items-center transition-colors hover:text-slate-900 dark:hover:text-slate-100"
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
                                    class="mb-2 flex items-center gap-2 font-medium text-slate-900 dark:text-slate-100"
                                >
                                    <MapPin class="h-4 w-4 text-amber-600" />
                                    Address
                                </p>
                                <ul
                                    v-if="addresses.length"
                                    class="space-y-2.5 text-muted-foreground"
                                >
                                    <li
                                        v-for="(address, index) in addresses"
                                        :key="`${address.address}-${index}`"
                                        class="rounded-lg border border-slate-100 bg-slate-50/60 p-3 dark:border-slate-800 dark:bg-slate-800/60"
                                    >
                                        <p class="leading-relaxed">
                                            <span
                                                v-if="address.label"
                                                class="font-medium text-slate-900 dark:text-slate-100"
                                                >{{ address.label }}:</span
                                            >
                                            {{ address.address }}
                                        </p>
                                        <a
                                            v-if="address.map_url"
                                            :href="address.map_url"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="mt-1.5 inline-flex items-center gap-1 text-xs font-medium text-blue-700 transition-colors hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300"
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
                        class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6 dark:border-slate-800 dark:bg-slate-900"
                    >
                        <h3
                            class="text-sm font-semibold tracking-[0.08em] text-muted-foreground uppercase"
                        >
                            Follow us
                        </h3>
                        <div class="mt-4 grid grid-cols-1 gap-2 sm:grid-cols-2">
                            <a
                                v-for="social in socialEntries"
                                :key="social.platform"
                                :href="social.url"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="inline-flex items-center gap-2 rounded-lg border border-slate-200 px-3 py-2.5 text-sm text-slate-700 transition-colors hover:bg-slate-50 hover:text-slate-900 dark:border-slate-800 dark:text-slate-200 dark:hover:bg-slate-800 dark:hover:text-slate-100"
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
                        class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6 md:p-8 dark:border-slate-800 dark:bg-slate-900"
                    >
                        <h2
                            class="text-xl font-semibold tracking-tight text-slate-900 dark:text-slate-100"
                        >
                            Send a message
                        </h2>
                        <p
                            class="mt-2 text-sm leading-relaxed text-muted-foreground"
                        >
                            Please provide at least one contact method (email or
                            phone). We usually respond within one business day.
                        </p>

                        <div
                            v-if="successMessage"
                            role="status"
                            aria-live="polite"
                            class="mt-5 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-900/60 dark:bg-emerald-950/40 dark:text-emerald-300"
                        >
                            {{ successMessage }}
                        </div>

                        <form class="mt-6 space-y-5" @submit.prevent="submit">
                            <div class="grid gap-5 md:grid-cols-2">
                                <div>
                                    <label
                                        for="name"
                                        class="text-sm font-medium text-slate-800 dark:text-slate-200"
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
                                        class="mt-2 w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 ring-offset-white transition outline-none placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 dark:ring-offset-slate-900 dark:placeholder:text-slate-500 dark:focus:ring-blue-900/30"
                                        placeholder="Your full name"
                                    />
                                    <p
                                        v-if="form.errors.name"
                                        class="mt-1 text-sm text-red-600 dark:text-red-400"
                                    >
                                        {{ form.errors.name }}
                                    </p>
                                </div>

                                <div>
                                    <label
                                        for="subject"
                                        class="text-sm font-medium text-slate-800 dark:text-slate-200"
                                        >Subject</label
                                    >
                                    <input
                                        id="subject"
                                        v-model="form.subject"
                                        type="text"
                                        class="mt-2 w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 ring-offset-white transition outline-none placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 dark:ring-offset-slate-900 dark:placeholder:text-slate-500 dark:focus:ring-blue-900/30"
                                        placeholder="How can we help?"
                                    />
                                    <p
                                        v-if="form.errors.subject"
                                        class="mt-1 text-sm text-red-600 dark:text-red-400"
                                    >
                                        {{ form.errors.subject }}
                                    </p>
                                </div>
                            </div>

                            <div class="grid gap-5 md:grid-cols-2">
                                <div>
                                    <label
                                        for="email"
                                        class="text-sm font-medium text-slate-800 dark:text-slate-200"
                                        >Email</label
                                    >
                                    <input
                                        id="email"
                                        v-model="form.email"
                                        type="email"
                                        autocomplete="email"
                                        class="mt-2 w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 ring-offset-white transition outline-none placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 dark:ring-offset-slate-900 dark:placeholder:text-slate-500 dark:focus:ring-blue-900/30"
                                        placeholder="you@example.com"
                                    />
                                    <p
                                        v-if="form.errors.email"
                                        class="mt-1 text-sm text-red-600 dark:text-red-400"
                                    >
                                        {{ form.errors.email }}
                                    </p>
                                </div>

                                <div>
                                    <label
                                        for="phone"
                                        class="text-sm font-medium text-slate-800 dark:text-slate-200"
                                        >Phone</label
                                    >
                                    <input
                                        id="phone"
                                        v-model="form.phone"
                                        type="text"
                                        autocomplete="tel"
                                        class="mt-2 w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 ring-offset-white transition outline-none placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 dark:ring-offset-slate-900 dark:placeholder:text-slate-500 dark:focus:ring-blue-900/30"
                                        placeholder="017XXXXXXXX"
                                    />
                                    <p
                                        v-if="form.errors.phone"
                                        class="mt-1 text-sm text-red-600 dark:text-red-400"
                                    >
                                        {{ form.errors.phone }}
                                    </p>
                                </div>
                            </div>

                            <div>
                                <label
                                    for="message"
                                    class="text-sm font-medium text-slate-800 dark:text-slate-200"
                                    >Message
                                    <span class="text-red-600">*</span></label
                                >
                                <textarea
                                    id="message"
                                    v-model="form.message"
                                    rows="6"
                                    class="mt-2 w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 ring-offset-white transition outline-none placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 dark:ring-offset-slate-900 dark:placeholder:text-slate-500 dark:focus:ring-blue-900/30"
                                    placeholder="Write your message..."
                                ></textarea>
                                <p
                                    v-if="form.errors.message"
                                    class="mt-1 text-sm text-red-600 dark:text-red-400"
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
                                class="flex flex-col-reverse gap-3 pt-1 sm:flex-row sm:items-center sm:justify-between"
                            >
                                <p class="text-xs text-muted-foreground">
                                    Fields marked with * are required.
                                </p>
                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="inline-flex w-full items-center justify-center rounded-xl bg-blue-600 px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus-visible:ring-2 focus-visible:ring-blue-200 focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-60 sm:w-auto"
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
