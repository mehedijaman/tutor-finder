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
    <Head title="Privacy Policy">
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    </Head>

    <PublicLayout>
        <section class="mx-auto max-w-7xl px-4 pb-8">
            <div
                class="rounded-3xl bg-gradient-to-r from-blue-600 to-sky-500 p-8 text-white shadow-sm md:p-10"
            >
                <p class="text-sm font-semibold text-white/90">
                    {{ siteName }}
                </p>
                <h1 class="mt-2 text-3xl font-extrabold md:text-4xl">
                    Privacy Policy
                </h1>
                <p class="mt-3 max-w-2xl text-white/90">
                    This policy explains how {{ siteName }} collects, uses, and
                    protects your information.
                </p>
                <p class="mt-4 text-sm text-white/80">
                    Last updated: {{ updatedAt }}
                </p>
            </div>
        </section>

        <main class="mx-auto max-w-7xl px-4 pb-16">
            <div
                class="rounded-2xl border border-slate-100 bg-white p-6 shadow-[0_10px_30px_rgba(2,32,71,0.06)] md:p-10"
            >
                <div
                    class="prose prose-slate prose-h2:mt-8 prose-h2:mb-3 prose-h3:mt-6 prose-h3:mb-2 max-w-none"
                >
                    <h2>1. Who we are</h2>
                    <p>
                        {{ siteName }} is a platform that helps
                        students/guardians connect with tutors. This Privacy
                        Policy applies to our website, applications, and
                        services (the "Services").
                    </p>

                    <h2>2. Information we collect</h2>
                    <h3>2.1 Information you provide</h3>
                    <ul>
                        <li>
                            <strong>Account data:</strong> name, email, password
                            (stored securely), role (student/guardian/tutor).
                        </li>
                        <li>
                            <strong>Profile data:</strong> grade/subjects
                            (students), qualifications/experience (tutors),
                            availability.
                        </li>
                        <li>
                            <strong>Communications:</strong> messages you send
                            to us (e.g., contact forms, support requests).
                        </li>
                    </ul>

                    <h3>2.2 Information collected automatically</h3>
                    <ul>
                        <li>
                            <strong>Device & usage data:</strong> pages visited,
                            actions taken, approximate location (from IP),
                            browser type.
                        </li>
                        <li>
                            <strong>Cookies:</strong> used for login,
                            preferences, and analytics (where enabled).
                        </li>
                    </ul>

                    <h3>2.3 Information from third parties (optional)</h3>
                    <p>
                        If you use third-party sign-in or integrated tools, we
                        may receive limited info from those services as
                        permitted by your settings and their policies.
                    </p>

                    <h2>3. How we use your information</h2>
                    <ul>
                        <li>
                            Provide and improve the Services (matching,
                            messaging, scheduling).
                        </li>
                        <li>Verify accounts and maintain platform safety.</li>
                        <li>
                            Respond to support requests and send service-related
                            notifications.
                        </li>
                        <li>
                            Analyze usage to improve performance and user
                            experience.
                        </li>
                        <li>
                            Comply with legal obligations and enforce our terms.
                        </li>
                    </ul>

                    <h2>4. How we share your information</h2>
                    <p>
                        We do not sell your personal information. We may share
                        data:
                    </p>
                    <ul>
                        <li>
                            <strong>With other users:</strong> limited profile
                            details needed for tutoring (e.g., tutor profile;
                            student needs).
                        </li>
                        <li>
                            <strong>With service providers:</strong> hosting,
                            analytics, email delivery, customer support tools.
                        </li>
                        <li>
                            <strong>For safety/legal reasons:</strong> to comply
                            with law, protect users, and prevent fraud or abuse.
                        </li>
                        <li>
                            <strong>Business transfers:</strong> if we are
                            involved in a merger, acquisition, or asset sale.
                        </li>
                    </ul>

                    <h2>5. Cookies and similar technologies</h2>
                    <p>
                        We use cookies to keep you signed in, remember
                        preferences, and understand site traffic. You can
                        control cookies through your browser settings. Some
                        features may not work without cookies.
                    </p>

                    <h2>6. Data retention</h2>
                    <p>
                        We keep information as long as needed to provide the
                        Services, comply with legal obligations, resolve
                        disputes, and enforce agreements. You may request
                        deletion (see "Your choices").
                    </p>

                    <h2>7. Security</h2>
                    <p>
                        We use reasonable safeguards to protect your
                        information. No system is 100% secure, so please use
                        strong passwords and keep your login details
                        confidential.
                    </p>

                    <h2>8. Your choices and rights</h2>
                    <ul>
                        <li>
                            Access, update, or correct your account information.
                        </li>
                        <li>
                            Request deletion of your account (subject to
                            legal/operational requirements).
                        </li>
                        <li>Opt out of non-essential communications.</li>
                        <li>Control cookies via browser settings.</li>
                    </ul>

                    <h2>9. Children's privacy</h2>
                    <p>
                        {{ siteName }} may be used by students. Guardians may
                        supervise or manage student accounts. We collect only
                        the information necessary to provide the Services and
                        encourage guardians to review activity and settings.
                    </p>

                    <h2>10. International transfers</h2>
                    <p>
                        If you access the Services from outside our operating
                        country, your information may be processed in other
                        locations where our service providers operate.
                    </p>

                    <h2>11. Changes to this policy</h2>
                    <p>
                        We may update this policy from time to time. If changes
                        are significant, we will provide notice as required. The
                        "Last updated" date will reflect the latest version.
                    </p>

                    <h2>12. Contact us</h2>
                    <p>
                        Questions about privacy? Contact us at
                        <strong>{{
                            primaryEmail || 'privacy@yourdomain.com'
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
                            href="/terms"
                            >Terms of Service</a
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
