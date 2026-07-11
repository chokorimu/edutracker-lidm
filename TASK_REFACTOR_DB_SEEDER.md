# Rencana Pengembangan & Perbaikan Sistem EduTrack - Final

Dokumen ini berisi panduan untuk menyempurnakan fitur "Sisa SKS", perbaikan "Wording Akademik", serta perbaikan fundamental pada arsitektur "Database & Seeder". Harap eksekusi seluruh instruksi secara berurutan.

---

## 1. Menampilkan "Sisa SKS" pada Monitoring SKS (Dashboard Siswa)

### Latar Belakang
Fitur estimasi kelulusan (`$profile['prediksi_lulus']['sisa_sks']`) saat ini hanya muncul di tab "Profil". Mengingat tujuannya untuk memotivasi mahasiswa Generasi Z, data ini jauh lebih relevan jika dimunculkan di halaman utama **Monitoring SKS** (bagian _ringkasan_ atas).

### Instructions
1. Buka `resources/views/pages/siswa/⚡dashboard.blade.php`.
2. **HAPUS DARI PROFIL:** Cari bagian tab `profil` (sekitar baris `676-685`). Hapus seluruh blok kode yang menampilkan *Prediksi Lulus / Sisa Semester* agar tab Profil lebih bersih.
3. **TAMBAH KE MONITORING SKS:** Pada bagian tab `monitoring` (sekitar baris `289-299`), terdapat blok `@foreach` yang menampilkan card (SKS Aktif, SKS Lulus, IPK Kumulatif).
4. **Sisipkan 1 array baru untuk "Sisa SKS":**
   - Karena `$profile['prediksi_lulus']` bernilai `null` jika mahasiswa masih semester 1, gunakan fallback/ternary:
     - **Value:** `(isset($profile['prediksi_lulus']['sisa_sks']) ? $profile['prediksi_lulus']['sisa_sks'] : max(0, 144 - ($profile['sks_lulus'] ?? 0))) . ' SKS'`
     - **Caption:** `"Sisa " . (isset($profile['prediksi_lulus']['sisa_semester']) ? $profile['prediksi_lulus']['sisa_semester'] : max(0, 8 - ($profile['semester'] ?? 1))) . " semester. Semangat!"`
     - **Warna:** Gunakan class seperti `text-pastel-ungu` atau yang relevan.
5. **Sesuaikan Layout:** Pastikan *grid* pembungkus loop diubah (misal menjadi `sm:grid-cols-2 xl:grid-cols-4`) agar ke-empat kartu tampil sejajar dan tidak berantakan.

---

## 2. Re-wording EduTrack Notifications & Risk Descriptions

### Latar Belakang
Bahasa aplikasi ini terkadang terlalu kaku dan menakutkan saat beban mahasiswa "overload". Perlu pendekatan _copywriting_ yang lebih suportif.

### Instructions
1. **Perbaiki Deskripsi Prediksi Risiko (Siswa):**
   Di `resources/views/pages/siswa/⚡dashboard.blade.php` (tab *Analytics* / Prediksi Risiko Akademik), tambahkan sub-teks di bawah persentase indikator:
   - Jika **AMAN (0-40%)**: *"Anda tidak perlu cemas dengan tugas dan ujian, beban masih dalam batas wajar untuk jadwal Anda saat ini."*
   - Jika **Perlu Perhatian (40-70%)**: *"Mulai atur waktu dari sekarang. Cicil tugas sedikit demi sedikit agar tidak menumpuk."*
   - Jika **Risiko Tinggi (70-100%)**: *"Prioritaskan kesehatanmu, kurangi kegiatan di luar, dan selesaikan tugas paling mendesak."*
2. **Ubah Pesan Overload (Sistem Job):**
   Di `app/Jobs/SendBebanNaikNotifications.php` (baris ~95), ubah kalimat `pesan` saat notifikasi *Overload/Berat* menjadi:
   - *"Beban tugas bertambah pada minggu ini. Tetap semangat, atur napas, dan mulai kerjakan satu per satu secara perlahan."*
3. **PENTING UNTUK TESTING:** Mengubah pesan notifikasi akan membuat tes gagal (karena mencari string lama). Buka `tests/Feature/DosenResourceTest.php` dan sesuaikan *assertions* string notifikasi agar kembali `PASS`.

---

## 3. Database Reset & Pembersihan Seeder (Manual Input)

### Latar Belakang
File Seeder saat ini (`UserSiswaSeeder.php`) men-generate terlalu banyak *dummy data* (KRS, Tugas, Matkul) yang redundan dan menyebabkan *crash* (contoh: list mata kuliah kosong akibat tidak sinkronnya nilai `semester` antara tabel Siswa, KRS, dan Matkul).

Mengingat _Testing Suite_ (PHPUnit) menggunakan _Factory/Create()_ independen dan **TIDAK TERGANTUNG PADA SEEDER**, kita akan membuang semua data rongsokan di Seeder dan mengandalkan input manual 100%.

### Instructions
1. **Buat Migration Jadwal (Mata Kuliah):**
   - Buat migration (e.g., `php artisan make:migration add_jadwal_to_mata_kuliah_table`).
   - Tambahkan kolom `hari` (string, nullable), `jam_mulai` (time, nullable), `jam_selesai` (time, nullable) ke tabel `mata_kuliah`.
   - Update model `App\Models\MataKuliah` untuk `$fillable`.
2. **Perbarui Form Admin (CRUD Mata Kuliah):**
   - Di `AdminResourceController.php` (bagian *resource definitions* mata kuliah), tambahkan kolom `hari`, `jam_mulai`, dan `jam_selesai` agar Admin dapat mengatur jadwal secara manual.
3. **Bersihkan Seeder (1 Entitas per File):**
   - Buka file seeder di `database/seeders/` (`UserAdminSeeder`, `UserSiswaSeeder`, `UserDosenSeeder`, `UserProdiSeeder`).
   - Hapus **SEMUA** kode pembuatan Mata Kuliah, KRS, Tugas, dan Nilai.
   - Pastikan setiap file seeder **HANYA** membuat **satu akun** untuk keperluan login awal pengembangan. (contoh: hanya 1 admin, 1 dosen, 1 siswa, 1 prodi).
4. **Tampilkan Jadwal (Dashboard Siswa):**
   - Di `DashboardController.php` (`buildMatakuliahData`), pastikan properti jadwal masuk ke respons query.
   - Di `resources/views/pages/siswa/⚡dashboard.blade.php` (tab *Monitoring SKS*), render `hari`, `jam_mulai` dan `jam_selesai` tepat di bawah teks Kode Mata Kuliah. Contoh: `PW01 (Senin, 08:00 - 10:30)`.

---

## Final Verification
Jalankan command berikut setelah mengeksekusi markdown ini:
```bash
php artisan cache:clear && php artisan view:clear
php artisan migrate:fresh --seed
php artisan test
```
Pastikan database kembali bersih, login awal bisa digunakan, form jadwal admin berfungsi, dan seluruh test tetap PASS (100%).