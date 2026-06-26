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
