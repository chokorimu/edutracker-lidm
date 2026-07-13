# TASK: Evaluasi & Improvement Codebase Skala 10K Mahasiswa

## Status & Analisis Saat Ini
Berdasarkan pembacaan codebase, sistem ini secara struktural sudah cukup solid, dibangun dengan Laravel terbaru (v13.x), sudah mengaplikasikan prinsip Single Responsibility di `BebanCalculator`, dan memiliki *Test Suite* yang sangat bagus dengan *coverage* fitur yang tinggi (36 Test PASS).

**Namun, jika aplikasi ini akan dipakai oleh 10.000 mahasiswa (dengan 1000+ MAU per hari), ada beberapa **bottleneck (titik hambat) performa** yang HARUS diperbaiki, terutama pada logika *Looping* dan Query Database.**

## Titik Masalah & Rencana Solusi:

### 1. Masalah N+1 & Memory Exhaustion pada `ProdiWeeklyTrend`
**Penyebab:** Di `BebanCalculator::prodiWeeklyTrend()`, ada query besar yang me-*load* `UserSiswa` dengan melakukan `JOIN` ke tabel `krs`, `mata_kuliah`, dan `tugas` untuk mengambil data 8 minggu terakhir. Jika ada 10K mahasiswa dengan masing-masing 7 mata kuliah dan puluhan tugas, `get()` akan menarik Ratusan Ribu hingga Jutaan Baris ke dalam Memory PHP sekaligus. Ini akan langsung menyebabkan *Out of Memory (OOM)*.
**Solusi:** Ubah eksekusinya menggunakan `Chunking` atau `DB::select` langsung berupa agregrasi COUNT mingguan di level SQL (Group By Week & Siswa), bukan menarik *raw rows* ke PHP.

### 2. Beban Komputasi `BebanCalculator::studentWeeklySummary`
**Penyebab:** Fungsi ini dipanggil berulang kali di dashboard (terutama saat dosen mengecek tab beban kelas). Di dalamnya, query memanggil `$student->id` menggunakan `whereIn` pada Tugas, yang dieksekusi **satu per satu per mahasiswa**.
**Solusi:** Lakukan *Eager Loading* menggunakan array atau `Collection` di Controller Dosen. Kita harus mem-*batch* query pencarian jumlah tugas & status submission berdasarkan *list of student IDs* (sekali query untuk 1 kelas penuh), alih-alih melempar 1 ID mahasiswa ke `studentWeeklySummary()` di dalam *foreach loop*.

### 3. Missing Rate Limiter pada Tombol Submit Tugas
**Penyebab:** Mahasiswa bisa saja mengeklik tombol "Submit" berulang kali saat koneksi lelet (karena tunnel ngrok). Route `siswa.tugas.submit` tidak dilengkapi dengan rate-limiter.
**Solusi:** Tambahkan middleware `throttle:10,1` atau yang sepadan pada route pengumpulan tugas agar mencegah eksploitasi *database connection limits*.

### 4. Cache Membengkak Tanpa Tag (Cache Stampede)
**Penyebab:** Dashboard siswa mengandalkan cache 10 menit. Jika 1000 mahasiswa login bersamaan jam 08:00 pagi saat sistem baru nyala, 1000 *Cache Miss* akan menembak 25 query ke database di detik yang sama (*Cache Stampede*).
**Solusi:** Pasang Atomic Locks `Cache::lock()` saat melakukan proses komputasi awal di `DashboardController`, sehingga user kedua yang datang di detik yang sama akan menunggu user pertama selesai mem-*build* cache.

## Instructions Untuk AI Agent Berikutnya:

### A. Refactor Level SQL (BebanCalculator)
- Buka `app/Services/BebanCalculator.php`.
- Refactor metode `prodiWeeklyTrend`:
  Jangan gunakan `->get()` untuk *cross-join* berskala besar. Gunakan `DB::query()->selectRaw(...)` untuk mengelompokkan jumlah tugas per ID siswa langsung di sisi Database (MySQL/SQLite). MySQL jauh lebih cepat melakukan `GROUP BY` ketimbang PHP *foreach*.
- Refactor metode `weeklyLoadDistribution`: Pastikan *query* aggregasi juga sudah benar dan berjalan efisien dengan menggunakan `groupBy` pada ID mahasiswa tanpa menarik relasi.

### B. Implementasi Rate Limiting
- Buka `routes/web.php` atau `DashboardController.php`.
- Tambahkan middleware throttle ke POST submit tugas: `->middleware('throttle:6,1')` (6 request per menit).

### C. Proteksi Cache Stampede
- Buka `DashboardController.php` pada fungsi `siswaDashboardData()`.
- Modifikasi pemanggilan `Cache::remember` dengan menggunakan mekanisme pencegahan cache stampede (contoh di Laravel 11/13 menggunakan parameter lock, atau cukup gunakan TTL pendek berjenjang dan `Cache::lock`). Jika terlalu kompleks, pastikan setidaknya *Eager Loading* di dalam fungsi pembuat datanya sudah optimal (`with('mataKuliah')`, `with('tugas')`).

## Acceptance Criteria
- Fungsi komputasi pada `BebanCalculator` tidak mengandung iterasi `get()` berukuran besar (Ribuan baris) ke dalam PHP array.
- Fungsi submit tugas telah dilindungi oleh *rate limiter*.
- Semua tes `php artisan test` **TETAP PASS**. Pastikan perubahan ke SQL Aggregation tidak merusak logika perhitungan beban untuk tes SQLite (Hati-hati dengan fungsi format tanggal MySQL `WEEK()` vs SQLite `strftime()`). Gunakan Carbon boundaries via PHP jika diperlukan.