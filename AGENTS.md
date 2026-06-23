# Edutracks — AI Agent Guide

## Overview

Academic tracking system (Indonesian university context) built with **Laravel 13 + Livewire/Volt**.
Three user roles: **Admin**, **Dosen** (lecturer), **Siswa** (student) — each with isolated dashboards and route middleware.

## Tech Stack

- **Backend:** Laravel 13, PHP 8.3+
- **Frontend:** Livewire 4 + Volt (single-file Livewire components), Blade views, Vite
- **Database:** SQLite by default (`.env.example`), migrations in `database/migrations/`
- **Testing:** PHPUnit 12 (`php artisan test`)
- **Dev tooling:** Laravel Pint (linting), Pail (log tailing), Pao (available in dev deps)

## Project Structure

```
edutracks/
├── app/
│   ├── Http/
│   │   ├── Controllers/     # AdminResourceController (CRUD), DashboardController (role dashboards + logout)
│   │   └── Middleware/       # EnsureAdmin, EnsureDosen, EnsureSiswa
│   ├── Models/              # Eloquent models (see below)
│   └── Providers/
├── config/
├── database/migrations/     # All custom tables + user tables
├── resources/views/
│   ├── layouts/app.blade.php
│   ├── livewire/admin/      # Admin Livewire components
│   ├── pages/admin/         # Admin Volt pages (⚡ prefix = Volt components)
│   ├── pages/auth/          # Login (Volt)
│   ├── pages/dosen/         # Dosen Volt pages
│   └── pages/siswa/         # Siswa Volt pages
├── routes/web.php           # All routes (no api.php usage)
├── tests/
└── composer.json
```

## Models (15 total)

| Model | Table | Purpose |
|-------|-------|---------|
| User | users | Base user (auth) |
| UserAdmin | user_admins | Admin profile |
| UserDosen | user_dosens | Dosen/lecturer profile |
| UserSiswa | user_siswas | Student profile |
| MataKuliah | mata_kuliah | Courses/subjects |
| Tugas | tugas | Assignments |
| NilaiTugas | nilai_tugas | Assignment grades |
| Krs | krs | Course registration (Kartu Rencana Studi) |
| DosenPa | dosen_pa | Academic advisor mapping |
| IpkHistory | ipk_history | GPA history |
| KalenderAkademik | kalender_akademik | Academic calendar |
| Notifikasi | notifikasi | Notifications (student-facing) |
| NotifikasiDosen | notifikasi_dosen | Notifications (dosen-facing) |
| Laporan | laporan | Reports |
| Pengaturan | pengaturan | Settings |

## Routes & Auth

- `/login` — Livewire/Volt auth page
- `/admin/dashboard` — Admin CRUD panel (middleware: `admin`)
- `/dosen/dashboard` — Dosen dashboard (middleware: `dosen`)
- `/siswa/dashboard` — Student dashboard (middleware: `siswa`)
- Role-based logout routes per role (`/admin/logout`, `/dosen/logout`, `/siswa/logout`)
- `AdminResourceController` provides generic CRUD: `index`, `store`, `update`, `destroy` on `/admin/dashboard/{resource}`

## Common Commands

```bash
composer run setup    # Full setup (install + migrate + build)
composer run dev      # Start dev (server, queue, pail, vite)
composer run test     # Clear config + run PHPUnit
php artisan test      # Run tests directly
./vendor/bin/pint     # Lint/format PHP
```

## Conventions

- Volt pages use ⚡ prefix in filenames (e.g. `⚡login.blade.php`)
- Livewire components live in `resources/views/livewire/`
- Models follow standard Laravel conventions — single class per file in `app/Models/`
- Migrations use timestamped filenames; custom tables added after the default Laravel ones
- Routes are all in `routes/web.php` — no API routes
- The `.env.example` defaults to SQLite; change `DB_CONNECTION` for MySQL/Postgres

## Adding New Features

1. Create migration → `php artisan make:migration`
2. Create/update model → `php artisan make:model`
3. Add Livewire component or Volt page as appropriate
4. Add route in `routes/web.php` with the correct role middleware
5. Run tests → `php artisan test`

## Changelog

### 2026-06-23 — Siswa dashboard: hardcoded → database queries
**File:** `app/Http/Controllers/DashboardController.php`

Replaced `siswaDashboardData()` hardcoded dummy data with real Eloquent queries. UI unchanged — view structure and key names are identical.

**Data sources per tab:**
- **Profile** (`profile`): `UserSiswa` fields + `IpkHistory` (latest IPK) + `MataKuliah` via KRS sum (SKS lulus/semester) + `DosenPa` (dosen name). Angkatan derived from NIM first 2 digits.
- **Matakuliah** (`matakuliah`): `Krs` where `semester = user->semester`, joined with `MataKuliah`. Beban computed from tugas count.
- **Tugas Mendatang** (`tugas_mendatang`): `Tugas` with deadline ≥ now, joined through `MataKuliah` → `Krs`. Limited to 4, ordered by deadline.
- **Notifikasi** (`notifikasi`): `Notifikasi` where `siswa_id = user->id`, latest 5. Waktu uses manual Indonesian diff (`diffForHumansId`).

**New private helpers:**
- `extractAngkatan($nim)` — parses 2-digit year prefix from NIM (2000+)
- `diffForHumansId($date)` — returns Indonesian relative time ("2 jam yang lalu")
- `emptySiswaData()` — fallback when no user is authenticated

### 2026-06-23 — Fixed seeders & login setup
**Files:** `database/seeders/DatabaseSeeder.php`, `database/seeders/UserAdminSeeder.php`, `database/seeders/UserDosenSeeder.php` (new), `database/seeders/UserSiswaSeeder.php`, `.env`

**Changes:**
- All seeders now use `Hash::make()` explicitly instead of relying on the `hashed` cast — more reliable across environments.
- Added `UserDosenSeeder` with credentials `dosen@edutrack.test` / `dosen123`.
- Updated `DatabaseSeeder` to include `UserDosenSeeder`.
- Switched `.env` from MySQL → SQLite (MySQL not available in sandbox; user can revert if needed).

**Credentials:**
| Role | Email | Password |
|---|---|---|
| Admin | `admin@edutrack.test` | `admin123` |
| Dosen | `dosen@edutrack.test` | `dosen123` |
| Siswa | `andi@edutrack.test` | `siswa123` |

**Setup:**
```bash
php artisan migrate:fresh --seed
```
