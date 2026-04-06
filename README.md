# Booking

A **Laravel 12** accommodation-booking web application: **PHP 8.2**, MySQL (or any Laravel-supported database), **Vite**, **Tailwind CSS**, and **Alpine.js**. **Laravel Breeze**-style authentication with **role-based areas** for **admin**, **host**, **staff**, and **customer**. Hosts manage **hotels** (thumbnail + gallery, provinces, address, pricing, **hotel-level amenities**) and **room types** (capacity, inventory, **floor area (m²)**, bed configuration lines, **room-level amenities**, images). Reference data is **seeded** (provinces, amenities). **Google OAuth** (Socialite), **email OTP** after registration, and **password change** guarded by **OTP** are supported where configured. UI uses a **sidebar shell** for authenticated areas, Tailwind pagination defaults, and flash messages. **Public catalog** at **`/`** lists **active** hotels (filter by **province**, **keyword**, **sort**); **`/hotels/{slug}`** shows gallery, description, amenities, and **active room types** (no auth). Suitable as a **course / portfolio** project (e.g. developed on **XAMPP**); **date-based booking** can be added next.

**Repository:** [https://github.com/KienCuongSoftware/Booking](https://github.com/KienCuongSoftware/Booking)

## Requirements

- **PHP** ^8.2  
- **Composer**  
- **Node.js** and **npm** (for Vite / Tailwind assets)  
- **MySQL** (or any Laravel-supported database)

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

   Set `DB_*` in `.env`.

   | Feature | `.env` keys |
   |--------|-------------|
   | Database | `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` |
   | **Google login** | `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET`, `GOOGLE_REDIRECT_URI` (default combines `APP_URL` + `/auth/google/callback`) |
   | **Email (registration OTP, password OTP, verification)** | `MAIL_MAILER`, `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_FROM_ADDRESS`, `MAIL_FROM_NAME` — use an **app password** for Gmail if applicable |
   | **Queues** (if you dispatch queued jobs / mail) | `QUEUE_CONNECTION` — `database` is set in `.env.example`; run `php artisan queue:work` or use `composer run dev` |

4. **Migrations**

   ```bash
   php artisan migrate
   ```

5. **Storage link** (hotel thumbnails, galleries, room-type images)

   ```bash
   php artisan storage:link
   ```

   Uploaded files under `storage/app/public/` are served at **`/storage/...`** (see `App\Support\PublicDisk`).

6. **(Optional) Seed data**

   Seeds **provinces**, **hotel amenities**, **room amenities**, and (if present) **`UserSeeder`** demo users — adjust `DatabaseSeeder` to match your needs.

   ```bash
   php artisan db:seed
   ```

7. **Frontend build**

   ```bash
   npm install
   npm run build
   ```

   For local development with hot reload, use `npm run dev` (see **Running** below).

## Running

```bash
php artisan serve
```

Open `http://127.0.0.1:8000` (or the URL Artisan prints).

### Development (server + queue + logs + Vite)

```bash
composer run dev
```

Runs `php artisan serve`, `queue:listen`, `pail`, and `npm run dev` via **Concurrently**.

## Features

### Public catalog (guest)

- **`GET /`** — Paginated hotel cards; query params: `q`, `province_code`, `sort` (`newest`, `price_asc`, `price_desc`, `name`).
- **`GET /hotels/{slug}`** — Hotel detail resolved by **globally unique** `hotels.slug`; inactive hotels return **404**.

### Authentication & profile

- **Register** → **email OTP verification** flow (`register/verify`, resend throttled).
- **Login** / **logout**; optional **Google OAuth** (`/auth/google`, callback).
- **Forgot password** / **reset password** (token link).
- **Email verification** (Breeze-style link flow) and **confirm password** where used.
- **Profile** — update name, email, password; **password change** can require **OTP** (`password/otp` routes).
- Root **`/`** is the **public catalog** for everyone; signed-in users still reach their **dashboard** from the header.

### Host (`auth` + `verified` + `role:host`)

- **Dashboard** — entry point after login.
- **Hotels** — full **CRUD**: province, address, star rating, description, **thumbnail**, **gallery** images, **old/new pricing**, **hotel amenities** sync, active flag; **pagination** on index.
- **Hotel detail** — view hotel and **active room types** (amenities, pricing, inventory preview).
- **Room types (“Phòng và giá”)** — list with filters by hotel, **pagination (5 per page)**; **create / edit / delete** per hotel: name, images, max guests, quantity, **area (m²)**, prices, bed lines, **room amenities**, visibility; delete confirmation modal (**Alpine** + `x-teleport="body"` for full-screen overlay).

### Staff (`role:staff`)

- **Dashboard**; **bookings** index, **pending**, and **history** (placeholders / flows for operational use).

### Customer (`role:customer`)

- **Dashboard**; **bookings** list, **cancellable** bookings, **rebook** entry points (UI flows aligned with course/demo scope).

### Admin (`role:admin`)

- **Dashboard**; **system overview** page for high-level monitoring.

### Layout & UI

- **Sidebar** navigation by role; **app** layout with header (title slot, profile, logout).
- **Tailwind** pagination views (`vendor.pagination.tailwind`) registered in `AppServiceProvider`.
- **Branding** — favicon SVGs under `public/`; `APP_NAME=Booking` in `.env.example`.

## Project structure (high level)

| Area | Paths |
|------|--------|
| **HTTP** | `Public\HotelCatalogController` (home + public hotel show), `Host\HotelController`, `Host\RoomTypeController`, `Host\BookingController`, `Host\DashboardController`, `Staff\*`, `Customer\*`, `Admin\*`, `ProfileController`, `Auth\*` (session, register, **RegisterOtpController**, **GoogleAuthController**, **PasswordOtpVerificationController**, password reset, email verification) |
| **Models** | `User`, `Hotel`, `HotelImage`, `Province`, `Amenity`, `RoomAmenity`, `RoomType`, `RoomTypeBedLine`, `RoomTypeImage`, … |
| **Form requests** | `App\Http\Requests\Host\StoreHotelRequest`, `UpdateHotelRequest`, `StoreRoomTypeRequest`, `UpdateRoomTypeRequest` |
| **Support** | `App\Support\PublicDisk` — stable **`/storage/...`** URLs for the public disk |
| **Views** | `resources/views/layouts/` (`app`, `guest`, `sidebar`, `navigation`), `public/hotels/*`, `components/public-layout.blade.php`, `host/hotels/*`, `host/room-types/*`, `components/` (`flash-status`, `icon/*`, `application-logo`), `auth/*`, role dashboards, `profile/*`, `vendor/pagination/*` |
| **Routes** | `routes/web.php` — role-prefixed groups (`admin`, `host`, `staff`, `customer`), Breeze routes in `routes/auth.php` |

There is **no** separate `routes/api.php` REST surface in this repository; APIs can be added later (e.g. Laravel Sanctum) if you extend the project.

## Caching & queues

- `.env.example` uses **`CACHE_STORE=database`** and **`SESSION_DRIVER=database`** so a local **XAMPP** setup does not require Redis.
- **`QUEUE_CONNECTION=database`** — run **`php artisan queue:work`** (or **`composer run dev`**) if you rely on **queued** jobs or mail.

## Image URLs

After `php artisan storage:link`, public disk files are available under:

- **`/storage/{path}`** — e.g. hotel and room-type images stored via `store(..., 'public')`.

## Testing

```bash
php artisan test
```

## Roadmap / possible extensions

| Area | Today | Possible direction |
|------|--------|-------------------|
| **Guest storefront** | Public **hotel list + detail** at `/` and `/hotels/{slug}` | Search facets, maps, availability calendar |
| **Bookings** | Role-scoped controllers / views | End-to-end reservation, payments, emails |
| **API** | Web routes only | Sanctum REST or SPA frontend |
| **i18n** | Mixed EN/VI strings in views | Laravel localization files |

This project is intended as a **learning / demo** monolith; production hardening (rate limits, audits, monitoring) is left to the maintainer.

## Code style

[Laravel Pint](https://laravel.com/docs/pint):

```bash
./vendor/bin/pint
```

## Contributing & security

See [CONTRIBUTING.md](CONTRIBUTING.md), [CODE_OF_CONDUCT.md](CODE_OF_CONDUCT.md), and [SECURITY.md](SECURITY.md). Pull request authors can use [PR_DESCRIPTION.md](PR_DESCRIPTION.md) as a template.

## License

Open-sourced under the [MIT License](LICENSE).
