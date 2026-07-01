# AI Warning Notes

Read this before making more changes in EduTrack. These are mistakes that already happened or is easy to repeat.

All known bugs from bug.md have been fixed, and bug.md has been removed.

## Routing And Roles

- `ProdiDashboardController` must be imported in `routes/web.php`.
- Login must attempt all active guards: `admin`, `dosen`, `siswa`, and `prodi`.
- Do not add a Prodi route without also verifying `php artisan route:list`.
- Role route names currently used by tests are:
  - `admin.dashboard`
  - `dosen.dashboard`
  - `siswa.dashboard`
  - `prodi.dashboard`

## Scheduling

- `beban:check` is scheduled in `routes/console.php`.
- Do not register the same schedule again in `app/Console/Kernel.php`.
- If `app/Console/Kernel.php` exists, keep it minimal and do not duplicate `Schedule::command('beban:check')`.
- Verify with:

```bash
php artisan schedule:list
```

## Dosen Workload Logic

- When creating a new `Tugas`, the workload calculation must count the new unsaved task.
- When updating a `Tugas`, exclude the current task ID and then add the updated task back into the count.
- Heavy/overload warnings must happen before save unless `override` is checked.
- Do not create one `notifikasi_dosen` per bimbingan student for a single task. That duplicates identical lecturer notifications.

## Early Warning Command

- `UserSiswa::dosenPa()` is a has-many relation.
- Do not treat `$siswa->dosenPa` as a single model.
- Pick the latest mapping when one Dosen PA is needed.
- Parse task deadlines with Carbon before comparing to `now()`.

## Siswa Profile Account Settings

- The Siswa dashboard account settings block is currently the active todo.
- Current target: `resources/views/pages/siswa/⚡dashboard.blade.php`, around lines 465-471.
- The rows `Keamanan Kata Sandi` and `Integrasi API SIAKAD` must not remain fake clickable controls.
- Either implement real actions with routes/Livewire handlers/tests, or make the rows visibly disabled/read-only.

## Verification Before Final Answer

Run these after code changes:

```bash
php artisan test
php artisan route:list
./vendor/bin/pint --dirty --test
```

If Pint fails on user-owned unrelated files, run Pint only on files you changed and clearly say what was skipped.

# todobug.md — Unverified Issues (require verification, no destructive action without proof)

## Bug 1 (UNVERIFIED — do not delete anything until confirmed): `app/Console/Kernel.php` likely dead code, possibly harmless

**Status: suspected, not confirmed.** Do not delete this file or any other file based on this entry alone — confirm first, per steps below.

### What I found

`bootstrap/app.php` has:
```php
commands: __DIR__.'/../routes/console.php',
```
This is Laravel 11+'s mechanism for auto-loading console routes (including `Schedule::command(...)` calls in `routes/console.php`). Confirmed: this project runs Laravel **13.16.1** (`composer.lock`).

A separate `app/Console/Kernel.php` also exists:
```php
class Kernel extends ConsoleKernel
{
    protected $commands = [];

    protected function commands()
    {
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }
}
```

### Why this might be a non-issue

Per Laravel's own docs and framework changelog (laravel/framework PR #52867, merged for the 11.x line): fresh Laravel 11+ apps do not ship an `App\Console\Kernel` class at all — `bootstrap/app.php`'s `commands:` key fully replaces it. Nothing in the standard boot path resolves `App\Console\Kernel` out of the container unless it's explicitly registered somewhere (e.g. in a service provider, or referenced by `composer.json`'s `extra` Laravel discovery config). I checked both `bootstrap/app.php` and `composer.json` in this repo and found no reference to `App\Console\Kernel` anywhere. That suggests this file is simply never instantiated — i.e., harmless dead code, not a double-registration bug.

**However** — I could not run `php artisan schedule:list` or any other Artisan command in my environment (no PHP interpreter available), so this is reasoning from framework behavior, not a verified test against this actual codebase. There could be a project-specific reason this file was added that isn't visible from a static read (e.g. some other package or service provider in this app that does explicitly resolve console kernels in a non-standard way).

### Required verification steps before any action

1. `php artisan schedule:list` — if `beban:check` appears exactly once, the file is confirmed inert (or harmless even if technically "loaded," since requiring the same file twice doesn't redefine `Schedule::command()` calls into two separate schedule entries — Laravel's `Schedule` is a singleton, so even a duplicate `require` would just call `->command()` twice on the same object, which WOULD actually create two entries — this needs to be checked, not assumed either way).
2. `php artisan beban:check` — run manually, check `notifikasi` and `notifikasi_dosen` tables for duplicate rows for the same task/student/day.
3. Only after both checks confirm a real duplicate: remove `app/Console/Kernel.php`. If checks show no duplication: leave the file, just add a one-line comment noting `bootstrap/app.php` already loads `routes/console.php`, so nobody adds new scheduling logic into `Kernel.php` thinking it's the only place it runs.
4. Do not delete this file as a guess. If you cannot run the verification commands (e.g. same sandbox limitation), leave the file untouched and say so explicitly rather than removing it preemptively.

## Bug 2 (confirmed, separate from Bug 1 — see prior task doc): fake clickable settings rows

Already documented in `TASK_FIX_SCHEDULE_AND_FAKE_SETTINGS.md` — "Keamanan Kata Sandi" / "Integrasi API SIAKAD" rows in the Siswa profile tab have `cursor-pointer` + chevron icon but no `href`/handler. This one IS confirmed by direct code read (no ambiguity — there's just no route or click handler present), unlike Bug 1.

## Restriction for whoever picks this up

- Do not delete, rename, or modify `app/Console/Kernel.php`, `routes/console.php`, or `bootstrap/app.php` without first running the verification steps above and reporting the actual output.
- Do not modify `app/Services/BebanCalculator.php` as part of investigating this — unrelated, and that file has many active call sites across Dosen/Siswa/Prodi dashboards that have broken before from unrelated edits.
- If you fix Bug 2 in the same pass, keep it as a separate, clearly labeled change in your summary from whatever you conclude about Bug 1 — don't bundle "fixed both" if Bug 1 turned out to need no code change.