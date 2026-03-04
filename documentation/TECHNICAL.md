# Tutor Finder - Technical Documentation

## Table of Contents

1. [System Overview](#system-overview)
2. [Technology Stack](#technology-stack)
3. [Architecture](#architecture)
4. [Installation](#installation)
5. [Configuration](#configuration)
6. [Database Schema](#database-schema)
7. [Authentication & Authorization](#authentication--authorization)
8. [API & Routes](#api--routes)
9. [Payment Integration](#payment-integration)
10. [Queue & Jobs](#queue--jobs)
11. [Testing](#testing)
12. [Deployment](#deployment)
13. [Maintenance](#maintenance)

---

## System Overview

Tutor Finder is a tutoring marketplace platform that connects guardians/students with qualified tutors. The platform provides:

- **Job Board**: Guardians post tutoring jobs, tutors apply
- **Verification System**: Identity and credential verification for tutors and guardians
- **Payment Processing**: Integrated payment gateways (bKash, SSLCommerz)
- **Admin Panel**: Complete administrative control over users, jobs, finance, and content
- **Wallet System**: Double-entry ledger for tracking payments and refunds

---

## Technology Stack

### Backend
| Component | Version | Purpose |
|-----------|---------|---------|
| PHP | 8.4+ | Runtime |
| Laravel | 12 | Framework |
| Laravel Fortify | 1.x | Authentication |
| Spatie Permission | 6.x | Role-based access control |
| Spatie Activity Log | 4.x | Audit logging |
| Spatie Media Library | 11.x | File uploads |

### Frontend
| Component | Version | Purpose |
|-----------|---------|---------|
| Vue.js | 3.x | UI Framework |
| Inertia.js | 2.x | SPA Bridge |
| Tailwind CSS | 4.x | Styling |
| TypeScript | 5.x | Type safety |
| Vite | 7.x | Build tool |

### Payment Gateways
| Gateway | Package | Purpose |
|---------|---------|---------|
| bKash | theihasan/laravel-bkash | Mobile payments |
| SSLCommerz | raziul/sslcommerz-laravel | Card/Bank payments |

### Infrastructure
| Component | Purpose |
|-----------|---------|
| SQLite/MySQL/PostgreSQL | Database |
| Redis (optional) | Cache & Queue |
| Laravel Sail | Docker development |

---

## Architecture

### Directory Structure

```
app/
├── Actions/           # Fortify action classes
├── Console/Commands/  # Artisan commands
├── Contracts/         # Interfaces
├── Enums/             # PHP 8.1+ enums
├── Http/
│   ├── Controllers/
│   │   ├── Admin/     # Admin panel controllers
│   │   ├── Auth/      # Authentication controllers
│   │   ├── Guardian/  # Guardian panel controllers
│   │   ├── Public/    # Public page controllers
│   │   ├── Settings/  # Settings controllers
│   │   └── Tutor/     # Tutor panel controllers
│   ├── Middleware/    # Custom middleware
│   └── Requests/      # Form request validation
├── Jobs/              # Queued jobs
├── Models/            # Eloquent models
├── Notifications/     # Notification classes
├── Policies/          # Authorization policies
├── Providers/         # Service providers
├── Services/          # Business logic services
│   ├── Finance/       # Financial services
│   └── Job/           # Job workflow services
└── Support/           # Helper classes
```

### Key Design Patterns

1. **Service Layer**: Business logic encapsulated in service classes
2. **Form Requests**: Validation separated from controllers
3. **Policies**: Authorization logic in dedicated policy classes
4. **Enums**: Type-safe status and type constants
5. **Double-Entry Ledger**: Financial transactions use balanced journal entries

---

## Installation

### Prerequisites

- PHP 8.4+
- Composer 2.x
- Node.js 20+
- npm 10+

### Quick Setup

```bash
# Clone repository
git clone <repository-url> tutor-finder
cd tutor-finder

# Run setup script
composer setup
```

### Manual Setup

```bash
# Install PHP dependencies
composer install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Run migrations
php artisan migrate

# Install frontend dependencies
npm install

# Build assets
npm run build
```

---

## Configuration

### Environment Variables

#### Application
```env
APP_NAME="Tutor Finder"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com
```

#### Database
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=tutor_finder
DB_USERNAME=root
DB_PASSWORD=
```

#### Queue
```env
QUEUE_CONNECTION=database
```

#### Mail
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailgun.org
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=tls
```

#### Payment Gateways

**bKash**
```env
BKASH_SANDBOX=false
BKASH_APP_KEY=your-app-key
BKASH_APP_SECRET=your-app-secret
BKASH_USERNAME=your-username
BKASH_PASSWORD=your-password
```

**SSLCommerz**
```env
SSLCOMMERZ_SANDBOX=false
SSLCOMMERZ_STORE_ID=your-store-id
SSLCOMMERZ_STORE_PASSWORD=your-store-password
```

#### SMS
```env
SMS_DRIVER=twilio
SMS_API_KEY=your-api-key
```

---

## Database Schema

### Core Models

| Model | Table | Description |
|-------|-------|-------------|
| User | users | All platform users |
| TutorProfile | tutor_profiles | Tutor-specific profile data |
| GuardianProfile | guardian_profiles | Guardian-specific profile data |
| TuitionJob | tuition_jobs | Job postings |
| TuitionJobApplication | tuition_job_applications | Tutor applications |
| TuitionJobAssignment | tuition_job_assignments | Confirmed hires |

### Financial Models

| Model | Table | Description |
|-------|-------|-------------|
| Invoice | invoices | Payment invoices |
| Payment | payments | Payment transactions |
| WalletLedgerEntry | wallet_ledger_entries | Double-entry ledger |
| RefundRequest | refund_requests | Refund requests |
| PaymentGateway | payment_gateways | Gateway configuration |

### Taxonomy Models

| Model | Table | Description |
|-------|-------|-------------|
| Country | countries | Countries |
| City | cities | Cities |
| Area | areas | Areas/localities |
| Category | categories | Subject categories |
| Subject | subjects | Subjects |
| SchoolClass | school_classes | School grade levels |
| TuitionType | tuition_types | Tuition types |

### Enums

| Enum | Values |
|------|--------|
| UserRole | Admin, Tutor, Guardian, Platform |
| UserStatus | Active, Suspended, Pending, PendingVerification |
| JobStatus | Open, Closed, Assigned, Cancelled |
| InvoiceStatus | Draft, Unpaid, Paid, Refunded, Void, Processing, Failed, Cancelled, Expired |
| PaymentStatus | Pending, Completed, Failed, Refunded |
| VerificationStatus | Pending, Approved, Rejected |
| TaxonomyStatus | Active, Inactive |

---

## Authentication & Authorization

### Authentication Flow

1. **Registration**: Users register via `/register` with role selection (Tutor/Guardian)
2. **OTP Verification**: Phone number verified via SMS OTP
3. **Email Verification**: Email verified via link
4. **Two-Factor Authentication**: Optional TOTP-based 2FA

### User Roles

| Role | Access |
|------|--------|
| Admin | Full system access via `/admin` |
| Tutor | Tutor dashboard via `/tutor` |
| Guardian | Guardian dashboard via `/guardian` |
| Platform | System account for ledger entries |

### Middleware

```php
'ensure.active'    // Ensures user account is active
'ensure.role:tutor' // Ensures user has specific role
'permission:job-view' // Spatie permission check
'role:admin'       // Spatie role check
```

### Policies

- `PaymentPolicy` - Payment viewing/management authorization
- Admin controllers use `permission` middleware

---

## API & Routes

### Route Files

| File | Prefix | Purpose |
|------|--------|---------|
| web.php | / | Public routes |
| admin.php | /admin | Admin panel |
| tutor.php | /tutor | Tutor panel |
| guardian.php | /guardian | Guardian panel |
| settings.php | /settings | User settings |

### Key Route Groups

**Public**
- `GET /` - Landing page
- `GET /jobs` - Job board
- `GET /contact` - Contact page
- `GET /faq` - FAQ page
- `GET /blog` - Blog

**Admin** (`/admin`)
- `/dashboard` - Admin dashboard
- `/users/*` - User management
- `/jobs/*` - Job management
- `/finance/*` - Finance management
- `/settings/*` - System settings

**Tutor** (`/tutor`)
- `/dashboard` - Tutor dashboard
- `/profile` - Profile management
- `/jobs` - Browse/apply for jobs
- `/finance/*` - Invoices, payments, wallet
- `/notifications` - Notifications

**Guardian** (`/guardian`)
- `/dashboard` - Guardian dashboard
- `/jobs/*` - Post/manage jobs
- `/finance/*` - Payment history

---

## Payment Integration

### Payment Flow

1. **Invoice Creation**: System creates invoice for verification/service
2. **Gateway Selection**: User selects bKash or SSLCommerz
3. **Payment Initiation**: Redirect to payment gateway
4. **Callback Processing**: Gateway sends success/failure callback
5. **Ledger Posting**: Double-entry journal entries created
6. **User Verification**: If verification payment, user is verified

### Payment Callbacks

```
POST /payment/bkash/callback     # bKash callback
POST /payment/sslcommerz/success # SSLCommerz success
POST /payment/sslcommerz/fail    # SSLCommerz failure
POST /payment/sslcommerz/cancel  # SSLCommerz cancel
POST /payment/sslcommerz/ipn     # SSLCommerz IPN
```

### Double-Entry Ledger

Every payment creates two balanced entries:
- **Debit** entry for payer (reduces balance)
- **Credit** entry for payee (increases balance)

Linked by `journal_uuid` for audit trail.

---

## Queue & Jobs

### Queue Configuration

Default driver: `database`

```bash
# Start queue worker
php artisan queue:listen

# Production worker
php artisan queue:work --tries=3 --timeout=90
```

### Queued Jobs

| Job | Purpose |
|-----|---------|
| SendOtpSmsJob | Send SMS OTP codes |
| PaymentNotification | Send payment notifications |

### Scheduled Tasks

```bash
# Run scheduler (add to crontab)
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

| Command | Schedule | Purpose |
|---------|----------|---------|
| app:cleanup | Daily 02:00 | Clean expired records |
| queue:prune-batches | Daily 03:00 | Prune old batch records |
| activitylog:clean | Weekly Sunday 04:00 | Clean old activity logs |

---

## Testing

### Test Framework

Pest PHP v4 with Laravel plugin.

### Running Tests

```bash
# Run all tests
php artisan test

# Run with compact output
php artisan test --compact

# Run specific test file
php artisan test tests/Feature/FinanceModuleTest.php

# Run specific test by name
php artisan test --filter="it calculates wallet balance"

# Run with coverage
php artisan test --coverage
```

### Test Structure

```
tests/
├── Feature/
│   ├── Admin/           # Admin panel tests
│   ├── Auth/            # Authentication tests
│   ├── Authorization/   # Authorization tests
│   ├── Console/         # Command tests
│   ├── Finance/         # Finance service tests
│   ├── Guardian/        # Guardian panel tests
│   ├── Payments/        # Payment tests
│   └── Tutor/           # Tutor panel tests
├── Unit/                # Unit tests
├── Pest.php             # Pest configuration
└── TestCase.php         # Base test case
```

### Current Coverage

- 344+ tests passing
- Feature tests with RefreshDatabase
- Unit tests for isolated logic

---

## Deployment

### Production Checklist

1. **Environment**
   ```bash
   APP_ENV=production
   APP_DEBUG=false
   ```

2. **Optimize Application**
   ```bash
   composer prod:optimize
   # Runs: config:cache, route:cache, view:cache, event:cache, icons:cache
   ```

3. **Build Assets**
   ```bash
   npm run build
   ```

4. **Run Migrations**
   ```bash
   php artisan migrate --force
   ```

5. **Start Queue Worker**
   ```bash
   php artisan queue:work --daemon
   ```

6. **Configure Scheduler**
   ```bash
   # Add to crontab
   * * * * * cd /path && php artisan schedule:run >> /dev/null 2>&1
   ```

### Server Requirements

- PHP 8.4+ with extensions: BCMath, Ctype, Fileinfo, JSON, Mbstring, OpenSSL, PDO, Tokenizer, XML
- Composer 2.x
- Node.js 20+ (build only)
- Web server (Nginx/Apache)
- SSL certificate
- Queue worker (Supervisor recommended)

### Nginx Configuration

```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /var/www/tutor-finder/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.4-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

---

## Maintenance

### Clear Caches

```bash
composer prod:clear
# Runs: config:clear, route:clear, view:clear, event:clear, icons:clear
```

### Cleanup Command

```bash
# Dry run (preview changes)
php artisan app:cleanup --dry-run

# Execute cleanup
php artisan app:cleanup
```

This command:
- Deletes expired OTP requests
- Marks expired unpaid invoices as `Expired`

### Log Management

```bash
# View logs in real-time
php artisan pail

# Access log viewer UI
# Navigate to /log-viewer (admin only)
```

### Database Backups

```bash
# Run backup
php artisan backup:run

# List backups
php artisan backup:list

# Clean old backups
php artisan backup:clean
```

### Activity Logs

Activity logs are automatically recorded for:
- Invoice changes
- Payment transactions
- Wallet ledger entries
- User profile updates

Clean old logs:
```bash
php artisan activitylog:clean --days=90
```

---

## Troubleshooting

### Common Issues

**1. Vite Manifest Error**
```
Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest
```
Solution: Run `npm run build` or `npm run dev`

**2. Queue Jobs Not Processing**
Solution: Ensure queue worker is running:
```bash
php artisan queue:work
```

**3. Payment Callback Failures**
Check:
- CSRF exclusion in `bootstrap/app.php` for IPN routes
- Gateway credentials in `.env`
- Callback URL configuration in payment gateway dashboard

**4. Permission Denied Errors**
```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

---

## Support

For technical support or bug reports, please contact the development team or create an issue in the repository.
