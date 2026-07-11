# TASK: Custom & Auto Rebalance Bobot Tugas (Dosen)

## Latar Belakang & Masalah
Saat ini, logika pembuatan tugas di EduTrack (`DosenResourceController@storeTugas`) selalu menyetel `bobot` = 0 di awal, kemudian secara otomatis mengeksekusi `rebalanceBobot()` untuk membagi rata 100% kepada seluruh tugas di mata kuliah tersebut (contoh: jika ada 2 tugas, otomatis jadi 50% dan 50%).

Ada permintaan baru dari Dosen:
**Dosen ingin bisa menentukan bobot tugas secara manual saat membuat tugas.**
- Jika dosen mengisi "20%", maka tugas itu bobotnya harus di-lock (dikunci) menjadi 20%.
- Tugas-tugas lama yang ada di mata kuliah tersebut (yang belum "di-lock" atau yang perlu disesuaikan) harus dikurangi atau dihitung ulang agar SISA persentasenya membagi sisa dari 100% dikurangi bobot kustom tadi.
- Contoh: Tugas lama ada 1, bobotnya 100%. Dosen tambah tugas baru, di-set 20% secara manual. Maka otomatis tugas lama berubah jadi 80%.

## Objective
Mengupdate fitur CRUD Tugas pada `DosenResourceController` agar menerima parameter `bobot`, dan me-refactor logika `rebalanceBobot` untuk mengakomodasi *"Locked Bobot"*.

## Instructions untuk AI Agent:

### 1. Update Database/Migration
- Buka `database/migrations/2026_06_22_143459_create_tugas_table.php`. Saat ini kolom `bobot` adalah float. Kita perlu memastikan sistem tahu mana tugas yang bobotnya di-kunci manual oleh dosen (locked) dan mana yang dihitung auto.
- **BUAT MIGRATION BARU** (e.g. `add_is_bobot_locked_to_tugas_table`): Tambahkan kolom `is_bobot_locked` (boolean, default: false) ke tabel `tugas`.
- Update `app/Models/Tugas.php` tambahkan `is_bobot_locked` ke `$fillable`.

### 2. Update Controller Validasi & Form Dosen
- Di `app/Http/Controllers/DosenResourceController.php` (fungsi `storeTugas` dan `updateTugas`), tambahkan validasi untuk input `bobot`: `'bobot' => 'nullable|numeric|min:0|max:100'`.
- Jika input `bobot` dikirim oleh dosen, maka set `$tugas->bobot = $validated['bobot']` dan `$tugas->is_bobot_locked = true`.
- Jika tidak diisi (null), set `$tugas->is_bobot_locked = false`.
- Di view `resources/views/pages/dosen/⚡dashboard.blade.php`, tambahkan input angka opsional untuk "Bobot Tugas (%)" di modal tambah tugas dan edit tugas.

### 3. Refactor Algoritma `rebalanceBobot()`
- Modifikasi `rebalanceBobot(int $mataKuliahId)` di `DosenResourceController`.
- **Logika Baru:**
  1. Ambil semua tugas di mata kuliah tersebut.
  2. Pisahkan mana yang *locked* (`is_bobot_locked == true`) dan *auto* (`is_bobot_locked == false`).
  3. Hitung total bobot yang *locked*: `$totalLocked = $tugasLocked->sum('bobot')`.
  4. Jika `$totalLocked > 100`, munculkan validasi/error saat dosen mau simpan tugas (Total persentase tidak boleh > 100%).
  5. Sisa persentase untuk yang *auto*: `$sisaBobot = max(0, 100 - $totalLocked)`.
  6. Bagi `$sisaBobot` sama rata ke tugas yang *auto*. Contoh: sisa 80%, tugas auto ada 2, masing-masing jadi 40%.
  7. Simpan (`saveQuietly()`) tugas-tugas *auto* yang nilainya berubah.

### 4. Perbaikan Testing & Warning!
> **WARNING:** Mengubah logika *rebalance* berpotensi membuat *Unit Test* lama menjadi `FAIL` (karena test yang lama mengekspektasikan pembagian bobot yang selalu merata tanpa peduli *lock*). 
- Buka `tests/Feature/DosenResourceTest.php`. Cari *test case* yang memiliki assertion `. 'bobot' => ...`. Anda **WAJIB** menyesuaikan *test* ini dengan logika yang baru, atau membuatkan kolom inputan seolah dosen memasukkan *bobot* secara *null* agar dites secara default (terbagi rata).
- Buat 1 *test case* baru: `test_dosen_can_set_custom_task_weight_and_auto_rebalance_the_rest` untuk membuktikan fitur ini berfungsi dengan benar.

## Acceptance Criteria
- Dosen bisa memasukkan persentase "Bobot Tugas" secara spesifik.
- Tugas lama yang tidak dikunci akan membagi sisa dari 100% secara merata.
- Menolak/Error jika total bobot yang dimasukkan manual melebihi 100%.
- Lolos *Test Suite* `php artisan test` tanpa ada *assertion* yang gagal.