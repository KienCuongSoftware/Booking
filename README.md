# Booking

A **Laravel 12** hotel booking monolith: **PHP 8.2**, MySQL (or any Laravel-supported database), **Vite**, **Tailwind CSS**, and **Alpine.js**. **Laravel Breeze**-style session auth with **roles**: **admin**, **host**, **staff**, and **customer**. Hosts manage **hotels** (thumbnail + gallery, provinces, pricing, amenities, email templates) and **room types** (inventory, area, beds, amenities, images). Customers browse the **public catalog**, **book by date**, pay via **PayPal** or **MoMo** (when enabled), manage **waitlists**, **favorites**, **messages**, **reviews**, and **invoices (PDF)**. **Google OAuth**, **email OTP after registration**, and **OTP-guarded password change** are supported when mail is configured. **Laravel Sanctum** exposes a small **REST API** under `/api/v1`. UI uses **sidebar** (admin/host/staff), **customer header** on public + customer flows, and **flash** feedback.

**Repository:** [https://github.com/KienCuongSoftware/Booking](https://github.com/KienCuongSoftware/Booking)

## Requirements

- **PHP** ^8.2  
- **Composer**  
- **Node.js** and **npm** (Vite / Tailwind)  
- **MySQL** (or SQLite / PostgreSQL per Laravel)

## Installation

1. **Clone**

   ```bash
   git clone https://github.com/KienCuongSoftware/Booking.git
   cd Booking
   ```

2. **Dependencies**

   ```bash
   composer install
   ```

3. **Environment**

   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

   Configure `DB_*` and feature flags (see table below).

   | Area | `.env` keys (examples) |
   |------|-------------------------|
   | Database | `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` |
   | **Google login** | `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET`, `GOOGLE_REDIRECT_URI` |
   | **Mail** (OTP register, password OTP, reminders, status emails) | `MAIL_*`, `MAIL_FROM_*` |
   | **Queues** | `QUEUE_CONNECTION` — use `database` + `php artisan queue:work` or `composer run dev` |
   | **PayPal** | `PAYPAL_CLIENT_ID`, `PAYPAL_CLIENT_SECRET`, `PAYPAL_MODE` |
   | **MoMo** | `MOMO_ENDPOINT`, `MOMO_PARTNER_CODE`, `MOMO_ACCESS_KEY`, `MOMO_SECRET_KEY` |
   | **Booking / platform** | `BOOKING_*` — see `.env.example` (dynamic pricing, hold TTL, PayPal/MoMo toggles, reminders, audit, idempotency, pending SLA) |

4. **Migrations**

   ```bash
   php artisan migrate
   ```

5. **Storage link** (thumbnails, galleries, room images)

   ```bash
   php artisan storage:link
   ```

   Public URLs use `App\Support\PublicDisk` and `/storage/...`.

6. **Seed data (optional)**

   Default `DatabaseSeeder` loads provinces, amenities, demo users, cancellation policy samples, and demo bookings (adjust in `database/seeders/DatabaseSeeder.php`).

   ```bash
   php artisan db:seed
   ```

   **Extra seeders (run explicitly):**

   - `php artisan db:seed --class=ReportsChartDemoSeeder` — chart/demo bookings (`RDEMO-*`, `reports-demo-*@example.test`).
   - `php artisan db:seed --class=RealisticMassDataSeeder` — large synthetic load (`RSIM-*`, `khach.*@sim-booking.local`, `PCODE-SIM-*`); see comments in that class for `REALISTIC_SIM_*` env limits.

7. **Frontend**

   ```bash
   npm install
   npm run build
   ```

   For HMR: `npm run dev` (or `composer run dev`).

## Running

```bash
php artisan serve
```

### Development (server + queue + logs + Vite)

```bash
composer run dev
```

## What’s in the system

### Public (no account)

- **`GET /`** — Active hotels: filters `q`, `province_code`, `sort` (`newest`, `price_asc`, `price_desc`, `name`); **average rating** on cards when available.
- **`GET /hotels/{slug}`** — Detail: gallery, description, amenities, **rating + review count**, room types; **favorite** toggle for signed-in customers.
- **Legal** — `/legal/cancellation-refunds`, `/legal/privacy`, `/legal/terms` (footer on public + guest layouts).
- **SEO** — `/sitemap.xml`, `/robots.txt`; **Open Graph / Twitter** meta and **canonical** URL on catalog pages via `public-layout`.
- **Guest check-in info** — `GET /check-in/guest?payload=…` (throttled): minimal booking summary for front desk (token-validated).

### Authentication & profile

- Register → **email OTP** (`register`, `register/verify`, resend throttled).
- Login / logout; **Google OAuth**; forgot / reset password; Breeze-style **email verification** routes where applicable; **password change** with **OTP** (`password/otp`).
- **Profile** — name, email, password.

### Customer (`/customer`, `role:customer`)

- **Bookings** — list, detail, **create** from hotel flow, **cancel** with policy preview, **edit dates** (where allowed), **rebook** entry.
- **Payments** — **PayPal** and **MoMo** resume/return/cancel routes; webhooks `POST /webhooks/paypal`, `POST /webhooks/momo`.
- **PDF invoice** — `bookings/{booking}/invoice.pdf` (Dompdf).
- **Electronic pass / QR** — check-in payload for host; link to **guest check-in** page.
- **Messages** — per-booking thread with host.
- **Favorites** — list + toggle on catalog/detail.
- **Waitlist** — list, create per hotel/room/dates.
- **Reviews** — after **completed** stays; **“My reviews”** index; reminder CTAs in **status-changed** and **follow-up** emails.
- **Inbox badge** — Alpine poll `GET /customer/inbox/unread-count` (throttled) for unread host messages.

### Host (`/host`, `role:host`)

- **Dashboard**; **hotels** CRUD (pricing multipliers, email templates JSON, etc.).
- **Room types** — CRUD, images, beds, amenities; **physical rooms** per type (`/host/room-types/{roomType}/physical-rooms`) for labels + **assignment on bookings** (overlap-checked); **availability** grid by type + **per physical room** when units exist.
- **Bookings** — filter, **status updates** (shared rules with staff), internal notes/tags, **mark paid**, **refund transaction** status, **check-in** (QR / token), **messages** with guest.
- **Cancellation policy** editor (tiers + reminder flags).
- **Reports** — charts (revenue / cancel / no-show), **CSV** and **PDF** export (6‑month window).
- **Promo codes** — CRUD per hotel.
- **Email templates** — per-hotel JSON editor.

### Staff (`/staff`, `role:staff`)

- **Dashboard**; bookings **index**, **pending**, **history**; **status updates** (same service as host, scoped).

### Admin (`/admin`, `role:admin`)

- **Dashboard**; **system overview**; **settings** (effective config display).
- **Users** — list (STT column), edit role/active/name/email.
- **Hotels** — system-wide list + **rich detail** (gallery, policy, room types, etc.).
- **Bookings** — system-wide list/filter.
- **Audit log** — browse recorded actions.

### REST API (`routes/api.php`)

- **`GET /api/v1/health`** — JSON health (throttled).
- **`GET /api/v1/hotels`**, **`GET /api/v1/hotels/{slug}`** — public catalog JSON (throttled).
- **`POST /api/v1/auth/token`** — Sanctum token (throttled).
- **`GET /api/v1/me`**, **`GET /api/v1/my-bookings`** — `auth:sanctum` (throttled).

### Platform services (selected)

- **Room availability** by `room_type.quantity` and overlapping **pending/confirmed** bookings (`RoomAvailabilityService`).
- **Booking lifecycle**, **cancellation fees**, **ledger / transactions**, **notifications** (created, status changed, reminders, follow-ups, host pending SLA), **waitlist** slot notifications.
- **Audit logging** (`AuditLogService`, configurable via `BOOKING_AUDIT_ENABLED`).
- **Idempotency** keys (where enabled), **PayPal / MoMo** checkout helpers.

## Project structure (high level)

| Area | Paths |
|------|--------|
| **Public HTTP** | `Public\HotelCatalogController`, `Public\LegalPageController`, `Public\SitemapController`, `Public\RobotsController`, `Public\GuestCheckInController` |
| **Host** | `Host\HotelController`, `Host\RoomTypeController`, `Host\PhysicalRoomController`, `Host\BookingController`, `Host\BookingCheckInController`, `Host\BookingMessageController`, `Host\AvailabilityController`, `Host\CancellationPolicyController`, `Host\ReportsController`, `Host\PromoCodeController`, `Host\HotelEmailTemplateController`, … |
| **Customer** | `Customer\BookingController`, `Customer\BookingPaymentController`, `Customer\BookingInvoiceController`, `Customer\BookingPassController`, `Customer\BookingMessageController`, `Customer\HotelFavoriteController`, `Customer\WaitlistController`, `Customer\ReviewController`, `Customer\CustomerInboxController` |
| **Staff / Admin** | `Staff\*`, `Admin\*` |
| **API** | `App\Http\Controllers\Api\V1\*` |
| **Webhooks** | `Webhooks\PayPalWebhookController`, `Webhooks\MoMoWebhookController` |
| **Models** | `User`, `Hotel`, `HotelImage`, `Booking`, `BookingMessage`, `Review`, `PhysicalRoom`, `PromoCode`, `AuditLog`, … |
| **Views** | `resources/views/public/*`, `host/*`, `customer/*`, `staff/*`, `admin/*`, `components/public-layout.blade.php`, `layouts/*` |
| **Routes** | `routes/web.php`, `routes/api.php`, `routes/auth.php` |

## Caching & queues

- `.env.example` favors **`CACHE_STORE=database`**, **`SESSION_DRIVER=database`**, **`QUEUE_CONNECTION=database`** for simple local / XAMPP setups.
- Use **`php artisan queue:work`** (or **`composer run dev`**) so **queued mail** and jobs run.

## PDF generation

- Customer **invoices** and host **report PDF** use **barryvdh/laravel-dompdf** (`Pdf::loadView(...)`).

## Testing

```bash
php artisan test
```

Feature tests include e.g. legal pages and sitemap (`RefreshDatabase` where needed).

## Possible next steps

| Idea | Notes |
|------|--------|
| **Compare / cart** (session) | Not implemented |
| **Full i18n** (EN/VI files) | Many strings are Vietnamese in views |
| **Stricter production** | WAF, monitoring, backups, CSP, rate limits beyond current API throttles |

## Code style

[Laravel Pint](https://laravel.com/docs/pint):

```bash
./vendor/bin/pint
```

## Contributing & security

See [CONTRIBUTING.md](CONTRIBUTING.md), [CODE_OF_CONDUCT.md](CODE_OF_CONDUCT.md), and [SECURITY.md](SECURITY.md). Pull request authors can use [PR_DESCRIPTION.md](PR_DESCRIPTION.md) as a template.

## License

Open-sourced under the [MIT License](LICENSE).
