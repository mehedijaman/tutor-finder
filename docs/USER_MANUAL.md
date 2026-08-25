# Tutor Finder - User Manual

## Table of Contents

1. [Introduction](#introduction)
2. [Getting Started](#getting-started)
3. [Account and Security](#account-and-security)
4. [Guardian Guide](#guardian-guide)
5. [Tutor Guide](#tutor-guide)
6. [Notifications and Push Alerts](#notifications-and-push-alerts)
7. [Support Tickets](#support-tickets)
8. [Payments, Verification, and Refunds](#payments-verification-and-refunds)
9. [Administrator Guide](#administrator-guide)
10. [Public Website Features](#public-website-features)
11. [FAQ](#faq)
12. [Glossary](#glossary)

---

## Introduction

Tutor Finder is a tutoring marketplace where:

- **Guardians** post tutoring jobs and hire tutors.
- **Tutors** browse jobs and apply.
- **Admins** manage moderation, verification, finance, and operations.

This manual covers the live workflows currently available in the application.

---

## Getting Started

### Create an Account

1. Open the website and click **Register**.
2. Choose your role: **Guardian** or **Tutor**.
3. Enter your name, phone, optional email, and password.
4. Submit registration.
5. Enter the SMS OTP code on the verification page.
6. After OTP verification, your account is activated.

### Log In

1. Open the login page.
2. Select the correct role (Guardian or Tutor).
3. Enter your email or phone and password.
4. Complete two-factor authentication if enabled.

### Admin Login

- Admin users sign in from `/admin/login`.

---

## Account and Security

### Profile Settings

From **Settings > Profile** you can:

- Update name and email.
- Update personal profile data (role-specific profile screens are available in each panel).

### Password

From **Settings > Password** you can change your password.

### Two-Factor Authentication

From **Settings > Two Factor** you can enable or disable 2FA.

### Appearance

From **Settings > Appearance** you can set your preferred UI theme behavior.

### Delete Account

From **Settings > Profile** you can delete your account after password confirmation.

---

## Guardian Guide

### 1. Guardian Panel

The dashboard shows:

- Current job statistics by status.
- Recent notices targeted to guardians.

### 2. Post a Tuition Job

Go to **Hiring Pipeline > Post New Job** and fill in:

- Title and description
- Tuition type, category, class, subjects
- Location (country/city/area)
- Student/tutor gender preferences
- Schedule (days/time)
- Budget and salary details

Important:

- New jobs start as **Pending** and require admin approval before becoming **Live**.

### 3. Manage Job Pipeline

Use **Hiring Pipeline** tabs:

- All Jobs
- Pending Approval
- Live (applications open)
- Confirmed Hires
- Cancelled Jobs
- Closed Jobs

### 4. Review Applications and Hire

From a job’s applications page, you can:

- Shortlist applicants
- Cancel applications
- Confirm a selected tutor

On confirmation:

- The system finalizes the hire workflow.
- Optional escrow billing may be generated depending on confirmation data.
- Other open applications for that job are cancelled automatically.

### 5. Profile and Verification

From **Profile** you can:

- Update guardian profile fields.
- Submit verification request.
- Track verification status and related invoice.

### 6. Payments and Escrow

From **Payments & Escrow** you can:

- View your invoices.
- Filter by invoice status.
- Pay unpaid invoices via **bKash** or **SSLCommerz**.

### 7. Tutor Reviews

From **My Reviews** you can:

- Submit reviews for eligible confirmed tutor assignments.
- Edit, delete, restore, or permanently remove your review entries.

### 8. Tutorials and Terms

- **Tutorials**: role-specific help videos/content.
- **Terms of Service**: guardian terms page.

---

## Tutor Guide

### 1. Tutor Panel

The dashboard shows:

- Application statistics by status.
- Recent notices targeted to tutors.

### 2. Complete Your Profile

From **Profile** you can maintain:

- Personal information and bio
- Academic details and education history
- Preferred tuition types, classes, subjects, and locations
- Availability and expected salary range

Additional tools:

- **Download CV** as PDF.
- **View as Guardian** to preview public profile rendering.

### 3. Browse and Apply to Jobs

From **Browse Jobs** you can:

- Search and filter live jobs.
- Open job details.
- Submit applications with optional cover letter and salary expectation.

### 4. Manage Applications

From **My Applications** you can track statuses:

- `applied`
- `shortlisted`
- `appointed`
- `confirmed`
- `cancelled`

You can withdraw when allowed and reapply to previously cancelled applications.

### 5. Verification

From **Profile** verification area:

- Submit verification request.
- Track current verification state.
- Pay verification invoice when issued.

### 6. Fees and Refunds

From **Fees & Invoices** you can:

- View invoice history.
- Pay unpaid invoices via bKash/SSLCommerz.

From **Refund Requests** you can:

- Submit refund requests for eligible assignments (paid service-fee criteria).
- Track request status and admin decisions.

### 7. Tutorials and Terms

- **Tutorials**: tutor-oriented guidance.
- **Terms of Service**: tutor terms page.

---

## Notifications and Push Alerts

### Notification Center

Use the bell icon in the header to:

- View recent unread notifications.
- Mark all notifications as read.
- Open the full notification page for your role.

### Realtime Alerts

The platform supports realtime notifications for key events (for example notices and ticket updates).

### Browser Push

If your browser supports push notifications, you can enable/disable push alerts from the notification dropdown.

---

## Support Tickets

Both tutors and guardians can use support tickets.

### Create Ticket

1. Open **Support Tickets**.
2. Click **Create**.
3. Select category and priority.
4. Add message and optional attachments.

### Follow Up

- View ticket thread.
- Post replies.
- Track status (`open`, `in_progress`, `closed`).

Admins can assign tickets internally and update statuses.

---

## Payments, Verification, and Refunds

### Invoice Payments

Unpaid invoices can be paid from your invoice page via:

- **bKash**
- **SSLCommerz**

### Verification Billing

Verification request lifecycle:

1. Submit verification request.
2. Admin reviews and may approve.
3. Admin issues verification invoice.
4. After successful payment, verification is finalized.

### Refund Requests (Tutor)

Refund lifecycle:

1. Tutor submits refund request for eligible assignment.
2. Admin approves or rejects.
3. If approved, admin records payout.
4. Status updates to paid on completion.

---

## Administrator Guide

### 1. Admin Dashboard

The dashboard provides platform-wide stats for:

- Users (tutors/guardians)
- Jobs and applications
- Verifications
- Finance and refunds
- Tickets and contact messages

### 2. User and Access Management

Admins can:

- Manage admin users
- Manage tutor and guardian accounts
- Suspend/activate users
- Reset managed user passwords
- Restore or permanently delete soft-deleted users
- Manage roles and permissions
- Impersonate users for support

### 3. Job and Hiring Oversight

Admins can:

- Review and update job statuses
- Approve pending jobs to go live
- Inspect applications and job outcomes

### 4. Verification Operations

Admins can:

- Review verification requests
- Approve/reject/cancel decisions
- Issue verification invoices
- Mark invoices paid manually when needed

### 5. Finance and Reporting

Admins can:

- View invoices, payment attempts, refunds, and ledger entries
- Approve/reject refund requests
- Mark approved refunds as paid
- Access reports and export CSV files:
  - Income
  - Tuition
  - Refunds
  - User registrations
  - Job performance

### 6. Content and Taxonomy Management

Admins can manage:

- Blog posts, categories, tags
- FAQs
- Pages (including policy pages)
- Tutorials
- Notices
- Testimonials
- Tuition taxonomies (country, city, area, category, class, subject, tuition type)

### 7. Platform Settings and Operations

Admins can configure:

- Site settings
- Payment gateways
- SMS settings (with test SMS)
- SMTP settings (with test email)

Operational tools:

- Backup management
- Activity logs
- Log viewer access
- Contact message inbox

---

## Public Website Features

Public visitors can:

- Browse live jobs and open job details
- Browse tutor profiles and reviews
- Read FAQs, blog posts, and tutorials
- Read privacy policy, terms, refund policy, and other CMS pages
- Submit contact messages

---

## FAQ

### Why does my new job not appear publicly right away?

Guardian jobs start as **pending** and are published after admin approval.

### Why can’t I apply to some jobs as a tutor?

Applications are only allowed when a job is live, published, not expired, and not already finalized.

### Why is my verification still incomplete after approval?

Approval and payment are separate steps. If an invoice was issued, verification finalizes after successful payment.

### Why can’t I submit a refund request for some assignments?

Refund requests are limited to eligible assignments with paid service-fee conditions.

### I am not receiving notifications in the browser.

Check:

- Browser notification permission
- Push toggle state in the notification dropdown
- Active session/login state

---

## Glossary

| Term | Meaning |
|------|---------|
| Job | A tutoring request posted by a guardian |
| Application | Tutor response to a job |
| Assignment | Confirmed tutor hire for a job |
| Verification Request | User request to become verified |
| Invoice | Bill generated by platform workflows |
| Refund Request | Tutor request to reverse eligible paid service fee |
| Ledger Entry | Debit/credit accounting record |
| Notice | Admin broadcast message to tutor/guardian audiences |

---

**Last updated: March 2026**
