# AI Warning Notes (Advanced Laravel Guidelines)

Read this before making more changes in EduTrack. These are critical architectural rules designed by a Senior Laravel Developer to prevent crashes, regressions, and performance bottlenecks.

## 1. Caching & Serialization (Preventing `__PHP_Incomplete_Class`)
- **NEVER cache raw Carbon instances or complex Eloquent objects** inside plain arrays returned by `Cache::remember`. When unserialized, they can degrade into `__PHP_Incomplete_Class` if the class footprint changes or cache drivers mismatch, causing `500 Internal Server Error` TypeErrors in Blade views.
- **Always normalize dates to strings** (`$date->format('Y-m-d H:i:s')`) and models to plain arrays/DTOs before putting them into cache.
- In Blade views, always use a fallback when parsing dates from cached arrays to prevent fatal crashes if the data is null/malformed: `Carbon::parse($task['deadline'] ?? now())`.
- If you alter the shape of cached data in controllers, **always run `php artisan cache:clear`** before testing.

## 2. Livewire `wire:navigate` Quirks (SPA Mode)
- We use `wire:navigate` on anchor tags for SPA-like transitions. This means the browser does NOT do a full page reload when switching tabs.
- **JavaScript initialization:** Any `<script>` tags that initialize plugins (like Chart.js in Prodi Dashboard or custom modals) might not re-trigger when the user navigates back. If you add JS, bind it to `document.addEventListener('livewire:navigated', function() { ... })` instead of `DOMContentLoaded`.

## 3. Database Query Strictness (SQLite vs MySQL & N+1)
- **Date Comparisons:** The PHPUnit tests run on SQLite, while production uses MySQL. `BETWEEN '2026-07-06' AND ...` will fail lexico-graphically against `2026-07-06 00:00:00` in SQLite. Always ensure queries and dummy data use full DateTime strings (`->format('Y-m-d H:i:s')`).
- **N+1 Queries:** When calculating `BebanCalculator`, always use `with('siswa')` or `with('mataKuliah')`. Never loop through collections (e.g. `$krs`) and call `$krs->siswa` if it wasn't eager-loaded.

## 4. Dosen Workload Logic
- When creating a new `Tugas`, the workload calculation MUST count the new unsaved task in its projection.
- When updating a `Tugas`, exclude the current task ID from the query, then add the updated projected date back into the count.
- Heavy/Overload warnings must trigger *before* save, forcing the user to check `override` to proceed.
- Do NOT create one `notifikasi_dosen` per student for a single task. That floods the lecturer with duplicate notifications. Group them.

## 5. Early Warning Command (`beban:check`)
- `UserSiswa::dosenPa()` is a `HasMany` relation (it tracks the history of PA assignments). Do NOT treat `$siswa->dosenPa` as a single model. Always use `->latest()->first()` to get the current PA.
- `beban:check` is scheduled in `routes/console.php`. Do NOT duplicate it in `app/Console/Kernel.php` (Laravel 11+ uses `routes/console.php` directly via `bootstrap/app.php`).

## 6. Routing And Roles
- The system uses multi-auth. Login must attempt all active guards: `admin`, `dosen`, `siswa`, and `prodi`.
- Role route names strictly required by tests: `admin.dashboard`, `dosen.dashboard`, `siswa.dashboard`, `prodi.dashboard`.

## 7. Mandatory Verification
Run these strictly after code changes. Never assume your code works without running the test suite:
```bash
php artisan cache:clear && php artisan view:clear
php artisan test
php artisan route:list
./vendor/bin/pint --dirty --test
```