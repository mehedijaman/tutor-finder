# Project Feature List

## Overview
Tutor Finder is a role-based tutoring marketplace built as a Laravel + Inertia + Vue application. It connects guardians and tutors through a moderated job marketplace, verification and payment workflows, operational support tools, and a full admin management system.

---

## Core System
- Multi-role authentication for `admin`, `tutor`, and `guardian` accounts.
- OTP-based phone verification during onboarding, plus email verification and optional two-factor authentication.
- Role-aware dashboard redirection and route protection via role/status middleware.
- Centralized role-permission access control with policy-based authorization.
- Profile, password, appearance, and security settings management.
- Unified notification center with unread/read tracking.
- Public pages for jobs, tutors, blog, FAQs, tutorials, legal pages, and dynamic CMS pages.
- Contact intake with request throttling and structured admin follow-up.

---

## Admin Panel
- Platform dashboard with user, job, verification, finance, and support metrics.
- Admin user management, role assignment, direct permission assignment, and recycle-bin recovery.
- Tutor and guardian lifecycle management (create, update, suspend/activate, password reset, restore, force delete).
- Role and permission management for access governance.
- Admin impersonation of user accounts for support and troubleshooting.
- Job moderation and lifecycle control (pending/live/confirmed/cancelled/closed) plus application visibility.
- Verification operations (approve/reject/cancel decisions, invoice generation, and payment finalization).
- Finance operations for invoices, payment attempts, refund decisions/payouts, and ledger review.
- Reporting suite with exportable income, tuition, refund, registration, and job-performance reports.
- CMS administration for blog posts/categories/tags, FAQs, pages, tutorials, notices, and testimonials.
- Tuition taxonomy management for countries, cities, areas, categories, classes, subjects, and tuition types.
- Support ticket assignment, thread replies, and status handling.
- Contact message inbox with status updates.
- Site, payment gateway, SMS gateway, and SMTP configuration with test actions.
- Backup management, activity log inspection, and operational maintenance controls.

---

## User Features
- Guardians can post tuition jobs and manage them through hiring pipeline states.
- Guardians can review incoming tutor applications, shortlist/cancel, and confirm tutor hires.
- Guardians can submit profile verification requests and pay verification invoices.
- Guardians can view invoices, submit tutor reviews, manage notifications, and use support tickets.
- Tutors can browse/filter live jobs and view detailed job information.
- Tutors can apply, withdraw, and reapply to jobs while tracking application statuses.
- Tutors can maintain detailed profiles (including education), download CV, and preview public profile view.
- Tutors can submit profile verification requests and manage invoices/refund requests.
- Tutors can manage notifications and support ticket conversations.
- Public visitors can browse tutors, browse jobs, read blog/FAQ/tutorial content, and submit contact messages.

---

## Modules

### Authentication & Access Module
- Role-specific login flow and dashboard routing.
- Pending-verification onboarding with OTP resend/verify flow.
- Two-factor authentication challenge flow.
- Role- and permission-based authorization controls.

### Job Marketplace Module
- Public and tutor-facing job board with advanced filters and sorting.
- Guardian job posting with structured academic, location, schedule, and budget data.
- Job state lifecycle from pending moderation to live and final outcomes.
- Detailed job pages with apply eligibility logic.

### Application & Hiring Workflow Module
- Tutor job applications with expected salary and cover letter.
- Guardian-side application review and status actions.
- Hire confirmation workflow creating assignment records.
- Automatic cancellation of competing open applications after hire confirmation.

### Verification Module
- Tutor and guardian verification request submission.
- Admin decision workflow (approve/reject/cancel).
- Verification fee invoicing and payment-linked verification completion.
- Profile verification queue views (pending, unverified, verified).

### Finance & Billing Module
- Invoice lifecycle management (issue, pay, fail/cancel/void, refund).
- Gateway-backed payment attempts and callback validation.
- Manual admin payment marking for controlled exceptions.
- Double-entry wallet ledger posting for payments and refund reversals.

### Refund Management Module
- Tutor-initiated refund requests linked to eligible paid service-fee invoices.
- Admin approve/reject decisions with notes.
- Refund payout recording and synchronized invoice/ledger updates.

### Notifications Module
- In-app database notifications for jobs, tickets, notices, and payments.
- Per-role notification inbox with read and mark-all-read actions.
- Real-time broadcast updates on role channels.
- Optional browser push notification subscription and delivery.

### Support Ticket Module
- Ticket creation by tutors and guardians with categories and priorities.
- Threaded conversations with attachment support.
- Admin assignment and status control (open, in-progress, closed).
- Automatic notification events for new tickets and replies.

### CMS & Content Module
- Blog publishing with categories, tags, media, and SEO metadata.
- FAQ management with audience targeting (tutor/guardian/both).
- Tutorial library management by audience and active status.
- Custom page management for dynamic content and legal pages.
- Testimonial and notice management with recycle-bin operations.

### Taxonomy Module
- Configurable tuition metadata: countries, cities, areas, categories, classes, subjects, tuition types.
- Status-driven activation/inactivation of taxonomy options.
- Taxonomy-backed filtering across job posting and discovery flows.

### Reporting & Analytics Module
- Dashboard trend charts and platform summary stats.
- Finance reports (income, refunds) with period filtering.
- Operational reports (tuition, job performance, user registrations).
- CSV export for all major report screens.

### Administration & Maintenance Module
- Backup run/cleanup/download/delete management.
- Activity log exploration for auditable actions.
- Scheduled cleanup tasks for expired OTP requests and invoices.
- Recycle-bin patterns for controlled restore/permanent deletion across modules.

---

## Integrations
- bKash payment gateway.
- SSLCommerz payment gateway.
- Manual payment gateway workflow for admin-handled settlements.
- SMS gateway integration (configurable providers via Laravel BD SMS ecosystem).
- SMTP integration with runtime-switchable outbound mail settings.
- Web Push integration for browser notifications.
- Realtime broadcasting via Laravel Reverb (Pusher-compatible channels).
- Spatie Activity Log for audit trails.
- Spatie Permission for RBAC.
- Spatie Media Library for managed uploads.
- Spatie Backup for backup orchestration and monitoring.
- Admin log viewer integration for operational diagnostics.

---

## Infrastructure Features
- Background jobs for OTP SMS delivery and notice fan-out (database + push).
- Event-driven domain workflows for application, hiring, notice, and ticket lifecycles.
- Queue-based asynchronous processing for notifications and integrations.
- Real-time event broadcasting on private user and role channels.
- Built-in notification system with database persistence and push delivery.
- Scheduled maintenance tasks for cleanup and log retention.
- Policy and middleware enforcement for role, status, and ownership boundaries.
- Rate limiting for login, OTP, and contact submission endpoints.
- Backup health monitoring and managed backup file operations.
- Financial integrity safeguards through transactional invoice/payment/refund workflows.
