<?php

namespace Database\Seeders;

use App\Enums\PageStatus;
use App\Models\Page;
use Illuminate\Database\Seeder;

class PageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $systemPages = [
            [
                'title' => 'Privacy Policy',
                'slug' => 'privacy-policy',
                'content' => $this->privacyPolicyContent(),
                'status' => PageStatus::Active,
                'is_system' => true,
            ],
            [
                'title' => 'Terms of Service',
                'slug' => 'terms-of-service',
                'content' => $this->termsOfServiceContent(),
                'status' => PageStatus::Active,
                'is_system' => true,
            ],
            [
                'title' => 'Refund Policy',
                'slug' => 'refund-policy',
                'content' => $this->refundPolicyContent(),
                'status' => PageStatus::Active,
                'is_system' => true,
            ],
            [
                'title' => 'About Us',
                'slug' => 'about-us',
                'content' => $this->aboutUsContent(),
                'status' => PageStatus::Active,
                'is_system' => true,
            ],
        ];

        foreach ($systemPages as $pageData) {
            Page::query()->firstOrCreate(
                ['slug' => $pageData['slug']],
                $pageData,
            );
        }

        $this->command->info('System pages seeded successfully.');
    }

    private function privacyPolicyContent(): string
    {
        return <<<'HTML'
        <h2>1. Who we are</h2>
        <p>We are a platform that helps students/guardians connect with tutors. This Privacy Policy applies to our website, applications, and services.</p>
        <h2>2. Information we collect</h2>
        <h3>2.1 Information you provide</h3>
        <ul>
        <li><strong>Account data:</strong> name, email, password (stored securely), role (student/guardian/tutor).</li>
        <li><strong>Profile data:</strong> grade/subjects (students), qualifications/experience (tutors), availability.</li>
        <li><strong>Communications:</strong> messages you send to us (e.g., contact forms, support requests).</li>
        </ul>
        <h3>2.2 Information collected automatically</h3>
        <ul>
        <li><strong>Device &amp; usage data:</strong> pages visited, actions taken, approximate location (from IP), browser type.</li>
        <li><strong>Cookies:</strong> used for login, preferences, and analytics (where enabled).</li>
        </ul>
        <h2>3. How we use your information</h2>
        <ul>
        <li>Provide and improve the Services (matching, scheduling, analytics).</li>
        <li>Communicate with you (service updates, support, marketing where opted-in).</li>
        <li>Enforce our Terms and protect against fraud or misuse.</li>
        </ul>
        <h2>4. How we share information</h2>
        <p>We do not sell your personal information. We may share data with service providers, when required by law, and with your consent.</p>
        <h2>5. Data retention</h2>
        <p>We retain your data as long as your account is active or as needed to provide Services. You may request deletion at any time.</p>
        <h2>6. Your rights</h2>
        <p>You may access, correct, or delete your personal data. Contact us for any privacy-related requests.</p>
        <h2>7. Contact us</h2>
        <p>For questions about this Privacy Policy, contact us through the contact page.</p>
        HTML;
    }

    private function termsOfServiceContent(): string
    {
        return <<<'HTML'
        <h2>1. Agreement to these terms</h2>
        <p>By accessing or using our platform, you agree to these Terms of Service. If you do not agree, do not use the Services.</p>
        <h2>2. The Services</h2>
        <p>Our platform provides tools to connect students/guardians with tutors, including profiles, matching, messaging, scheduling, and related features.</p>
        <h2>3. Eligibility and accounts</h2>
        <ul>
        <li>You must provide accurate information and keep it up to date.</li>
        <li>You are responsible for activity under your account and for maintaining account security.</li>
        <li>Guardians may manage accounts on behalf of students where applicable.</li>
        </ul>
        <h2>4. User responsibilities</h2>
        <ul>
        <li>Use the Services lawfully and respectfully.</li>
        <li>No harassment, hate, discrimination, or abusive behavior.</li>
        <li>No impersonation, fraud, or misleading information.</li>
        </ul>
        <h2>5. Payments and fees</h2>
        <p>Some features may require payment. All fees are described on the platform and are subject to change with notice.</p>
        <h2>6. Termination</h2>
        <p>We may suspend or terminate accounts that violate these terms or for any other reason at our discretion.</p>
        <h2>7. Limitation of liability</h2>
        <p>We are not liable for indirect, incidental, or consequential damages arising from your use of the Services.</p>
        <h2>8. Changes to terms</h2>
        <p>We may update these Terms periodically. Continued use constitutes acceptance of the updated Terms.</p>
        HTML;
    }

    private function aboutUsContent(): string
    {
        return <<<'HTML'
        <h2>Our Mission</h2>
        <p>We are dedicated to connecting students and guardians with qualified, verified tutors to create better learning outcomes for everyone.</p>
        <h2>What We Do</h2>
        <p>Our platform makes it easy to find the right tutor for your needs. Whether you need help with academics, test preparation, or skill development, we have a network of experienced tutors ready to help.</p>
        <h2>Why Choose Us</h2>
        <ul>
        <li><strong>Verified Tutors:</strong> Every tutor goes through a verification process to ensure quality.</li>
        <li><strong>Easy Matching:</strong> Our platform helps you find tutors based on subject, location, and availability.</li>
        <li><strong>Secure Payments:</strong> Safe and transparent payment processing for all services.</li>
        <li><strong>Support:</strong> Our dedicated support team is here to help you every step of the way.</li>
        </ul>
        <h2>Contact Us</h2>
        <p>Have questions? Reach out to us through our contact page. We would love to hear from you!</p>
        HTML;
    }

    private function refundPolicyContent(): string
    {
        return <<<'HTML'
        <h2>1. Scope</h2>
        <p>This Refund Policy explains when and how refunds may be issued for platform-related payments.</p>
        <h2>2. Eligible refund scenarios</h2>
        <ul>
        <li><strong>Duplicate payment:</strong> if you are charged more than once for the same invoice.</li>
        <li><strong>Service not delivered:</strong> where a confirmed service could not be delivered due to verified platform-side issues.</li>
        <li><strong>Billing error:</strong> if an incorrect amount is charged because of a technical or administrative mistake.</li>
        </ul>
        <h2>3. Non-refundable cases</h2>
        <ul>
        <li>Change of mind after a confirmed service is started.</li>
        <li>Disputes outside documented platform and service terms.</li>
        <li>Requests submitted after the allowed claim window.</li>
        </ul>
        <h2>4. Refund request timeline</h2>
        <p>Refund requests should be submitted as soon as possible. Requests received after 7 days of payment may be declined unless required by law.</p>
        <h2>5. How to request a refund</h2>
        <ol>
        <li>Open a support request with payment details (invoice number, amount, date).</li>
        <li>Provide a short explanation and any relevant evidence (receipts, screenshots).</li>
        <li>Our team will review and communicate the decision.</li>
        </ol>
        <h2>6. Processing time</h2>
        <p>Approved refunds are usually processed within 7-10 business days. Processing time may vary by payment provider.</p>
        <h2>7. Partial refunds</h2>
        <p>Where appropriate, we may issue partial refunds based on completed service portion and applicable fees.</p>
        <h2>8. Changes to this policy</h2>
        <p>We may update this Refund Policy from time to time. The updated version will be published on this page.</p>
        HTML;
    }
}
