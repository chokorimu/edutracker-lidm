# EduTrack SKS — Cleaned Status

## Tech Stack

- **Backend:** Laravel 13, PHP 8.3+
- **Frontend:** Livewire 4 + Volt (single-file), Blade, TailwindCSS, Vite
- **Database:** SQLite (default) / MySQL
- **Testing:** PHPUnit 12
- **Dev tooling:** Laravel Pint, Pail

---

## Completed Features

| Fitur | Lokasi | Keterangan |
|-------|--------|------------|
| Auth login role-based | `pages/auth/⚡login.blade.php` | Admin, Dosen, Siswa |
| Middleware per role | `app/Http/Middleware/` | EnsureAdmin, EnsureDosen, EnsureSiswa |
| Dashboard Siswa (DB-driven) | `DashboardController@siswaDashboardData` | Profile, Matakuliah, Tugas Mendatang, Notifikasi |
| Dashboard Dosen (tabs) | `pages/dosen/⚡dashboard.blade.php` | Tugas CRUD, Beban monitoring, Notifikasi, Profil |
| BebanCalculator service | `app/Services/BebanCalculator.php` | Kategorisasi: ringan/normal/berat/overload |
| Model KRS, MataKuliah, Tugas, NilaiTugas | `app/Models/` | Relasi sudah terdefinisi |
| Notifikasi Siswa & Dosen (terpisah) | `notifikasi`, `notifikasi_dosen` tables | Skema sudah dipisah |
| IpkHistory | `app/Models/IpkHistory.php` | Riwayat IPK per semester |
| DosenPa mapping | `app/Models/DosenPa.php` | Pemetaan dosen PA ke siswa |
| KalenderAkademik | `app/Models/KalenderAkademik.php` | Model ada |
| Laporan & Pengaturan | `app/Models/Laporan.php`, `Pengaturan.php` | Model ada |
| Admin CRUD panel | `AdminResourceController` | Generic CRUD semua resource |
| Seeder (Admin, Dosen, Siswa) | `database/seeders/` | Credentials siap pakai |
| Early Warning System | `app/Console/Commands/CheckBebanAkademik.php` | Scheduled daily at 07:00 |

---

## Credentials Dev

| Role | Email | Password |
|------|-------|----------|
| Admin | `admin@edutrack.test` | `admin123` |
| Dosen | `dosen@edutrack.test` | `dosen123` |
| Siswa | `andi@edutrack.test` | `siswa123` |

```bash
php artisan migrate:fresh --seed
composer run dev
```
