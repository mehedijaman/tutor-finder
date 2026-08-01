# 🎓 Tutor Finder — Smart Tutoring Marketplace Platform

[![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![Inertia.js](https://img.shields.io/badge/Inertia.js-v2.x-9553E9?style=for-the-badge&logo=inertia&logoColor=white)](https://inertiajs.com)
[![Vue.js](https://img.shields.io/badge/Vue.js-3.x-4FC08D?style=for-the-badge&logo=vuedotjs&logoColor=white)](https://vuejs.org)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-v4.x-06B6D4?style=for-the-badge&logo=tailwindcss&logoColor=white)](https://tailwindcss.com)
[![PHP](https://img.shields.io/badge/PHP-8.4-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![Pest](https://img.shields.io/badge/Pest_Testing-4.x-10B981?style=for-the-badge&logo=pest&logoColor=white)](https://pestphp.com)
[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg?style=for-the-badge)](LICENSE)

> **Tutor Finder** is an enterprise-grade, full-stack tutoring marketplace connecting **Guardians & Students** with verified **Tutors**. Built on **Laravel 12, Inertia v2, Vue 3, and Tailwind CSS v4**, the platform provides seamless job posting, advanced tutor discovery, interactive applicant management, automated verification workflows, and integrated payment gateways.

---

## 🌟 Key Features

### 👨‍👩‍👧 Guardian & Student Portal
- **3-Step Job Posting Wizard**: Step-by-step tuition requirement posting with dynamic Subject, Class, Curriculum selection, schedule setup, tutor gender preference, and live summary card.
- **Direct Tutor Requests**: Browse verified tutor profiles and send targeted tuition requests.
- **Applicant Management & CV View**: View applicant lists, inspect tutor education & qualifications, and open/download single-click generated PDF CVs.
- **Tuition Management & Re-open Flow**: Track live, pending, assigned, or closed jobs, with the ability to re-open cancelled listings.
- **Expanded Profile Settings**: Emergency contacts, student relationships, preferred contact schedules, city, and area location details.

### 👩‍🏫 Tutor Portal
- **Advanced Job Search & Board**: Filter active tuition jobs by Job ID, Date Range, Location/City, Tutor Gender, Curriculum, and Subjects.
- **Gender Preference Validation**: Enforced matching criteria ensuring application eligibility based on guardian preferences.
- **Instant Job Applications**: 1-click application submission with live status tracking (`applied`, `shortlisted`, `hired`, `cancelled`).
- **Identity & Fee Verification**: BDT 200 verification fee payment workflow, verified profile badges, and document preview.
- **Location Intelligence**: Nearby job counts and regional tuition opportunity statistics mapped to tutor city.

### 🛡️ Admin Management Dashboard
- **12-Month Financial Analytics**: Interactive Revenue vs Refund trend charts, monthly income breakdown, and financial ledger reports.
- **Tutor Shortlisting & Hiring Settlement**: Full administrative control to shortlist candidates, confirm hiring, settle platform fees, or process refund requests.
- **Internal Admin Notes**: Dedicated private note tracking for Guardian and Tutor profiles.
- **Dynamic Taxonomy Management**: Manage subjects, classes, categories, and locations with inline quick-create API (`/taxonomies/quick-create`).
- **Support Ticket & CMS Management**: Full ticket resolution system, dynamic notice board, and page management (Privacy Policy, Terms of Service, Refund Policy).

---

## 🏗️ Technology Stack

| Layer | Technology |
| :--- | :--- |
| **Backend Framework** | Laravel 12.x (PHP 8.4) |
| **Frontend Bridge** | Inertia.js v2 |
| **Frontend Framework** | Vue 3 (Composition API `<script setup lang="ts">`) |
| **Styling & UI** | Tailwind CSS v4, Reka UI, Lucide Icons |
| **Type Safety & Routes** | TypeScript, `@laravel/vite-plugin-wayfinder` |
| **Authentication** | Laravel Fortify (Headless Auth) |
| **Payments & Gateway** | SSLCommerz, bKash Integration |
| **Notifications** | Web Push (VAPID / FCM) & Local SMS Gateways |
| **PDF Generation** | Spatie Laravel PDF (Browsershot / Chrome) |
| **Testing & Quality** | Pest 4 PHP, Laravel Pint (Code Formatter), Prettier |

---

## 🚀 Getting Started

### Prerequisites

Ensure your system meets the following requirements:
- **PHP**: `^8.4` (with `pdo`, `mbstring`, `openssl`, `bcmath` extensions)
- **Node.js**: `^20.19` or `>=22.12`
- **Composer**: `^2.x`
- **Database**: MySQL `^8.0` / PostgreSQL `^15` / SQLite

---

### Installation Setup

1. **Clone the Repository**
   ```bash
   git clone https://github.com/mehedijaman/tutor-finder.git
   cd tutor-finder
   ```

2. **Install PHP Dependencies**
   ```bash
   composer install
   ```

3. **Install JavaScript Dependencies**
   ```bash
   npm install
   ```

4. **Environment Configuration**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Database Migration & Seeding**
   Configure your database parameters in `.env`, then run:
   ```bash
   php artisan migrate --seed
   ```

6. **Build Frontend Assets & Run Development Server**
   ```bash
   # Run full development stack (Laravel server + Vite dev server)
   composer run dev
   
   # Or run manually in separate terminals:
   php artisan serve
   npm run dev
   ```
   Access the app in your browser at `http://127.0.0.1:8000`.

---

## 🧪 Testing & Code Quality

Tutor Finder maintains high code quality enforced through automated tests, static analysis, and strict linting.

```bash
# Run all automated tests (Pest 4)
php artisan test --compact

# Run a specific test suite or file
php artisan test tests/Feature/Tutor/TutorJobApplicationTest.php

# PHP Code Formatting (Laravel Pint)
composer lint

# JavaScript/TypeScript Formatting & Linting
npm run lint:check
npm run format:check

# Vue TypeScript Type Checking
npm run types:check

# Complete CI Verification Suite
composer ci:check
```

---

## 📂 Project Architecture

```
tutor-finder/
├── app/
│   ├── Http/
│   │   ├── Controllers/          # Admin, Guardian, Tutor, and Public Controllers
│   │   └── Requests/             # Form Validation Requests
│   ├── Models/                   # Eloquent Models & Casts (User, TuitionJob, Profiles)
│   └── Services/                 # Domain Services (ApplicationService, VerificationService)
├── database/
│   ├── migrations/               # Database Schema Migrations
│   └── seeders/                  # System Seeders & Taxonomy Data
├── resources/
│   ├── js/
│   │   ├── components/           # Reusable Vue & Reka UI Components
│   │   ├── composables/          # Vue Composition API Utilities
│   │   ├── layouts/              # AppLayout, TutorLayout, PublicLayout
│   │   ├── pages/                # Inertia Vue Pages (Admin, Guardian, Tutor, Public)
│   │   └── types/                # TypeScript Interfaces & API Types
│   └── views/                    # Root Blade Entrypoint (app.blade.php)
├── routes/                       # Web, Admin, Guardian, Tutor & Console Routes
└── tests/                        # Pest Feature & Unit Test Suite
```

---

## 🛡️ Security & Compliance

- **Role-Based Access Control**: Strict middleware guards (`ensure.role:admin`, `ensure.role:tutor`, `ensure.role:guardian`).
- **CSRF & XSS Protection**: Built-in CSRF tokens and sanitized HTML input processing.
- **Headless Authentication**: Powered by Laravel Fortify with 2FA, OTP verification, and rate limiting.
- **Strict Data Scoping**: Explicit Eloquent query scoping preventing cross-account resource leaks.

---

## 📜 License

This project is open-source software licensed under the [MIT License](LICENSE).

---

## 📬 Contact & Support

For platform inquiries, bug reports, or feature requests:
- **Email**: [support@tutorfinder.com](mailto:support@tutorfinder.com)
- **Repository**: [github.com/mehedijaman/tutor-finder](https://github.com/mehedijaman/tutor-finder)