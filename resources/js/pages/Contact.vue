<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { useSiteSettings } from '@/composables/useSiteSettings';
import { dashboard, login, register } from '@/routes';

withDefaults(
    defineProps<{
        canRegister: boolean;
    }>(),
    {
        canRegister: true,
    },
);

const { siteName, primaryPhone, primaryEmail, primaryAddress } = useSiteSettings();

// Adjust this to your Laravel route name/url for handling contact submissions.
const form = useForm({
    name: '',
    email: '',
    subject: '',
    message: '',
});

function submit() {
    // If you have a named route helper, use it here instead:
    // form.post(route('contact.store'));
    form.post('/contact', {
        preserveScroll: true,
        onSuccess: () => form.reset('name', 'email', 'subject', 'message'),
    });
}
</script>

<template>
    <Head title="Contact">
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    </Head>

    <div class="min-h-screen bg-slate-50 text-slate-900">
        <!-- Auth header (matches your Welcome layout style) -->
        <header class="mx-auto w-full max-w-7xl px-4 py-4 text-sm">
            <nav class="flex items-center justify-end gap-4">
                <Link
                    v-if="$page.props.auth.user"
                    :href="dashboard()"
                    class="inline-block rounded-sm border border-[#19140035] px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#1915014a] dark:border-[#3E3E3A] dark:text-[#EDEDEC] dark:hover:border-[#62605b]"
                >
                    Dashboard
                </Link>
                <template v-else>
                    <Link
                        :href="login()"
                        class="inline-block rounded-sm border border-transparent px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#19140035] dark:text-[#EDEDEC] dark:hover:border-[#3E3E3A]"
                    >
                        Log in
                    </Link>
                    <Link
                        v-if="canRegister"
                        :href="register()"
                        class="inline-block rounded-sm border border-[#19140035] px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#1915014a] dark:border-[#3E3E3A] dark:text-[#EDEDEC] dark:hover:border-[#62605b]"
                    >
                        Register
                    </Link>
                </template>
            </nav>
        </header>

        <!-- Page header -->
        <section class="mx-auto max-w-7xl px-4 pb-8">
            <div class="rounded-3xl bg-gradient-to-r from-blue-600 to-sky-500 p-8 md:p-10 text-white shadow-sm">
                <p class="text-white/90 text-sm font-semibold">{{ siteName }}</p>
                <h1 class="mt-2 text-3xl md:text-4xl font-extrabold">Contact us</h1>
                <p class="mt-3 max-w-2xl text-white/90">
                    Send us a message and we’ll get back to you as soon as possible.
                </p>
            </div>
        </section>

        <!-- Content -->
        <main class="mx-auto max-w-7xl px-4 pb-16">
            <div class="grid lg:grid-cols-5 gap-8 items-start">
                <!-- Left info -->
                <aside class="lg:col-span-2 space-y-6">
                    <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-[0_10px_30px_rgba(2,32,71,0.06)]">
                        <h2 class="text-lg font-bold">Reach us</h2>
                        <p class="mt-2 text-sm text-slate-600">
                            Prefer email or phone? Use any option below.
                        </p>

                        <dl class="mt-5 space-y-4 text-sm">
                            <div class="flex items-start gap-3">
                                <div class="h-10 w-10 rounded-xl bg-blue-50 grid place-items-center text-blue-700">
                                    ✉️
                                </div>
                                <div>
                                    <dt class="font-semibold">Email</dt>
                                    <dd class="text-slate-600">{{ primaryEmail || 'hello@yourdomain.com' }}</dd>
                                </div>
                            </div>

                            <div class="flex items-start gap-3">
                                <div class="h-10 w-10 rounded-xl bg-emerald-50 grid place-items-center text-emerald-700">
                                    📞
                                </div>
                                <div>
                                    <dt class="font-semibold">Phone</dt>
                                    <dd class="text-slate-600">{{ primaryPhone || '+1 (000) 000-0000' }}</dd>
                                </div>
                            </div>

                            <div class="flex items-start gap-3">
                                <div class="h-10 w-10 rounded-xl bg-amber-50 grid place-items-center text-amber-700">
                                    📍
                                </div>
                                <div>
                                    <dt class="font-semibold">Location</dt>
                                    <dd class="text-slate-600">{{ primaryAddress || 'Your City, Country' }}</dd>
                                </div>
                            </div>
                        </dl>
                    </div>

                    <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-[0_10px_30px_rgba(2,32,71,0.06)]">
                        <h3 class="text-lg font-bold">Support hours</h3>
                        <p class="mt-2 text-sm text-slate-600">We typically respond within 24 hours.</p>
                        <div class="mt-4 grid grid-cols-2 gap-3 text-sm">
                            <div class="rounded-xl bg-slate-50 border border-slate-100 p-4">
                                <p class="font-semibold">Mon–Fri</p>
                                <p class="text-slate-600">9:00 AM – 6:00 PM</p>
                            </div>
                            <div class="rounded-xl bg-slate-50 border border-slate-100 p-4">
                                <p class="font-semibold">Weekend</p>
                                <p class="text-slate-600">Limited</p>
                            </div>
                        </div>
                    </div>
                </aside>

                <!-- Form -->
                <section class="lg:col-span-3">
                    <div class="rounded-2xl border border-slate-100 bg-white p-6 md:p-8 shadow-[0_10px_30px_rgba(2,32,71,0.06)]">
                        <h2 class="text-xl font-extrabold">Send a message</h2>
                        <p class="mt-2 text-sm text-slate-600">
                            Fill out the form and we’ll reply to your email.
                        </p>

                        <!-- Success message (optional if your backend flashes something) -->
                        <div
                            v-if="$page.props.flash?.success"
                            class="mt-5 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800"
                        >
                            {{ $page.props.flash.success }}
                        </div>

                        <form class="mt-6 space-y-5" @submit.prevent="submit">
                            <div class="grid md:grid-cols-2 gap-5">
                                <div>
                                    <label class="text-sm font-semibold" for="name">Full name</label>
                                    <input
                                        id="name"
                                        v-model="form.name"
                                        type="text"
                                        autocomplete="name"
                                        class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                                        placeholder="Your name"
                                    />
                                    <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.name }}
                                    </p>
                                </div>

                                <div>
                                    <label class="text-sm font-semibold" for="email">Email</label>
                                    <input
                                        id="email"
                                        v-model="form.email"
                                        type="email"
                                        autocomplete="email"
                                        class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                                        placeholder="you@example.com"
                                    />
                                    <p v-if="form.errors.email" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.email }}
                                    </p>
                                </div>
                            </div>

                            <div>
                                <label class="text-sm font-semibold" for="subject">Subject</label>
                                <input
                                    id="subject"
                                    v-model="form.subject"
                                    type="text"
                                    class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                                    placeholder="How can we help?"
                                />
                                <p v-if="form.errors.subject" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.subject }}
                                </p>
                            </div>

                            <div>
                                <label class="text-sm font-semibold" for="message">Message</label>
                                <textarea
                                    id="message"
                                    v-model="form.message"
                                    rows="6"
                                    class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                                    placeholder="Write your message..."
                                />
                                <p v-if="form.errors.message" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.message }}
                                </p>
                            </div>

                            <div class="flex flex-col sm:flex-row gap-3 sm:items-center sm:justify-between">
                                <p class="text-xs text-slate-500">
                                    By sending this message, you agree to our terms and privacy policy.
                                </p>

                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-6 py-3 text-sm font-semibold text-white hover:bg-blue-700 disabled:opacity-60 disabled:cursor-not-allowed"
                                >
                                    <span v-if="!form.processing">Send message</span>
                                    <span v-else>Sending…</span>
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Optional: Map placeholder like many landing pages -->
                    <div class="mt-6 rounded-2xl border border-slate-100 bg-white p-3 shadow-[0_10px_30px_rgba(2,32,71,0.06)]">
                        <div
                            class="h-56 rounded-xl bg-gradient-to-br from-slate-100 to-blue-50 grid place-items-center text-slate-500 text-sm"
                        >
                            Map / Location embed (optional)
                        </div>
                    </div>
                </section>
            </div>
        </main>

        <!-- Footer -->
        <footer class="bg-slate-900 text-slate-200">
            <div class="mx-auto max-w-7xl px-4 py-10">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-3 text-sm text-slate-400">
                    <p>© {{ new Date().getFullYear() }} {{ siteName }}. All rights reserved.</p>
                    <div class="flex items-center gap-4">
                        <a class="hover:text-slate-200" href="#">Privacy</a>
                        <a class="hover:text-slate-200" href="#">Terms</a>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</template>
