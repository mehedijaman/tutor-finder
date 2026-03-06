# Tutor Finder - Technical Documentation

## Table of Contents

1. [System Overview](#system-overview)
2. [Technology Stack](#technology-stack)
3. [Application Architecture](#application-architecture)
4. [Core Domains and Modules](#core-domains-and-modules)
5. [Authentication and Authorization](#authentication-and-authorization)
6. [Route and Panel Structure](#route-and-panel-structure)
7. [Data Model Overview](#data-model-overview)
8. [Finance and Payment Workflows](#finance-and-payment-workflows)
9. [Notifications, Events, and Realtime](#notifications-events-and-realtime)
10. [Queues, Jobs, and Scheduling](#queues-jobs-and-scheduling)
11. [Configuration and Environment](#configuration-and-environment)
12. [Development and QA](#development-and-qa)
13. [Deployment and Operations](#deployment-and-operations)
14. [Troubleshooting](#troubleshooting)

---

## System Overview

Tutor Finder is a multi-role tutoring marketplace built with Laravel + Inertia + Vue.

Primary business flow:

1. Guardians post tuition jobs.
2. Admin reviews and publishes jobs.
3. Tutors apply to live jobs.
4. Guardians shortlist and confirm a tutor.
5. System creates assignment + billing artifacts (service fee and optional escrow invoice).
6. Payments are processed via configured gateways.
7. Verification, finance, reporting, and support are managed through role-specific panels.

Main user roles:

- **Admin**: platform operations, moderation, finance, configuration.
- **Tutor**: profile, job applications, invoices, refund requests, verification.
- **Guardian**: job posting, hiring decisions, invoices, tutor reviews, verification.
- **Platform**: internal finance owner account used in ledger/accounting workflows.

---

## Technology Stack

### Backend

| Component | Version/Package | Purpose |
|-----------|------------------|---------|
| PHP | 8.4.x | Runtime |
| Laravel | 12.x | Web framework |
| Inertia Laravel | v2 | Server-client page bridge |
| Laravel Fortify | v1 | Authentication and security |
| Laravel Reverb | v1 | Realtime broadcasting |
| Spatie Permission | v6 | RBAC (roles/permissions) |
| Spatie Activity Log | v4 | Audit logging |
| Spatie Media Library | v11 | Media/file management |
| Spatie Backup | v10 | Backup management |
| Laravel Web Push Channel | v10 | Push notifications |
| lab404/laravel-impersonate | v1 | Admin impersonation |

### Frontend

| Component | Version/Package | Purpose |
|-----------|------------------|---------|
| Vue | v3 | UI framework |
| @inertiajs/vue3 | v2 | Inertia client |
| TypeScript | v5 | Type safety |
| Tailwind CSS | v4 | Styling |
| Vite | v7 | Bundling/build |
| Laravel Echo + Pusher JS | v2/v8 | Realtime client |
| TipTap | v2 | Rich-text editor for CMS content |

### Payments and Messaging

| Integration | Package/Source | Role |
|------------|----------------|------|
| bKash | `theihasan/laravel-bkash` | Payment gateway |
| SSLCommerz | `raziul/sslcommerz-laravel` | Payment gateway |
| Manual gateway | Internal workflow | Admin-marked payments |
| SMS providers | `xenon/laravelbdsms` | OTP and test SMS delivery |

---

## Application Architecture

### Panel-Oriented Web Architecture

The app is organized into role-based web panels (not a separate REST API project):

- Public web pages (`/`)
- Admin panel (`/admin/*`)
- Tutor panel (`/tutor/*`)
- Guardian panel (`/guardian/*`)
- Shared account settings (`/settings/*`)

### Backend Layering

- **Controllers**: request orchestration + response mapping.
- **Form Requests**: validation and request contracts.
- **Services**: domain workflows (job lifecycle, hiring, verification, finance, refunds, support).
- **Policies + Middleware**: authorization and access gates.
- **Events/Listeners/Notifications**: async and decoupled user communication.
- **Enums**: centralized state/value contracts.

### Frontend Layering

- Inertia pages in `resources/js/pages`.
- Shared UI components in `resources/js/components`.
- Role-based layouts and navigation in `resources/js/layouts`.
- Wayfinder-generated route helpers in `resources/js/actions` and `resources/js/routes`.

### High-Level Workflow Boundaries

- **Marketplace domain**: jobs, applications, assignment confirmation.
- **Verification domain**: request, approval/rejection, invoice issuance, payment completion.
- **Finance domain**: invoices, payment attempts, refunds, double-entry ledger.
- **Support/content domain**: tickets, blog, pages, FAQ, tutorials, notices.

---

## Core Domains and Modules

### 1. Job Marketplace

- Guardian job posting with taxonomy-driven form options.
- Admin moderation and publication lifecycle.
- Tutor job board with filtering and sorting.
- Application management and hiring confirmation.

### 2. Verification

- Tutor/guardian verification request submission.
- Admin decision workflow (approve/reject/cancel).
- Verification invoice generation.
- Auto-finalization of verification status after successful invoice payment.

### 3. Finance

- Invoice issuance and status lifecycle.
- Gateway payment attempt orchestration.
- Admin manual payment overrides.
- Refund request approval and payout flows.
- Balanced ledger journal posting.

### 4. Notifications and Realtime

- Database notifications across roles.
- Role/private broadcast channels.
- Browser push subscriptions and delivery.
- Notice fan-out jobs to role audiences.

### 5. Content and Operations

- Blog with categories/tags/media.
- FAQ, tutorials, pages, testimonials, notices.
- Contact message inbox.
- Backup, activity logs, reporting, system settings.

---

## Authentication and Authorization

### Authentication Flow

1. User registers as tutor or guardian.
2. Account starts in `pending_verification` state.
3. OTP is issued and verified.
4. Account is activated.
5. Optional email verification and optional two-factor challenge.

### Login Behavior

- Tutor/guardian login is role-aware.
- Admin has a dedicated login flow under `/admin/login`.
- Suspended users are blocked.

### Authorization

- Role middleware (`ensure.role:*`) protects panel scopes.
- Active status middleware (`ensure.active`) prevents non-active access.
- Spatie permission middleware protects admin actions.
- Policies enforce resource ownership/access for invoices, payments, jobs, applications, and support tickets.

---

## Route and Panel Structure

### Route Files

| File | Scope |
|------|-------|
| `routes/web.php` | Public + shared authenticated endpoints |
| `routes/admin.php` | Admin panel |
| `routes/tutor.php` | Tutor panel |
| `routes/guardian.php` | Guardian panel |
| `routes/settings.php` | Shared/user + admin settings |
| `routes/channels.php` | Broadcast channels |
| `routes/console.php` | Scheduler and console routes |

### Notable Public Routes

- `/` home page
- `/jobs`, `/jobs/{slug}`
- `/tutors`, `/tutors/{id}`
- `/faq`, `/blog`, `/blog/{slug}`
- `/contact`
- `/tutorials`
- `/privacy-policy`, `/terms-of-service`, `/refund-policy`
- `/pages/{slug}` (dynamic CMS pages)

### Payment Callback Routes

- `GET /payment/bkash/callback`
- `POST /payment/sslcommerz/ipn`
- `GET /payment/sslcommerz/success`
- `GET /payment/sslcommerz/fail`
- `GET /payment/sslcommerz/cancel`

### API Note

There is no dedicated `routes/api.php` API surface in the current project; the platform is implemented as an Inertia web application with controller-driven endpoints.

---

## Data Model Overview

### Core Entities

| Model | Purpose |
|-------|---------|
| `User` | Role-based platform account |
| `TutorProfile` / `TutorEducation` | Tutor profile and education history |
| `GuardianProfile` | Guardian profile data |
| `TuitionJob` | Job posting |
| `TuitionJobApplication` | Tutor application to a job |
| `TuitionJobAssignment` | Confirmed hire assignment |

### Verification and Finance Entities

| Model | Purpose |
|-------|---------|
| `VerificationRequest` | Verification request lifecycle |
| `Invoice` | Billable record |
| `Payment` | Gateway/manual payment attempt record |
| `RefundRequest` | Refund workflow record |
| `WalletLedgerEntry` | Double-entry accounting record |
| `PaymentGateway` | Configured gateway credentials/status |

### Content and Support Entities

| Model | Purpose |
|-------|---------|
| `BlogPost`, `BlogCategory`, `BlogTag` | Blog module |
| `Faq`, `Page`, `Tutorial`, `Notice`, `Testimonial` | CMS/content modules |
| `ContactMessage` | Public contact submissions |
| `SupportTicket`, `SupportTicketMessage` | Support ticketing and threads |
| `TutorReview` | Guardian feedback for tutors |

### Taxonomy Entities

- `Country`, `City`, `Area`
- `Category`, `SchoolClass`, `Subject`, `TuitionType`

### Status Enums (Selected)

- `UserStatus`: `active`, `suspended`, `pending`, `pending_verification`
- `JobStatus`: `pending`, `live`, `confirmed`, `cancelled`, `closed`
- `ApplicationStatus`: `applied`, `shortlisted`, `appointed`, `confirmed`, `cancelled`
- `InvoiceStatus`: `draft`, `unpaid`, `processing`, `paid`, `failed`, `cancelled`, `void`, `expired`, `refunded`
- `PaymentStatus`: `pending`, `paid`, `failed`, `cancelled`, `refunded`
- `VerificationStatus`: `unverified`, `pending`, `approved`, `invoiced`, `verified`, `rejected`, `cancelled`
- `RefundStatus`: `pending`, `approved`, `rejected`, `paid`

---

## Finance and Payment Workflows

### Invoice Lifecycle

- Invoices are issued for verification fees, platform service fees, and optional month-1 escrow.
- Invoice numbers are generated by a dedicated service.
- Payable invoices can be paid through bKash/SSLCommerz or manually marked paid by admin.

### Payment Flow

1. User starts payment from invoice center.
2. Payment attempt record is created in `pending` state.
3. Gateway initialization returns redirect URL + reference.
4. Callback validation finalizes payment.
5. Invoice is marked paid (or failed/cancelled when applicable).
6. Ledger journals are posted.

### Hire-Triggered Billing

On hire confirmation:

- Assignment is created.
- Platform service fee invoice is issued (typically tutor payer).
- Optional month-1 escrow invoice can be issued (guardian payer).

### Refund Flow

- Tutor submits refund request on eligible paid service-fee assignment.
- Admin approves or rejects.
- On payout, refund payment record is created and invoice is marked refunded.
- Ledger reversal entries are posted.

---

## Notifications, Events, and Realtime

### Notifications

- Database notifications for tutors, guardians, and admins.
- Notification inbox pages per panel (`/tutor/notifications`, `/guardian/notifications`, `/admin/notifications`).
- Mark single/all as read support.

### Push Notifications

- Browser push subscription endpoints:
  - `POST /push-subscriptions`
  - `DELETE /push-subscriptions`
- Push notifications are used for selected notice/ticket flows when subscribed.

### Domain Events (Examples)

- `ApplicationSubmitted`, `ApplicationWithdrawn`, `ApplicationStatusUpdated`
- `HireConfirmed`
- `TicketCreated`, `TicketReplied`
- `NoticeCreated` (broadcasted)

### Broadcast Channels

- `App.Models.User.{id}`
- `role.tutor`
- `role.guardian`
- `role.admin`

---

## Queues, Jobs, and Scheduling

### Jobs

| Job | Responsibility |
|-----|----------------|
| `SendOtpSmsJob` | Sends OTP SMS messages |
| `SendNoticeNotificationsJob` | Stores notice notifications for target users |
| `SendNoticePushNotificationsJob` | Sends notice push payloads to subscriptions |

### Queue

Default queue driver is database (`QUEUE_CONNECTION=database` by default).

Common commands:

```bash
php artisan queue:work
php artisan queue:listen --tries=1 --timeout=0
```

### Scheduler

Defined in `routes/console.php`:

- `app:cleanup` daily at `02:00`
- `queue:prune-batches --hours=48` daily at `03:00`
- `activitylog:clean --days=90` weekly on Sunday at `04:00`

---

## Configuration and Environment

### Primary Config Areas

- Authentication/Fortify: `config/fortify.php`
- OTP: `config/otp.php`
- Queue: `config/queue.php`
- Broadcast/Reverb: `config/broadcasting.php`, `config/reverb.php`
- Mail/SMTP: `config/mail.php`
- SMS providers: `config/sms.php`
- Backups: `config/backup.php`
- Permissions: `config/permission.php`
- Activity logs: `config/activitylog.php`

### Settings Managed in Admin UI

- Site settings (branding/contact/social/legal)
- Payment gateway credentials/status (bKash, SSLCommerz, Manual)
- SMTP settings (create/update/test)
- SMS settings (create/update/test)

### Important Env Defaults

- `QUEUE_CONNECTION=database`
- `OTP_SMS_DRIVER=log|gateway`
- `SMS_DEFAULT_PROVIDER=...`
- Standard Laravel mail/database/app env keys

---

## Development and QA

### Local Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm install
npm run build
```

Or use:

```bash
composer setup
```

### Dev Stack

```bash
composer run dev
```

Includes server, queue listener, log tailing, and Vite.

### Lint/Format/Type Check

```bash
composer lint
vendor/bin/pint --parallel --test
npm run lint
npm run lint:check
npm run format
npm run format:check
npm run types:check
```

### Tests

Framework: Pest (Laravel plugin).

```bash
php artisan test
php artisan test --compact
php artisan test --feature
php artisan test --unit
php artisan test --filter=testName
```

---

## Deployment and Operations

### Recommended Production Steps

1. Configure production `.env`.
2. Install dependencies and build assets.
3. Run migrations with `--force`.
4. Cache config/routes/views/events/icons.
5. Run queue workers under process supervisor.
6. Configure scheduler cron.

Useful commands:

```bash
composer prod:optimize
composer prod:clear
php artisan migrate --force
npm run build
```

### Backups and Logs

- Backup operations available in admin panel and via Artisan (`backup:run`, `backup:clean`, etc.).
- Activity logs are queryable in admin UI.
- Log viewer UI is available for authorized admins.

### Cleanup Command

```bash
php artisan app:cleanup
php artisan app:cleanup --dry-run
```

Performs:

- Expired OTP cleanup.
- Expired invoice status updates.

---

## Troubleshooting

### Vite Manifest Error

Error:

`Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest`

Fix:

```bash
npm run build
```

For local development, run:

```bash
npm run dev
# or
composer run dev
```

### Queue Not Processing

```bash
php artisan queue:work
```

Also confirm `QUEUE_CONNECTION` and worker supervision in production.

### Payment Callback Mismatch

- Verify gateway credentials/status in admin settings.
- Verify callback URL configuration on provider dashboard.
- Inspect payment attempts and invoice status in admin finance screens.

### OTP Delivery Issues

- Validate `OTP_SMS_DRIVER` and active/default SMS setting.
- Use admin SMS test endpoint to validate credentials.
- Review logs/queue worker status.

---

**Last updated: March 2026**
