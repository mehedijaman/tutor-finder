<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { useSiteSettings } from '@/composables/useSiteSettings';
import PublicLayout from '@/layouts/PublicLayout.vue';
import { contact, termsOfService } from '@/routes';

const updatedAt = 'March 5, 2026';
const { siteName, primaryEmail } = useSiteSettings();
</script>

<template>
    <Head title="Refund Policy" />

    <PublicLayout>
        <section class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 pb-8">
            <div
                class="rounded-3xl bg-gradient-to-r from-blue-600 to-sky-500 p-8 text-white shadow-sm md:p-10"
            >
                <p class="text-sm font-semibold text-white/90">
                    {{ siteName }}
                </p>
                <h1 class="mt-2 text-3xl font-extrabold md:text-4xl">
                    Refund Policy
                </h1>
                <p class="mt-3 max-w-2xl text-white/90">
                    This page explains eligibility, process, and timelines for
                    refund requests on {{ siteName }}.
                </p>
                <p class="mt-4 text-sm text-white/80">
                    Last updated: {{ updatedAt }}
                </p>
            </div>
        </section>

        <main class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 pb-16">
            <div
                class="rounded-2xl border border-slate-100 bg-white p-6 shadow-[0_10px_30px_rgba(2,32,71,0.06)] md:p-10"
            >
                <div
                    class="prose prose-slate prose-h2:mt-8 prose-h2:mb-3 prose-h3:mt-6 prose-h3:mb-2 max-w-none"
                >
                    <h2>1. Scope</h2>
                    <p>
                        This Refund Policy explains when and how refunds may be
                        issued for platform-related payments.
                    </p>

                    <h2>2. Eligible refund scenarios</h2>
                    <ul>
                        <li>
                            <strong>Duplicate payment:</strong> if you are
                            charged more than once for the same invoice.
                        </li>
                        <li>
                            <strong>Service not delivered:</strong> where a
                            confirmed service could not be delivered due to
                            verified platform-side issues.
                        </li>
                        <li>
                            <strong>Billing error:</strong> if an incorrect
                            amount is charged because of a technical or
                            administrative mistake.
                        </li>
                    </ul>

                    <h2>3. Non-refundable cases</h2>
                    <ul>
                        <li>
                            Change of mind after a confirmed service is started.
                        </li>
                        <li>
                            Disputes outside documented platform and service
                            terms.
                        </li>
                        <li>
                            Requests submitted after the allowed claim window.
                        </li>
                    </ul>

                    <h2>4. Request timeline</h2>
                    <p>
                        Refund requests should be submitted as soon as possible.
                        Requests received after 7 days of payment may be
                        declined unless required by law.
                    </p>

                    <h2>5. Processing time</h2>
                    <p>
                        Approved refunds are usually processed within 7-10
                        business days. Processing time may vary by payment
                        provider.
                    </p>

                    <h2>6. Need help?</h2>
                    <p>
                        Contact us at
                        <strong>{{
                            primaryEmail || 'support@yourdomain.com'
                        }}</strong>
                        or submit a support request through our contact page.
                    </p>
                </div>

                <div
                    class="mt-8 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div class="text-sm text-slate-500">
                        Related:
                        <Link
                            :href="termsOfService()"
                            class="font-semibold text-blue-700 hover:text-blue-800"
                        >
                            Terms of Service
                        </Link>
                    </div>
                    <Link
                        :href="contact()"
                        class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-6 py-3 text-sm font-semibold text-white hover:bg-blue-700"
                    >
                        Contact us
                    </Link>
                </div>
            </div>
        </main>
    </PublicLayout>
</template>
