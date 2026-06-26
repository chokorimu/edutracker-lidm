# AI Agent How-To: Developing New Features in EduTrack

This guide helps AI agents implement new features without breaking existing functionality.

## Project Context

- **Framework**: Laravel 13 + Livewire 4 + Volt
- **Database**: SQLite (default) or MySQL/Postgres
- **Roles**: Admin, Dosen, Siswa, Prodi
- **Testing**: PHPUnit, Laravel Pint

## Essential Verification Commands

Always run these after any code change:

```bash
php artisan test
php artisan route:list
./vendor/bin/pint --dirty --test
```

## Key Rules by Feature Area

### 1. Routing & Authentication

- When adding new routes, verify with `php artisan route:list`.
- Role route names must match:
  - `admin.dashboard`
  - `dosen.dashboard`
  - `siswa.dashboard`
  - `prodi.dashboard`
- Login must attempt all active guards: `admin`, `dosen`, `siswa`, `prodi`.
- Use appropriate middleware (`admin`, `dosen`, `siswa`, `prodi`).

### 2. Scheduling (Cron Jobs)

- `beban:check` is scheduled in `routes/console.php`.
- **Do not** register the same schedule in `app/Console/Kernel.php` — it causes duplicate entries.
- Verify schedule with:
  ```bash
  php artisan schedule:list
  ```

### 3. Dosen Workload Logic (`BebanCalculator`)

- **Critical**: `app/Services/BebanCalculator.php` has many active call sites.
- When creating a new `Tugas`:
  - Workload calculation must count the **unsaved** task.
- When updating a `Tugas`:
  - Exclude the current task ID, then add the updated task back into the count.
- Heavy/overload warnings must happen **before** save unless `override` is checked.
- **Do not** create one `notifikasi_dosen` per bimbingan student for a single task.

### 4. Early Warning Command (`CheckBebanAkademik`)

- `UserSiswa::dosenPa()` is a **has-many** relation.
- Do **not** treat `$siswa->dosenPa` as a single model.
- Pick the **latest** mapping when one Dosen PA is needed.
- Parse task deadlines with Carbon before comparing to `now()`.

### 5. Database Models

- All models are in `app/Models/`.
- Table naming: singular model name, snake_case (e.g., `UserSiswa` -> `user_siswa`).
- Use `$table` property if table name differs from convention (e.g., `UserSiswa` uses `user_siswa`).

### 6. Views & Components

- Volt pages use ⚡ prefix (e.g., `⚡login.blade.php`).
- Livewire components live in `resources/views/livewire/`.
- Blade views in `resources/views/pages/{role}/`.

### 7. Testing

- Tests are in `tests/Feature/` and `tests/Unit/`.
- Run specific test:
  ```bash
  php artisan test --filter=TestName
  ```

## Common Pitfalls

| Pitfall | Prevention |
|---------|-------------|
| N+1 Queries | Use `with()` for eager loading |
| Memory Bloat | Use SQL aggregation (`COUNT`, `SUM`) instead of PHP loops |
| Duplicate Schedule Entries | Don't add scheduling to both `routes/console.php` and `app/Console/Kernel.php` |
| Fake Clickable UI Elements | Implement real routes/handlers or mark as disabled |
| Wrong Guard Used | Ensure login attempts all active guards |

## Adding a New Feature

1. **Plan**: Identify affected models, routes, controllers, and views.
2. **Migrate**: Create migration if new database tables/columns are needed.
   ```bash
   php artisan make:migration create_new_table
   ```
3. **Model**: Update/create model if needed.
4. **Route**: Add route in `routes/web.php` with correct middleware.
5. **Controller**: Create controller if needed.
6. **View**: Add Volt page or Livewire component.
7. **Test**: Add test in `tests/Feature/`.
8. **Verify**: Run verification commands.

## File Structure Reference

```
edutracks/
├── app/
│   ├── Console/Commands/     # Artisan commands
│   ├── Http/
│   │   ├── Controllers/      # Resource & dashboard controllers
│   │   └── Middleware/       # Role middleware
│   ├── Models/               # Eloquent models
│   └── Services/             # Business logic (BebanCalculator)
├── database/
│   ├── migrations/          # Database migrations
│   └── seeders/              # Seeders
├── resources/views/
│   ├── layouts/              # Base layouts
│   ├── livewire/             # Livewire components
│   └── pages/                # Volt pages (⚡ prefix)
├── routes/
│   ├── console.php           # Console routes & scheduling
│   └── web.php              # Web routes
└── tests/
    └── Feature/              # Feature tests
```