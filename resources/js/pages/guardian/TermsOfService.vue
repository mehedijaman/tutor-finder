<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { useSiteSettings } from '@/composables/useSiteSettings';
import GuardianLayout from '@/layouts/GuardianLayout.vue';

type PageData = {
    title: string;
    slug: string;
    content: string;
    updated_at: string | null;
};

const props = defineProps<{
    page: PageData | null;
}>();

const breadcrumbs = [
    { title: 'Terms of Service', href: '/guardian/terms-of-service' },
];
const updatedAt = 'March 1, 2026';
const { siteName, primaryEmail } = useSiteSettings();
</script>

<template>
    <Head title="Terms of Service" />

    <GuardianLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-4 sm:p-6 lg:p-8">
            <div
                class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6"
            >
                <h1 class="text-2xl sm:text-3xl font-semibold tracking-tight">
                    Terms of Service
                </h1>
                <p class="mt-1 text-sm text-muted-foreground">
                    Last updated:
                    {{
                        page?.updated_at
                            ? new Date(page.updated_at).toLocaleDateString()
                            : updatedAt
                    }}
                </p>
            </div>

            <!-- Dynamic content from Page model -->
            <div
                v-if="page"
                class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm sm:p-10"
            >
                <div
                    class="prose prose-slate prose-h2:mt-8 prose-h2:mb-3 prose-h3:mt-6 prose-h3:mb-2 max-w-none"
                    v-html="page.content"
                />
            </div>

            <!-- Fallback hardcoded content -->
            <div
                v-else
                class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm sm:p-10"
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
            </div>
        </div>
    </GuardianLayout>
</template>
