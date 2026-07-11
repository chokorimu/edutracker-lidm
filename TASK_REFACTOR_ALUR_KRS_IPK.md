# TASK: Refactor Alur KRS, SKS Lulus, dan Tutup Semester (Kalkulasi IPK)

## Latar Belakang & Logika Bisnis
Saat ini ada sedikit kerancuan antara SKS Aktif dan SKS Lulus. Logika akademik yang benar adalah:
1. **KRS Aktif / SKS Aktif:** Mahasiswa sedang mengontrak mata kuliah di semester berjalannya (misal: Semester 3, 24 SKS). Nilai belum keluar, tugas masih berjalan, dan ini **belum masuk SKS Lulus**.
2. **SKS Lulus:** Kumpulan SKS dari semester yang **sudah ditutup/selesai** (sudah keluar IPK-nya). Misal: Sem 1 (20 SKS) + Sem 2 (20 SKS) = 40 SKS Lulus.
3. **Sisa SKS:** Mutlak 144 - SKS Lulus (contoh: 144 - 40 = 104 Sisa SKS). SKS yang sedang berjalan (aktif) tidak mengurangi sisa SKS.
4. **Tutup Semester (Kalkulasi IPK Otomatis):** Fitur di halaman Admin ini berfungsi sebagai pelatuk "Tutup Semester". Saat admin mengkalkulasi IPK untuk seorang mahasiswa, KRS mahasiswa tersebut akan diubah statusnya menjadi `selesai`. Hal ini otomatis akan "mengosongkan" dashboard aktif mahasiswa (SKS aktif jadi 0, list matkul dan tugas hilang), sehingga siap untuk diisikan paket KRS semester berikutnya.

## Objective
Merefaktor fitur Auto-Generate IPK di Admin agar bertindak sebagai penutup semester (mengarsipkan KRS), serta merapikan perhitungan "SKS Lulus", "SKS Aktif", dan "Sisa SKS" di Dashboard Siswa agar sesuai dengan logika di atas.

## Instructions untuk AI Agent:

### 1. Refactor `AdminResourceController@generateIpkAuto` (Tutup Semester)
Buka `app/Http/Controllers/AdminResourceController.php`. Pada method `generateIpkAuto`:
- Saat ini sistem mengambil data KRS: `$krsList = Krs::where('siswa_id', $siswaId)->where('semester', $semester)->get();`
- **TUGAS:** Setelah IPK dihitung dan berhasil disimpan ke `IpkHistory::updateOrCreate`, lakukan *update* pada data KRS tersebut. Ubah kolom `status` di tabel KRS dari `aktif` menjadi `selesai` (pastikan kolom status tersedia di `$fillable` / skema).
- **Aturan Naik Semester:** JANGAN menaikkan (auto-increment) semester mahasiswa di tabel `user_siswa` secara otomatis di sini. Biarkan dashboard mahasiswa kosong/bersih dulu. Mahasiswa baru akan naik semester ketika Admin nanti meng-input paket KRS baru dan memperbarui data profil mahasiswa secara manual.

### 2. Validasi Perhitungan Dashboard Siswa (Tampilkan Hanya yang Aktif)
Buka `app/Http/Controllers/DashboardController.php` (Fungsi `buildProfileData`, `buildMatakuliahData`, `buildTugasData`, dll).
- **SKS Aktif & Mata Kuliah Aktif:** Pastikan query pencarian KRS / Mata Kuliah hanya mengambil yang `status = 'aktif'`. Jadi begitu admin menekan tombol "Kalkulasi IPK", dashboard siswa akan otomatis kosong (bersih) dan SKS Aktif menjadi 0.
- **SKS Lulus:** Pastikan perhitungan SKS Lulus HANYA mengambil `total_sks` dari tabel `ipk_history` secara total.
  ```php
  $sksLulus = (int) $user->ipkHistory()->sum('total_sks');
  ```
- **Sisa SKS:** Hitung murni `144 - $sksLulus`. SKS yang sedang diambil (`SKS Aktif`) **TIDAK BOLEH** mengurangi Sisa SKS.

## Acceptance Criteria
- [ ] Tombol `Kalkulasi IPK` di Admin sukses merubah status KRS di semester tersebut menjadi `selesai`.
- [ ] Setelah dikalkulasi, dashboard mahasiswa menjadi kosong (0 SKS Aktif, tidak ada tugas, tidak ada jadwal matkul) karena semua KRS-nya sudah tidak "aktif".
- [ ] Di Dashboard Siswa: `SKS Lulus` adalah akumulasi SKS dari semester yang sudah selesai (dari tabel IPK History).
- [ ] Di Dashboard Siswa: `Sisa SKS` = 144 - SKS Lulus.
- [ ] Lulus pengujian `php artisan test`. Pastikan untuk memperbaiki file *test* yang mungkin gagal karena logika status KRS ini.