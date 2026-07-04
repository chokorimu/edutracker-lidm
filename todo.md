# Rencana Pengembangan & Pengerjaan Fitur (Dosen & Admin) - Menuju Siap Produksi

Dokumen ini berisi spesifikasi kebutuhan, hasil verifikasi, status bug, dan panduan langkah demi langkah untuk memastikan stabilitas sistem EduTrack.

---

## FASE 1: Dashboard Dosen - Optimasi Daftar Tugas & Preview Beban

### 1. Dropdown Mahasiswa pada Setiap Tugas (`dosen/dashboard?tab=kelas&mk=<id>`)
- [x] Bungkus tabel mahasiswa menggunakan `<details>` + `<summary>` dropdown kolapsibel.
  > Implementasi: Tabel mahasiswa dibungkus dalam `<details class="group mt-3 rounded-xl border ...">` dengan summary "Lihat Status Pengumpulan & Nilai Mahasiswa (N)" dan chevron yang rotate saat dibuka.
- [x] Input nilai, komentar, dan download submission tetap berfungsi normal di dalam dropdown.

### 2. Akurasi Perhitungan Tugas dan Rentang Tanggal Dinamis
- [x] `resolveDosenAggregatePreviewWeek()` sudah menggunakan `now()->startOfWeek()` — sudah dinamis.
- [x] `pendingSubmissionStatsForCourseWeek()` sudah mengecualikan tugas yang disubmit — sudah benar.
  > Metode ini menghitung `pending = total_tasks - submitted` per mahasiswa, lalu mengembalikan `avg_tasks` berdasarkan rata-rata pending.

### 3. Caching & Cache Invalidation
- [x] Implementasi `Cache::remember('dosen_preview_{id}', 3600, ...)` pada `aggregatePreview` di tab kelas.
- [x] Cache invalidation saat dosen membuat tugas (`storeTugas`).
- [x] Cache invalidation saat dosen menghapus tugas (`destroyTugas`).
- [x] Cache invalidation saat siswa mengunggah submission (`submitTugas`).
- [x] Test cache invalidation ditulis dan lolos.

---

## FASE 2: Dashboard Admin - Otomasi Pengisian IPK Mahasiswa

### 1. Integrasi Nilai Mata Kuliah ke Riwayat IPK
- [x] Route baru: `POST /admin/ipk-history/generate-auto` → `admin.ipk-history.generate-auto`.
- [x] Method `generateIpkAuto(Request)` di `AdminResourceController`:
  - Validasi `siswa_id` + `semester`.
  - Ambil KRS + mataKuliah, cek kelengkapan nilai huruf.
  - Hitung IPS = Σ(Bobot × SKS) / Σ(SKS) dengan peta konversi A=4.0 s.d. E=0.0.
  - Simpan via `IpkHistory::updateOrCreate()`.
  - Auto-recommend SKS: IPK ≥ 3.5 → 24, ≥ 3.0 → 22, ≥ 2.75 → 20, < 2.75 → 18.
- [x] Form UI pada halaman `ipk-history` admin: dropdown siswa, input semester, tombol "Hitung & Simpan IPK".
- [x] Error handling: feedback jika nilai huruf belum lengkap atau KRS kosong.

### 2. Tests
- [x] `test_admin_can_auto_generate_ipk_from_krs` — kalkulasi IPK benar (A×3 + B+×4 = 3.6).
- [x] `test_auto_generate_ipk_fails_when_grades_missing` — error feedback saat nilai kosong.
- [x] `test_dosen_preview_cache_is_invalidated_on_tugas_create_and_delete` — cache invalidation.

---

## FASE 3: Investigasi & Perbaikan Hidden Bugs

### 1. Perbandingan Tanggal di SQLite (Resolved)
- **Deskripsi Bug**: Di database SQLite (yang digunakan di lingkungan testing PHPUnit), type comparison untuk field `deadline` bertipe date-only (misal `2026-07-06`) dengan datetime range (`BETWEEN '2026-07-06 00:00:00' AND ...`) akan gagal secara leksikografis karena `'2026-07-06' < '2026-07-06 00:00:00'`. Ini menyebabkan `computeStatusBeban` mendeteksi 0 tugas dan menghasilkan status `ringan` di test environment.
- **Rincian Fix**: Ubah format deadline tugas dummy pada `tests/Feature/DosenResourceTest.php` dari `toDateString()` menjadi `setTime(10, 0)->format('Y-m-d H:i:s')` agar selalu memiliki komponen waktu datetime.
- **Status**: [x] Selesai & diverifikasi (Semua 36 test sukses).

### 2. Duplikasi Kernel Console (Resolved)
- **Deskripsi Bug**: Diduga terjadi duplikasi schedule karena file `app/Console/Kernel.php` dan `routes/console.php` sama-masing memuat command `beban:check`.
- **Rincian Fix**: Setelah dilakukan verifikasi, file `app/Console/Kernel.php` tidak ditemukan (sudah dihapus), dan command `beban:check` hanya dimuat di `routes/console.php` sehingga schedule berjalan tepat satu kali.
- **Status**: [x] Diverifikasi menggunakan `php artisan schedule:list` (Hanya berjalan sekali).

### 3. Clickable Controls Palsu di Siswa Profile Tab (Resolved)
- **Deskripsi Bug**: Informasi di `warning.md` menunjukkan adanya baris menu "Keamanan Kata Sandi" dan "Integrasi API SIAKAD" yang clickable tapi tidak memiliki fungsi.
- **Rincian Fix**: Setelah dilakukan pengecekan mendalam ke `⚡dashboard.blade.php`, baris menu tersebut sudah tidak ada/tidak diimplementasikan, sehingga tidak memerlukan tindakan lebih lanjut.
- **Status**: [x] Bersih & Valid.

---

## Rencana Pengujian & Verifikasi Akhir

Untuk memastikan tidak ada regresi pada kode, jalankan perintah berikut:

```bash
php artisan test
php artisan route:list
./vendor/bin/pint --dirty --test
```

Semua verifikasi di atas telah dijalankan dan dilaporkan **PASSED** (100%).
