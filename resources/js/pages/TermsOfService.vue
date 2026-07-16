<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { useSiteSettings } from '@/composables/useSiteSettings';
import PublicLayout from '@/layouts/PublicLayout.vue';

withDefaults(
    defineProps<{
        canRegister: boolean;
    }>(),
    { canRegister: true },
);

const updatedAt = 'March 1, 2026';
const { siteName, primaryEmail } = useSiteSettings();
</script>

<template>
    <Head title="Terms of Service">
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    </Head>

    <PublicLayout>
        <section class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 pb-8">
            <div
                class="rounded-3xl bg-gradient-to-r from-blue-600 to-sky-500 p-8 text-white shadow-sm md:p-10"
            >
                <p class="text-sm font-semibold text-white/90">
                    {{ siteName }}
                </p>
                <h1 class="mt-2 text-3xl font-extrabold md:text-4xl">
                    Terms of Service
                </h1>
                <p class="mt-3 max-w-2xl text-white/90">
                    These terms govern your use of {{ siteName }} and outline
                    responsibilities for students, guardians, and tutors.
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
                    <h2>1. Agreement to these terms</h2>
                    <p>
                        By accessing or using {{ siteName }} ("we," "us,"
                        "our"), you agree to these Terms of Service ("Terms").
                        If you do not agree, do not use the Services.
                    </p>

                    <h2>2. The Services</h2>
                    <p>
                        {{ siteName }} provides tools to connect
                        students/guardians with tutors, including profiles,
                        matching, messaging, scheduling, and related features.
                        We may change or discontinue features at any time.
                    </p>

                    <h2>3. Eligibility and accounts</h2>
                    <ul>
                        <li>
                            You must provide accurate information and keep it up
                            to date.
                        </li>
                        <li>
                            You are responsible for activity under your account
                            and for maintaining account security.
                        </li>
                        <li>
                            Guardians may manage accounts on behalf of students
                            where applicable.
                        </li>
                    </ul>

                    <h2>4. User responsibilities</h2>
                    <h3>4.1 Acceptable use</h3>
                    <ul>
                        <li>Use the Services lawfully and respectfully.</li>
                        <li>
                            No harassment, hate, discrimination, or abusive
                            behavior.
                        </li>
                        <li>
                            No impersonation, fraud, or misleading information.
                        </li>
                        <li>
                            No attempts to disrupt the platform (e.g., hacking,
                            scraping, malware).
                        </li>
                    </ul>

                    <h3>4.2 Tutor responsibilities</h3>
                    <ul>
                        <li>
                            Provide truthful qualifications and availability.
                        </li>
                        <li>
                            Communicate professionally with students/guardians.
                        </li>
                        <li>
                            Follow applicable laws and safeguarding
                            expectations.
                        </li>
                    </ul>

                    <h3>4.3 Student/guardian responsibilities</h3>
                    <ul>
                        <li>
                            Provide accurate learning needs and scheduling
                            information.
                        </li>
                        <li>
                            Engage respectfully with tutors and follow platform
                            rules.
                        </li>
                    </ul>

                    <h2>5. Payments and fees (if applicable)</h2>
                    <p>
                        If you enable paid tutoring or platform fees, additional
                        payment terms may apply. You are responsible for any
                        charges you incur. Refunds, cancellations, and disputes
                        follow our posted policies (if any).
                    </p>

                    <h2>6. Content and messaging</h2>
                    <p>
                        You may be able to post content or send messages. You
                        retain ownership of your content, but grant us a license
                        to host, display, and process it to provide the
                        Services. You are responsible for your content.
                    </p>

                    <h2>7. Safety and verification</h2>
                    <p>
                        We may offer verification tools, but we do not guarantee
                        that any user is who they claim to be. Use caution and
                        good judgment when interacting with others.
                    </p>

                    <h2>8. Termination</h2>
                    <p>
                        You may stop using the Services at any time. We may
                        suspend or terminate access if you violate these Terms
                        or if necessary to protect the platform or users.
                    </p>

                    <h2>9. Disclaimers</h2>
                    <p>
                        The Services are provided "as is" and "as available." We
                        do not guarantee uninterrupted or error-free operation.
                        We are not responsible for outcomes of tutoring sessions
                        or user conduct.
                    </p>

                    <h2>10. Limitation of liability</h2>
                    <p>
                        To the maximum extent permitted by law,
                        {{ siteName }} will not be liable for indirect,
                        incidental, special, consequential, or punitive damages,
                        or any loss of data, profits, or reputation arising from
                        your use of the Services.
                    </p>

                    <h2>11. Changes to these terms</h2>
                    <p>
                        We may update these Terms from time to time. Continued
                        use after changes become effective means you accept the
                        updated Terms.
                    </p>

                    <h2>12. Contact</h2>
                    <p>
                        Questions about these Terms? Contact us at
                        <strong>{{
                            primaryEmail || 'support@yourdomain.com'
                        }}</strong>
                        or via the contact page.
                    </p>
                </div>

                <div
                    class="mt-8 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div class="text-sm text-slate-500">
                        Related:
                        <a
                            class="font-semibold text-blue-700 hover:text-blue-800"
                            href="/privacy"
                            >Privacy Policy</a
                        >
                    </div>
                    <a
                        href="/contact"
                        class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-6 py-3 text-sm font-semibold text-white hover:bg-blue-700"
                    >
                        Contact us
                    </a>
                </div>
            </div>
        </main>
    </PublicLayout>
</template>
